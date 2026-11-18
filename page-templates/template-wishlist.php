<?php
/**
 * Template Name: علاقه‌مندی‌ها
 *
 * Shows the visitor's wishlist (also available as the [saayeh_wishlist] shortcode).
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

get_header();
$saayeh_count = count( saayeh_wishlist_get() );
?>
<main id="main" class="site-main x-wrap x-sec x-sec--tight" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<div class="x-sec__head">
			<div>
				<p class="x-eyebrow"><?php esc_html_e( 'علاقه‌مندی‌ها', 'saayeh' ); ?></p>
				<h1 class="x-h2"><?php the_title(); ?></h1>
			</div>
			<span class="x-muted">
				<?php
				/* translators: %s: number of products */
				echo esc_html( sprintf( __( '%s محصول', 'saayeh' ), saayeh_num( $saayeh_count ) ) );
				?>
			</span>
		</div>
		<?php
		if ( has_shortcode( get_the_content(), 'saayeh_wishlist' ) ) {
			echo '<div class="entry-content">';
			the_content();
			echo '</div>';
		} else {
			echo saayeh_wishlist_shortcode(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
		}
	endwhile;
	?>
</main>
<?php
get_footer();
