<?php
/**
 * Theme setup: supports, menus, sidebars, RTL forcing.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports.
 */
function saayeh_setup() {
	load_theme_textdomain( 'saayeh', SAAYEH_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 88,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 900,
			'gallery_thumbnail_image_width' => 200,
			'product_grid'          => array(
				'default_rows'    => 4,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 5,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_image_size( 'saayeh-card', 600, 800, true );
	add_image_size( 'saayeh-wide', 1600, 900, true );

	register_nav_menus(
		array(
			'primary' => __( 'منوی اصلی', 'saayeh' ),
			'footer'  => __( 'منوی فوتر', 'saayeh' ),
			'footer2' => __( 'منوی فوتر (ستون دوم)', 'saayeh' ),
			'legal'   => __( 'پیوندهای پایین صفحه', 'saayeh' ),
		)
	);

	add_editor_style( 'assets/css/editor.css' );

	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'saayeh_setup' );

/**
 * Widget areas.
 */
function saayeh_widgets_init() {
	// "blog" is registered first so WordPress' default widgets land there, not in the footer.
	$areas = array(
		'blog'     => __( 'ابزارک‌های وبلاگ', 'saayeh' ),
		'footer-1' => __( 'فوتر - ستون ۱', 'saayeh' ),
		'footer-2' => __( 'فوتر - ستون ۲', 'saayeh' ),
		'footer-3' => __( 'فوتر - ستون ۳', 'saayeh' ),
	);
	foreach ( $areas as $id => $name ) {
		register_sidebar(
			array(
				'id'            => $id,
				'name'          => $name,
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'saayeh_widgets_init' );

/**
 * Force RTL direction for the document when the site locale is not RTL
 * (the theme is designed RTL-first). Can be turned off in the Customizer.
 *
 * @param string $output Language attributes.
 * @return string
 */
function saayeh_language_attributes( $output ) {
	if ( is_admin() || is_rtl() || ! get_theme_mod( 'saayeh_force_rtl', true ) ) {
		return $output;
	}
	$output = preg_replace( '/\sdir="[^"]*"/', '', $output );
	return trim( 'dir="rtl" ' . $output );
}
add_filter( 'language_attributes', 'saayeh_language_attributes' );

/**
 * Body classes.
 *
 * @param array $classes Classes.
 * @return array
 */
function saayeh_body_class( $classes ) {
	if ( ! is_rtl() && get_theme_mod( 'saayeh_force_rtl', true ) ) {
		$classes[] = 'saayeh-force-rtl';
	}
	if ( saayeh_is_wc() ) {
		$classes[] = 'woocommerce-active';
	}
	return $classes;
}
add_filter( 'body_class', 'saayeh_body_class' );

/**
 * Flush rewrite rules once after switching to the theme (wishlist endpoint).
 */
function saayeh_after_switch_theme() {
	update_option( 'saayeh_flush_rewrite', 1 );
}
add_action( 'after_switch_theme', 'saayeh_after_switch_theme' );

/**
 * Perform the deferred rewrite flush once endpoints are registered.
 */
function saayeh_maybe_flush_rewrite() {
	if ( get_option( 'saayeh_flush_rewrite' ) ) {
		delete_option( 'saayeh_flush_rewrite' );
		flush_rewrite_rules( false );
	}
}
add_action( 'init', 'saayeh_maybe_flush_rewrite', 999 );

/**
 * Shorter excerpts.
 *
 * @return int
 */
function saayeh_excerpt_length() {
	return 28;
}
add_filter( 'excerpt_length', 'saayeh_excerpt_length' );

/**
 * Excerpt more string.
 *
 * @return string
 */
function saayeh_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'saayeh_excerpt_more' );

/**
 * Add a pingback header on singular content.
 */
function saayeh_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'saayeh_pingback_header' );
