<?php
/**
 * Generic helpers: digits, icons, colors.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Convert Latin (and Arabic-Indic) digits to Persian digits.
 *
 * @param string|int|float $value Value.
 * @return string
 */
function saayeh_fa_digits( $value ) {
	return strtr(
		(string) $value,
		array(
			'0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
			'5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
			'٠' => '۰', '١' => '۱', '٢' => '۲', '٣' => '۳', '٤' => '۴',
			'٥' => '۵', '٦' => '۶', '٧' => '۷', '٨' => '۸', '٩' => '۹',
		)
	);
}

/**
 * Convert Persian / Arabic-Indic digits to Latin digits (for validation and storage).
 *
 * @param string $value Value.
 * @return string
 */
function saayeh_en_digits( $value ) {
	return strtr(
		(string) $value,
		array(
			'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
			'۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
			'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
			'٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
		)
	);
}

/**
 * Convert digits to Persian only in text nodes of an HTML string,
 * leaving tags, attributes and HTML entities (e.g. &#x062A;) untouched.
 *
 * @param string $html HTML.
 * @return string
 */
function saayeh_fa_digits_html( $html ) {
	if ( '' === $html || ! preg_match( '/\d/', $html ) ) {
		return $html;
	}
	$parts = preg_split( '/(<[^>]*>|&#?[a-zA-Z0-9]+;)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	if ( false === $parts ) {
		return $html;
	}
	foreach ( $parts as $i => $part ) {
		if ( '' === $part || '<' === $part[0] || '&' === $part[0] ) {
			continue;
		}
		$parts[ $i ] = saayeh_fa_digits( $part );
	}
	return implode( '', $parts );
}

/**
 * Whether Persian digits should be used on the front end.
 *
 * @return bool
 */
function saayeh_use_fa_digits() {
	$use = (bool) get_theme_mod( 'saayeh_persian_digits', true );
	return (bool) apply_filters( 'saayeh_use_persian_digits', $use );
}

/**
 * Format a number for display (thousands separator + Persian digits).
 *
 * @param int|float $n Number.
 * @return string
 */
function saayeh_num( $n ) {
	$out = number_format_i18n( (float) $n );
	return saayeh_use_fa_digits() ? saayeh_fa_digits( $out ) : $out;
}

/**
 * Inline SVG icons (stroke-based, matching the design).
 *
 * @param string $name Icon name.
 * @param int    $size Size in px.
 * @return string Safe SVG markup.
 */
function saayeh_icon( $name, $size = 22 ) {
	$s     = (int) $size;
	$open  = '<svg width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true" focusable="false">';
	$paths = array(
		'heart'     => '<path d="M12 20s-7.5-4.6-7.5-10.2A4.2 4.2 0 0112 7.3a4.2 4.2 0 017.5 2.5C19.5 15.4 12 20 12 20z"/>',
		'user'      => '<circle cx="12" cy="8.2" r="3.8"/><path d="M4.4 20.7c.9-3.9 3.9-6 7.6-6s6.7 2.1 7.6 6"/>',
		'bag'       => '<path d="M4.4 7.6h15.2l-1.1 13.1H5.5L4.4 7.6z"/><path d="M8.7 7.6V5.5a3.3 3.3 0 016.6 0v2.1"/>',
		'search'    => '<circle cx="10.5" cy="10.5" r="7.5"/><path d="M16.5 16.5l5.2 5.2"/>',
		'menu'      => '<path d="M2.4 7.2h19.2M2.4 16.8h19.2"/>',
		'close'     => '<path d="M4 4l16 16M20 4L4 20"/>',
		'filter'    => '<path d="M3 6h18M6 12h12M10 18h4"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor"/>',
		'telegram'  => '<path d="M21 4L2.8 11.1c-.9.4-.9 1.6.1 1.9l4.6 1.4 1.8 5.6c.2.7 1.1.9 1.6.4l2.6-2.5 4.8 3.5c.7.5 1.6.1 1.8-.7L22.6 5.5c.3-1-.7-1.9-1.6-1.5z"/><path d="M7.5 14.4l10-7.4-7.7 8.6"/>',
		'whatsapp'  => '<path d="M3.5 20.5l1.3-4.4A8.5 8.5 0 1112 20.5a8.4 8.4 0 01-4.2-1.1l-4.3 1.1z"/><path d="M9 8.5c.3 2.6 2.1 5 4.9 6.1l1.3-1.2 2 .9-.4 1.7c-4.3.3-8.8-3.6-9.1-8.1L9.3 7.3l1 2-1.3 1.2"/>',
		'x'         => '<path d="M4 4l16 16M20 4L4 20"/>',
		'linkedin'  => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7.5 10v6.5M7.5 7.3v.2M11 16.5V10m0 3a2.5 2.5 0 015 0v3.5"/>',
		'youtube'   => '<rect x="2.5" y="5.5" width="19" height="13" rx="3.5"/><path d="M10 9.2v5.6l4.8-2.8L10 9.2z"/>',
		'aparat'    => '<circle cx="12" cy="12" r="9"/><circle cx="9" cy="8.6" r="1.6"/><circle cx="15.4" cy="9" r="1.6"/><circle cx="8.6" cy="15" r="1.6"/><circle cx="15" cy="15.4" r="1.6"/>',
		'plus'      => '<path d="M12 5v14M5 12h14"/>',
		'minus'     => '<path d="M5 12h14"/>',
	);
	$body = isset( $paths[ $name ] ) ? $paths[ $name ] : '';
	return $open . $body . '</svg>';
}

