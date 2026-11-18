<?php
/**
 * Single post.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main x-wrap" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'x-article' ); ?>>
			<header class="x-article__head">
				<?php saayeh_post_meta(); ?>
				<h1 class="x-h1"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="x-muted" style="font-size:17px;max-width:56ch;margin:0 auto"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="x-article__img"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?></figure>
			<?php endif; ?>
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<nav class="nav-links" aria-label="' . esc_attr__( 'صفحات نوشته', 'saayeh' ) . '">',
						'after'  => '</nav>',
					)
				);
				?>
			</div>
			<?php
			$saayeh_tags = get_the_tags();
			if ( $saayeh_tags ) :
				?>
				<div class="x-tags">
					<?php foreach ( $saayeh_tags as $saayeh_tag ) : ?>
						<a class="x-chip" href="<?php echo esc_url( get_tag_link( $saayeh_tag ) ); ?>">#<?php echo esc_html( $saayeh_tag->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php
			the_post_navigation(
				array(
					'prev_text' => '<small>' . esc_html__( 'نوشته قبلی', 'saayeh' ) . '</small><span>%title</span>',
					'next_text' => '<small>' . esc_html__( 'نوشته بعدی', 'saayeh' ) . '</small><span>%title</span>',
				)
			);
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
