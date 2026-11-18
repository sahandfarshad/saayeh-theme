<?php
/**
 * Product card in loops.
 *
 * This template overrides woocommerce/templates/content-product.php.
 *
 * @package Saayeh
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

$saayeh_gallery = $product->get_gallery_image_ids();
$saayeh_name    = $product->get_name();
$saayeh_sub     = saayeh_product_subline( $product );
?>
<li <?php wc_product_class( 'x-card', $product ); ?>>
	<?php echo saayeh_heart_button( $product->get_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
	<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="x-card__img woocommerce-LoopProduct-link" tabindex="-1" aria-hidden="true">
		<?php
		echo wp_kses_post(
			$product->get_image(
				'woocommerce_thumbnail',
				array(
					'alt'     => $saayeh_name . ( $saayeh_sub ? '، ' . $saayeh_sub : '' ),
					'loading' => 'lazy',
				)
			)
		);
		if ( $saayeh_gallery ) {
			echo wp_get_attachment_image( $saayeh_gallery[0], 'woocommerce_thumbnail', false, array( 'class' => 'x-card__alt', 'alt' => '', 'loading' => 'lazy' ) );
		}
		echo saayeh_product_badge_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
		?>
	</a>
	<div class="x-card__row">
		<div>
			<h3 class="woocommerce-loop-product__title"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $saayeh_name ); ?></a></h3>
			<?php if ( $saayeh_sub ) : ?>
				<p class="x-muted"><?php echo esc_html( $saayeh_sub ); ?></p>
			<?php endif; ?>
		</div>
		<p class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
	</div>
	<?php
	saayeh_quick_add( $product );
	do_action( 'saayeh_product_card_after', $product );
	?>
</li>
