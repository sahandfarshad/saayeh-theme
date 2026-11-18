<?php
/**
 * Site header.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#F6F3EE">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'رفتن به محتوای اصلی', 'saayeh' ); ?></a>

<?php
$saayeh_ann = saayeh_mod( 'saayeh_announcement' );
if ( $saayeh_ann ) :
	$saayeh_ann_link = saayeh_mod( 'saayeh_announcement_link' );
	?>
	<div class="x-ann" role="region" aria-label="<?php esc_attr_e( 'اطلاعیه', 'saayeh' ); ?>">
		<?php if ( $saayeh_ann_link ) : ?>
			<a href="<?php echo esc_url( $saayeh_ann_link ); ?>"><?php echo esc_html( $saayeh_ann ); ?></a>
		<?php else : ?>
			<?php echo esc_html( $saayeh_ann ); ?>
		<?php endif; ?>
	</div>
<?php endif; ?>

<header class="x-head" id="masthead">
	<div class="x-wrap x-head__in">
		<button type="button" class="x-icon x-burger" id="x-burger" aria-label="<?php esc_attr_e( 'منو', 'saayeh' ); ?>" aria-expanded="false" aria-controls="x-nav">
			<?php saayeh_the_icon( 'menu', 20 ); ?>
		</button>

		<?php saayeh_logo(); ?>

		<nav class="x-nav" id="x-nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'saayeh' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 2,
					'fallback_cb'    => 'saayeh_menu_fallback',
				)
			);
			?>
		</nav>

		<div class="x-tools">
			<form role="search" method="get" class="x-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-live-search>
				<label class="screen-reader-text" for="x-q"><?php esc_html_e( 'جستجوی محصولات', 'saayeh' ); ?></label>
				<input id="x-q" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'جستجو…', 'saayeh' ); ?>" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="x-live">
				<?php if ( saayeh_is_wc() ) : ?>
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>
				<span class="x-search__ico"><?php saayeh_the_icon( 'search', 16 ); ?></span>
				<button type="submit"><?php esc_html_e( 'جستجو', 'saayeh' ); ?></button>
				<div class="x-live" id="x-live" role="listbox" aria-label="<?php esc_attr_e( 'نتایج جستجو', 'saayeh' ); ?>"></div>
			</form>

			<?php if ( saayeh_is_wc() ) : ?>
				<?php $saayeh_wc = count( saayeh_wishlist_get() ); ?>
				<a class="x-icon" href="<?php echo esc_url( saayeh_wishlist_url() ); ?>" aria-label="<?php esc_attr_e( 'علاقه‌مندی‌ها', 'saayeh' ); ?>">
					<?php saayeh_the_icon( 'heart' ); ?>
					<span class="x-count saayeh-wish-count" data-count="<?php echo esc_attr( $saayeh_wc ); ?>"><?php echo esc_html( saayeh_use_fa_digits() ? saayeh_fa_digits( $saayeh_wc ) : $saayeh_wc ); ?></span>
				</a>
			<?php endif; ?>

			<a class="x-icon<?php echo is_user_logged_in() ? ' is-in' : ''; ?>" href="<?php echo esc_url( saayeh_account_url() ); ?>" aria-label="<?php echo is_user_logged_in() ? esc_attr__( 'حساب کاربری من', 'saayeh' ) : esc_attr__( 'ورود یا ثبت‌نام', 'saayeh' ); ?>">
				<?php saayeh_the_icon( 'user' ); ?>
			</a>

			<?php if ( saayeh_is_wc() ) : ?>
				<a class="x-icon" id="x-cart-btn" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'سبد خرید', 'saayeh' ); ?>" aria-controls="x-drawer" aria-expanded="false">
					<?php saayeh_the_icon( 'bag' ); ?>
					<?php echo saayeh_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>
