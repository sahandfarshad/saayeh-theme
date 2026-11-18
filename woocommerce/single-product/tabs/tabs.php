<?php
/**
 * Product tabs rendered as an accordion (native <details>, no JS needed).
 *
 * This template overrides woocommerce/templates/single-product/tabs/tabs.php.
 * Note: classes `wc-tabs`, `woocommerce-tabs` and `panel` are intentionally not
 * used so WooCommerce's tab script does not hide the accordion panels.
 *
 * @package Saayeh
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $product_tabs ) ) : ?>
	<div class="x-acc">
		<?php
		$saayeh_first = true;
		foreach ( $product_tabs as $key => $product_tab ) :
			?>
			<details id="tab-<?php echo esc_attr( $key ); ?>" <?php echo $saayeh_first ? 'open' : ''; ?>>
				<summary id="tab-title-<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?></summary>
				<div class="x-acc__body">
					<?php
					if ( isset( $product_tab['callback'] ) ) {
						call_user_func( $product_tab['callback'], $key, $product_tab );
					}
					?>
				</div>
			</details>
			<?php
			$saayeh_first = false;
		endforeach;
		?>
		<?php do_action( 'woocommerce_product_after_tabs' ); ?>
	</div>
<?php endif; ?>
