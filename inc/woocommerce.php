<?php
/**
 * WooCommerce integration: wrappers, product cards, swatches, cart drawer, AJAX, filters.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Layout wrappers, breadcrumbs, cleanup
 * ---------------------------------------------------------------------- */

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 60 );
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );

/**
 * Open main wrapper.
 */
function saayeh_wc_wrapper_start() {
	echo '<main id="main" class="site-main x-wrap" tabindex="-1">';
}
add_action( 'woocommerce_before_main_content', 'saayeh_wc_wrapper_start', 10 );

/**
 * Close main wrapper.
 */
function saayeh_wc_wrapper_end() {
	echo '</main>';
}
add_action( 'woocommerce_after_main_content', 'saayeh_wc_wrapper_end', 10 );

/**
 * Breadcrumb defaults.
 *
 * @param array $args Args.
 * @return array
 */
function saayeh_breadcrumb_defaults( $args ) {
	$args['delimiter']   = '<span aria-hidden="true"> / </span>';
	$args['wrap_before'] = '<nav class="woocommerce-breadcrumb x-crumb" aria-label="' . esc_attr__( 'مسیر صفحه', 'saayeh' ) . '">';
	$args['wrap_after']  = '</nav>';
	$args['home']        = __( 'خانه', 'saayeh' );
	return $args;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'saayeh_breadcrumb_defaults' );

/**
 * Hide breadcrumbs on the shop archive (it has its own heading).
 */
function saayeh_maybe_remove_breadcrumb() {
	if ( is_shop() || is_product_taxonomy() ) {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	}
}
add_action( 'wp', 'saayeh_maybe_remove_breadcrumb' );

/**
 * Products per page and columns.
 *
 * @return int
 */
function saayeh_loop_columns() {
	return 4;
}
add_filter( 'loop_shop_columns', 'saayeh_loop_columns' );

/**
 * Related products args.
 *
 * @param array $args Args.
 * @return array
 */
function saayeh_related_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'saayeh_related_args' );

/**
 * Related heading.
 *
 * @return string
 */
function saayeh_related_heading() {
	return __( 'شاید بپسندید', 'saayeh' );
}
add_filter( 'woocommerce_product_related_products_heading', 'saayeh_related_heading' );

/**
 * Persian labels for catalog ordering.
 *
 * @param array $options Options.
 * @return array
 */
function saayeh_catalog_orderby( $options ) {
	$labels = array(
		'menu_order' => __( 'پیش‌فرض', 'saayeh' ),
		'popularity' => __( 'پرفروش‌ترین', 'saayeh' ),
		'rating'     => __( 'بیشترین امتیاز', 'saayeh' ),
		'date'       => __( 'جدیدترین', 'saayeh' ),
		'price'      => __( 'ارزان‌ترین', 'saayeh' ),
		'price-desc' => __( 'گران‌ترین', 'saayeh' ),
	);
	foreach ( $options as $k => $v ) {
		if ( isset( $labels[ $k ] ) ) {
			$options[ $k ] = $labels[ $k ];
		}
	}
	return $options;
}
add_filter( 'woocommerce_catalog_orderby', 'saayeh_catalog_orderby', 20 );

/**
 * Add-to-cart button texts (Persian regardless of the site language).
 *
 * @param string $text Text.
 * @return string
 */
