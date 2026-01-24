<?php
/**
 * Plugin Name: UCP for WooCommerce
 * Description: Enables WooCommerce stores to be compatible with the Universal Commerce Protocol (UCP).
 * Version: 0.1.0
 * Author: Redemptive Software
 * Author URI: https://redemptivesoftware.com
 * Text Domain: woo-ucp
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package extension
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WOO_UCP_MAIN_PLUGIN_FILE' ) ) {
	define( 'WOO_UCP_MAIN_PLUGIN_FILE', __FILE__ );
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload_packages.php';

use WooUcp\Admin\Setup;
use WooUcp\Discovery\Manifest;
use WooUcp\API\Controller as APIController;
use WooUcp\Integration\CheckoutHandler;
use WooUcp\Security\AuthServer;
use WooUcp\Catalog\StructuredData;

// phpcs:disable WordPress.Files.FileName

/**
 * WooCommerce fallback notice.
 *
 * @since 0.1.0
 */
function woo_ucp_missing_wc_notice() {
	/* translators: %s WC download URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Woo Ucp requires WooCommerce to be installed and active. You can download %s here.', 'woo_ucp' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

register_activation_hook( __FILE__, 'woo_ucp_activate' );

/**
 * Activation hook.
 *
 * @since 0.1.0
 */
function woo_ucp_activate() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'woo_ucp_missing_wc_notice' );
		return;
	}

	// Initialize components to register rewrite rules before flushing.
	WooUcp::instance();
	flush_rewrite_rules();
}

if ( ! class_exists( 'WooUcp' ) ) :
	/**
	 * The WooUcp class.
	 */
	class WooUcp {
		/**
		 * The plugin version.
		 *
		 * @var string
		 */
		public $version = '0.1.0';

		/**
		 * This class instance.
		 *
		 * @var WooUcp|null
		 */
		private static $instance = null;

		/**
		 * Constructor.
		 */
		public function __construct() {
			if ( is_admin() ) {
				new Setup();
			}

			$this->init_components();
		}

		/**
		 * Initialize plugin components.
		 */
		private function init_components() {
			new Manifest();
			new APIController();
			new CheckoutHandler();
			new AuthServer();
			new StructuredData();
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'woo_ucp' ), $this->version );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'woo_ucp' ), $this->version );
		}

		/**
		 * Gets the main instance.
		 *
		 * Ensures only one instance can be loaded.
		 *
		 * @return WooUcp
		 */
		public static function instance(): self {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
endif;

add_action( 'plugins_loaded', 'woo_ucp_init', 10 );

/**
 * Initialize the plugin.
 *
 * @since 0.1.0
 */
function woo_ucp_init() {
	load_plugin_textdomain( 'woo_ucp', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'woo_ucp_missing_wc_notice' );
		return;
	}

	WooUcp::instance();
}
