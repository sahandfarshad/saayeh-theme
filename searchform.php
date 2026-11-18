<?php
/**
 * Search form.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

$saayeh_sid = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="x-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $saayeh_sid ); ?>"><?php esc_html_e( 'جستجو برای:', 'saayeh' ); ?></label>
	<input id="<?php echo esc_attr( $saayeh_sid ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'جستجو…', 'saayeh' ); ?>">
	<span class="x-search__ico"><?php saayeh_the_icon( 'search', 16 ); ?></span>
	<button type="submit"><?php esc_html_e( 'جستجو', 'saayeh' ); ?></button>
</form>