/**
 * Allowed SVG tags for wp_kses when echoing icons.
 *
 * @return array
 */
function saayeh_svg_kses() {
	$common = array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true );
	return array(
		'svg'    => array( 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'aria-hidden' => true, 'focusable' => true, 'class' => true, 'role' => true ),
		'path'   => $common,
		'circle' => $common + array( 'cx' => true, 'cy' => true, 'r' => true ),
		'rect'   => $common + array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true ),
	);
}

/**
 * Echo an icon safely.
 *
 * @param string $name Icon.
 * @param int    $size Size.
 */
function saayeh_the_icon( $name, $size = 22 ) {
	echo wp_kses( saayeh_icon( $name, $size ), saayeh_svg_kses() );
}

/**
 * Mix two hex colors.
 *
 * @param string $a      Hex color A.
 * @param string $b      Hex color B.
 * @param float  $weight Weight of A (0..1).
 * @return string
 */
function saayeh_mix_hex( $a, $b, $weight ) {
	$a = ltrim( (string) $a, '#' );
	$b = ltrim( (string) $b, '#' );
	if ( 3 === strlen( $a ) ) {
		$a = $a[0] . $a[0] . $a[1] . $a[1] . $a[2] . $a[2];
	}
	if ( 3 === strlen( $b ) ) {
		$b = $b[0] . $b[0] . $b[1] . $b[1] . $b[2] . $b[2];
	}
	if ( 6 !== strlen( $a ) || 6 !== strlen( $b ) ) {
		return '#' . $a;
	}
	$out = '#';
	for ( $i = 0; $i < 3; $i++ ) {
		$ca   = hexdec( substr( $a, $i * 2, 2 ) );
		$cb   = hexdec( substr( $b, $i * 2, 2 ) );
		$out .= str_pad( dechex( (int) round( $ca * $weight + $cb * ( 1 - $weight ) ) ), 2, '0', STR_PAD_LEFT );
	}
	return $out;
}

/**
 * Find the page ID that holds a given shortcode or slug (cached in a transient-less static).
 *
 * @param string $slug Page slug fallback.
 * @param string $shortcode Shortcode tag.
 * @return int
 */
function saayeh_find_page( $slug, $shortcode = '' ) {
	static $cache = array();
	$key = $slug . '|' . $shortcode;
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	$id   = 0;
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' === $page->post_status ) {
		$id = (int) $page->ID;
	} elseif ( $shortcode ) {
		$q = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				's'              => '[' . $shortcode,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		$id = $q ? (int) $q[0] : 0;
	}
	$cache[ $key ] = $id;
	return $id;
}
