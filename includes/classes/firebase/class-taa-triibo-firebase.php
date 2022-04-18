<?php

/**
 * Firebase controller
 *
 * Handles requests to firebase.
 *
 * @package TAA_Triibo_Firebase
 * @since   1.0.0
 * @version 1.0.0
 */

defined( "ABSPATH" ) || exit;

/**
 * Firebase controller class.
 */
class TAA_Triibo_Firebase
{
	public function init()
	{
        /**
         * Verifica o ambiente que está
         * $isDev = strpos(home_url(), "dev-");
         */
        if ( strpos( home_url(), "dev-" ) !== FALSE )
        {
            // $isDev = "TRUE";
            define( "TAA_API_URL",       "https://triibo-api-gateway-hml.triibo.com.br/"                ); // HOMOLOG
            define( "TAA_API_TOKEN",     "7472216240776562417070486d4c6170693139"                       ); // HOMOLOG
            define( "TAA_CHANNEL_ID",    "54522121626f2d416854682d4150312d4368614e4e656c2d776562417070" ); // HOMOLOG
            define( "TAA_CHANNEL_GROUP", "triibo-webapp"                                                ); // HOMOLOG
            define( "TAA_CHANNEL_NAME",  "triibo-webapp"                                                ); // HOMOLOG
            define( "TAA_CHANNEL_TOKEN", "305221624057656241707021403131"                               ); // HOMOLOG
        }
        else
        {
            // $isDev = "FALSE";
            define( "TAA_API_URL",       "https://triibo-api-gateway.triibo.com.br/"                    ); // PROD
            define( "TAA_API_TOKEN",     "57336266417050612750524C4170495029"                           ); // PROD
            define( "TAA_CHANNEL_ID",    "21726962305052442443686E336C6C573342417050"                   ); // PROD
            define( "TAA_CHANNEL_GROUP", "triibo-webapp"                                                ); // PROD
            define( "TAA_CHANNEL_NAME",  "triibo-webapp"                                                ); // PROD
            define( "TAA_CHANNEL_TOKEN", "38522162405733624170505052236368403138"                       ); // PROD
        }
	}

    /**
     * Atualiza usuario na Triibo
     */
    public static function update_user_triibo( $cellphone, $email, $action )
    {
        $cellphone        = "+" . preg_replace( "/\D/", "", $cellphone );

        $channelTokenId_1 = self::get_channel_token( NULL );
        $registerUser     = [
            "triiboHeader"    => [
                "apiToken"       => TAA_API_TOKEN,
                "channelGroup"   => TAA_CHANNEL_GROUP,
                "channelName"    => TAA_CHANNEL_NAME,
                "channelId"      => TAA_CHANNEL_ID,
                "sessionId"      => "001",
                "transactionId"  => "002",
                "channelTokenId" => $channelTokenId_1["success"]["newToken"]["channelTokenId"]
            ],
            "linkedAccount" => NULL,
            "cellphone"     => $cellphone,
        ];

        // cria usuário se não existir e retorna as informações
        $resp1       = self::exec_call("login", $registerUser);

        if ( ! $resp1["success"] ) return;

        $user        = $resp1["user"];
        $uId         = $user["uId"];
        $contactList = $user["contactList"];

        switch ($action) {
            case "ADD":
                array_push( $contactList, [
                    "type"     => "emailMarketplace",
                    "value"    => $email,
                    "verified" => true
                ]);
            break;

            case "UPDATE":
                $id = array_search("email", array_column($contactList, "type"));
                $contactList[$id]["verified"] = true;

                array_push( $contactList, [
                    "type"     => "emailMarketplace",
                    "value"    => $email,
                    "verified" => true
                ]);
            break;

            case "CREATE":
                array_push( $contactList, [
                    "type"     => "email",
                    "value"    => $email,
                    "verified" => true
                ]);
                array_push( $contactList, [
                    "type"     => "emailMarketplace",
                    "value"    => $email,
                    "verified" => true
                ]);
            break;
        }

        $channelTokenId_2 = self::get_channel_token( $uId );
        $setUserInfo      = [
            "triiboHeader"    => [
                "apiToken"       => TAL_API_TOKEN,
                "channelGroup"   => TAL_CHANNEL_GROUP,
                "channelName"    => TAL_CHANNEL_NAME,
                "channelId"      => TAL_CHANNEL_ID,
                "sessionId"      => "001",
                "transactionId"  => "002",
                "channelTokenId" => $channelTokenId_2["success"]["newToken"]["channelTokenId"],
                "uId"            => $uId
            ],
            "queryPartnerAPI" => [ "setUserInfo" ],
            "userInfo"        => [
                "syncType"    => "merge",
                "uId"         => $uId,
                "contactList" => [ ... $contactList ],
            ],
        ];

        // atualiza informações do usuario
        $resp2 = self::exec_call("setUserInfo_v1", $setUserInfo);

        if ( ! $resp2["success"] )
        {
            $data  = PHP_EOL . "[" . date("Y-m-d H:i:s") . "] PRODUCTION.ERROR ";
            $data .= "Sync failed: " . json_encode($resp2, TRUE);

            file_put_contents( TAA_dir . "taa.log", $data, FILE_APPEND );
        }

        return $uId;
    }

    /**
     * Defini channelToken para as chamadas das APIs
     */
    private static function get_channel_token( $uId )
    {
        $passPhrase         = floor( rand( 1000000000, 9999999999 ) ) . $uId . floor( rand( 1000000000, 9999999999 ) );
        $challenge          = self::hash_triibo( TAA_API_TOKEN, $passPhrase, TAA_CHANNEL_TOKEN );
        $token_triiboHeader = [
            "triiboHeader" => [
                "apiToken"      => TAA_API_TOKEN,
                "channelGroup"  => TAA_CHANNEL_GROUP,
                "channelName"   => TAA_CHANNEL_NAME,
                "channelId"     => TAA_CHANNEL_ID,
                "sessionId"     => "001",
                "transactionId" => "002",
                "uId"           => $uId,
                "passPhrase"    => $passPhrase,
                "challenge"     => $challenge
            ],
        ];

        return self::exec_call("getChannelToken_v1", $token_triiboHeader);
    }

    /**
     * Cria hash para o triiboHeader
     */
    private static function hash_triibo( $apiToken, $passPhrase, $channelToken )
    {
        $challenge1 = md5( $passPhrase . $apiToken     );
        $challenge2 = md5( $challenge1 . $channelToken );
        return $challenge2;
    }

    /**
     * Executa chamadas ao Firebase
     */
    private static function exec_call($api, $params)
    {
        $ch = curl_init();

		curl_setopt_array( $ch, [
			CURLOPT_URL            => TAA_API_URL . $api,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 60,
			CURLOPT_SSL_VERIFYPEER => FALSE ,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_HTTPHEADER     => [ "Content-type: application/json" ],
            CURLOPT_POSTFIELDS     => json_encode( $params )
        ]);

        $result = curl_exec( $ch );
        curl_close( $ch );

        return json_decode( $result, TRUE );
    }
}

new TAA_Triibo_Firebase();