function saayeh_add_to_cart_text( $text ) {
	return __( 'افزودن به سبد', 'saayeh' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'saayeh_add_to_cart_text' );

// Variation titles without attributes; attributes are listed separately in cart/drawer.
add_filter( 'woocommerce_product_variation_title_include_attributes', '__return_false' );

/* -------------------------------------------------------------------------
 * Persian digits in prices
 * ---------------------------------------------------------------------- */

/**
 * Convert digits in formatted prices.
 *
 * @param string $html Price HTML.
 * @return string
 */
function saayeh_wc_price_digits( $html ) {
	if ( ! saayeh_use_fa_digits() || ( is_admin() && ! wp_doing_ajax() ) ) {
		return $html;
	}
	return saayeh_fa_digits_html( $html );
}
add_filter( 'wc_price', 'saayeh_wc_price_digits', 20 );
add_filter( 'woocommerce_get_price_html', 'saayeh_wc_price_digits', 20 );
add_filter( 'woocommerce_get_availability_text', 'saayeh_wc_price_digits', 20 );

/* -------------------------------------------------------------------------
 * Product helpers
 * ---------------------------------------------------------------------- */

/**
 * Badge for a product.
 *
 * @param WC_Product $product Product.
 * @return array{text:string,class:string}|null
 */
function saayeh_product_badge( $product ) {
	$custom = trim( (string) get_post_meta( $product->get_id(), '_saayeh_badge', true ) );
	if ( ! $product->is_in_stock() ) {
		return array( 'text' => __( 'ناموجود', 'saayeh' ), 'class' => 'x-badge--out' );
	}
	if ( $product->is_on_sale() ) {
		return array( 'text' => $custom ? $custom : __( 'تخفیف', 'saayeh' ), 'class' => 'x-badge--sale' );
	}
	if ( $custom ) {
		return array( 'text' => $custom, 'class' => '' );
	}
	return null;
}

/**
 * Badge markup.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function saayeh_product_badge_html( $product ) {
	$b = saayeh_product_badge( $product );
	return $b ? '<span class="x-badge ' . esc_attr( $b['class'] ) . '">' . esc_html( $b['text'] ) . '</span>' : '';
}

/**
 * Find the color/size attribute taxonomy names of a product.
 *
 * @param string $kind 'color' or 'size'.
 * @return string[] taxonomy names.
 */
function saayeh_attr_taxonomies( $kind ) {
	$map = array(
		'color' => array( 'pa_color', 'pa_colour', 'pa_rang' ),
		'size'  => array( 'pa_size', 'pa_saiz' ),
	);
	return (array) apply_filters( 'saayeh_' . $kind . '_taxonomies', $map[ $kind ] );
}

/**
 * Whether a taxonomy is a color attribute.
 *
 * @param string $taxonomy Taxonomy.
 * @return bool
 */
function saayeh_is_color_tax( $taxonomy ) {
	return in_array( $taxonomy, saayeh_attr_taxonomies( 'color' ), true );
}

/**
 * Get swatch hex for a term.
 *
 * @param WP_Term|int $term Term.
 * @return string
 */
function saayeh_term_hex( $term ) {
	$id  = is_object( $term ) ? $term->term_id : (int) $term;
	$hex = sanitize_hex_color( (string) get_term_meta( $id, 'saayeh_color_hex', true ) );
	return $hex ? $hex : '';
}

/**
 * Subline for cards: "first color · category".
 *
 * @param WC_Product $product Product.
 * @return string
 */
function saayeh_product_subline( $product ) {
	$parts = array();
	foreach ( saayeh_attr_taxonomies( 'color' ) as $tax ) {
		$terms = wc_get_product_terms( $product->get_id(), $tax, array( 'fields' => 'names' ) );
		if ( $terms ) {
			$parts[] = $terms[0];
			break;
		}
	}
	$cats = wc_get_product_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names', 'orderby' => 'parent' ) );
	if ( $cats ) {
		$parts[] = $cats[0];
	}
	return implode( ' · ', $parts );
}

/**
 * Options for quick-add: one entry per size (or per variation when there is no size attribute).
 *
 * @param WC_Product $product Product.
 * @return array[] { id, label, in_stock }
 */
function saayeh_quick_add_options( $product ) {
	if ( $product->is_type( 'simple' ) ) {
		if ( ! $product->is_purchasable() ) {
			return array();
		}
		return array(
			array(
				'id'       => $product->get_id(),
				'label'    => __( 'تک‌سایز', 'saayeh' ),
				'in_stock' => $product->is_in_stock(),
			),
		);
	}
	if ( ! $product->is_type( 'variable' ) ) {
		return array();
	}
	/** @var WC_Product_Variable $product */
	$size_tax = '';
	$attrs    = $product->get_variation_attributes();
	foreach ( saayeh_attr_taxonomies( 'size' ) as $tax ) {
		if ( isset( $attrs[ $tax ] ) ) {
			$size_tax = $tax;
			break;
		}
	}
	$children  = $product->get_children();
	$defaults  = $product->get_default_attributes();
	$by_size   = array();
	$fallback  = array();
	foreach ( $children as $vid ) {
		$v = wc_get_product( $vid );
		if ( ! $v || ! $v->exists() || 'publish' !== $v->get_status() || '' === $v->get_price() ) {
			continue;
		}
		$va = $v->get_variation_attributes( false );
		if ( in_array( '', $va, true ) ) {
			continue; // "Any" attributes need the full form.
		}
		if ( $size_tax ) {
			$slug = isset( $va[ $size_tax ] ) ? $va[ $size_tax ] : '';
			if ( '' === $slug ) {
				continue;
			}
			$score = ( $v->is_in_stock() ? 2 : 0 );
			$match = true;
			foreach ( $defaults as $k => $dv ) {
				if ( isset( $va[ $k ] ) && $va[ $k ] !== $dv && $k !== $size_tax ) {
					$match = false;
				}
			}
			$score += $match ? 1 : 0;
			if ( ! isset( $by_size[ $slug ] ) || $by_size[ $slug ]['score'] < $score ) {
				$by_size[ $slug ] = array( 'id' => $vid, 'in_stock' => $v->is_in_stock(), 'score' => $score );
			}
		} else {
			$fallback[] = array(
				'id'       => $vid,
				'label'    => wc_get_formatted_variation( $v, true, false, false ),
				'in_stock' => $v->is_in_stock(),
			);
		}
	}
	if ( ! $size_tax ) {
		return array_slice( $fallback, 0, 6 );
	}
	$out   = array();
	$terms = wc_get_product_terms( $product->get_id(), $size_tax, array( 'fields' => 'all' ) );
	foreach ( $terms as $term ) {
		if ( isset( $by_size[ $term->slug ] ) ) {
			$out[] = array(
				'id'       => $by_size[ $term->slug ]['id'],
				'label'    => $term->name,
				'in_stock' => $by_size[ $term->slug ]['in_stock'],
			);
		}
	}
	return $out;
}

/**
 * Quick add markup for cards.
 *
 * @param WC_Product $product Product.
 */
function saayeh_quick_add( $product ) {
	$opts = saayeh_quick_add_options( $product );
	if ( ! $opts || ! $product->is_in_stock() ) {
		return;
	}
	$name = $product->get_name();
	echo '<div class="x-qs">';
	if ( 1 === count( $opts ) && $product->is_type( 'simple' ) ) {
		printf(
			'<button type="button" class="x-qs__one" data-add="%1$d" data-name="%2$s">%3$s</button>',
			(int) $opts[0]['id'],
			esc_attr( $name ),
			esc_html__( 'افزودن به سبد', 'saayeh' )
		);
	} else {
		echo '<span>' . esc_html__( 'افزودن سریع:', 'saayeh' ) . '</span>';
		foreach ( $opts as $o ) {
			if ( $o['in_stock'] ) {
				/* translators: %s: size */
				$label = sprintf( __( 'افزودن سایز %s', 'saayeh' ), $o['label'] );
				printf(
					'<button type="button" data-add="%1$d" data-name="%2$s" data-size="%3$s" aria-label="%4$s">%5$s</button>',
					(int) $o['id'],
					esc_attr( $name ),
					esc_attr( $o['label'] ),
					esc_attr( $label ),
					esc_html( $o['label'] )
				);
			} else {
				/* translators: %s: size */
				$label = sprintf( __( '%s ناموجود', 'saayeh' ), $o['label'] );
				printf( '<button type="button" disabled aria-label="%1$s">%2$s</button>', esc_attr( $label ), esc_html( $o['label'] ) );
			}
		}
	}
	echo '</div>';
}

/**
 * Render a grid of products using the theme's product card.
 *
 * @param WC_Product[] $products Products.
 */
function saayeh_products_grid( $products ) {
	global $post;
	$prev = $post;
	echo '<ul class="products x-grid columns-4">';
	foreach ( $products as $p ) {
		$post = get_post( $p->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		wc_get_template_part( 'content', 'product' );
	}
	echo '</ul>';
	$post = $prev; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	wp_reset_postdata();
}

/* -------------------------------------------------------------------------
 * Single product: eyebrow, color line, heart, tabs, quantity stepper
 * ---------------------------------------------------------------------- */

/**
 * Badge eyebrow above the title.
 */
function saayeh_single_eyebrow() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$b = saayeh_product_badge( $product );
	if ( $b ) {
		echo '<p class="x-eyebrow">' . esc_html( $b['text'] ) . '</p>';
	}
}
add_action( 'woocommerce_single_product_summary', 'saayeh_single_eyebrow', 4 );

/**
 * Category/color line under the title.
 */
function saayeh_single_subline() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$line = saayeh_product_subline( $product );
	if ( $line ) {
		echo '<p class="x-muted" style="margin:0">' . esc_html( $line ) . '</p>';
	}
}
add_action( 'woocommerce_single_product_summary', 'saayeh_single_subline', 6 );

