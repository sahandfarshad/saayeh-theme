<?php
/**
 * Checkout form with stepper visuals (single-page, fully functional WooCommerce checkout).
 *
 * This template overrides woocommerce/templates/checkout/form-checkout.php.
 *
 * @package Saayeh
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="x-co-top">
	<div>
		<?php saayeh_checkout_stepper(); ?>
		<?php do_action( 'woocommerce_before_checkout_form', $checkout ); ?>
	</div>
	<div></div>
</div>
<?php
// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'برای تسویه حساب باید وارد حساب کاربری خود شوید.', 'saayeh' ) ) );
	return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'تسویه حساب', 'saayeh' ); ?>" novalidate>
	<div class="x-co">
		<div class="x-co__main">
			<?php if ( $checkout->get_checkout_fields() ) : ?>

				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<div class="col2-set" id="customer_details">
					<div class="col-1">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>

					<div class="col-2">
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>
				</div>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

			<?php endif; ?>
		</div>

		<aside class="x-co__aside">
			<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

			<h3 id="order_review_heading"><?php esc_html_e( 'خلاصه سفارش', 'saayeh' ); ?></h3>

			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
		</aside>
	</div>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
