<?php
/**
 * Product archive (shop, categories, tags, product search).
 *
 * This template overrides woocommerce/templates/archive-product.php.
 *
 * @package Saayeh
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );

$saayeh_total = (int) wc_get_loop_prop( 'total' );
?>
<section class="x-sec x-sec--tight" style="padding-bottom:0">
	<header class="x-sec__head woocommerce-products-header">
		<div>
			<p class="x-eyebrow"><?php esc_html_e( 'فروشگاه', 'saayeh' ); ?></p>
			<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
				<h1 class="x-h2 woocommerce-products-header__title page-title">
					<?php
					if ( is_search() ) {
						/* translators: %s: query */
						echo esc_html( sprintf( __( 'نتایج «%s»', 'saayeh' ), get_search_query() ) );
					} elseif ( is_shop() ) {
						esc_html_e( 'همه محصولات', 'saayeh' );
					} else {
						woocommerce_page_title();
					}
					?>
				</h1>
			<?php endif; ?>
			<?php do_action( 'woocommerce_archive_description' ); ?>
		</div>
		<span class="x-muted">
			<?php
			/* translators: %s: number of products */
			echo esc_html( sprintf( __( '%s محصول', 'saayeh' ), saayeh_num( $saayeh_total ) ) );
			?>
		</span>
	</header>

	<?php saayeh_shop_filters(); ?>

	<?php
	if ( woocommerce_product_loop() ) {
		do_action( 'woocommerce_before_shop_loop' );

		woocommerce_product_loop_start();

		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
			}
		}

		woocommerce_product_loop_end();

		do_action( 'woocommerce_after_shop_loop' );
	} else {
		do_action( 'woocommerce_no_products_found' );
	}
	?>
</section>
<?php
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
