<?php

// Mock WordPress functions.
function add_action() {}
function add_filter() {}
function add_rewrite_rule() {}
function get_query_var() {}
function rest_url( $path ) { return 'https://example.com/wp-json/' . $path; }
function wp_send_json( $data ) { echo json_encode( $data ); }
function home_url() { return 'https://example.com'; }
function is_user_logged_in() { return true; }
function get_current_user_id() { return 1; }
function wp_generate_password( $length, $special ) { return 'mock_password'; }
function set_transient() {}
function get_transient() {}
function delete_transient() {}
function add_query_arg() { return 'https://example.com'; }
function wc_get_checkout_url() { return 'https://example.com/checkout'; }
function wp_redirect() {}
function wc_get_product() { return null; }
function get_woocommerce_currency() { return 'USD'; }
function wp_get_attachment_url() { return ''; }
function wp_set_current_user() {}

// Mock WordPress classes.
class WP_REST_Controller {}
class WP_REST_Server {
    const READABLE = 'GET';
    const CREATABLE = 'POST';
    const EDITABLE = 'PATCH';
}
class WP_REST_Response {
    public function __construct( $data, $status ) {}
}
class WP_Error {
    public function __construct( $code, $message, $data ) {}
}

require_once __DIR__ . '/../vendor/autoload.php';
