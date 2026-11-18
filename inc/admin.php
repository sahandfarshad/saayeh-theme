<?php
/**
 * Admin screens: newsletter subscribers list, setup notices.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the subscribers page under Appearance.
 */
function saayeh_admin_menu() {
	add_theme_page(
		__( 'مشترکین خبرنامه', 'saayeh' ),
		__( 'مشترکین خبرنامه', 'saayeh' ),
		'manage_options',
		'saayeh-subscribers',
		'saayeh_subscribers_page'
	);
}
add_action( 'admin_menu', 'saayeh_admin_menu' );

/**
 * CSV export of subscribers.
 */
function saayeh_subscribers_export() {
	if ( ! isset( $_GET['page'], $_GET['saayeh_export'] ) || 'saayeh-subscribers' !== $_GET['page'] ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی ندارید.', 'saayeh' ) );
	}
	check_admin_referer( 'saayeh_export' );
	$list = get_option( 'saayeh_newsletter_subscribers', array() );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=saayeh-subscribers.csv' );
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'email', 'subscribed_at' ) );
	foreach ( (array) $list as $email => $time ) {
		fputcsv( $out, array( $email, gmdate( 'c', (int) $time ) ) );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_init', 'saayeh_subscribers_export' );

/**
 * Subscribers page.
 */
function saayeh_subscribers_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['saayeh_remove'] ) && check_admin_referer( 'saayeh_remove_sub' ) ) {
		$list = (array) get_option( 'saayeh_newsletter_subscribers', array() );
		unset( $list[ strtolower( sanitize_email( wp_unslash( $_POST['saayeh_remove'] ) ) ) ] );
		update_option( 'saayeh_newsletter_subscribers', $list, false );
	}
	$list = (array) get_option( 'saayeh_newsletter_subscribers', array() );
	arsort( $list );
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'مشترکین خبرنامه', 'saayeh' ); ?></h1>
		<a class="page-title-action" href="<?php echo esc_url( wp_nonce_url( admin_url( 'themes.php?page=saayeh-subscribers&saayeh_export=1' ), 'saayeh_export' ) ); ?>"><?php esc_html_e( 'خروجی CSV', 'saayeh' ); ?></a>
		<hr class="wp-header-end">
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'ایمیل', 'saayeh' ); ?></th><th><?php esc_html_e( 'تاریخ عضویت', 'saayeh' ); ?></th><th></th></tr></thead>
			<tbody>
			<?php if ( ! $list ) : ?>
				<tr><td colspan="3"><?php esc_html_e( 'هنوز کسی عضو نشده است.', 'saayeh' ); ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $list as $email => $time ) : ?>
				<tr>
					<td dir="ltr"><?php echo esc_html( $email ); ?></td>
					<td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' H:i', (int) $time ) ); ?></td>
					<td>
						<form method="post" style="margin:0">
							<?php wp_nonce_field( 'saayeh_remove_sub' ); ?>
							<button class="button-link-delete button-link" name="saayeh_remove" value="<?php echo esc_attr( $email ); ?>"><?php esc_html_e( 'حذف', 'saayeh' ); ?></button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Notices: WooCommerce missing; demo importer suggestion.
 */
function saayeh_admin_notices() {
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'themes', 'plugins' ), true ) ) {
		return;
	}
	if ( ! saayeh_is_wc() ) {
		printf(
			'<div class="notice notice-warning"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
			esc_html__( 'پوسته سایه برای فروشگاه به افزونه ووکامرس نیاز دارد.', 'saayeh' ),
			esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ),
			esc_html__( 'نصب ووکامرس', 'saayeh' )
		);
	}
	if ( ! function_exists( 'sdi_init' ) && ! defined( 'SDI_VERSION' ) && ! get_user_meta( get_current_user_id(), 'saayeh_dismiss_demo_notice', true ) ) {
		printf(
			'<div class="notice notice-info"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
			esc_html__( 'برای راه‌اندازی سریع، افزونه «درون‌ریزی دمو سایه» را نصب کنید تا محصولات، صفحات و تنظیمات نمونه با یک کلیک ساخته شوند.', 'saayeh' ),
			esc_url( wp_nonce_url( add_query_arg( 'saayeh_dismiss_demo', 1 ), 'saayeh_dismiss_demo' ) ),
			esc_html__( 'دیگر نشان نده', 'saayeh' )
		);
	}
}
add_action( 'admin_notices', 'saayeh_admin_notices' );

/**
 * Dismiss the demo notice.
 */
function saayeh_dismiss_demo_notice() {
	if ( isset( $_GET['saayeh_dismiss_demo'], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'saayeh_dismiss_demo' ) ) {
		update_user_meta( get_current_user_id(), 'saayeh_dismiss_demo_notice', 1 );
	}
}
add_action( 'admin_init', 'saayeh_dismiss_demo_notice' );
