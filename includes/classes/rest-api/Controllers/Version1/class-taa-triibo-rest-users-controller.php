<?php

/**
 * REST API Users controller
 *
 * Handles requests to the /users endpoint.
 *
 * @package Taa_Triibo_Rest_Api/Users
 * @since   1.0.0
 * @version 1.1.0
 */

defined( "ABSPATH" ) || exit;

/**
 * REST API Users controller class.
 *
 * @package Triibo\RestApi
 */
class TAA_TRIIBO_REST_Users_Controller extends WP_REST_Controller
{
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = "triibo-rest/v1";

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = "users";

	/**
	 * Stores the request.
	 *
	 * @var array
	 */
	protected $request = array();

	protected $log;

	protected $id = "triibo_auth_app";

	public function __construct()
	{
		$this->log = ( function_exists( "wc_get_logger" ) ) ? wc_get_logger() : new WC_Logger();
	}

	/**
	 * Register the routes for users.
	 */
	public function register_routes()
	{
		register_rest_route(
			$this->namespace,
			"/$this->rest_base/login",
			array(
				array(
					"methods"             => WP_REST_Server::READABLE, // WP_REST_Server::EDITABLE, // POST, PUT, PATCH
					"callback"            => array( $this, "get_item" ),
					"permission_callback" => array( $this, "get_item_permissions_check" ),
					"args"                => $this->get_collection_params(),
				),
			)
		);
	}

	public function get_item_permissions_check( $request )
	{
		return true;
	}

	public function get_item( $request )
	{
		$url = $request->get_param( "url" ) ?: null;
		$tkn = $request->get_param( "mkt" ) ?: null;

		if ( !self::checkHost() )
		{
			$bearer = $request->get_header( "authorization" );

			/**
			 * ios     - manda apenas bearer
			 * android - manda apenas token
			 *
			 * ausencia dos dois é problema
			 */
			if ( !$bearer && !$tkn ) self::redirect( $url );
			if ( !$this->validate_token( $bearer, $tkn ) ) self::redirect( $url );
		}

		$cellphone = $request->get_param( "cellphone" ) ?: null;
        $cellphone = preg_replace( "/\D/", "", $cellphone );
		set_transient( "_taa_triibo_cellphone", $cellphone );

		$data      = $request->get_param( "data" ) ?: null;
		$user      = null;
		$params    = null;

		$this->log->add( $this->id, "==================================" );
		$this->log->add( $this->id, "==================================" );
		$this->log->add( $this->id, "==================================" );
		$this->log->add( $this->id, json_encode( [ $cellphone, $data ] ) );

		// celular e email ausentes na request
		// redireciona para $url ou para "/", caso $url = null
		if ( !$cellphone && !$data ) self::redirect( $url );

		// passo 2
		if ( $data )
		{
			$arr      = explode( "|", $data );
			$type     = $arr[ 0 ]; // Type:     email || emailMarketplace
			$email    = $arr[ 1 ]; // Value:    me@example.com
			$verified = $arr[ 2 ]; // Verified: true || false || 1 || 0
			$verified = filter_var( $verified, FILTER_VALIDATE_BOOLEAN );

			// passo 3
			if ( $type == "emailMarketplace" && $verified )
			{
				// passo 4
				$user_id = email_exists( $email );

				$this->log->add( $this->id, "C1 - MKT user found and verified: +$cellphone" );

				// passo 5 ( passo_comum )
				self::make_login( $user_id, $url );
			}
			// passo 6
			else
			{
				// passo 7
				$user_id = email_exists( $email );

				// passo 8
				if ( $user_id && $verified )
				{
					// passo 9
					update_user_meta( $user_id, "triiboId_phone", "+$cellphone" );

					// passo 10 ( passo_comum )
					TAA_TRIIBO_Firebase::update_user_triibo( $cellphone, $email, "ADD" );

					$this->log->add( $this->id, "C2 - MKT user found and verified: +$cellphone" );

					self::make_login( $user_id, $url );
				}
				// passo 8
				else if ( $user_id && !$verified )
				{
					// passo 11
					$params = self::send_email_code( $email ) ? "taac=code&email={$email}" : null;

					$this->log->add( $this->id, "C3 - MKT user found but not verified: +$cellphone" );

					// passo 12
					self::redirect( $url, $params );
				}
				// passo 16
				else if ( !$user_id && $verified )
				{
					// passo 17
					$params = "taac=newacc&email={$email}";

					$this->log->add( $this->id, "C4 - MKT user not found but verified: +{$cellphone}" );

					self::redirect( $url, $params );
				}
				// passo 16
				else // ( !$user_id && !$verified )
				{
					// passo 21
					$params = self::send_email_code( $email ) ? "taac=code&email={$email}" : null;

					$this->log->add( $this->id, "C5 - MKT user not found and not verified: +$cellphone" );

					self::redirect( $url, $params );
				}
			}
		}
		else if ( $cellphone )
		{
			// passo 25
			$user = get_users( [
				"meta_key"   => "triiboId_phone",
				"meta_value" => "+$cellphone"
			] );

			if ( !empty( $user ) )
			{
				// passo 26
				$user   = reset( $user );
				$email  = $user->user_email;

				// passo 27
				$params = "taac=acc&email=$email";

				$this->log->add( $this->id, "C6 - MKT account found but not verified: +$cellphone" );

				self::redirect( $url, $params );
			}
			else
			{
				// passo 29
				$params = "taac=newacc";

				$this->log->add( $this->id, "C7 - MKT new account: +$cellphone" );

				self::redirect( $url, $params );
			}
		}
		else
		{
			wp_clear_auth_cookie();

			$this->log->add( $this->id, "C8 - Missing data" );

			self::redirect( $url );
		}
	}

