<?php
/**
 * Front page: hero, categories, new products, editorial split, featured, trust strip, newsletter, latest posts.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

get_header();

$saayeh_shop_url = saayeh_is_wc() ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$saayeh_cta_link = saayeh_mod( 'saayeh_hero_cta_link' ) ? saayeh_mod( 'saayeh_hero_cta_link' ) : $saayeh_shop_url;
$saayeh_hero_img = saayeh_mod_image_url( 'saayeh_hero_image', 'hero.jpg' );
?>
<main id="main" class="site-main" tabindex="-1">

	<section class="x-hero" aria-labelledby="x-hero-title">
		<?php if ( $saayeh_hero_img ) : ?>
			<?php
			$saayeh_hero_id = absint( get_theme_mod( 'saayeh_hero_image' ) );
			if ( $saayeh_hero_id && wp_attachment_is_image( $saayeh_hero_id ) ) {
				echo wp_get_attachment_image( $saayeh_hero_id, 'full', false, array( 'alt' => '', 'fetchpriority' => 'high', 'loading' => 'eager', 'sizes' => '100vw' ) );
			} else {
				echo '<img src="' . esc_url( $saayeh_hero_img ) . '" alt="" fetchpriority="high">';
			}
			?>
		<?php endif; ?>
		<div class="x-wrap x-hero__copy">
			<div class="x-hero__box">
				<?php if ( saayeh_mod( 'saayeh_hero_eyebrow' ) ) : ?>
					<p class="x-eyebrow"><?php echo esc_html( saayeh_mod( 'saayeh_hero_eyebrow' ) ); ?></p>
				<?php endif; ?>
				<h1 id="x-hero-title"><?php echo esc_html( saayeh_mod( 'saayeh_hero_title' ) ); ?></h1>
				<?php if ( saayeh_mod( 'saayeh_hero_subtitle' ) ) : ?>
					<p><?php echo esc_html( saayeh_mod( 'saayeh_hero_subtitle' ) ); ?></p>
				<?php endif; ?>
				<?php if ( saayeh_mod( 'saayeh_hero_cta_text' ) ) : ?>
					<a href="<?php echo esc_url( $saayeh_cta_link ); ?>" class="x-btn x-btn--light"><?php echo esc_html( saayeh_mod( 'saayeh_hero_cta_text' ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php
	// Optional page content of the static front page.
	if ( 'page' === get_option( 'show_on_front' ) ) {
		while ( have_posts() ) {
			the_post();
			if ( '' !== trim( get_the_content() ) ) {
				echo '<section class="x-wrap x-sec" style="padding-bottom:0"><div class="entry-content">';
				the_content();
				echo '</div></section>';
			}
		}
	}
	?>

	<?php
	if ( saayeh_is_wc() && saayeh_mod( 'saayeh_home_show_cats' ) ) :
		$saayeh_cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'number'     => 5,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
				'orderby'    => 'term_order',
			)
		);
		if ( ! is_wp_error( $saayeh_cats ) && $saayeh_cats ) :
			?>
			<section class="x-wrap x-sec" style="padding-bottom:0" aria-labelledby="x-cats-t">
				<div class="x-sec__head">
					<div>
						<p class="x-eyebrow"><?php esc_html_e( 'دسته‌بندی‌ها', 'saayeh' ); ?></p>
						<h2 class="x-h2" id="x-cats-t"><?php esc_html_e( 'برای هر روز، یک انتخاب درست', 'saayeh' ); ?></h2>
					</div>
					<a class="x-link" href="<?php echo esc_url( $saayeh_shop_url ); ?>"><?php esc_html_e( 'همه محصولات', 'saayeh' ); ?></a>
				</div>
				<div class="x-tiles">
					<?php foreach ( $saayeh_cats as $saayeh_cat ) : ?>
						<?php
						$saayeh_thumb = (int) get_term_meta( $saayeh_cat->term_id, 'thumbnail_id', true );
						if ( ! $saayeh_thumb ) {
							$saayeh_first = wc_get_products( array( 'category' => array( $saayeh_cat->slug ), 'limit' => 1, 'return' => 'ids', 'status' => 'publish' ) );
							$saayeh_thumb = $saayeh_first ? (int) get_post_thumbnail_id( $saayeh_first[0] ) : 0;
						}
						?>
						<a class="x-tile" href="<?php echo esc_url( get_term_link( $saayeh_cat ) ); ?>">
							<span class="x-tile__img"><?php echo $saayeh_thumb ? wp_get_attachment_image( $saayeh_thumb, 'woocommerce_thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ) : ''; ?></span>
							<span><?php echo esc_html( $saayeh_cat->name ); ?> <small><?php echo esc_html( saayeh_num( $saayeh_cat->count ) ); ?></small></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
			<?php
		endif;
	endif;
	?>

	<?php
	if ( saayeh_is_wc() ) :
		$saayeh_new = wc_get_products(
			array(
				'status'     => 'publish',
				'limit'      => max( 1, (int) saayeh_mod( 'saayeh_home_new_count' ) ),
				'orderby'    => 'date',
				'order'      => 'DESC',
				'visibility' => 'catalog',
			)
		);
		if ( $saayeh_new ) :
			?>
			<section class="x-wrap x-sec" aria-labelledby="x-new-t">
				<div class="x-sec__head">
					<div>
						<p class="x-eyebrow"><?php esc_html_e( 'تازه‌ها', 'saayeh' ); ?></p>
						<h2 class="x-h2" id="x-new-t"><?php esc_html_e( 'تازه‌های این فصل', 'saayeh' ); ?></h2>
					</div>
					<a class="x-link" href="<?php echo esc_url( add_query_arg( 'orderby', 'date', $saayeh_shop_url ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'saayeh' ); ?></a>
				</div>
				<?php saayeh_products_grid( $saayeh_new ); ?>
			</section>
			<?php
		endif;
	endif;
	?>

	<?php
	$saayeh_ed_img  = saayeh_mod_image_url( 'saayeh_editorial_image', 'p-knit.jpg', 'large' );
	$saayeh_ed_link = saayeh_mod( 'saayeh_editorial_cta_link' ) ? saayeh_mod( 'saayeh_editorial_cta_link' ) : $saayeh_shop_url;
	if ( saayeh_mod( 'saayeh_editorial_title' ) ) :
		?>
		<section class="x-split" aria-labelledby="x-ed-t">
			<?php if ( $saayeh_ed_img ) : ?>
				<img src="<?php echo esc_url( $saayeh_ed_img ); ?>" alt="" loading="lazy">
			<?php endif; ?>
			<div class="x-split__copy">
				<?php if ( saayeh_mod( 'saayeh_editorial_eyebrow' ) ) : ?>
					<p class="x-eyebrow"><?php echo esc_html( saayeh_mod( 'saayeh_editorial_eyebrow' ) ); ?></p>
				<?php endif; ?>
				<h2 class="x-h2" id="x-ed-t"><?php echo esc_html( saayeh_mod( 'saayeh_editorial_title' ) ); ?></h2>
				<p><?php echo esc_html( saayeh_mod( 'saayeh_editorial_text' ) ); ?></p>
				<?php if ( saayeh_mod( 'saayeh_editorial_cta_text' ) ) : ?>
					<div><a class="x-btn x-btn--ghost" href="<?php echo esc_url( $saayeh_ed_link ); ?>"><?php echo esc_html( saayeh_mod( 'saayeh_editorial_cta_text' ) ); ?></a></div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	if ( saayeh_is_wc() && saayeh_mod( 'saayeh_home_show_featured' ) ) :
		$saayeh_feat = wc_get_products(
			array(
				'status'     => 'publish',
				'limit'      => 4,
				'featured'   => true,
				'visibility' => 'catalog',
			)
		);
		if ( $saayeh_feat ) :
			?>
			<section class="x-wrap x-sec" aria-labelledby="x-feat-t">
				<div class="x-sec__head">
					<div>
						<p class="x-eyebrow"><?php esc_html_e( 'منتخب', 'saayeh' ); ?></p>
						<h2 class="x-h2" id="x-feat-t"><?php esc_html_e( 'انتخاب‌های ماندگار', 'saayeh' ); ?></h2>
					</div>
				</div>
				<?php saayeh_products_grid( $saayeh_feat ); ?>
			</section>
			<?php
		endif;
	endif;
	?>

	<section class="x-wrap x-sec" style="padding-top:0" aria-label="<?php esc_attr_e( 'مزایای خرید', 'saayeh' ); ?>">
		<?php saayeh_trust_strip(); ?>
	</section>

	<?php
	if ( saayeh_mod( 'saayeh_home_show_posts' ) ) :
		$saayeh_posts = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 3,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( $saayeh_posts->have_posts() ) :
			$saayeh_blog = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : '';
			?>
			<section class="x-wrap x-sec" style="padding-top:0" aria-labelledby="x-blog-t">
				<div class="x-sec__head">
					<div>
						<p class="x-eyebrow"><?php esc_html_e( 'مجله', 'saayeh' ); ?></p>
						<h2 class="x-h2" id="x-blog-t"><?php esc_html_e( 'خواندنی‌های سایه', 'saayeh' ); ?></h2>
					</div>
					<?php if ( $saayeh_blog ) : ?>
						<a class="x-link" href="<?php echo esc_url( $saayeh_blog ); ?>"><?php esc_html_e( 'همه نوشته‌ها', 'saayeh' ); ?></a>
					<?php endif; ?>
				</div>
				<div class="x-posts">
					<?php
					while ( $saayeh_posts->have_posts() ) {
						$saayeh_posts->the_post();
						saayeh_post_card( 'h3' );
					}
					wp_reset_postdata();
					?>
				</div>
			</section>
			<?php
		endif;
	endif;
	?>

	<section class="x-wrap x-sec" style="padding-top:0" id="x-newsletter" aria-labelledby="x-news-t">
		<div class="x-news">
			<p class="x-eyebrow"><?php esc_html_e( 'خبرنامه', 'saayeh' ); ?></p>
			<h2 class="x-h2" id="x-news-t"><?php echo esc_html( saayeh_mod( 'saayeh_newsletter_title' ) ); ?></h2>
			<p class="x-muted"><?php echo esc_html( saayeh_mod( 'saayeh_newsletter_text' ) ); ?></p>
			<?php saayeh_newsletter_form(); ?>
		</div>
	</section>

</main>
<?php
get_footer();
