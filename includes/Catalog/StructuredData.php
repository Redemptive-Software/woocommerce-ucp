<?php

namespace WooUcp\Catalog;

/**
 * Structured Data handler for UCP.
 *
 * Enhances WooCommerce product structured data with UCP-specific metadata.
 */
class StructuredData {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_structured_data_product', array( $this, 'enhance_product_data' ), 10, 2 );
	}

	/**
	 * Enhance product structured data with UCP metadata.
	 *
	 * @param array       $markup  Structured data markup.
	 * @param \WC_Product $product Product object.
	 * @return array
	 */
	public function enhance_product_data( $markup, $product ) {
		// Add UCP-specific potential action for checkout.
		$markup['potentialAction'] = array(
			'@type'       => 'BuyAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => rest_url( 'ucp/v1/checkout-sessions' ),
				'description' => 'Initiate UCP agentic checkout',
				'actionPlatform' => array(
					'http://schema.org/DesktopWebPlatform',
					'http://schema.org/MobileWebPlatform',
					'https://ucp.dev/AgentPlatform',
				),
			),
		);

		// Ensure Shipping and Return policies are present (UCP requirements).
		if ( ! isset( $markup['offers'] ) ) {
			return $markup;
		}

		if ( is_array( $markup['offers'] ) && ! isset( $markup['offers']['@type'] ) ) {
			// Multi-offer (variations)
			foreach ( $markup['offers'] as &$offer ) {
				$this->add_shipping_and_returns( $offer, $product );
			}
		} else {
			// Single offer
			$this->add_shipping_and_returns( $markup['offers'], $product );
		}

		return $markup;
	}

	/**
	 * Add shipping and return policy metadata to an offer.
	 *
	 * @param array       $offer   The offer array.
	 * @param \WC_Product $product Product object.
	 */
	private function add_shipping_and_returns( &$offer, $product ) {
		if ( ! isset( $offer['hasMerchantReturnPolicy'] ) ) {
			$offer['hasMerchantReturnPolicy'] = array(
				'@type'              => 'MerchantReturnPolicy',
				'applicableCountry'  => 'US',
				'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnPeriod',
				'merchantReturnDays' => 30,
				'returnMethod'       => 'https://schema.org/ReturnByMail',
				'returnFees'         => 'https://schema.org/FreeReturn',
			);
		}

		if ( ! isset( $offer['shippingDetails'] ) ) {
			$offer['shippingDetails'] = array(
				'@type' => 'OfferShippingDetails',
				'shippingRate' => array(
					'@type' => 'MonetaryAmount',
					'value' => 0,
					'currency' => get_woocommerce_currency(),
				),
				'shippingDestination' => array(
					'@type' => 'DefinedRegion',
					'addressCountry' => 'US',
				),
			);
		}
	}
}