	private static function redirect( string $url = null, string $params = null )
	{
		if ( $url )
		{
			if ( ( strpos( $url, "?" ) ) !== false && $params )
				$url .= "&$params";
			elseif ( $params )
				$url .= "?$params";
		}
		else
		{
			$url  = home_url();
		}

		wp_safe_redirect( esc_url_raw( $url ) );
		exit();
	}

	private static function send_email_code( string $email )
    {
        $validCode = str_pad( mt_rand( 0, 999999 ), 6, "0", STR_PAD_LEFT );

        $to      = $email;
        $subject = "Seu código de ativação";
        $body    = "<html lang='pt-br'><body><p>Seu código de ativação do Marketplace Triibo é:</p><p style='font-size:18px;'><b>$validCode</b></p><p style='font-size:12px;margin:0'>Não compartilhe esse código com ninguém.</p></body></html>";
        $headers = array( "Content-Type: text/html; charset=UTF-8","From: Marketplace Triibo <equipemarketplace@triibo.com.br>" );

        if ( wp_mail( $to, $subject, $body, $headers ) )
		{
			set_transient( "_taa_triibo_code", $validCode, 300 );

			return true;
		}
		else
		{
			return false;
		}
    }

	private static function make_login( int $user_id, string $url )
	{
		clean_user_cache( $user_id );
		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );
		self::redirect( $url );
	}

	private static function get_env( $mkt = null )
	{
		if ( $mkt )
			return ( self::checkHost() ? "dev-" : "" );
		else
			return ( self::checkHost() ? "-hml" : "" );
	}

	private static function get_url()
	{
		return "https://" . self::get_env( true ) . "marketplace.triibo.com.br";
	}

	private static function get_api_url()
	{
		return "https://api" . self::get_env() . ".triibo.com.br";
	}

	private static function is_prod()
	{
		return ( empty( self::get_env() ) ) ? true : false;
	}

	private function validate_token( string $bearer = null, string $tkn = null )
	{
		$bearer  = ( !$bearer && $tkn ) ? base64_decode( $tkn ) : $bearer;

		$ch      = curl_init();

		$api_key = ( self::is_prod() ) ? "xp8bqzil-b15e-da2v-3jcm-25pniix0y859" : "652e0505-f010-48a4-a9a5-ce12783777ea";

		$headers = [
			"Origin: " . self::get_url(),
			"Authorization: $bearer",
			"Content-type: application/json",
			"x-api-key: $api_key"
		];

		curl_setopt_array( $ch, [
			CURLOPT_URL            => self::get_api_url() . "/auth/marketplace",
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 60,
			CURLOPT_SSL_VERIFYPEER => FALSE ,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_HTTPHEADER     => $headers
        ] );

        $resp = curl_exec( $ch );
        curl_close( $ch );

        $resp = json_decode( $resp, TRUE );

		return ( !$resp[ "success" ] ) ? false : true;
	}

	private static function checkHost()
	{
		return ( strpos( home_url(), "dev-"      ) !== FALSE ||
				 strpos( home_url(), "localhost" ) !== FALSE ||
				 strpos( home_url(), "127.0.0.1" ) !== FALSE ) ? true : false;
	}
}
