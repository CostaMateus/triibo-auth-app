<?php
/**
 * Initialize this version of the REST API.
 *
 * @package Taa_Triibo_Rest_Api
 * @since   1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class responsible for loading the REST API and all REST API namespaces.
 */
class TAA_Triibo_Rest_Api
{
	/**
	 * Initialize the plugin public actions.
	 */
	public static function init()
	{
        $env = ( strpos( home_url(), "dev-" ) !== FALSE ) ? "dev" : "prod";

        self::includes();

        add_action( "rest_api_init", array( __CLASS__, "register_rest_routes" ) );
    }

	/**
	 * Includes.
	 */
	private static function includes()
    {
		include_once dirname( __FILE__ ) . "/Controllers/Version1/class-taa-triibo-rest-users-controller.php";
    }


    /**
     * Register REST API routes.
     */
    function register_rest_routes()
    {
        foreach ( self::get_rest_namespaces() as $controllers )
        {
            foreach ( $controllers as $controller_class )
            {
                $controller = new $controller_class();
                $controller->register_routes();
            }
        }
    }

    /**
     * Get API namespaces - new namespaces should be registered here.
     *
     * @return array List of Namespaces and Main controller classes.
     */
    private static function get_rest_namespaces()
    {
        return apply_filters(
            "triibo_rest_api_namespaces",
            [
                "triibo-rest/v1" => [
                    "users" => "TAA_TRIIBO_REST_Users_Controller",
                ]
            ]
        );
    }

}