/**
 * Heart next to the add-to-cart button.
 */
function saayeh_single_heart() {
	global $product;
	if ( $product instanceof WC_Product ) {
		echo saayeh_heart_button( $product->get_id(), 'x-wish-pd' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
	}
}
add_action( 'woocommerce_after_add_to_cart_button', 'saayeh_single_heart' );

/**
 * Show heart even when out of stock (no add-to-cart form).
 */
function saayeh_single_heart_oos() {
	global $product;
	if ( $product instanceof WC_Product && ! $product->is_in_stock() && $product->is_type( 'simple' ) ) {
		echo '<div class="x-buy">' . saayeh_heart_button( $product->get_id(), 'x-wish-pd' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'woocommerce_single_product_summary', 'saayeh_single_heart_oos', 31 );

/**
 * Tabs: rename and add shipping & returns.
 *
 * @param array $tabs Tabs.
 * @return array
 */
function saayeh_product_tabs( $tabs ) {
	if ( isset( $tabs['description'] ) ) {
		$tabs['description']['title'] = __( 'توضیحات', 'saayeh' );
	}
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = __( 'مشخصات', 'saayeh' );
	}
	if ( isset( $tabs['reviews'] ) ) {
		global $product;
		$count = $product instanceof WC_Product ? $product->get_review_count() : 0;
		/* translators: %s: count */
		$tabs['reviews']['title'] = sprintf( __( 'نظرات (%s)', 'saayeh' ), saayeh_num( $count ) );
	}
	$tabs['saayeh_shipping'] = array(
		'title'    => __( 'ارسال و مرجوعی', 'saayeh' ),
		'priority' => 40,
		'callback' => 'saayeh_shipping_tab',
	);
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'saayeh_product_tabs', 20 );

/**
 * Shipping tab content.
 */
function saayeh_shipping_tab() {
	$threshold = (int) saayeh_mod( 'saayeh_free_shipping_threshold' );
	$text      = __( 'ارسال با پست پیشتاز در ۲ تا ۴ روز کاری و با پیک در تهران همان روز یا روز بعد انجام می‌شود.', 'saayeh' );
	if ( $threshold > 0 ) {
		/* translators: %s: amount */
		$text .= ' ' . sprintf( __( 'برای خریدهای بالای %s ارسال رایگان است.', 'saayeh' ), wp_strip_all_tags( wc_price( $threshold ) ) );
	}
	$text .= ' ' . __( 'تا ۷ روز پس از تحویل می‌توانید کالا را بدون پرسش مرجوع کنید.', 'saayeh' );
	echo '<p>' . esc_html( apply_filters( 'saayeh_shipping_tab_text', $text ) ) . '</p>';
}

/**
 * Quantity stepper buttons around the WooCommerce quantity input.
 */
function saayeh_qty_minus() {
	echo '<button type="button" class="x-qty-btn" data-qty="-1" aria-label="' . esc_attr__( 'کاهش', 'saayeh' ) . '">−</button>';
}
/**
 * Plus button.
 */
function saayeh_qty_plus() {
	echo '<button type="button" class="x-qty-btn" data-qty="1" aria-label="' . esc_attr__( 'افزایش', 'saayeh' ) . '">+</button>';
}
add_action( 'woocommerce_before_quantity_input_field', 'saayeh_qty_plus' );
add_action( 'woocommerce_after_quantity_input_field', 'saayeh_qty_minus' );

/* -------------------------------------------------------------------------
 * Variation swatches (color circles + size buttons over hidden selects)
 * ---------------------------------------------------------------------- */

/**
 * Add hidden-select class.
 *
 * @param array $args Args.
 * @return array
 */
function saayeh_dropdown_args( $args ) {
	$args['class'] = trim( ( isset( $args['class'] ) ? $args['class'] : '' ) . ' x-hidden-select' );
	return $args;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'saayeh_dropdown_args' );

/**
 * Append swatches after each variation select.
 *
 * @param string $html Select HTML.
 * @param array  $args Args.
 * @return string
 */
function saayeh_variation_swatches( $html, $args ) {
	if ( empty( $args['product'] ) || empty( $args['attribute'] ) ) {
		return $html;
	}
	$attribute = $args['attribute'];
	$product   = $args['product'];
	$options   = $args['options'];
	if ( empty( $options ) && $product ) {
		$attrs   = $product->get_variation_attributes();
		$options = isset( $attrs[ $attribute ] ) ? $attrs[ $attribute ] : array();
	}
	if ( empty( $options ) ) {
		return $html;
	}

	$html = str_replace( '<select ', '<select tabindex="-1" aria-hidden="true" ', $html );

	$is_color = saayeh_is_color_tax( $attribute );
	$items    = array();
	if ( taxonomy_exists( $attribute ) ) {
		$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
		foreach ( $terms as $term ) {
			if ( in_array( $term->slug, $options, true ) ) {
				$items[] = array( 'value' => $term->slug, 'name' => $term->name, 'hex' => $is_color ? saayeh_term_hex( $term ) : '' );
			}
		}
	} else {
		foreach ( $options as $opt ) {
			$items[] = array( 'value' => $opt, 'name' => $opt, 'hex' => '' );
		}
	}

	$label = wc_attribute_label( $attribute, $product );
	/* translators: %s: attribute name */
	$out  = '<div class="x-swatches' . ( $is_color ? ' x-swatches--color' : ' x-sizes' ) . '" role="group" aria-label="' . esc_attr( sprintf( __( 'انتخاب %s', 'saayeh' ), $label ) ) . '" data-attr="' . esc_attr( 'attribute_' . sanitize_title( $attribute ) ) . '">';
	foreach ( $items as $it ) {
		$on = (string) $args['selected'] === (string) $it['value'];
		if ( $is_color && $it['hex'] ) {
			$out .= sprintf(
				'<button type="button" class="x-swatch%1$s" data-value="%2$s" aria-pressed="%3$s" aria-label="%4$s" title="%4$s"><i style="background:%5$s"></i></button>',
				$on ? ' is-on' : '',
				esc_attr( $it['value'] ),
				$on ? 'true' : 'false',
				esc_attr( $it['name'] ),
				esc_attr( $it['hex'] )
			);
		} else {
			$out .= sprintf(
				'<button type="button" class="x-size%1$s" data-value="%2$s" aria-pressed="%3$s">%4$s</button>',
				$on ? ' is-on' : '',
				esc_attr( $it['value'] ),
				$on ? 'true' : 'false',
				esc_html( $it['name'] )
			);
		}
	}
	if ( in_array( $attribute, saayeh_attr_taxonomies( 'size' ), true ) && apply_filters( 'saayeh_show_size_guide', true, $product ) ) {
		$out .= '<button type="button" class="x-sizeguide-btn" data-sizeguide aria-haspopup="dialog">' . esc_html__( 'راهنمای سایز', 'saayeh' ) . '</button>';
	}
	$out .= '</div>';
	return $html . $out;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'saayeh_variation_swatches', 20, 2 );

/**
 * Size guide modal markup (printed in the footer on product pages).
 */
function saayeh_size_guide_modal() {
	if ( ! is_product() ) {
		return;
	}
	ob_start();
	?>
	<table>
		<thead><tr><th><?php esc_html_e( 'سایز', 'saayeh' ); ?></th><th><?php esc_html_e( 'دور سینه', 'saayeh' ); ?></th><th><?php esc_html_e( 'دور کمر', 'saayeh' ); ?></th><th><?php esc_html_e( 'قد لباس', 'saayeh' ); ?></th></tr></thead>
		<tbody>
			<tr><td>S</td><td><?php echo esc_html( saayeh_fa_digits( '88–92' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '72–76' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '68' ) ); ?></td></tr>
			<tr><td>M</td><td><?php echo esc_html( saayeh_fa_digits( '96–100' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '80–84' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '70' ) ); ?></td></tr>
			<tr><td>L</td><td><?php echo esc_html( saayeh_fa_digits( '104–108' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '88–92' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '72' ) ); ?></td></tr>
			<tr><td>XL</td><td><?php echo esc_html( saayeh_fa_digits( '112–116' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '96–100' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '74' ) ); ?></td></tr>
		</tbody>
	</table>
	<h3><?php esc_html_e( 'شلوار (سایز اروپایی)', 'saayeh' ); ?></h3>
	<table>
		<thead><tr><th><?php esc_html_e( 'سایز', 'saayeh' ); ?></th><th><?php esc_html_e( 'دور کمر', 'saayeh' ); ?></th><th><?php esc_html_e( 'دور باسن', 'saayeh' ); ?></th></tr></thead>
		<tbody>
			<tr><td><?php echo esc_html( saayeh_fa_digits( '36' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '66–70' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '90–94' ) ); ?></td></tr>
			<tr><td><?php echo esc_html( saayeh_fa_digits( '38' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '70–74' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '94–98' ) ); ?></td></tr>
			<tr><td><?php echo esc_html( saayeh_fa_digits( '40' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '74–78' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '98–102' ) ); ?></td></tr>
			<tr><td><?php echo esc_html( saayeh_fa_digits( '42' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '78–82' ) ); ?></td><td><?php echo esc_html( saayeh_fa_digits( '102–106' ) ); ?></td></tr>
		</tbody>
	</table>
	<p class="x-muted" style="margin:0"><?php esc_html_e( 'اندازه‌ها به سانتی‌متر و بر اساس اندازه‌ی بدن است. اگر بین دو سایز هستید، سایز بزرگ‌تر را انتخاب کنید.', 'saayeh' ); ?></p>
	<?php
	$table = (string) apply_filters( 'saayeh_size_guide_html', ob_get_clean() );
	?>
	<dialog class="x-modal" id="x-sizeguide" aria-labelledby="x-sizeguide-t">
		<div class="x-modal__head">
			<h2 id="x-sizeguide-t"><?php esc_html_e( 'راهنمای سایز', 'saayeh' ); ?></h2>
			<button type="button" class="x-icon" data-close-dialog aria-label="<?php esc_attr_e( 'بستن', 'saayeh' ); ?>"><?php saayeh_the_icon( 'close', 18 ); ?></button>
		</div>
		<div class="x-modal__body"><?php echo wp_kses_post( $table ); ?></div>
	</dialog>
	<?php
}
add_action( 'wp_footer', 'saayeh_size_guide_modal', 5 );

/* -------------------------------------------------------------------------
 * Cart drawer: fragments, free-shipping bar, AJAX quantity updates
 * ---------------------------------------------------------------------- */

/**
 * Fragments: header cart count.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function saayeh_cart_fragments( $fragments ) {
	$fragments['span.saayeh-cart-count'] = saayeh_cart_count_html();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'saayeh_cart_fragments' );

/**
 * Free-shipping progress bar.
 */
function saayeh_free_shipping_bar() {
	$threshold = (float) apply_filters( 'saayeh_free_shipping_threshold', (float) saayeh_mod( 'saayeh_free_shipping_threshold' ) );
	if ( $threshold <= 0 || ! WC()->cart ) {
		return;
	}
	$sub  = (float) WC()->cart->get_displayed_subtotal();
	$sub -= (float) WC()->cart->get_discount_total();
	if ( WC()->cart->display_prices_including_tax() ) {
		$sub -= (float) WC()->cart->get_discount_tax();
	}
	$left = max( 0, $threshold - $sub );
	$pct  = min( 100, $threshold > 0 ? ( $sub / $threshold ) * 100 : 100 );
	echo '<div class="x-progress" role="status">';
	if ( $left > 0 ) {
		/* translators: %s: amount left */
		echo wp_kses_post( sprintf( __( '%s تا ارسال رایگان', 'saayeh' ), wc_price( $left ) ) );
	} else {
		esc_html_e( 'ارسال شما رایگان است', 'saayeh' );
	}
	echo '<i aria-hidden="true"><b style="width:' . esc_attr( round( $pct, 2 ) ) . '%"></b></i></div>';
}
add_action( 'woocommerce_before_cart', 'saayeh_free_shipping_bar', 5 );

/**
 * AJAX: change a cart line quantity (0 removes it). Responds with fragments.
 */
function saayeh_ajax_cart_qty() {
	ob_start();
	check_ajax_referer( 'saayeh', 'nonce' );
	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['qty'] ) ? max( 0, (int) $_POST['qty'] ) : 0;
	$item = $key ? WC()->cart->get_cart_item( $key ) : array();
	if ( ! $item ) {
		wp_send_json_error( array( 'message' => __( 'این کالا در سبد نیست.', 'saayeh' ) ) );
	}
	if ( 0 === $qty ) {
		WC()->cart->remove_cart_item( $key );
	} else {
		$product = $item['data'];
		$max     = $product instanceof WC_Product ? $product->get_max_purchase_quantity() : -1;
		if ( $max > 0 && $qty > $max ) {
			$qty = $max;
		}
		if ( $product instanceof WC_Product && ! $product->has_enough_stock( $qty ) ) {
			wp_send_json_error( array( 'message' => __( 'موجودی این کالا کافی نیست.', 'saayeh' ) ) );
		}
		WC()->cart->set_quantity( $key, $qty, true );
	}
	WC()->cart->calculate_totals();
	WC_AJAX::get_refreshed_fragments();
}
add_action( 'wp_ajax_saayeh_cart_qty', 'saayeh_ajax_cart_qty' );
add_action( 'wp_ajax_nopriv_saayeh_cart_qty', 'saayeh_ajax_cart_qty' );

/**
 * Thumbnails next to item names in the checkout order review.
 *
 * @param string $name      Name HTML.
 * @param array  $cart_item Item.
 * @return string
 */
function saayeh_checkout_item_name( $name, $cart_item ) {
	if ( ! is_checkout() || empty( $cart_item['data'] ) || ! did_action( 'woocommerce_review_order_before_cart_contents' ) ) {
		return $name;
	}
	$img = $cart_item['data']->get_image( 'thumbnail' );
	return '<span class="x-mini">' . $img . '<span>' . $name . '</span></span>';
}
add_filter( 'woocommerce_cart_item_name', 'saayeh_checkout_item_name', 20, 2 );

/* -------------------------------------------------------------------------
 * Shop archive filters (category chips, size, color, price, sort)
 * ---------------------------------------------------------------------- */

/**
 * Normalise filter params: arrays from checkboxes → comma lists, Persian digits → Latin.
 */
function saayeh_normalize_filter_query() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only catalog filters.
	foreach ( $_GET as $key => $value ) {
		if ( 0 === strpos( (string) $key, 'filter_' ) && is_array( $value ) ) {
			$clean          = array_filter( array_map( 'sanitize_title', wp_unslash( $value ) ) );
			$_GET[ $key ]     = implode( ',', $clean );
			$_REQUEST[ $key ] = $_GET[ $key ];
		}
	}
	foreach ( array( 'min_price', 'max_price' ) as $key ) {
		if ( isset( $_GET[ $key ] ) ) {
			$v = preg_replace( '/[^\d]/', '', saayeh_en_digits( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) ) );
			if ( '' === $v ) {
				unset( $_GET[ $key ], $_REQUEST[ $key ] );
			} else {
				$_GET[ $key ]     = $v;
				$_REQUEST[ $key ] = $v;
			}
		}
	}
	// phpcs:enable
}
add_action( 'init', 'saayeh_normalize_filter_query', 1 );

