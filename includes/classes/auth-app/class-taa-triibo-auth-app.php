<?php

/**
 * Initialize this version of the Auth.
 *
 * @package Taa_Triibo_Auth_App
 * @since   1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class responsible for validating the redirection of the user from the app to the mkt.
 */
class TAA_Triibo_Auth_App
{

    public function __construct()
    {
        add_action( "wp_ajax_taa_validate_code",           array( $this, "taa_validate_code"      ) );
        add_action( "wp_ajax_nopriv_taa_validate_code",    array( $this, "taa_validate_code"      ) );

        add_action( "wp_ajax_taa_new_account",             array( $this, "taa_new_account"        ) );
        add_action( "wp_ajax_nopriv_taa_new_account",      array( $this, "taa_new_account"        ) );

        add_action( "wp_ajax_taa_validate_account",        array( $this, "taa_validate_account"   ) );
        add_action( "wp_ajax_nopriv_taa_validate_account", array( $this, "taa_validate_account"   ) );
    }

    public function taa_validate_code()
    {
        $result    = [
            "code"    => 5000,
            "error"   => true,
            "message" => "Error"
        ];

        $code      = $_POST[ "code" ];
        $email     = $_POST[ "email" ];

        $sent_code = get_transient( "_taa_triibo_code" );
        delete_transient( "_taa_triibo_code" );

        if ( $sent_code )
        {
            // passo 12
            if ( $sent_code == $code )
            {
				$user_id = email_exists( $email );

                if ( $user_id )
                {
                    $cellphone = get_transient( "_taa_triibo_cellphone" );
                    delete_transient( "_taa_triibo_cellphone" );

                    // passo 13
					update_user_meta( $user_id, "triiboId_phone", "+$cellphone" );

					// passo 14 ( passo_comum )
                    TAA_Triibo_Firebase::update_user_triibo( $cellphone, $email, "UPDATE" );
                    self::make_login( $user_id );

                    $result = [
                        "code"    => 2000,
                        "error"   => null,
                        "message" => "Usuário conectado"
                    ];
                }
                else
                {
                    $result = [
                        "code"    => 2001,
                        "error"   => null,
                        "message" => "Criar conta"
                    ];
                }
            }
            else
            {
                $result = [
                    "code"    => 4001,
                    "error"   => true,
                    "message" => "Código inválido"
                ];
            }
        }
        else
        {
            $result = [
                "code"    => 4002,
                "error"   => true,
                "message" => "Código expirado"
            ];
        }

        echo json_encode( $result );
        die();
    }

    public function taa_new_account()
    {
        $result    = [
            "code"    => 5000,
            "error"   => true,
            "message" => "Error"
        ];

        $email     = $_POST[ "email" ];
        $pass      = $_POST[ "pass"  ];

        $cellphone = get_transient( "_taa_triibo_cellphone" );
        delete_transient( "_taa_triibo_cellphone" );

        $user      = get_user_by( "email", $email );

        // valida se conta existe e se a senha está correta
        if ( $user )
        {
            if ( wp_check_password( $pass, $user->data->user_pass, $user->ID ) )
            {
                add_user_meta( $user->ID, "triiboId_phone", "+$cellphone" );

                TAA_Triibo_Firebase::update_user_triibo( $cellphone, $email, "CREATE" );
                self::make_login( $user->ID );

                $result = [
                    "code"    => 2000,
                    "error"   => null,
                    "message" => "Usuário conectado"
                ];
            }
            else
            {
                $result = [
                    "code"    => 4001,
                    "error"   => true,
                    "message" => "E-mail e/ou senha incorreta"
                ];
            }
        }
        else
        {
            $name    = explode( "@", $email )[ 0 ];

            // passo 18
            $user_id = wp_insert_user( [
                "user_login"    => $email,
                "user_pass"     => wp_hash_password( "Triibo" . date( "Y" ) . "@!#" ),
                "user_nicename" => $name,
                "user_email"    => $email,
                "display_name"  => $name,
                "nickname"      => $name,
                "first_name"    => $name,
                "last_name"     => "",
            ] );

            if ( ! is_wp_error( $user_id ) )
            {
                wp_set_password( $pass, $user_id );

                // passo 19
                add_user_meta( $user_id, "triiboId_phone", "+$cellphone" );

                $user = get_user_by( "id", $user_id );

                $user->set_role( "subscriber" );
                if ( class_exists( "WooCommerce" ) ) $user->set_role( "customer" );

                // passo 20 ( passo_comum )
                TAA_Triibo_Firebase::update_user_triibo( $cellphone, $email, "UPDATE" );
                self::make_login( $user_id );

                $result = [
                    "code"    => 2000,
                    "error"   => null,
                    "message" => "Conta criada"
                ];
            }
            else
            {
                $result = [
                    "code"    => 5000,
                    "error"   => true,
                    "message" => "Falha na criação da conta"
                ];
            }
        }

        echo json_encode( $result );
        die();
    }

    public function taa_validate_account()
    {
        $result    = [
            "code"    => 5000,
            "error"   => true,
            "message" => "Error"
        ];

        $email     = $_POST[ "email" ];
        $pass      = $_POST[ "pass" ];

        $cellphone = get_transient( "_taa_triibo_cellphone" );
        delete_transient( "_taa_triibo_cellphone" );

        $user = get_user_by( "email", $email );

        if ( ! $user )
        {
            $result = [
                "code"    => 4004,
                "error"   => true,
                "message" => "Usuário não encontrado",
            ];
        }
        else
        {
            if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID ) )
            {
                // passo 28 ( passo_comum )
                TAA_Triibo_Firebase::update_user_triibo( $cellphone, $email, "CREATE" );
                self::make_login( $user->ID );

                $result = [
                    "code"    => 2000,
                    "error"   => null,
                    "message" => "Usuário conectado"
                ];
            }
            else
            {
                $result = [
                    "code"    => 4001,
                    "error"   => true,
                    "message" => "E-mail e/ou senha incorreta"
                ];
            }
        }

        echo json_encode( $result );
        die();
    }

	private static function make_login( int $user_id )
	{
		clean_user_cache( $user_id );
		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );
	}
}

new TAA_Triibo_Auth_App();
