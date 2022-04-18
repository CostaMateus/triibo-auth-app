<?php

/**
 * REST API Users controller
 *
 * Handles requests to the /users endpoint.
 *
 * @package Taa_Triibo_Rest_Api/Users
 * @since   1.0.0
 * @version 1.0.0
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

	/**
	 * Register the routes for users.
	 */
	public function register_routes()
	{
		// users/5511987654321
		// users/5511987654321?email=me@example.com
		register_rest_route(
			$this->namespace,
			"/$this->rest_base/login",
			// "/" . $this->rest_base . "/(?P<cellphone>[\d]+)",
			array(
				// "args" => array(
				// 	"cellphone" => array(
				// 		"description" => "user cell phone",
				// 		"type"        => "integer",
				// 	),
				// ),
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
		// Chamar api de validação de BearerToken // self::redirect( $url );
		// $api_token = $request->get_header( "api-token" );

		$cellphone = $request->get_param( "cellphone" ) ?: null;
        $cellphone = preg_replace( "/\D/", "", $cellphone );
		set_transient( "_taa_triibo_cellphone", $cellphone );

		$data      = $request->get_param( "data" ) ?: null;
		$url       = $request->get_param( "url" )  ?: null;
		$user      = null;
		$params    = null;

		// celular e email ausentes na request
		// redireciona para $url ou para "/", caso $url null
		if ( ! $cellphone && ! $data) self::redirect( $url );

		// passo 2
		if ( $data )
		{
			$arr      = explode( "|", $data );
			$type     = $arr[0]; // Type:     email || emailMarketplace
			$email    = $arr[1]; // Value:    me@example.com
			$verified = $arr[2]; // Verified: true || false || 1 || 0
			$verified = filter_var( $verified, FILTER_VALIDATE_BOOLEAN );

			// passo 3
			if ( $type == "emailMarketplace" && $verified)
			{
				// passo 4
				$user_id = email_exists( $email );

				// passo 5 (passo_comum)
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

					// passo 10 (passo_comum)
					TAA_TRIIBO_Firebase::update_user_triibo( $cellphone, $email, "ADD" );
					self::make_login( $user_id, $url );
				}
				// passo 8
				else if ( $user_id && ! $verified )
				{
					// passo 11
					$params = self::send_email_code( $email ) ? "taac=code&email=$email" : null;
					// passo 12
					self::redirect( $url, $params );
				}
				// passo 16
				else if ( ! $user_id && $verified )
				{
					// passo 17
					$params = "taac=newacc&email=$email";
					self::redirect( $url, $params );
				}
				// passo 16
				else // ( ! $user_id && ! $verified )
				{
					// passo 21
					$params = self::send_email_code( $email ) ? "taac=code&email=$email" : null;
					self::redirect( $url, $params );
				}
			}
		}
		else if ( $cellphone )
		{
			// passo 25
			$user = get_users([
				"meta_key"   => "triiboId_phone",
				"meta_value" => "+$cellphone"
			]);

			if ( ! empty( $user ) )
			{
				// passo 26
				$user   = reset( $user );
				$email  = $user->user_email;

				// passo 27
				$params = "taac=acc&email=$email";
				self::redirect( $url, $params );
			}
			else
			{
				// passo 29
				$params = "taac=newacc";
				self::redirect( $url, $params );
			}
		}
		else
		{
			wp_clear_auth_cookie();
			self::redirect( $url );
		}
	}

	private static function redirect( string $url = null, string $params = null )
	{
		if ( $url )
		{
			$url .= $params ? "?$params" : "";
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
        $validCode = str_pad(mt_rand(0, 999999), 6, "0", STR_PAD_LEFT);

        $to        = $email;
        $subject   = "Seu código de ativação";
        $body      = "<html lang='pt-br'><body><p>Seu código de ativação do Marketplace Triibo é:</p><p style='font-size:18px;margin:0'><b>$validCode</b></p></body></html>";
        $headers   = array("Content-Type: text/html; charset=UTF-8","From: Marketplace Triibo <equipemarketplace@triibo.com.br>");

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
		clean_user_cache($user_id);
		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );
		self::redirect( $url );
	}
}
