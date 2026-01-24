<?php
declare(strict_types=1);

namespace WooUcp\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use WP_REST_Request;
use WP_Error;

/**
 * UCP REST API Controller.
 */
class Controller extends WP_REST_Controller {

	/**
	 * Namespace for the UCP API.
	 *
	 * @var string
	 */
	protected $namespace = 'ucp/v1';

	/**
	 * Base path for the UCP API.
	 *
	 * @var string
	 */
	protected $rest_base = 'checkout-sessions';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register UCP routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\w-]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/products/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_product' ),
					'permission_callback' => '__return_true', // Public catalog access
				),
			)
		);
	}

	/**
	 * Check permissions for UCP endpoints.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$header = $request->get_header( 'Authorization' );

		if ( ! $header || ! str_starts_with( (string) $header, 'Bearer ' ) ) {
			return new WP_Error( 'rest_forbidden', 'Missing or invalid Authorization header', array( 'status' => 401 ) );
		}

		$token   = substr( $header, 7 );
		$user_id = \WooUcp\Security\AuthServer::validate_token( $token );

		if ( ! $user_id ) {
			return new WP_Error( 'rest_forbidden', 'Invalid access token', array( 'status' => 401 ) );
		}

		// Set the current user for the REST request context.
		wp_set_current_user( $user_id );

		return true;
	}

	/**
	 * Create a new UCP checkout session.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$params = $request->get_params();
		$items  = isset( $params['items'] ) ? $params['items'] : array();

		$session_id = wp_generate_password( 32, false );

		$handler = new \WooUcp\Integration\CheckoutHandler();
		$success = $handler->save_session( $session_id, $items );

		if ( ! $success ) {
			return new WP_Error( 'ucp_error', 'Failed to create session/cart', array( 'status' => 500 ) );
		}

		return new WP_REST_Response(
			array(
				'id'           => $session_id,
				'status'       => 'open',
				'checkout_url' => $handler->get_checkout_url( $session_id ),
			),
			201
		);
	}

	/**
	 * Get a UCP checkout session.
	 */
	public function get_item( $request ) {
		$id = $request['id'];
		return new WP_REST_Response( array( 'id' => $id, 'status' => 'open' ), 200 );
	}

	/**
	 * Update a UCP checkout session.
	 */
	public function update_item( $request ) {
		$id = $request['id'];
		return new WP_REST_Response( array( 'id' => $id, 'status' => 'updated' ), 200 );
	}

	/**
	 * Get product data optimized for UCP agents.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_product( $request ) {
		$product_id = (int) $request['id'];
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			return new WP_Error( 'ucp_product_not_found', 'Product not found', array( 'status' => 404 ) );
		}

		return new WP_REST_Response(
			array(
				'id'          => $product->get_id(),
				'name'        => $product->get_name(),
				'description' => $product->get_short_description() ?: $product->get_description(),
				'price'       => $product->get_price(),
				'currency'    => get_woocommerce_currency(),
				'images'      => $this->get_product_images( $product ),
				'ucp_metadata' => array(
					'checkout_endpoint' => rest_url( $this->namespace . '/' . $this->rest_base ),
					'capability'        => 'checkout',
				),
			),
			200
		);
	}

	/**
	 * Get product image URLs.
	 */
	private function get_product_images( $product ) {
		$images         = array();
		$attachment_ids = array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() );

		foreach ( array_filter( $attachment_ids ) as $id ) {
			$images[] = wp_get_attachment_url( $id );
		}

		return $images;
	}
}
