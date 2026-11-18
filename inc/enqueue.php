<?php
/**
 * Styles and scripts.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue front-end assets.
 */
function saayeh_enqueue() {
	wp_enqueue_style( 'saayeh-style', get_stylesheet_uri(), array(), SAAYEH_VERSION );
	$accent = saayeh_accent_css();
	if ( $accent ) {
		wp_add_inline_style( 'saayeh-style', $accent );
	}

	// The theme's own styles fully cover WooCommerce UI; keep only the core layout sheet off.
	wp_enqueue_script( 'saayeh', SAAYEH_URI . '/assets/js/saayeh.js', array(), SAAYEH_VERSION, true );

	$data = array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'saayeh' ),
		'faDigits'  => saayeh_use_fa_digits(),
		'wishlist'  => array_map( 'intval', saayeh_wishlist_get() ),
		'wishUrl'   => saayeh_wishlist_url(),
		'isWc'      => saayeh_is_wc(),
		'i18n'      => array(
			/* translators: %s: product name */
			'added'        => __( '%s به سبد اضافه شد', 'saayeh' ),
			'viewCart'     => __( 'مشاهده سبد', 'saayeh' ),
			/* translators: %s: product name */
			'wishAdded'    => __( '%s به علاقه‌مندی‌ها اضافه شد', 'saayeh' ),
			/* translators: %s: product name */
			'wishRemoved'  => __( '%s از علاقه‌مندی‌ها حذف شد', 'saayeh' ),
			'wishAdd'      => __( 'افزودن به علاقه‌مندی‌ها', 'saayeh' ),
			'wishRemove'   => __( 'حذف از علاقه‌مندی‌ها', 'saayeh' ),
			'error'        => __( 'خطایی رخ داد؛ لطفاً دوباره تلاش کنید.', 'saayeh' ),
			'pickOptions'  => __( 'لطفاً اول سایز و رنگ را انتخاب کنید.', 'saayeh' ),
			'noResults'    => __( 'محصولی پیدا نشد.', 'saayeh' ),
			'allResults'   => __( 'مشاهده همه نتایج', 'saayeh' ),
			'badEmail'     => __( 'لطفاً یک ایمیل معتبر وارد کنید.', 'saayeh' ),
			'badMobile'    => __( 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.', 'saayeh' ),
			'badPostcode'  => __( 'کد پستی باید دقیقاً ۱۰ رقم باشد.', 'saayeh' ),
			'outOfStock'   => __( 'ناموجود', 'saayeh' ),
			'couponEmpty'  => __( 'کد تخفیف را وارد کنید.', 'saayeh' ),
		),
	);

	if ( saayeh_is_wc() ) {
		wp_enqueue_script( 'wc-cart-fragments' );
		$data['wcAjaxUrl']        = WC_AJAX::get_endpoint( '%%endpoint%%' );
		$data['applyCouponNonce'] = wp_create_nonce( 'apply-coupon' );
		$data['removeCouponNonce'] = wp_create_nonce( 'remove-coupon' );
		$data['cartUrl']          = wc_get_cart_url();
		$data['checkoutUrl']      = wc_get_checkout_url();
		$data['isCart']           = is_cart();
		$data['isCheckout']       = is_checkout();
		$data['shopUrl']          = wc_get_page_permalink( 'shop' );
	}

	wp_localize_script( 'saayeh', 'SAAYEH', $data );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'saayeh_enqueue', 20 );

/**
 * Preload the font file.
 */
function saayeh_preload_font() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( SAAYEH_URI . '/assets/fonts/Vazirmatn-Variable.woff2' )
	);
}
add_action( 'wp_head', 'saayeh_preload_font', 1 );

/**
 * Disable WooCommerce default styles (the theme styles everything itself),
 * but keep the small "smallscreen"/layout sheets off too for a clean slate.
 *
 * @param array $styles Styles.
 * @return array
 */
function saayeh_wc_styles( $styles ) {
	return array();
}
add_filter( 'woocommerce_enqueue_styles', 'saayeh_wc_styles' );

/**
 * Block editor assets: the font + basic typography inside the editor.
 */
function saayeh_editor_assets() {
	wp_enqueue_style( 'saayeh-editor-font', SAAYEH_URI . '/assets/css/editor.css', array(), SAAYEH_VERSION );
}
add_action( 'enqueue_block_editor_assets', 'saayeh_editor_assets' );
