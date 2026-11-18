<?php
/**
 * Search results (posts/pages). Product searches use the WooCommerce archive.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main x-wrap x-sec x-sec--tight" tabindex="-1">
	<header class="x-sec__head">
		<div>
			<p class="x-eyebrow"><?php esc_html_e( 'جستجو', 'saayeh' ); ?></p>
			<h1 class="x-h2">
				<?php
				/* translators: %s: query */
				echo esc_html( sprintf( __( 'نتایج «%s»', 'saayeh' ), get_search_query() ) );
				?>
			</h1>
		</div>
		<?php get_search_form(); ?>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="x-posts">
			<?php
			while ( have_posts() ) {
				the_post();
				saayeh_post_card();
			}
			?>
		</div>
		<?php saayeh_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
