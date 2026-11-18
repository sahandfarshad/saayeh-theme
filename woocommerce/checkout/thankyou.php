<?php
/**
 * Thank-you page.
 *
 * This template overrides woocommerce/templates/checkout/thankyou.php.
 *
 * @package Saayeh
 * @version 11.2.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-order x-done">

	<?php
	if ( $order ) :

		do_action( 'woocommerce_before_thankyou', $order->get_id() );
		?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<svg class="x-check x-check--fail" viewBox="0 0 84 84" fill="none" stroke="var(--x-red)" stroke-width="2" aria-hidden="true"><circle cx="42" cy="42" r="38"/><path d="M30 30l24 24M54 30L30 54" stroke-linecap="round"/></svg>
			<h1 class="x-h2"><?php esc_html_e( 'پرداخت انجام نشد', 'saayeh' ); ?></h1>
			<p class="x-muted woocommerce-thankyou-order-failed">
				<?php
				$saayeh_failed_text = apply_filters( 'woocommerce_thankyou_order_failed_text', __( 'متأسفانه بانک یا درگاه پرداخت این تراکنش را رد کرد. لطفاً دوباره تلاش کنید.', 'saayeh' ), $order );
				echo wp_kses_post( is_string( $saayeh_failed_text ) ? $saayeh_failed_text : '' );
				?>
			</p>
			<p class="x-done__actions woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="x-btn pay"><?php esc_html_e( 'پرداخت دوباره', 'saayeh' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="x-btn x-btn--ghost"><?php esc_html_e( 'حساب کاربری', 'saayeh' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<svg class="x-check" viewBox="0 0 84 84" fill="none" stroke="var(--x-ink)" stroke-width="2" aria-hidden="true"><circle cx="42" cy="42" r="38"/><path d="M27 43l10 10 20-22" stroke-linecap="round" stroke-linejoin="round"/></svg>
			<h1 class="x-h2"><?php esc_html_e( 'سفارش شما ثبت شد', 'saayeh' ); ?></h1>
			<p class="x-muted">
				<?php esc_html_e( 'شماره سفارش:', 'saayeh' ); ?>
				<b style="color:var(--x-ink)"><?php echo esc_html( saayeh_use_fa_digits() ? saayeh_fa_digits( $order->get_order_number() ) : $order->get_order_number() ); ?></b>
			</p>

			<div class="x-receipt">
				<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
					<li class="woocommerce-order-overview__date date">
						<?php esc_html_e( 'تاریخ', 'saayeh' ); ?>
						<strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
					</li>
					<li class="woocommerce-order-overview__total total">
						<?php esc_html_e( 'مبلغ کل', 'saayeh' ); ?>
						<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
					</li>
					<?php if ( $order->get_payment_method_title() ) : ?>
						<li class="woocommerce-order-overview__payment-method method">
							<?php esc_html_e( 'روش پرداخت', 'saayeh' ); ?>
							<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
						</li>
					<?php endif; ?>
					<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
						<li class="woocommerce-order-overview__email email">
							<?php esc_html_e( 'ایمیل', 'saayeh' ); ?>
							<strong dir="ltr"><?php echo esc_html( $order->get_billing_email() ); ?></strong>
						</li>
					<?php endif; ?>
				</ul>

				<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
				<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
			</div>

			<div class="x-done__actions">
				<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() ) : ?>
					<a class="x-btn x-btn--ghost" href="<?php echo esc_url( $order->get_view_order_url() ); ?>"><?php esc_html_e( 'پیگیری سفارش', 'saayeh' ); ?></a>
				<?php endif; ?>
				<a class="x-btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'بازگشت به فروشگاه', 'saayeh' ); ?></a>
			</div>

		<?php endif; ?>

	<?php else : ?>

		<svg class="x-check" viewBox="0 0 84 84" fill="none" stroke="var(--x-ink)" stroke-width="2" aria-hidden="true"><circle cx="42" cy="42" r="38"/><path d="M27 43l10 10 20-22" stroke-linecap="round" stroke-linejoin="round"/></svg>
		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>

</div>
