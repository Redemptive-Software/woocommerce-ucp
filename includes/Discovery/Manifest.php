<?php
declare(strict_types=1);

namespace WooUcp\Discovery;

/**
 * UCP Manifest Discovery class.
 *
 * Handles the disclosure of UCP capabilities via the .well-known/ucp endpoint.
 */
class Manifest {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'render_manifest' ) );
	}

	/**
	 * Add rewrite rule for .well-known/ucp and OAuth discovery.
	 */
	public function add_rewrite_rules() {
		add_rewrite_rule( '^\.well-known/ucp/?$', 'index.php?ucp_manifest=1', 'top' );
		add_rewrite_rule( '^\.well-known/oauth-authorization-server/?$', 'index.php?ucp_oauth_discovery=1', 'top' );
	}

	/**
	 * Add query variables for discovery.
	 *
	 * @param array $vars Query variables.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'ucp_manifest';
		$vars[] = 'ucp_oauth_discovery';
		return $vars;
	}

	/**
	 * Render the discovery JSONs.
	 */
	public function render_manifest() {
		if ( get_query_var( 'ucp_manifest' ) ) {
			$this->render_ucp_manifest();
		} elseif ( get_query_var( 'ucp_oauth_discovery' ) ) {
			$this->render_oauth_discovery();
		}
	}

	/**
	 * Render the UCP manifest JSON.
	 */
	private function render_ucp_manifest() {
		$manifest = array(
			'version'      => '2026-01-11',
			'endpoints'    => array(
				'checkout_sessions' => rest_url( 'ucp/v1/checkout-sessions' ),
				'identity_linking'  => home_url( 'ucp/auth' ),
			),
			'capabilities' => array(
				'checkout',
				'identity_linking',
				'order_management',
			),
		);

		wp_send_json( $manifest );
	}

	/**
	 * Render the OAuth 2.0 discovery JSON.
	 */
	private function render_oauth_discovery() {
		$discovery = array(
			'issuer'                                => home_url(),
			'authorization_endpoint'                => home_url( 'ucp/auth' ),
			'token_endpoint'                        => rest_url( 'ucp/v1/token' ),
			'response_types_supported'              => array( 'code' ),
			'subject_types_supported'               => array( 'public' ),
			'id_token_signing_alg_values_supported' => array( 'RS256' ),
			'scopes_supported'                      => array( 'openid', 'profile', 'email', 'ucp:scopes:checkout_session' ),
			'token_endpoint_auth_methods_supported' => array( 'client_secret_basic' ),
		);

		wp_send_json( $discovery );
	}
}
