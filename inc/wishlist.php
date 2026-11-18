<?php
/**
 * Lightweight wishlist (no plugin).
 * Logged-in users: user meta `_saayeh_wishlist`. Guests: cookie `saayeh_wishlist`.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

define( 'SAAYEH_WISHLIST_COOKIE', 'saayeh_wishlist' );
define( 'SAAYEH_WISHLIST_META', '_saayeh_wishlist' );
define( 'SAAYEH_WISHLIST_MAX', 100 );

/**
 * Parse a list of IDs.
 *
 * @param mixed $raw Raw value.
 * @return int[]
 */
function saayeh_wishlist_parse( $raw ) {
	if ( is_string( $raw ) ) {
		$raw = explode( ',', $raw );
	}
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$ids = array_values( array_unique( array_filter( array_map( 'absint', $raw ) ) ) );
	return array_slice( $ids, 0, SAAYEH_WISHLIST_MAX );
}

/**
 * Current wishlist IDs.
 *
 * @return int[]
 */
function saayeh_wishlist_get() {
	if ( isset( $GLOBALS['saayeh_wishlist_override'] ) ) {
		return $GLOBALS['saayeh_wishlist_override'];
	}
	if ( is_user_logged_in() ) {
		$ids = saayeh_wishlist_parse( get_user_meta( get_current_user_id(), SAAYEH_WISHLIST_META, true ) );
	} else {
		$ids = isset( $_COOKIE[ SAAYEH_WISHLIST_COOKIE ] ) ? saayeh_wishlist_parse( sanitize_text_field( wp_unslash( $_COOKIE[ SAAYEH_WISHLIST_COOKIE ] ) ) ) : array();
	}
	return $ids;
}

/**
 * Persist wishlist IDs.
 *
 * @param int[] $ids IDs.
 */
function saayeh_wishlist_save( $ids ) {
	$ids = saayeh_wishlist_parse( $ids );
	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), SAAYEH_WISHLIST_META, $ids );
	} elseif ( ! headers_sent() ) {
		setcookie(
			SAAYEH_WISHLIST_COOKIE,
			implode( ',', $ids ),
			array(
				'expires'  => time() + YEAR_IN_SECONDS,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => false,
				'samesite' => 'Lax',
			)
		);
	}
	$GLOBALS['saayeh_wishlist_override'] = $ids;
	do_action( 'saayeh_wishlist_changed', $ids );
}

/**
 * Whether a product is in the wishlist.
 *
 * @param int $id Product ID.
 * @return bool
 */
function saayeh_in_wishlist( $id ) {
	return in_array( (int) $id, saayeh_wishlist_get(), true );
}

/**
 * Wishlist page URL.
 *
 * @return string
 */
function saayeh_wishlist_url() {
	$page = (int) apply_filters( 'saayeh_wishlist_page_id', saayeh_find_page( 'wishlist', 'saayeh_wishlist' ) );
	if ( $page ) {
		return get_permalink( $page );
	}
	if ( saayeh_is_wc() && is_user_logged_in() ) {
		return wc_get_account_endpoint_url( 'wishlist' );
	}
	return home_url( '/' );
}

/**
 * Heart button markup.
 *
 * @param int    $id    Product ID.
 * @param string $class Extra classes.
 * @return string
 */
function saayeh_heart_button( $id, $class = 'x-heart' ) {
	$on    = saayeh_in_wishlist( $id );
	$label = $on ? __( 'حذف از علاقه‌مندی‌ها', 'saayeh' ) : __( 'افزودن به علاقه‌مندی‌ها', 'saayeh' );
	return sprintf(
		'<button type="button" class="x-icon %1$s%2$s" data-wish="%3$d" aria-pressed="%4$s" aria-label="%5$s" data-name="%6$s">%7$s</button>',
		esc_attr( $class ),
		$on ? ' is-on' : '',
		(int) $id,
		$on ? 'true' : 'false',
		esc_attr( $label ),
		esc_attr( wp_strip_all_tags( get_the_title( $id ) ) ),
		wp_kses( saayeh_icon( 'heart', 20 ), saayeh_svg_kses() )
	);
}

/**
 * AJAX toggle.
 */
