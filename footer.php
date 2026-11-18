<?php
/**
 * Site footer, cart drawer, toast.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="x-foot" id="colophon">
	<div class="x-wrap">
		<div class="x-foot__grid">
			<div>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="x-logo" rel="home"><?php bloginfo( 'name' ); ?></a>
				<?php if ( saayeh_mod( 'saayeh_footer_about' ) ) : ?>
					<p style="margin:6px 0 0;max-width:36ch"><?php echo esc_html( saayeh_mod( 'saayeh_footer_about' ) ); ?></p>
				<?php endif; ?>
				<?php if ( saayeh_mod( 'saayeh_contact_phone' ) ) : ?>
					<p style="margin:12px 0 0"><?php esc_html_e( 'پشتیبانی:', 'saayeh' ); ?> <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', saayeh_en_digits( saayeh_mod( 'saayeh_contact_phone' ) ) ) ); ?>" dir="ltr"><?php echo esc_html( saayeh_mod( 'saayeh_contact_phone' ) ); ?></a></p>
				<?php endif; ?>
				<?php saayeh_social_links(); ?>
			</div>

			<div>
				<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
					<?php dynamic_sidebar( 'footer-1' ); ?>
				<?php elseif ( has_nav_menu( 'footer' ) ) : ?>
					<nav aria-label="<?php esc_attr_e( 'منوی فوتر', 'saayeh' ); ?>">
						<h2><?php echo esc_html( wp_get_nav_menu_name( 'footer' ) ); ?></h2>
						<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'depth' => 1 ) ); ?>
					</nav>
				<?php endif; ?>
			</div>

			<div>
				<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
					<?php dynamic_sidebar( 'footer-2' ); ?>
				<?php elseif ( has_nav_menu( 'footer2' ) ) : ?>
					<nav aria-label="<?php esc_attr_e( 'منوی فوتر دوم', 'saayeh' ); ?>">
						<h2><?php echo esc_html( wp_get_nav_menu_name( 'footer2' ) ); ?></h2>
						<?php wp_nav_menu( array( 'theme_location' => 'footer2', 'container' => false, 'depth' => 1 ) ); ?>
					</nav>
				<?php endif; ?>
			</div>

			<div>
				<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
					<?php dynamic_sidebar( 'footer-3' ); ?>
				<?php endif; ?>
				<h2><?php esc_html_e( 'نمادهای اعتماد', 'saayeh' ); ?></h2>
				<div class="x-trustbadges">
					<?php
					$saayeh_badges = saayeh_mod( 'saayeh_trust_badges_html' );
					if ( $saayeh_badges ) {
						echo saayeh_sanitize_badges( $saayeh_badges ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized with wp_kses.
					} else {
						echo '<span class="x-trustbadge-ph">' . esc_html__( 'جای نماد اعتماد الکترونیکی (اینماد)', 'saayeh' ) . '</span>';
						echo '<span class="x-trustbadge-ph">' . esc_html__( 'جای نشان ساماندهی', 'saayeh' ) . '</span>';
					}
					?>
				</div>
			</div>
		</div>

		<div class="x-foot__bottom">
			<span>
				<?php
				$saayeh_copy = saayeh_mod( 'saayeh_copyright' );
				if ( $saayeh_copy ) {
					echo esc_html( $saayeh_copy );
				} else {
					/* translators: 1: year, 2: site name */
					echo esc_html( sprintf( __( '© %1$s %2$s. همه‌ی حقوق محفوظ است.', 'saayeh' ), saayeh_fa_digits( wp_date( 'Y' ) ), get_bloginfo( 'name' ) ) );
				}
				?>
			</span>
			<?php if ( has_nav_menu( 'legal' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'پیوندهای پایین صفحه', 'saayeh' ); ?>">
					<?php wp_nav_menu( array( 'theme_location' => 'legal', 'container' => false, 'depth' => 1 ) ); ?>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php if ( saayeh_is_wc() && ! is_cart() && ! is_checkout() ) : ?>
	<div class="x-scrim" id="x-scrim"></div>
	<aside class="x-drawer" id="x-drawer" aria-label="<?php esc_attr_e( 'سبد خرید', 'saayeh' ); ?>" aria-hidden="true" role="dialog" aria-modal="true" tabindex="-1">
		<div class="x-drawer__head">
			<h2><?php esc_html_e( 'سبد خرید', 'saayeh' ); ?></h2>
			<button type="button" class="x-icon" id="x-close" aria-label="<?php esc_attr_e( 'بستن', 'saayeh' ); ?>"><?php saayeh_the_icon( 'close', 18 ); ?></button>
		</div>
		<div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
	</aside>
<?php endif; ?>

<div class="x-toast" id="x-toast" role="status" aria-live="polite"><span id="x-toast-t"></span><button type="button" id="x-toast-b"><?php esc_html_e( 'مشاهده سبد', 'saayeh' ); ?></button></div>

<?php wp_footer(); ?>
</body>
</html>
