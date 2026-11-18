<?php
/**
 * Customizer settings.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values of all theme mods.
 *
 * @return array
 */
function saayeh_defaults() {
	return apply_filters(
		'saayeh_theme_mod_defaults',
		array(
			'saayeh_announcement'            => __( 'ارسال رایگان برای خریدهای بالای ۳ میلیون تومان', 'saayeh' ),
			'saayeh_announcement_link'       => '',
			'saayeh_tagline'                 => __( 'پوشاک مینیمال، برای هر روز', 'saayeh' ),
			'saayeh_accent_color'            => '#B39B7A',
			'saayeh_force_rtl'               => true,
			'saayeh_persian_digits'          => true,
			'saayeh_hero_image'              => '',
			'saayeh_hero_eyebrow'            => __( 'کالکشن زمستان ۱۴۰۵', 'saayeh' ),
			'saayeh_hero_title'              => __( 'سادگی، امضای توست', 'saayeh' ),
			'saayeh_hero_subtitle'           => __( 'لباس‌هایی آرام و ماندگار، از پارچه‌های طبیعی.', 'saayeh' ),
			'saayeh_hero_cta_text'           => __( 'خرید کالکشن زمستان', 'saayeh' ),
			'saayeh_hero_cta_link'           => '',
			'saayeh_home_show_cats'          => true,
			'saayeh_home_new_count'          => 4,
			'saayeh_home_show_featured'      => true,
			'saayeh_home_show_posts'         => true,
			'saayeh_editorial_image'         => '',
			'saayeh_editorial_eyebrow'       => __( 'ساخت دست', 'saayeh' ),
			'saayeh_editorial_title'         => __( 'هر رج، با حوصله', 'saayeh' ),
			'saayeh_editorial_text'          => __( 'بافت‌های سایه را بافنده‌های محلی با نخ پشم مرینو و طی چند روز می‌بافند. نتیجه، لباسی است که با هر بار پوشیدن نرم‌تر می‌شود و سال‌ها می‌ماند.', 'saayeh' ),
			'saayeh_editorial_cta_text'      => __( 'مشاهده بافت', 'saayeh' ),
			'saayeh_editorial_cta_link'      => '',
			'saayeh_trust_1_title'           => __( 'ارسال سریع', 'saayeh' ),
			'saayeh_trust_1_text'            => __( 'تحویل ۲ تا ۴ روز کاری', 'saayeh' ),
			'saayeh_trust_2_title'           => __( '۷ روز ضمانت بازگشت', 'saayeh' ),
			'saayeh_trust_2_text'            => __( 'بدون پرسش', 'saayeh' ),
			'saayeh_trust_3_title'           => __( 'پرداخت امن', 'saayeh' ),
			'saayeh_trust_3_text'            => __( 'درگاه بانکی معتبر', 'saayeh' ),
			'saayeh_trust_4_title'           => __( 'پشتیبانی', 'saayeh' ),
			'saayeh_trust_4_text'            => __( 'هر روز، ۹ تا ۲۱', 'saayeh' ),
			'saayeh_newsletter_title'        => __( 'اول از همه باخبر شوید', 'saayeh' ),
			'saayeh_newsletter_text'         => __( 'کالکشن‌های تازه و فروش‌های ویژه، ماهی یک‌بار.', 'saayeh' ),
			'saayeh_free_shipping_threshold' => 3000000,
			'saayeh_social_instagram'        => '',
			'saayeh_social_telegram'         => '',
			'saayeh_social_whatsapp'         => '',
			'saayeh_social_x'                => '',
			'saayeh_social_linkedin'         => '',
			'saayeh_social_youtube'          => '',
			'saayeh_social_aparat'           => '',
			'saayeh_footer_about'            => __( 'سایه از یک کارگاه کوچک در تهران شروع شد؛ با این باور که کمد لباس خوب از چند تکه‌ی درست ساخته می‌شود.', 'saayeh' ),
			'saayeh_trust_badges_html'       => '',
			'saayeh_copyright'               => '',
			'saayeh_contact_email'           => '',
			'saayeh_contact_phone'           => '',
		)
	);
}

