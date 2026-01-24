<?php
declare(strict_types=1);

namespace WooUcp\Integration;

/**
 * Checkout Integration Handler.
 */
class CheckoutHandler {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_restore_ucp_session' ) );
	}

	/**
	 * Save a UCP session to the database.
	 *
	 * @param string $session_id UCP session ID.
	 * @param array  $items      UCP line items.
	 * @return bool
	 */
	public function save_session( string $session_id, array $items ): bool {
		return set_transient( 'ucp_session_' . $session_id, array(
			'items' => $items,
			'ts'    => time(),
		), DAY_IN_SECONDS );
	}

	/**
	 * Restore a UCP session if the ucp_session query var is present.
	 */
	public function maybe_restore_ucp_session() {
		if ( ! isset( $_GET['ucp_session'] ) ) {
			return;
		}

		$session_id = sanitize_text_field( $_GET['ucp_session'] );
		$data       = get_transient( 'ucp_session_' . $session_id );

		if ( ! $data || ! isset( $data['items'] ) ) {
			return;
		}

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		WC()->cart->empty_cart();

		foreach ( $data['items'] as $item ) {
			$product_id = isset( $item['product_id'] ) ? intval( $item['product_id'] ) : 0;
			$quantity   = isset( $item['quantity'] ) ? intval( $item['quantity'] ) : 1;

			if ( $product_id ) {
				WC()->cart->add_to_cart( $product_id, $quantity );
			}
		}
	}

	/**
	 * Get the checkout URL for a UCP session.
	 *
	 * @param string $session_id Session ID.
	 * @return string
	 */
	public function get_checkout_url( string $session_id ): string {
		return add_query_arg( 'ucp_session', $session_id, wc_get_checkout_url() );
	}
}
