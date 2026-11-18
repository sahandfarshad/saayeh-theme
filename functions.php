<?php
/**
 * Saayeh theme bootstrap.
 *
 * @package Saayeh
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'SAAYEH_VERSION', '1.0.0' );
define( 'SAAYEH_DIR', get_template_directory() );
define( 'SAAYEH_URI', get_template_directory_uri() );

/**
 * Whether WooCommerce is active.
 *
 * @return bool
 */
function saayeh_is_wc() {
	return class_exists( 'WooCommerce' );
}

require SAAYEH_DIR . '/inc/helpers.php';
require SAAYEH_DIR . '/inc/setup.php';
require SAAYEH_DIR . '/inc/customizer.php';
require SAAYEH_DIR . '/inc/enqueue.php';
require SAAYEH_DIR . '/inc/template-tags.php';
require SAAYEH_DIR . '/inc/forms.php';
require SAAYEH_DIR . '/inc/wishlist.php';
require SAAYEH_DIR . '/inc/admin.php';

// WooCommerce-dependent features are loaded once plugins are available.
add_action(
	'after_setup_theme',
	function () {
		if ( saayeh_is_wc() ) {
			require SAAYEH_DIR . '/inc/woocommerce.php';
			require SAAYEH_DIR . '/inc/checkout.php';
			require SAAYEH_DIR . '/inc/i18n-fallback.php';
			require SAAYEH_DIR . '/inc/product-meta.php';
			require SAAYEH_DIR . '/inc/gateways/loader.php';
		}
	},
	5
);
