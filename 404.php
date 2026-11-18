<?php
/**
 * 404 page.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main x-wrap x-404" tabindex="-1">
	<p class="x-h1" aria-hidden="true"><?php echo esc_html( saayeh_fa_digits( '404' ) ); ?></p>
	<h1 class="x-h2"><?php esc_html_e( 'این صفحه پیدا نشد', 'saayeh' ); ?></h1>
	<p class="x-muted"><?php esc_html_e( 'شاید آدرس اشتباه است یا صفحه جابه‌جا شده. از جستجو کمک بگیرید.', 'saayeh' ); ?></p>
	<?php get_search_form(); ?>
	<p>
		<a class="x-btn" href="<?php echo esc_url( saayeh_is_wc() ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'رفتن به فروشگاه', 'saayeh' ); ?></a>
	</p>
</main>
<?php
get_footer();
