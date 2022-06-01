<?php
/**
 * Plugin Name:          #Triibo_Auth_App
 * Description:          Loga o usuário oriundo do Triibo App. Depende do WooCommerce.
 * Author:               Mateus Costa
 * Author URI:           https://costamateus.com.br/
 * Version:              1.0.0
 * Text Domain:          triibo-auth-app
 * WC requires at least: 3.6
 * WC tested up to:      5.8
 *
 * @package Triibo_Auth_App
 */

defined( "ABSPATH" ) || exit;

if ( ! is_plugin_active( "woocommerce/woocommerce.php" ) ) exit;


// init class auth-app
if ( ! class_exists( "TAA_Triibo_Auth_App" ) )
{
	include_once dirname( __FILE__ ) . "/includes/classes/auth-app/class-taa-triibo-auth-app.php";
	// add_action( "plugins_loaded", "TAA_Triibo_Auth_App" );
}


// init class firebase
if ( ! class_exists( "TAA_Triibo_Firebase" ) )
{
	include_once dirname( __FILE__ ) . "/includes/classes/firebase/class-taa-triibo-firebase.php";
	// add_action( "plugins_loaded", "TAA_Triibo_Firebase" );
}


// init class rest-api
if ( ! class_exists( "TAA_Triibo_Rest_Api" ) )
{
	include_once dirname( __FILE__ ) . "/includes/classes/rest-api/class-taa-triibo-rest-api.php";
	// add_action( "plugins_loaded", "TAA_Triibo_Rest_Api" );
}


if ( ! defined( "TAA_dir" ) ) define( "TAA_dir", plugin_dir_path( __FILE__ ) );


// add style/script/html
add_action( "wp_footer", "TAA_add_parts" );
function TAA_add_parts()
{
	include_once( TAA_dir . "includes/modals/new-account.php"      );
	include_once( TAA_dir . "includes/modals/new-password.php"     );
	include_once( TAA_dir . "includes/modals/validate-account.php" );
	include_once( TAA_dir . "includes/modals/validate-code.php"    );

	// create my own version codes
	$js_path  = "includes/assets/script.js";
	$css_path = "includes/assets/style.css";

	$js_ver   = date( "ymd-His", filemtime( plugin_dir_path( __FILE__ ) . $js_path  ));
	$css_ver  = date( "ymd-His", filemtime( plugin_dir_path( __FILE__ ) . $css_path ));

	wp_enqueue_script( "TAA_script", plugins_url( $js_path,  __FILE__ ), array( "jquery" ), $js_ver  );
	wp_register_style( "TAA_css",    plugins_url( $css_path, __FILE__ ), false,             $css_ver );
	wp_enqueue_style ( "TAA_css" );
}
