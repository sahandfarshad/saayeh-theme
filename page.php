<?php
/**
 * Page template. WooCommerce pages (cart, checkout, account) render full width.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

get_header();
$saayeh_is_wc_page = saayeh_is_wc() && ( is_cart() || is_checkout() || is_account_page() );
?>
<main id="main" class="site-main x-wrap x-page<?php echo $saayeh_is_wc_page ? '' : ' x-page--narrow'; ?>" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if ( ! ( saayeh_is_wc() && is_checkout() && ! empty( is_wc_endpoint_url( 'order-received' ) ) ) ) : ?>
				<header class="x-page__head">
					<?php if ( $saayeh_is_wc_page ) : ?>
						<p class="x-eyebrow"><?php echo esc_html( is_cart() ? __( 'سبد خرید', 'saayeh' ) : ( is_checkout() ? __( 'تسویه حساب', 'saayeh' ) : __( 'حساب کاربری', 'saayeh' ) ) ); ?></p>
					<?php endif; ?>
					<h1 class="x-h1"><?php the_title(); ?></h1>
				</header>
			<?php endif; ?>
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
			<?php
			if ( ! $saayeh_is_wc_page && ( comments_open() || get_comments_number() ) ) {
				comments_template();
			}
			?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
