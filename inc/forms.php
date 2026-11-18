<?php
/**
 * Contact form shortcode, newsletter signup and live search endpoints.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Contact form: [saayeh_contact]
 * ---------------------------------------------------------------------- */

/**
 * Render the contact form.
 *
 * @return string
 */
function saayeh_contact_shortcode() {
	$status = isset( $_GET['saayeh_contact'] ) ? sanitize_key( wp_unslash( $_GET['saayeh_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	ob_start();
	?>
	<form class="x-contact" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate data-saayeh-validate>
		<?php if ( 'sent' === $status ) : ?>
			<p class="woocommerce-message" role="status"><?php esc_html_e( 'پیام شما دریافت شد. به‌زودی پاسخ می‌دهیم.', 'saayeh' ); ?></p>
		<?php elseif ( 'invalid' === $status ) : ?>
			<p class="woocommerce-error" role="alert"><?php esc_html_e( 'لطفاً نام، ایمیل یا موبایل معتبر و متن پیام را وارد کنید.', 'saayeh' ); ?></p>
		<?php elseif ( 'failed' === $status ) : ?>
			<p class="woocommerce-error" role="alert"><?php esc_html_e( 'ارسال پیام انجام نشد. لطفاً بعداً دوباره تلاش کنید.', 'saayeh' ); ?></p>
		<?php endif; ?>
		<input type="hidden" name="action" value="saayeh_contact">
		<input type="hidden" name="redirect" value="<?php echo esc_url( get_permalink() ); ?>">
		<?php wp_nonce_field( 'saayeh_contact', 'saayeh_contact_nonce' ); ?>
		<div class="x-field">
			<label for="sc-name"><?php esc_html_e( 'نام و نام خانوادگی', 'saayeh' ); ?> <span class="required" aria-hidden="true">*</span></label>
			<input class="x-input" id="sc-name" name="sc_name" type="text" autocomplete="name" required minlength="3">
		</div>
		<div class="x-field">
			<label for="sc-contact"><?php esc_html_e( 'ایمیل یا شماره موبایل', 'saayeh' ); ?> <span class="required" aria-hidden="true">*</span></label>
			<input class="x-input" id="sc-contact" name="sc_contact" type="text" dir="ltr" required>
		</div>
		<div class="x-field">
			<label for="sc-subject"><?php esc_html_e( 'موضوع', 'saayeh' ); ?></label>
			<input class="x-input" id="sc-subject" name="sc_subject" type="text">
		</div>
		<div class="x-field">
			<label for="sc-message"><?php esc_html_e( 'پیام', 'saayeh' ); ?> <span class="required" aria-hidden="true">*</span></label>
			<textarea class="x-input" id="sc-message" name="sc_message" rows="6" required minlength="10"></textarea>
		</div>
		<div class="x-hp" aria-hidden="true">
			<label for="sc-website"><?php esc_html_e( 'این فیلد را خالی بگذارید', 'saayeh' ); ?></label>
			<input id="sc-website" name="sc_website" type="text" tabindex="-1" autocomplete="off">
		</div>
		<button type="submit" class="x-btn"><?php esc_html_e( 'ارسال پیام', 'saayeh' ); ?></button>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'saayeh_contact', 'saayeh_contact_shortcode' );

/**
 * Handle the contact form submission (PRG).
 */
function saayeh_contact_submit() {
	$redirect = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : home_url( '/' );
	$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );

	if ( ! isset( $_POST['saayeh_contact_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['saayeh_contact_nonce'] ) ), 'saayeh_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'saayeh_contact', 'invalid', $redirect ) );
		exit;
	}
	// Honeypot: silently accept bots.
	if ( ! empty( $_POST['sc_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'saayeh_contact', 'sent', $redirect ) );
		exit;
	}

	$name    = isset( $_POST['sc_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sc_name'] ) ) : '';
	$contact = isset( $_POST['sc_contact'] ) ? saayeh_en_digits( sanitize_text_field( wp_unslash( $_POST['sc_contact'] ) ) ) : '';
	$subject = isset( $_POST['sc_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['sc_subject'] ) ) : '';
	$message = isset( $_POST['sc_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sc_message'] ) ) : '';

	$valid_contact = is_email( $contact ) || preg_match( '/^09\d{9}$/', $contact );
	if ( mb_strlen( $name ) < 2 || ! $valid_contact || mb_strlen( $message ) < 5 ) {
		wp_safe_redirect( add_query_arg( 'saayeh_contact', 'invalid', $redirect ) );
		exit;
	}

	$to = saayeh_mod( 'saayeh_contact_email' );
	$to = is_email( $to ) ? $to : get_option( 'admin_email' );
	/* translators: 1: site name, 2: subject */
	$mail_subject = sprintf( __( '[%1$s] پیام تازه از فرم تماس: %2$s', 'saayeh' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $subject ? $subject : __( 'بدون موضوع', 'saayeh' ) );
	$body         = sprintf(
		"%s: %s\n%s: %s\n\n%s",
		__( 'نام', 'saayeh' ),
		$name,
		__( 'راه ارتباطی', 'saayeh' ),
		$contact,
		$message
	);
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $contact ) ) {
		$headers[] = 'Reply-To: ' . $name . ' <' . $contact . '>';
	}
	$sent = wp_mail( $to, $mail_subject, $body, $headers );

	do_action( 'saayeh_contact_submitted', compact( 'name', 'contact', 'subject', 'message' ), $sent );

	wp_safe_redirect( add_query_arg( 'saayeh_contact', $sent ? 'sent' : 'failed', $redirect ) );
	exit;
}
add_action( 'admin_post_saayeh_contact', 'saayeh_contact_submit' );
add_action( 'admin_post_nopriv_saayeh_contact', 'saayeh_contact_submit' );

/* -------------------------------------------------------------------------
 * Newsletter
 * ---------------------------------------------------------------------- */

/**
 * Newsletter form markup.
 */
function saayeh_newsletter_form() {
	?>
	<form class="x-news__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-saayeh-newsletter novalidate>
		<input type="hidden" name="action" value="saayeh_newsletter">
		<?php wp_nonce_field( 'saayeh', 'nonce', false ); ?>
		<label for="x-news-email" class="screen-reader-text"><?php esc_html_e( 'ایمیل', 'saayeh' ); ?></label>
		<input class="x-input" type="email" id="x-news-email" name="email" placeholder="<?php esc_attr_e( 'ایمیل شما', 'saayeh' ); ?>" dir="ltr" autocomplete="email" required>
		<button class="x-btn" type="submit"><?php esc_html_e( 'عضویت', 'saayeh' ); ?></button>
	</form>
	<p class="x-err x-news__msg" aria-live="polite"><?php
	if ( isset( $_GET['saayeh_news'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo 'ok' === $_GET['saayeh_news'] ? esc_html__( 'عضویت شما ثبت شد. ممنون!', 'saayeh' ) : esc_html__( 'لطفاً یک ایمیل معتبر وارد کنید.', 'saayeh' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	?></p>
	<?php
}

/**
 * Store a subscriber.
 *
 * @param string $email Email.
 * @return bool|string True on success, 'exists' when already subscribed, false when invalid.
 */
function saayeh_newsletter_add( $email ) {
	$email = sanitize_email( $email );
	if ( ! is_email( $email ) ) {
		return false;
	}
	$list = get_option( 'saayeh_newsletter_subscribers', array() );
	$list = is_array( $list ) ? $list : array();
	if ( isset( $list[ strtolower( $email ) ] ) ) {
		return 'exists';
	}
	$list[ strtolower( $email ) ] = time();
	update_option( 'saayeh_newsletter_subscribers', $list, false );
	do_action( 'saayeh_newsletter_subscribed', $email );
	return true;
}

/**
 * AJAX newsletter.
 */
function saayeh_ajax_newsletter() {
	check_ajax_referer( 'saayeh', 'nonce' );
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$res   = saayeh_newsletter_add( $email );
	if ( false === $res ) {
		wp_send_json_error( array( 'message' => __( 'لطفاً یک ایمیل معتبر وارد کنید.', 'saayeh' ) ) );
	}
	wp_send_json_success(
		array(
			'message' => 'exists' === $res ? __( 'این ایمیل قبلاً عضو خبرنامه شده است.', 'saayeh' ) : __( 'عضویت شما ثبت شد. ممنون!', 'saayeh' ),
		)
	);
}
add_action( 'wp_ajax_saayeh_newsletter', 'saayeh_ajax_newsletter' );
add_action( 'wp_ajax_nopriv_saayeh_newsletter', 'saayeh_ajax_newsletter' );

/**
 * No-JS fallback for the newsletter form.
 */
function saayeh_post_newsletter() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$ok   = false;
	if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'saayeh' ) ) {
		$ok = (bool) saayeh_newsletter_add( isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '' );
	}
	wp_safe_redirect( add_query_arg( 'saayeh_news', $ok ? 'ok' : 'bad', $back ) . '#x-newsletter' );
	exit;
}
add_action( 'admin_post_saayeh_newsletter', 'saayeh_post_newsletter' );
add_action( 'admin_post_nopriv_saayeh_newsletter', 'saayeh_post_newsletter' );

/* -------------------------------------------------------------------------
 * Live search
 * ---------------------------------------------------------------------- */

/**
 * AJAX live search (products when WooCommerce is active, otherwise posts).
 */
function saayeh_ajax_live_search() {
	check_ajax_referer( 'saayeh', 'nonce' );
	$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	if ( mb_strlen( $q ) < 2 ) {
		wp_send_json_success( array( 'items' => array() ) );
	}
	$is_wc = saayeh_is_wc();
	$args  = array(
		'post_type'      => $is_wc ? 'product' : array( 'post', 'page' ),
		'post_status'    => 'publish',
		's'              => $q,
		'posts_per_page' => 6,
		'no_found_rows'  => false,
	);
	if ( $is_wc ) {
		$args['tax_query'] = WC()->query->get_tax_query(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- respects catalog visibility.
	}
	$query = new WP_Query( apply_filters( 'saayeh_live_search_args', $args, $q ) );
	$items = array();
	foreach ( $query->posts as $post ) {
		$item = array(
			'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'url'   => get_permalink( $post ),
			'img'   => get_the_post_thumbnail_url( $post, 'thumbnail' ),
			'price' => '',
		);
		if ( $is_wc ) {
			$product = wc_get_product( $post );
			if ( $product ) {
				$item['price'] = wp_kses_post( $product->get_price_html() );
			}
		}
		$items[] = $item;
	}
	$all = $is_wc ? add_query_arg( array( 's' => $q, 'post_type' => 'product' ), home_url( '/' ) ) : add_query_arg( 's', $q, home_url( '/' ) );
	wp_send_json_success(
		array(
			'items' => $items,
			'total' => (int) $query->found_posts,
			'all'   => $all,
		)
	);
}
add_action( 'wp_ajax_saayeh_live_search', 'saayeh_ajax_live_search' );
add_action( 'wp_ajax_nopriv_saayeh_live_search', 'saayeh_ajax_live_search' );
