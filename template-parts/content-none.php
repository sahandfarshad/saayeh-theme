<?php
/**
 * Nothing found.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="x-empty">
	<p class="x-empty__t"><?php esc_html_e( 'چیزی پیدا نشد.', 'saayeh' ); ?></p>
	<p><?php esc_html_e( 'عبارت دیگری را جستجو کنید.', 'saayeh' ); ?></p>
	<?php get_search_form(); ?>
</div>
