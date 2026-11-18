<?php
/**
 * Single product content.
 *
 * This template overrides woocommerce/templates/content-single-product.php.
 *
 * @package Saayeh
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
	<div class="x-pd">
		<div class="x-pd__gallery">
			<?php echo saayeh_product_badge_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
			<?php do_action( 'woocommerce_before_single_product_summary' ); ?>
		</div>
		<div class="x-pd__info summary entry-summary">
			<?php do_action( 'woocommerce_single_product_summary' ); ?>
		</div>
	</div>
	<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
</div>
<?php do_action( 'woocommerce_after_single_product' ); ?>
