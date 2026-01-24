declare(strict_types=1);

namespace WooUcp\Security;

use WP_REST_Server;
use WP_REST_Response;
use WP_REST_Request;
use WP_Error;

/**
 * UCP Authentication Server class.
 *
 * Implements standard OAuth 2.0 flows for Identity Linking.
 */
class AuthServer {
	/**
	 * Namespace for the UCP Auth API.
	 *
	 * @var string
	 */
	protected $namespace = 'ucp/v1';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register Auth routes.
	 */
	public function register_routes() {
		// Authorization Code endpoint.
		register_rest_route(
			$this->namespace,
			'/auth',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'handle_authorization' ),
				'permission_callback' => '__return_true',
			)
		);

		// Token endpoint.
		register_rest_route(
			$this->namespace,
			'/token',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'handle_token_exchange' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Handle authorization request.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 */
	public function handle_authorization( $request ) {
		$client_id    = $request->get_param( 'client_id' );
		$redirect_uri = $request->get_param( 'redirect_uri' );
		$state        = $request->get_param( 'state' );

		// In a real implementation, this would redirect to a login/consent page.
		// For MVP, we'll simulate a successful authorization if the user is logged in.
		if ( ! is_user_logged_in() ) {
			auth_redirect();
		}

		$code = wp_generate_password( 16, false );
		set_transient( 'ucp_auth_code_' . $code, array(
			'user_id'   => get_current_user_id(),
			'client_id' => $client_id,
		), 10 * MINUTE_IN_SECONDS );

		$url = add_query_arg( array(
			'code'  => $code,
			'state' => $state,
		), $redirect_uri );

		wp_redirect( $url );
		exit;
	}

	/**
	 * Handle token exchange.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 */
	public function handle_token_exchange( $request ) {
		$code      = $request->get_param( 'code' );
		$auth_data = get_transient( 'ucp_auth_code_' . $code );

		if ( ! $auth_data ) {
			return new WP_Error( 'invalid_grant', 'Invalid or expired authorization code', array( 'status' => 400 ) );
		}

		delete_transient( 'ucp_auth_code_' . $code );

		$token = wp_generate_password( 40, false );
		set_transient( 'ucp_access_token_' . $token, $auth_data, DAY_IN_SECONDS );

		return new WP_REST_Response( array(
			'access_token' => $token,
			'token_type'   => 'Bearer',
			'expires_in'   => DAY_IN_SECONDS,
			'scope'        => 'ucp:scopes:checkout_session',
		), 200 );
	}

	/**
	 * Validate a Bearer token.
	 *
	 * @param string $token The token.
	 * @return int|bool User ID if valid, false otherwise.
	 */
	public static function validate_token( $token ) {
		$data = get_transient( 'ucp_access_token_' . $token );
		return $data ? $data['user_id'] : false;
	}
}