/**
 * Get a theme mod with the theme default.
 *
 * @param string $key Mod name.
 * @return mixed
 */
function saayeh_mod( $key ) {
	$d = saayeh_defaults();
	return get_theme_mod( $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

/**
 * Resolve an image theme mod (attachment ID or URL) to a URL, with a bundled fallback.
 *
 * @param string $key      Mod.
 * @param string $fallback File name in demo/images.
 * @param string $size     Image size.
 * @return string
 */
function saayeh_mod_image_url( $key, $fallback = '', $size = 'full' ) {
	$val = get_theme_mod( $key, '' );
	if ( is_numeric( $val ) && (int) $val > 0 ) {
		$url = wp_get_attachment_image_url( (int) $val, $size );
		if ( $url ) {
			return $url;
		}
	} elseif ( is_string( $val ) && '' !== $val && wp_http_validate_url( $val ) ) {
		return $val;
	}
	return $fallback ? SAAYEH_URI . '/demo/images/' . $fallback : '';
}

/**
 * Sanitize checkbox.
 *
 * @param mixed $v Value.
 * @return bool
 */
function saayeh_sanitize_checkbox( $v ) {
	return (bool) $v;
}

/**
 * Sanitize image mod (attachment ID or URL).
 *
 * @param mixed $v Value.
 * @return int|string
 */
function saayeh_sanitize_image( $v ) {
	if ( is_numeric( $v ) ) {
		return absint( $v );
	}
	return esc_url_raw( $v );
}

/**
 * Sanitize badge HTML (eNamad / Samandehi codes): links and images only.
 *
 * @param string $v Value.
 * @return string
 */
function saayeh_sanitize_badges( $v ) {
	return wp_kses(
		$v,
		array(
			'a'   => array( 'href' => true, 'target' => true, 'rel' => true, 'referrerpolicy' => true, 'title' => true, 'id' => true ),
			'img' => array( 'src' => true, 'alt' => true, 'style' => true, 'id' => true, 'width' => true, 'height' => true, 'referrerpolicy' => true, 'code' => true, 'data-code' => true ),
		)
	);
}

/**
 * Register Customizer panels and settings.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function saayeh_customize_register( $wp_customize ) {
	$d = saayeh_defaults();

	$add = function ( $id, $section, $label, $type = 'text', $sanitize = 'sanitize_text_field', $extra = array() ) use ( $wp_customize, $d ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => isset( $d[ $id ] ) ? $d[ $id ] : '',
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);
		if ( 'image' === $type ) {
			$wp_customize->add_control(
				new WP_Customize_Media_Control(
					$wp_customize,
					$id,
					array(
						'label'     => $label,
						'section'   => $section,
						'mime_type' => 'image',
					)
				)
			);
		} elseif ( 'color' === $type ) {
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array( 'label' => $label, 'section' => $section ) ) );
		} else {
			$wp_customize->add_control( $id, array_merge( array( 'label' => $label, 'section' => $section, 'type' => $type ), $extra ) );
		}
	};

	$wp_customize->add_panel( 'saayeh', array( 'title' => __( 'تنظیمات پوسته سایه', 'saayeh' ), 'priority' => 30 ) );

	// General.
	$wp_customize->add_section( 'saayeh_general', array( 'title' => __( 'عمومی و رنگ', 'saayeh' ), 'panel' => 'saayeh' ) );
	$add( 'saayeh_accent_color', 'saayeh_general', __( 'رنگ تأکیدی (شنی)', 'saayeh' ), 'color', 'sanitize_hex_color' );
	$add( 'saayeh_tagline', 'saayeh_general', __( 'شعار کنار لوگو', 'saayeh' ) );
	$add( 'saayeh_announcement', 'saayeh_general', __( 'متن نوار اعلان بالای صفحه', 'saayeh' ) );
	$add( 'saayeh_announcement_link', 'saayeh_general', __( 'لینک نوار اعلان (اختیاری)', 'saayeh' ), 'url', 'esc_url_raw' );
	$add( 'saayeh_free_shipping_threshold', 'saayeh_general', __( 'حد ارسال رایگان برای نوار پیشرفت (تومان، صفر = پنهان)', 'saayeh' ), 'number', 'absint' );
	$add( 'saayeh_force_rtl', 'saayeh_general', __( 'اجبار راست‌چین حتی اگر زبان سایت فارسی نباشد', 'saayeh' ), 'checkbox', 'saayeh_sanitize_checkbox' );
	$add( 'saayeh_persian_digits', 'saayeh_general', __( 'نمایش اعداد فارسی در قیمت‌ها', 'saayeh' ), 'checkbox', 'saayeh_sanitize_checkbox' );

	// Hero.
	$wp_customize->add_section( 'saayeh_hero', array( 'title' => __( 'بخش اصلی صفحه نخست (هیرو)', 'saayeh' ), 'panel' => 'saayeh' ) );
	$add( 'saayeh_hero_image', 'saayeh_hero', __( 'تصویر هیرو', 'saayeh' ), 'image', 'saayeh_sanitize_image' );
	$add( 'saayeh_hero_eyebrow', 'saayeh_hero', __( 'متن کوچک بالای عنوان', 'saayeh' ) );
	$add( 'saayeh_hero_title', 'saayeh_hero', __( 'عنوان', 'saayeh' ) );
	$add( 'saayeh_hero_subtitle', 'saayeh_hero', __( 'زیرعنوان', 'saayeh' ), 'textarea' );
	$add( 'saayeh_hero_cta_text', 'saayeh_hero', __( 'متن دکمه', 'saayeh' ) );
	$add( 'saayeh_hero_cta_link', 'saayeh_hero', __( 'لینک دکمه (خالی = فروشگاه)', 'saayeh' ), 'url', 'esc_url_raw' );

	// Home sections.
	$wp_customize->add_section( 'saayeh_home', array( 'title' => __( 'بخش‌های صفحه نخست', 'saayeh' ), 'panel' => 'saayeh' ) );
	$add( 'saayeh_home_show_cats', 'saayeh_home', __( 'نمایش دسته‌بندی‌ها', 'saayeh' ), 'checkbox', 'saayeh_sanitize_checkbox' );
	$add( 'saayeh_home_new_count', 'saayeh_home', __( 'تعداد محصولات تازه', 'saayeh' ), 'number', 'absint' );
	$add( 'saayeh_home_show_featured', 'saayeh_home', __( 'نمایش محصولات منتخب (ویژه)', 'saayeh' ), 'checkbox', 'saayeh_sanitize_checkbox' );
	$add( 'saayeh_home_show_posts', 'saayeh_home', __( 'نمایش آخرین نوشته‌های وبلاگ', 'saayeh' ), 'checkbox', 'saayeh_sanitize_checkbox' );
	$add( 'saayeh_newsletter_title', 'saayeh_home', __( 'عنوان خبرنامه', 'saayeh' ) );
	$add( 'saayeh_newsletter_text', 'saayeh_home', __( 'متن خبرنامه', 'saayeh' ) );

	// Editorial.
	$wp_customize->add_section( 'saayeh_editorial', array( 'title' => __( 'بخش روایت (تصویر و متن)', 'saayeh' ), 'panel' => 'saayeh' ) );
	$add( 'saayeh_editorial_image', 'saayeh_editorial', __( 'تصویر', 'saayeh' ), 'image', 'saayeh_sanitize_image' );
	$add( 'saayeh_editorial_eyebrow', 'saayeh_editorial', __( 'متن کوچک', 'saayeh' ) );
	$add( 'saayeh_editorial_title', 'saayeh_editorial', __( 'عنوان', 'saayeh' ) );
	$add( 'saayeh_editorial_text', 'saayeh_editorial', __( 'متن', 'saayeh' ), 'textarea', 'sanitize_textarea_field' );
	$add( 'saayeh_editorial_cta_text', 'saayeh_editorial', __( 'متن دکمه', 'saayeh' ) );
	$add( 'saayeh_editorial_cta_link', 'saayeh_editorial', __( 'لینک دکمه', 'saayeh' ), 'url', 'esc_url_raw' );

	// Trust items.
	$wp_customize->add_section( 'saayeh_trust', array( 'title' => __( 'نوار اعتماد', 'saayeh' ), 'panel' => 'saayeh' ) );
	for ( $i = 1; $i <= 4; $i++ ) {
		/* translators: %d: item number */
		$add( "saayeh_trust_{$i}_title", 'saayeh_trust', sprintf( __( 'مورد %d - عنوان', 'saayeh' ), $i ) );
		/* translators: %d: item number */
		$add( "saayeh_trust_{$i}_text", 'saayeh_trust', sprintf( __( 'مورد %d - توضیح', 'saayeh' ), $i ) );
	}

	// Social.
	$wp_customize->add_section( 'saayeh_social', array( 'title' => __( 'شبکه‌های اجتماعی', 'saayeh' ), 'panel' => 'saayeh' ) );
	foreach ( saayeh_social_networks() as $key => $label ) {
		$add( 'saayeh_social_' . $key, 'saayeh_social', $label, 'url', 'esc_url_raw' );
	}

	// Footer.
	$wp_customize->add_section( 'saayeh_footer', array( 'title' => __( 'فوتر و نمادها', 'saayeh' ), 'panel' => 'saayeh' ) );
	$add( 'saayeh_footer_about', 'saayeh_footer', __( 'متن کوتاه درباره فروشگاه', 'saayeh' ), 'textarea', 'sanitize_textarea_field' );
	$add( 'saayeh_trust_badges_html', 'saayeh_footer', __( 'کد نماد اعتماد (اینماد، ساماندهی) - فقط تگ‌های a و img', 'saayeh' ), 'textarea', 'saayeh_sanitize_badges' );
	$add( 'saayeh_copyright', 'saayeh_footer', __( 'متن کپی‌رایت (خالی = پیش‌فرض)', 'saayeh' ) );
	$add( 'saayeh_contact_email', 'saayeh_footer', __( 'ایمیل دریافت پیام‌های فرم تماس (خالی = ایمیل مدیر)', 'saayeh' ), 'email', 'sanitize_email' );
	$add( 'saayeh_contact_phone', 'saayeh_footer', __( 'تلفن پشتیبانی', 'saayeh' ) );
}
add_action( 'customize_register', 'saayeh_customize_register' );

/**
 * Supported social networks.
 *
 * @return array
 */
function saayeh_social_networks() {
	return array(
		'instagram' => __( 'اینستاگرام', 'saayeh' ),
		'telegram'  => __( 'تلگرام', 'saayeh' ),
		'whatsapp'  => __( 'واتس‌اپ', 'saayeh' ),
		'x'         => __( 'ایکس (توییتر)', 'saayeh' ),
		'linkedin'  => __( 'لینکدین', 'saayeh' ),
		'youtube'   => __( 'یوتیوب', 'saayeh' ),
		'aparat'    => __( 'آپارات', 'saayeh' ),
	);
}

/**
 * Output CSS variables for a custom accent color.
 */
function saayeh_accent_css() {
	$accent = sanitize_hex_color( saayeh_mod( 'saayeh_accent_color' ) );
	if ( ! $accent || 0 === strcasecmp( $accent, '#B39B7A' ) ) {
		return '';
	}
	$ink  = saayeh_mix_hex( $accent, '#1C1A17', 0.55 );
	$soft = saayeh_mix_hex( $accent, '#F6F3EE', 0.3 );
	return ':root{--x-sand:' . $accent . ';--x-sand-ink:' . $ink . ';--x-sand-soft:' . $soft . ';}';
}
