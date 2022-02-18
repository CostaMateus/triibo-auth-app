<?php
/**
 * Plugin Name:          #Triibo_Auth_App
 * Description:          Loga o usuário oriundo do Triibo App
 * Author:               Mateus Costa
 * Author URI:           https://costamateus.com.br/
 * Version:              1.0.0
 * Text Domain:          triibo-auth-app
 * WC requires at least: 3.6
 * WC tested up to:      5.8
 *
 * @package Triibo_Auth_App
 */

if ( ! defined( "ABSPATH" ) ) exit;

if ( ! is_plugin_active( "woocommerce/woocommerce.php" ) ) exit;

// init class rest-api
if ( ! class_exists( "TAA_Triibo_Rest_Api" ) )
{
	include_once dirname( __FILE__ ) . "/includes/classes/rest-api/class-taa-triibo-rest-api.php";
	add_action( "plugins_loaded", array( "TAA_Triibo_Rest_Api", "init" ) );
}

// init class auth-app
if ( ! class_exists( "TAA_Triibo_Auth_App" ) )
{
	include_once dirname( __FILE__ ) . "/includes/classes/auth-app/class-taa-triibo-auth-app.php";
	add_action( "plugins_loaded", array( "TAA_Triibo_Auth_App", "init" ) );
}

// add style/script/html
add_action( "wp_footer", "TAA_add_modal_teste" );
function TAA_add_modal_teste()
{
	include_once( plugin_dir_path( __FILE__ ) . "includes/assets/style.php"            );
	include_once( plugin_dir_path( __FILE__ ) . "includes/modals/new-account.php"      );
	include_once( plugin_dir_path( __FILE__ ) . "includes/modals/new-password.php"     );
	include_once( plugin_dir_path( __FILE__ ) . "includes/modals/validate-account.php" );
	include_once( plugin_dir_path( __FILE__ ) . "includes/modals/validate-code.php"    );
	include_once( plugin_dir_path( __FILE__ ) . "includes/assets/script.php"           );
}
