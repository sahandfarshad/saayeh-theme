<?php
/**
 * Template Name: تمام‌عرض
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main x-wrap x-page" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="x-page__head"><h1 class="x-h1"><?php the_title(); ?></h1></header>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