/**
 * Archive base URL (without pagination).
 *
 * @return string
 */
function saayeh_archive_base_url() {
	if ( is_product_taxonomy() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? wc_get_page_permalink( 'shop' ) : $link;
	}
	return wc_get_page_permalink( 'shop' );
}

/**
 * Current filter query args to keep when switching category.
 *
 * @return array
 */
function saayeh_current_filter_args() {
	$keep = array();
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	foreach ( $_GET as $key => $value ) {
		if ( is_string( $value ) && ( 0 === strpos( $key, 'filter_' ) || 0 === strpos( $key, 'query_type_' ) || in_array( $key, array( 'min_price', 'max_price', 'orderby' ), true ) ) ) {
			$keep[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( $value ) );
		}
	}
	// phpcs:enable
	return $keep;
}

/**
 * Category chips.
 */
function saayeh_category_chips() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}
	$current = is_product_category() ? get_queried_object_id() : 0;
	$args    = saayeh_current_filter_args();
	echo '<ul class="x-chips" aria-label="' . esc_attr__( 'دسته‌بندی‌ها', 'saayeh' ) . '">';
	printf(
		'<li><a class="x-chip%1$s" href="%2$s"%3$s>%4$s</a></li>',
		$current ? '' : ' is-on',
		esc_url( add_query_arg( $args, wc_get_page_permalink( 'shop' ) ) ),
		$current ? '' : ' aria-current="page"',
		esc_html__( 'همه', 'saayeh' )
	);
	foreach ( $terms as $term ) {
		$on = ( $current === $term->term_id );
		printf(
			'<li><a class="x-chip%1$s" href="%2$s"%3$s>%4$s</a></li>',
			$on ? ' is-on' : '',
			esc_url( add_query_arg( $args, get_term_link( $term ) ) ),
			$on ? ' aria-current="page"' : '',
			esc_html( $term->name )
		);
	}
	echo '</ul>';
}