function saayeh_ajax_wishlist_toggle() {
	check_ajax_referer( 'saayeh', 'nonce' );
	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$ok = $id && 'publish' === get_post_status( $id ) && in_array( get_post_type( $id ), (array) apply_filters( 'saayeh_wishlist_post_types', array( 'product' ) ), true );
	if ( ! $ok ) {
		wp_send_json_error( array( 'message' => __( 'محصول معتبر نیست.', 'saayeh' ) ), 400 );
	}
	$ids = saayeh_wishlist_get();
	$pos = array_search( $id, $ids, true );
	if ( false === $pos ) {
		array_unshift( $ids, $id );
		$in = true;
	} else {
		unset( $ids[ $pos ] );
		$in = false;
	}
	saayeh_wishlist_save( $ids );
	$ids = saayeh_wishlist_get();
	wp_send_json_success(
		array(
			'in'    => $in,
			'ids'   => $ids,
			'count' => count( $ids ),
			'name'  => wp_strip_all_tags( get_the_title( $id ) ),
		)
	);
}
add_action( 'wp_ajax_saayeh_wishlist_toggle', 'saayeh_ajax_wishlist_toggle' );
add_action( 'wp_ajax_nopriv_saayeh_wishlist_toggle', 'saayeh_ajax_wishlist_toggle' );

/**
 * No-JS fallback: ?saayeh_wish=ID&_wpnonce=...
 */
