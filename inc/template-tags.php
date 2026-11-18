<?php
/**
 * Template tags used across templates.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Logo (custom logo or site name + tagline).
 *
 * @param bool $tagline Show tagline.
 */
function saayeh_logo( $tagline = true ) {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	$tag = 'p';
	printf(
		'<%1$s class="x-logo-wrap" style="margin:0"><a href="%2$s" class="x-logo" rel="home">%3$s%4$s</a></%1$s>',
		tag_escape( $tag ),
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) ),
		$tagline && saayeh_mod( 'saayeh_tagline' ) ? '<small>' . esc_html( saayeh_mod( 'saayeh_tagline' ) ) . '</small>' : ''
	);
}

/**
 * Cart count badge markup (also used as a WooCommerce fragment).
 *
 * @return string
 */
function saayeh_cart_count_html() {
	$count = ( saayeh_is_wc() && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
	return '<span class="x-count saayeh-cart-count" data-count="' . esc_attr( $count ) . '">' . esc_html( saayeh_use_fa_digits() ? saayeh_fa_digits( $count ) : $count ) . '</span>';
}

/**
 * Social links list.
 */
function saayeh_social_links() {
	$out = '';
	foreach ( saayeh_social_networks() as $key => $label ) {
		$url = saayeh_mod( 'saayeh_social_' . $key );
		if ( ! $url ) {
			continue;
		}
		$out .= sprintf(
			'<a href="%1$s" target="_blank" rel="noopener me" aria-label="%2$s">%3$s</a>',
			esc_url( $url ),
			esc_attr( $label ),
			wp_kses( saayeh_icon( $key, 18 ), saayeh_svg_kses() )
		);
	}
	if ( $out ) {
		echo '<div class="x-social">' . $out . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}

/**
 * Trust strip.
 */
function saayeh_trust_strip() {
	$items = '';
	for ( $i = 1; $i <= 4; $i++ ) {
		$t = saayeh_mod( "saayeh_trust_{$i}_title" );
		if ( ! $t ) {
			continue;
		}
		$items .= '<div><b>' . esc_html( $t ) . '</b><span>' . esc_html( saayeh_mod( "saayeh_trust_{$i}_text" ) ) . '</span></div>';
	}
	if ( $items ) {
		echo '<div class="x-trust">' . $items . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}

/**
 * Post meta line: date + category.
 */
function saayeh_post_meta() {
	$date = get_the_date();
	echo '<p class="x-meta"><time datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( saayeh_use_fa_digits() ? saayeh_fa_digits( $date ) : $date ) . '</time>';
	$cats = get_the_category();
	if ( $cats ) {
		echo '<span aria-hidden="true">·</span><a href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';
	}
	echo '</p>';
}

/**
 * Blog post card used in archives and on the front page.
 *
 * @param string $heading Heading tag.
 */
function saayeh_post_card( $heading = 'h2' ) {
	$heading = in_array( $heading, array( 'h2', 'h3' ), true ) ? $heading : 'h2';
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'x-post' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<a class="x-post__img" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
			</a>
		<?php endif; ?>
		<?php saayeh_post_meta(); ?>
		<<?php echo tag_escape( $heading ); ?>><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo tag_escape( $heading ); ?>>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?></p>
	</article>
	<?php
}

/**
 * Pagination for post archives.
 */
function saayeh_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => __( 'قبلی', 'saayeh' ),
			'next_text'          => __( 'بعدی', 'saayeh' ),
			'screen_reader_text' => __( 'صفحه‌بندی نوشته‌ها', 'saayeh' ),
		)
	);
}

/**
 * Persian digits for post/page pagination numbers.
 *
 * @param string $html Markup.
 * @return string
 */
function saayeh_fa_nav_digits( $html ) {
	return saayeh_use_fa_digits() ? saayeh_fa_digits_html( $html ) : $html;
}
add_filter( 'paginate_links_output', 'saayeh_fa_nav_digits' );

/**
 * Fallback for the primary menu when none is assigned.
 */
function saayeh_menu_fallback() {
	echo '<ul>';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'خانه', 'saayeh' ) . '</a></li>';
	if ( saayeh_is_wc() ) {
		echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'فروشگاه', 'saayeh' ) . '</a></li>';
	}
	$about = get_page_by_path( 'about' );
	if ( $about ) {
		echo '<li><a href="' . esc_url( get_permalink( $about ) ) . '">' . esc_html( get_the_title( $about ) ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Account URL.
 *
 * @return string
 */
function saayeh_account_url() {
	if ( saayeh_is_wc() ) {
		return wc_get_page_permalink( 'myaccount' );
	}
	return is_user_logged_in() ? admin_url( 'profile.php' ) : wp_login_url();
}