/**
 * Filter panel + sort select.
 */
function saayeh_shop_filters() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$chosen  = WC_Query::get_layered_nav_chosen_attributes();
	$min     = isset( $_GET['min_price'] ) ? absint( $_GET['min_price'] ) : '';
	$max     = isset( $_GET['max_price'] ) ? absint( $_GET['max_price'] ) : '';
	$orderby = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
	// phpcs:enable
	$has_any = ! empty( $chosen ) || '' !== $min || '' !== $max;

	$groups = array();
	foreach ( array( 'size', 'color' ) as $kind ) {
		foreach ( saayeh_attr_taxonomies( $kind ) as $tax ) {
			if ( taxonomy_exists( $tax ) ) {
				$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true ) );
				if ( ! is_wp_error( $terms ) && $terms ) {
					$groups[] = array( 'tax' => $tax, 'kind' => $kind, 'terms' => $terms );
				}
				break;
			}
		}
	}

	$orderby_opts = apply_filters(
		'woocommerce_catalog_orderby',
		array(
			'menu_order' => __( 'پیش‌فرض', 'saayeh' ),
			'popularity' => __( 'پرفروش‌ترین', 'saayeh' ),
			'rating'     => __( 'بیشترین امتیاز', 'saayeh' ),
			'date'       => __( 'جدیدترین', 'saayeh' ),
			'price'      => __( 'ارزان‌ترین', 'saayeh' ),
			'price-desc' => __( 'گران‌ترین', 'saayeh' ),
		)
	);
	if ( ! wc_review_ratings_enabled() ) {
		unset( $orderby_opts['rating'] );
	}
	?>
	<div class="x-bar">
		<?php saayeh_category_chips(); ?>
		<div class="x-bar__end">
			<?php if ( $groups ) : ?>
				<button type="button" class="x-chip x-filter-toggle" aria-expanded="<?php echo $has_any ? 'true' : 'false'; ?>" aria-controls="x-filters"><?php saayeh_the_icon( 'filter', 16 ); ?><?php esc_html_e( 'فیلترها', 'saayeh' ); ?></button>
			<?php endif; ?>
			<label class="x-muted" style="font-size:13px" for="x-orderby"><?php esc_html_e( 'مرتب‌سازی:', 'saayeh' ); ?></label>
			<select class="x-select" id="x-orderby" name="orderby" form="x-shop-form" data-autosubmit>
				<?php foreach ( $orderby_opts as $id => $name ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<form id="x-shop-form" class="x-filters" method="get" action="<?php echo esc_url( saayeh_archive_base_url() ); ?>" <?php echo $has_any ? '' : 'hidden'; ?> aria-label="<?php esc_attr_e( 'فیلتر محصولات', 'saayeh' ); ?>">
		<?php
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['s'] ) ) {
			echo '<input type="hidden" name="s" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) . '"><input type="hidden" name="post_type" value="product">';
		}
		// phpcs:enable
		foreach ( $groups as $g ) :
			$name     = wc_attribute_taxonomy_slug( $g['tax'] );
			$selected = isset( $chosen[ $g['tax'] ] ) ? $chosen[ $g['tax'] ]['terms'] : array();
			?>
			<fieldset>
				<legend><?php echo esc_html( wc_attribute_label( $g['tax'] ) ); ?></legend>
				<input type="hidden" name="query_type_<?php echo esc_attr( $name ); ?>" value="or">
				<div class="x-opts">
					<?php foreach ( $g['terms'] as $term ) : ?>
						<label class="x-opt-chip">
							<input type="checkbox" name="filter_<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( in_array( $term->slug, $selected, true ) ); ?>>
							<span>
								<?php if ( 'color' === $g['kind'] && saayeh_term_hex( $term ) ) : ?>
									<i class="x-dot" style="background:<?php echo esc_attr( saayeh_term_hex( $term ) ); ?>"></i>
								<?php endif; ?>
								<?php echo esc_html( $term->name ); ?>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php endforeach; ?>
		<fieldset>
			<legend><?php esc_html_e( 'محدوده قیمت (تومان)', 'saayeh' ); ?></legend>
			<div class="x-price-range">
				<label class="screen-reader-text" for="x-min-price"><?php esc_html_e( 'حداقل قیمت', 'saayeh' ); ?></label>
				<input class="x-input" id="x-min-price" type="text" inputmode="numeric" name="min_price" value="<?php echo esc_attr( $min ); ?>" placeholder="<?php esc_attr_e( 'از', 'saayeh' ); ?>" dir="ltr">
				<span aria-hidden="true">—</span>
				<label class="screen-reader-text" for="x-max-price"><?php esc_html_e( 'حداکثر قیمت', 'saayeh' ); ?></label>
				<input class="x-input" id="x-max-price" type="text" inputmode="numeric" name="max_price" value="<?php echo esc_attr( $max ); ?>" placeholder="<?php esc_attr_e( 'تا', 'saayeh' ); ?>" dir="ltr">
			</div>
		</fieldset>
		<div class="x-filters__actions">
			<button type="submit" class="x-btn x-btn--sm"><?php esc_html_e( 'اعمال فیلترها', 'saayeh' ); ?></button>
			<?php if ( $has_any ) : ?>
				<a class="x-link" href="<?php echo esc_url( saayeh_archive_base_url() ); ?>"><?php esc_html_e( 'حذف همه فیلترها', 'saayeh' ); ?></a>
			<?php endif; ?>
		</div>
	</form>
	<?php
}