function saayeh_wishlist_nojs() {
	if ( ! isset( $_GET['saayeh_wish'], $_GET['_wpnonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'saayeh_wish' ) ) {
		return;
	}
	$id  = absint( $_GET['saayeh_wish'] );
	$ids = saayeh_wishlist_get();
	$pos = array_search( $id, $ids, true );
	if ( false === $pos ) {
		if ( 'product' === get_post_type( $id ) ) {
			array_unshift( $ids, $id );
		}
	} else {
		unset( $ids[ $pos ] );
	}
	saayeh_wishlist_save( $ids );
	wp_safe_redirect( remove_query_arg( array( 'saayeh_wish', '_wpnonce' ) ) );
	exit;
}
add_action( 'template_redirect', 'saayeh_wishlist_nojs' );

/**
 * Merge the guest cookie into the user's list on login.
 *
 * @param string  $login Login.
 * @param WP_User $user  User.
 */
function saayeh_wishlist_merge_on_login( $login, $user ) {
	if ( empty( $_COOKIE[ SAAYEH_WISHLIST_COOKIE ] ) ) {
		return;
	}
	$guest = saayeh_wishlist_parse( sanitize_text_field( wp_unslash( $_COOKIE[ SAAYEH_WISHLIST_COOKIE ] ) ) );
	$mine  = saayeh_wishlist_parse( get_user_meta( $user->ID, SAAYEH_WISHLIST_META, true ) );
	update_user_meta( $user->ID, SAAYEH_WISHLIST_META, saayeh_wishlist_parse( array_merge( $mine, $guest ) ) );
	if ( ! headers_sent() ) {
		setcookie( SAAYEH_WISHLIST_COOKIE, '', time() - HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
	}
}
add_action( 'wp_login', 'saayeh_wishlist_merge_on_login', 10, 2 );

/**
 * Wishlist list markup ([saayeh_wishlist]).
 *
 * @return string
 */
function saayeh_wishlist_shortcode() {
	if ( ! saayeh_is_wc() ) {
		return '<p class="x-empty">' . esc_html__( 'برای استفاده از علاقه‌مندی‌ها، ووکامرس را فعال کنید.', 'saayeh' ) . '</p>';
	}
	$ids = saayeh_wishlist_get();
	ob_start();
	echo '<div class="x-wishlist" data-saayeh-wishlist>';
	$products = array_filter( array_map( 'wc_get_product', $ids ) );
	if ( ! $products ) {
		?>
		<div class="x-empty">
			<p class="x-empty__t"><?php esc_html_e( 'فهرست علاقه‌مندی‌های شما خالی است', 'saayeh' ); ?></p>
			<p style="margin:0 0 24px"><?php esc_html_e( 'با زدن قلب روی هر محصول، آن را اینجا نگه دارید.', 'saayeh' ); ?></p>
			<a class="x-btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'رفتن به فروشگاه', 'saayeh' ); ?></a>
		</div>
		<?php
	} else {
		foreach ( $products as $product ) {
			/** @var WC_Product $product */
			$pid  = $product->get_id();
			$opts = saayeh_quick_add_options( $product );
			?>
			<div class="x-line" data-wish-row="<?php echo esc_attr( $pid ); ?>">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true"><?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?></a>
				<div>
					<div class="x-line__row x-line__row--top">
						<h3><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
						<span class="x-line__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
					</div>
					<p><?php echo esc_html( saayeh_product_subline( $product ) ); ?></p>
					<div class="x-line__row">
						<?php if ( ! $product->is_in_stock() ) : ?>
							<span class="x-muted" style="font-size:13px"><?php esc_html_e( 'ناموجود', 'saayeh' ); ?></span>
						<?php elseif ( count( $opts ) > 1 ) : ?>
							<label class="screen-reader-text" for="ws-<?php echo esc_attr( $pid ); ?>"><?php esc_html_e( 'انتخاب سایز', 'saayeh' ); ?></label>
							<select class="x-select" id="ws-<?php echo esc_attr( $pid ); ?>" data-wish-variation>
								<?php foreach ( $opts as $o ) : ?>
									<option value="<?php echo esc_attr( $o['id'] ); ?>" <?php disabled( ! $o['in_stock'] ); ?>><?php echo esc_html( $o['label'] ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php elseif ( 1 === count( $opts ) ) : ?>
							<span class="x-muted" style="font-size:13px"><?php echo esc_html( $opts[0]['label'] ); ?></span>
						<?php else : ?>
							<span></span>
						<?php endif; ?>
						<div style="display:flex;gap:14px;align-items:center">
							<button type="button" class="x-link" data-wish="<?php echo esc_attr( $pid ); ?>" data-wish-remove="1" data-name="<?php echo esc_attr( $product->get_name() ); ?>"><?php esc_html_e( 'حذف', 'saayeh' ); ?></button>
							<?php if ( $product->is_in_stock() && $opts ) : ?>
								<button type="button" class="x-btn x-btn--sm" data-add="<?php echo esc_attr( $opts[0]['id'] ); ?>" data-wish-add="<?php echo esc_attr( $pid ); ?>" data-name="<?php echo esc_attr( $product->get_name() ); ?>"><?php esc_html_e( 'افزودن به سبد', 'saayeh' ); ?></button>
							<?php else : ?>
								<a class="x-btn x-btn--sm x-btn--ghost" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'مشاهده', 'saayeh' ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'saayeh_wishlist', 'saayeh_wishlist_shortcode' );

/* My Account tab «علاقه‌مندی‌ها». */

/**
 * Register the endpoint.
 */
function saayeh_wishlist_endpoint() {
	add_rewrite_endpoint( 'wishlist', EP_PAGES );
}
add_action( 'init', 'saayeh_wishlist_endpoint' );

/**
 * Add query var for WooCommerce.
 *
 * @param array $vars Vars.
 * @return array
 */
function saayeh_wishlist_query_vars( $vars ) {
	$vars['wishlist'] = 'wishlist';
	return $vars;
}
add_filter( 'woocommerce_get_query_vars', 'saayeh_wishlist_query_vars' );

/**
 * Menu item.
 *
 * @param array $items Items.
 * @return array
 */
function saayeh_wishlist_account_item( $items ) {
	$new = array();
	foreach ( $items as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'orders' === $key ) {
			$new['wishlist'] = __( 'علاقه‌مندی‌ها', 'saayeh' );
		}
	}
	if ( ! isset( $new['wishlist'] ) ) {
		$new['wishlist'] = __( 'علاقه‌مندی‌ها', 'saayeh' );
	}
	return $new;
}
add_filter( 'woocommerce_account_menu_items', 'saayeh_wishlist_account_item' );

/**
 * Endpoint content.
 */
function saayeh_wishlist_account_content() {
	echo '<h2 class="x-h2" style="font-size:24px;margin-bottom:24px">' . esc_html__( 'علاقه‌مندی‌ها', 'saayeh' ) . '</h2>';
	echo saayeh_wishlist_shortcode(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
}
add_action( 'woocommerce_account_wishlist_endpoint', 'saayeh_wishlist_account_content' );

/**
 * Endpoint title.
 *
 * @param string $title Title.
 * @return string
 */
function saayeh_wishlist_endpoint_title( $title ) {
	return __( 'علاقه‌مندی‌ها', 'saayeh' );
}
add_filter( 'woocommerce_endpoint_wishlist_title', 'saayeh_wishlist_endpoint_title' );
