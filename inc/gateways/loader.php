<?php
/**
 * Payment gateway loader.
 *
 * Gateways shipped with the theme are registered through the
 * `saayeh_payment_gateways` filter, so other plugins/child themes can add
 * (or remove) gateways using the same structure:
 *
 *     add_filter( 'saayeh_payment_gateways', function ( $gateways ) {
 *         $gateways['my_gateway'] = array(
 *             'class' => 'My_Gateway_Class',          // extends WC_Payment_Gateway
 *             'file'  => __DIR__ . '/class-my-gateway.php', // optional; required on demand
 *         );
 *         return $gateways;
 *     } );
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/**
 * List of gateways provided through the theme.
 *
 * @return array id => array{class:string,file?:string}
 */
function saayeh_payment_gateways() {
	return (array) apply_filters(
		'saayeh_payment_gateways',
		array(
			'saayeh_zarinpal' => array(
				'class' => 'Saayeh_Gateway_Zarinpal',
				'file'  => __DIR__ . '/class-saayeh-zarinpal.php',
			),
		)
	);
}

/**
 * Load gateway classes once WooCommerce's payment base class exists.
 */
function saayeh_load_gateways() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}
	foreach ( saayeh_payment_gateways() as $gw ) {
		if ( ! empty( $gw['class'] ) && ! class_exists( $gw['class'] ) && ! empty( $gw['file'] ) && is_readable( $gw['file'] ) ) {
			require_once $gw['file'];
		}
	}
}
add_action( 'woocommerce_init', 'saayeh_load_gateways' );

/**
 * Register gateways with WooCommerce.
 *
 * @param array $methods Gateway class names.
 * @return array
 */
function saayeh_register_gateways( $methods ) {
	saayeh_load_gateways();
	foreach ( saayeh_payment_gateways() as $gw ) {
		if ( ! empty( $gw['class'] ) && class_exists( $gw['class'] ) && is_subclass_of( $gw['class'], 'WC_Payment_Gateway' ) ) {
			$methods[] = $gw['class'];
		}
	}
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'saayeh_register_gateways' );
