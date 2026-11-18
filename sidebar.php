<?php
/**
 * Blog sidebar (only rendered when widgets exist).
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

if ( is_active_sidebar( 'blog' ) ) : ?>
	<aside class="widget-area" aria-label="<?php esc_attr_e( 'ابزارک‌ها', 'saayeh' ); ?>">
		<?php dynamic_sidebar( 'blog' ); ?>
	</aside>
<?php endif; ?>
