<?php
/**
 * Main template: blog index and fallback archive.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main x-wrap x-sec x-sec--tight" tabindex="-1">
	<header class="x-sec__head">
		<div>
			<p class="x-eyebrow"><?php esc_html_e( 'مجله', 'saayeh' ); ?></p>
			<h1 class="x-h2">
				<?php
				if ( is_home() && get_option( 'page_for_posts' ) ) {
					echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) );
				} elseif ( is_archive() ) {
					echo wp_kses_post( get_the_archive_title() );
				} else {
					esc_html_e( 'نوشته‌ها', 'saayeh' );
				}
				?>
			</h1>
			<?php the_archive_description( '<div class="x-muted" style="margin-top:8px">', '</div>' ); ?>
		</div>
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
