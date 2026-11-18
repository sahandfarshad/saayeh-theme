<?php
/**
 * Admin fields: product badge (_saayeh_badge) and color swatch hex (saayeh_color_hex term meta).
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Badge field in the product "General" tab.
 */
function saayeh_badge_field() {
	woocommerce_wp_text_input(
		array(
			'id'          => '_saayeh_badge',
			'label'       => __( 'برچسب کارت محصول', 'saayeh' ),
			'placeholder' => __( 'مثلاً: جدید، پرفروش', 'saayeh' ),
			'desc_tip'    => true,
			'description' => __( 'متن کوتاهی که روی تصویر محصول نمایش داده می‌شود. برای محصولات حراج، خالی بگذارید تا «تخفیف» نمایش داده شود.', 'saayeh' ),
		)
	);
}
add_action( 'woocommerce_product_options_general_product_data', 'saayeh_badge_field' );

/**
 * Save badge.
 *
 * @param WC_Product $product Product.
 */
function saayeh_badge_save( $product ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the product edit nonce.
	if ( isset( $_POST['_saayeh_badge'] ) ) {
		$product->update_meta_data( '_saayeh_badge', sanitize_text_field( wp_unslash( $_POST['_saayeh_badge'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
}
add_action( 'woocommerce_admin_process_product_object', 'saayeh_badge_save' );

/**
 * Hook color fields onto every color attribute taxonomy.
 */
function saayeh_register_color_term_fields() {
	foreach ( saayeh_attr_taxonomies( 'color' ) as $tax ) {
		add_action( $tax . '_add_form_fields', 'saayeh_color_add_field' );
		add_action( $tax . '_edit_form_fields', 'saayeh_color_edit_field' );
		add_action( 'created_' . $tax, 'saayeh_color_save' );
		add_action( 'edited_' . $tax, 'saayeh_color_save' );
		add_filter( 'manage_edit-' . $tax . '_columns', 'saayeh_color_column' );
		add_filter( 'manage_' . $tax . '_custom_column', 'saayeh_color_column_content', 10, 3 );
	}
	register_meta(
		'term',
		'saayeh_color_hex',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_hex_color',
			'show_in_rest'      => true,
		)
	);
}
add_action( 'admin_init', 'saayeh_register_color_term_fields' );

/**
 * Add form field.
 */
function saayeh_color_add_field() {
	wp_nonce_field( 'saayeh_color_hex', 'saayeh_color_hex_nonce' );
	?>
	<div class="form-field">
		<label for="saayeh_color_hex"><?php esc_html_e( 'کد رنگ نمونه', 'saayeh' ); ?></label>
		<input type="color" name="saayeh_color_hex" id="saayeh_color_hex" value="#cccccc">
		<p><?php esc_html_e( 'این رنگ به‌صورت دایره‌ی انتخاب رنگ در صفحه محصول نمایش داده می‌شود.', 'saayeh' ); ?></p>
	</div>
	<?php
}

/**
 * Edit form field.
 *
 * @param WP_Term $term Term.
 */
function saayeh_color_edit_field( $term ) {
	$hex = saayeh_term_hex( $term );
	?>
	<tr class="form-field">
		<th scope="row"><label for="saayeh_color_hex"><?php esc_html_e( 'کد رنگ نمونه', 'saayeh' ); ?></label></th>
		<td>
			<?php wp_nonce_field( 'saayeh_color_hex', 'saayeh_color_hex_nonce' ); ?>
			<input type="color" name="saayeh_color_hex" id="saayeh_color_hex" value="<?php echo esc_attr( $hex ? $hex : '#cccccc' ); ?>">
		</td>
	</tr>
	<?php
}

/**
 * Save term meta.
 *
 * @param int $term_id Term.
 */
function saayeh_color_save( $term_id ) {
	if ( ! isset( $_POST['saayeh_color_hex_nonce'], $_POST['saayeh_color_hex'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['saayeh_color_hex_nonce'] ) ), 'saayeh_color_hex' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}
	$hex = sanitize_hex_color( wp_unslash( $_POST['saayeh_color_hex'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( $hex ) {
		update_term_meta( $term_id, 'saayeh_color_hex', $hex );
	}
}

/**
 * Column header.
 *
 * @param array $cols Columns.
 * @return array
 */
function saayeh_color_column( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		if ( 'name' === $k ) {
			$new['saayeh_color'] = __( 'رنگ', 'saayeh' );
		}
		$new[ $k ] = $v;
	}
	return $new;
}

/**
 * Column content.
 *
 * @param string $content Content.
 * @param string $column  Column.
 * @param int    $term_id Term.
 * @return string
 */
function saayeh_color_column_content( $content, $column, $term_id ) {
	if ( 'saayeh_color' !== $column ) {
		return $content;
	}
	$hex = saayeh_term_hex( $term_id );
	return $hex ? '<span style="display:inline-block;width:22px;height:22px;border-radius:50%;border:1px solid #ccc;background:' . esc_attr( $hex ) . '"></span>' : '—';
}