/**
 * Empty results message.
 */
function saayeh_no_products_found() {
	echo '<div class="x-empty"><p class="x-empty__t">' . esc_html__( 'محصولی با این مشخصات پیدا نشد.', 'saayeh' ) . '</p>';
	echo '<a class="x-btn x-btn--ghost" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'دیدن همه محصولات', 'saayeh' ) . '</a></div>';
}
remove_action( 'woocommerce_no_products_found', 'wc_no_products_found' );
add_action( 'woocommerce_no_products_found', 'saayeh_no_products_found' );

/* -------------------------------------------------------------------------
 * My Account wrapper
 * ---------------------------------------------------------------------- */

/**
 * Persian labels for account menu.
 *
 * @param array $items Items.
 * @return array
 */
function saayeh_account_menu_labels( $items ) {
	$labels = array(
		'dashboard'       => __( 'پیشخوان', 'saayeh' ),
		'orders'          => __( 'سفارش‌های من', 'saayeh' ),
		'downloads'       => __( 'دانلودها', 'saayeh' ),
		'edit-address'    => __( 'آدرس‌ها', 'saayeh' ),
		'payment-methods' => __( 'روش‌های پرداخت', 'saayeh' ),
		'edit-account'    => __( 'جزئیات حساب', 'saayeh' ),
		'customer-logout' => __( 'خروج', 'saayeh' ),
	);
	foreach ( $items as $k => $v ) {
		if ( isset( $labels[ $k ] ) && ! is_rtl() ) {
			$items[ $k ] = $labels[ $k ];
		}
	}
	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'saayeh_account_menu_labels', 5 );

/**
 * Persian digits in the mini cart / cart quantities shown as text.
 *
 * @param string $html HTML.
 * @return string
 */
function saayeh_digits_filter( $html ) {
	return saayeh_use_fa_digits() ? saayeh_fa_digits_html( $html ) : $html;
}
add_filter( 'woocommerce_widget_cart_item_quantity', 'saayeh_digits_filter', 20 );
