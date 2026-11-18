<?php
/**
 * Checkout: Iranian address fields, provinces, validation, shipping rules.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Iran provinces in Persian, keyed by WooCommerce's IR state codes.
 *
 * @return array
 */
function saayeh_iran_provinces() {
	return array(
		'THR' => 'تهران',
		'ABZ' => 'البرز',
		'ESF' => 'اصفهان',
		'FRS' => 'فارس',
		'RKH' => 'خراسان رضوی',
		'EAZ' => 'آذربایجان شرقی',
		'WAZ' => 'آذربایجان غربی',
		'ADL' => 'اردبیل',
		'BHR' => 'بوشهر',
		'CHB' => 'چهارمحال و بختیاری',
		'SKH' => 'خراسان جنوبی',
		'NKH' => 'خراسان شمالی',
		'KHZ' => 'خوزستان',
		'ZJN' => 'زنجان',
		'SMN' => 'سمنان',
		'SBN' => 'سیستان و بلوچستان',
		'QHM' => 'قم',
		'GZN' => 'قزوین',
		'KRD' => 'کردستان',
		'KRN' => 'کرمان',
		'KRH' => 'کرمانشاه',
		'KBD' => 'کهگیلویه و بویراحمد',
		'GLS' => 'گلستان',
		'GIL' => 'گیلان',
		'LRS' => 'لرستان',
		'MZN' => 'مازندران',
		'MKZ' => 'مرکزی',
		'HRZ' => 'هرمزگان',
		'HDN' => 'همدان',
		'ILM' => 'ایلام',
		'YZD' => 'یزد',
	);
}

/**
 * Replace IR states with Persian names.
 *
 * @param array $states States.
 * @return array
 */
function saayeh_states( $states ) {
	$states['IR'] = saayeh_iran_provinces();
	return $states;
}
add_filter( 'woocommerce_states', 'saayeh_states' );

/**
 * Default checkout country = Iran.
 *
 * @param string $value Value.
 * @return string
 */
function saayeh_default_country( $value ) {
	return $value ? $value : 'IR';
}
add_filter( 'default_checkout_billing_country', 'saayeh_default_country' );
add_filter( 'default_checkout_shipping_country', 'saayeh_default_country' );

/**
 * Persian, Iran-friendly default address fields.
 *
 * @param array $fields Fields.
 * @return array
 */
function saayeh_address_fields( $fields ) {
	unset( $fields['company'] );

	$fields['first_name']['label']        = __( 'نام', 'saayeh' );
	$fields['first_name']['autocomplete'] = 'given-name';
	$fields['last_name']['label']         = __( 'نام خانوادگی', 'saayeh' );
	$fields['last_name']['autocomplete']  = 'family-name';
	if ( isset( $fields['country'] ) ) {
		$fields['country']['label']    = __( 'کشور', 'saayeh' );
		$fields['country']['priority'] = 30;
	}
	$fields['state']['label']    = __( 'استان', 'saayeh' );
	$fields['state']['required'] = true;
	$fields['state']['priority'] = 50;
	$fields['state']['class']    = array( 'form-row-first', 'address-field' );

	$fields['city']['label']    = __( 'شهر', 'saayeh' );
	$fields['city']['priority'] = 60;
	$fields['city']['class']    = array( 'form-row-last', 'address-field' );

	$fields['address_1']['label']       = __( 'آدرس کامل', 'saayeh' );
	$fields['address_1']['placeholder'] = __( 'خیابان، کوچه، پلاک', 'saayeh' );
	$fields['address_1']['priority']    = 70;
	$fields['address_1']['class']       = array( 'form-row-wide', 'address-field' );

	if ( isset( $fields['address_2'] ) ) {
		$fields['address_2']['label']       = __( 'واحد / طبقه', 'saayeh' );
		$fields['address_2']['label_class'] = array();
		$fields['address_2']['placeholder'] = __( 'واحد، طبقه (اختیاری)', 'saayeh' );
		$fields['address_2']['priority']    = 80;
		$fields['address_2']['class']       = array( 'form-row-first', 'address-field' );
		$fields['address_2']['required']    = false;
	}

	if ( isset( $fields['phone'] ) ) {
		$fields['phone'] = array_merge( $fields['phone'], saayeh_phone_field_args() );
	}

	$fields['postcode']['label']             = __( 'کد پستی ۱۰ رقمی', 'saayeh' );
	$fields['postcode']['priority']          = 90;
	$fields['postcode']['required']          = true;
	$fields['postcode']['class']             = array( 'form-row-last', 'address-field' );
	$fields['postcode']['custom_attributes'] = array(
		'inputmode' => 'numeric',
		'dir'       => 'ltr',
		'maxlength' => '10',
	);
	return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'saayeh_address_fields', 20 );

/**
 * Mobile phone field arguments.
 *
 * @return array
 */
function saayeh_phone_field_args() {
	return array(
		'label'             => __( 'شماره موبایل', 'saayeh' ),
		'required'          => true,
		'type'              => 'tel',
		'placeholder'       => '09xxxxxxxxx',
		'priority'          => 22,
		'class'             => array( 'form-row-first' ),
		'validate'          => array( 'phone' ),
		'autocomplete'      => 'tel',
		'custom_attributes' => array(
			'inputmode' => 'numeric',
			'dir'       => 'ltr',
			'maxlength' => '11',
		),
	);
}

/**
 * Locale overrides so WooCommerce's address JS keeps our labels for Iran.
 *
 * @param array $locale Locales.
 * @return array
 */
function saayeh_country_locale( $locale ) {
	$locale['IR'] = array(
		'state'     => array( 'label' => __( 'استان', 'saayeh' ), 'required' => true, 'priority' => 50 ),
		'city'      => array( 'label' => __( 'شهر', 'saayeh' ), 'priority' => 60 ),
		'address_1' => array( 'label' => __( 'آدرس کامل', 'saayeh' ), 'priority' => 70 ),
		'address_2' => array( 'priority' => 80, 'required' => false ),
		'postcode'  => array( 'label' => __( 'کد پستی ۱۰ رقمی', 'saayeh' ), 'required' => true, 'priority' => 90 ),
		'phone'     => array( 'label' => __( 'شماره موبایل', 'saayeh' ), 'required' => true, 'priority' => 22 ),
	);
	return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'saayeh_country_locale' );

/**
 * Billing: mobile number required.
 *
 * @param array $fields Fields.
 * @return array
 */
function saayeh_billing_fields( $fields ) {
	if ( isset( $fields['billing_phone'] ) ) {
		$fields['billing_phone'] = array_merge( $fields['billing_phone'], saayeh_phone_field_args() );
	}
	if ( isset( $fields['billing_email'] ) ) {
		$fields['billing_email']['label']             = __( 'ایمیل', 'saayeh' );
		$fields['billing_email']['priority']          = 24;
		$fields['billing_email']['class']             = array( 'form-row-last' );
		$fields['billing_email']['custom_attributes'] = array( 'dir' => 'ltr' );
		$fields['billing_email']['required']          = (bool) apply_filters( 'saayeh_require_email', true );
	}
	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'saayeh_billing_fields', 20 );

/**
 * Order notes label.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function saayeh_checkout_fields( $fields ) {
	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = __( 'توضیحات سفارش', 'saayeh' );
		$fields['order']['order_comments']['placeholder'] = __( 'مثلاً زمان مناسب برای تحویل (اختیاری)', 'saayeh' );
	}
	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'saayeh_checkout_fields', 20 );

/**
 * Normalise Persian digits in posted checkout data before validation.
 *
 * @param array $data Data.
 * @return array
 */
function saayeh_checkout_posted_data( $data ) {
	foreach ( array( 'billing_phone', 'shipping_phone', 'billing_postcode', 'shipping_postcode' ) as $key ) {
		if ( isset( $data[ $key ] ) ) {
			$data[ $key ] = preg_replace( '/[\s\-]/', '', saayeh_en_digits( $data[ $key ] ) );
		}
	}
	if ( isset( $data['billing_phone'] ) && preg_match( '/^(?:\+98|0098|98)(9\d{9})$/', $data['billing_phone'], $m ) ) {
		$data['billing_phone'] = '0' . $m[1];
	}
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'saayeh_checkout_posted_data' );

/**
 * Validate Iranian mobile + postcode.
 *
 * @param array    $data   Posted data.
 * @param WP_Error $errors Errors.
 */
function saayeh_checkout_validate( $data, $errors ) {
	$country = isset( $data['billing_country'] ) ? $data['billing_country'] : 'IR';
	if ( 'IR' !== $country && '' !== $country ) {
		return;
	}
	if ( ! empty( $data['billing_phone'] ) && ! saayeh_is_ir_mobile( $data['billing_phone'] ) ) {
		$errors->add( 'billing_phone_validation', __( '<strong>شماره موبایل</strong> باید ۱۱ رقم و با ۰۹ شروع شود.', 'saayeh' ), array( 'id' => 'billing_phone' ) );
	}
	$pc_keys = array( 'billing_postcode' );
	if ( ! empty( $data['ship_to_different_address'] ) ) {
		$pc_keys[] = 'shipping_postcode';
	}
	foreach ( $pc_keys as $key ) {
		if ( ! empty( $data[ $key ] ) && ! saayeh_is_ir_postcode( $data[ $key ] ) ) {
			$errors->add( $key . '_validation', __( '<strong>کد پستی</strong> باید دقیقاً ۱۰ رقم باشد.', 'saayeh' ), array( 'id' => $key ) );
		}
	}
}
add_action( 'woocommerce_after_checkout_validation', 'saayeh_checkout_validate', 10, 2 );

/**
 * Also validate on My Account → addresses.
 *
 * @param int    $user_id      User.
 * @param string $address_type billing|shipping.
 */
function saayeh_account_address_validate( $user_id, $address_type ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce verified the nonce before this hook.
	$pc_key = $address_type . '_postcode';
	if ( isset( $_POST[ $pc_key ] ) ) {
		$pc = saayeh_en_digits( sanitize_text_field( wp_unslash( $_POST[ $pc_key ] ) ) );
		if ( '' !== $pc && ! saayeh_is_ir_postcode( $pc ) ) {
			wc_add_notice( __( 'کد پستی باید دقیقاً ۱۰ رقم باشد.', 'saayeh' ), 'error' );
		}
	}
	if ( 'billing' === $address_type && isset( $_POST['billing_phone'] ) ) {
		$ph = saayeh_en_digits( sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) );
		if ( '' !== $ph && ! saayeh_is_ir_mobile( $ph ) ) {
			wc_add_notice( __( 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.', 'saayeh' ), 'error' );
		}
	}
	// phpcs:enable
}
add_action( 'woocommerce_after_save_address_validation', 'saayeh_account_address_validate', 10, 2 );

/**
 * Iranian mobile check.
 *
 * @param string $v Value.
 * @return bool
 */
function saayeh_is_ir_mobile( $v ) {
	return (bool) preg_match( '/^09\d{9}$/', saayeh_en_digits( trim( (string) $v ) ) );
}

/**
 * Iranian postcode check.
 *
 * @param string $v Value.
 * @return bool
 */
function saayeh_is_ir_postcode( $v ) {
	return (bool) preg_match( '/^\d{10}$/', saayeh_en_digits( trim( (string) $v ) ) );
}

/**
 * Store normalised postcode/phone for Iran.
 *
 * @param bool   $valid    Valid.
 * @param string $postcode Postcode.
 * @param string $country  Country.
 * @return bool
 */
function saayeh_postcode_valid( $valid, $postcode, $country ) {
	if ( 'IR' === $country ) {
		return saayeh_is_ir_postcode( $postcode );
	}
	return $valid;
}
add_filter( 'woocommerce_validate_postcode', 'saayeh_postcode_valid', 10, 3 );

/**
 * Shipping rules:
 *  - rates whose label contains «تهران» (e.g. پیک تهران) are only offered when the province is Tehran;
 *  - when a free-shipping rate is available, other paid «post» flat rates are hidden.
 *
 * @param array $rates   Rates.
 * @param array $package Package.
 * @return array
 */
function saayeh_package_rates( $rates, $package ) {
	$state = isset( $package['destination']['state'] ) ? $package['destination']['state'] : '';
	$has_free = false;
	foreach ( $rates as $rate ) {
		if ( 'free_shipping' === $rate->get_method_id() ) {
			$has_free = true;
		}
	}
	foreach ( $rates as $id => $rate ) {
		$is_tehran_only = (bool) apply_filters( 'saayeh_is_tehran_only_rate', false !== mb_strpos( $rate->get_label(), 'تهران' ), $rate );
		if ( $is_tehran_only && 'THR' !== $state ) {
			unset( $rates[ $id ] );
			continue;
		}
		if ( $has_free && apply_filters( 'saayeh_hide_paid_when_free', true ) && 'flat_rate' === $rate->get_method_id() && ! $is_tehran_only ) {
			unset( $rates[ $id ] );
		}
	}
	return $rates;
}
add_filter( 'woocommerce_package_rates', 'saayeh_package_rates', 20, 2 );

/**
 * Checkout stepper markup.
 */
function saayeh_checkout_stepper() {
	$steps = array(
		'details'  => __( 'مشخصات', 'saayeh' ),
		'address'  => __( 'آدرس', 'saayeh' ),
		'shipping' => __( 'ارسال', 'saayeh' ),
		'payment'  => __( 'پرداخت', 'saayeh' ),
	);
	echo '<ol class="x-stepper" data-saayeh-stepper aria-label="' . esc_attr__( 'مراحل خرید', 'saayeh' ) . '">';
	$i = 0;
	foreach ( $steps as $key => $label ) {
		++$i;
		printf(
			'<li class="%1$s" data-step="%2$s">%3$s. %4$s</li>',
			1 === $i ? 'is-on' : '',
			esc_attr( $key ),
			esc_html( saayeh_fa_digits( $i ) ),
			esc_html( $label )
		);
	}
	echo '</ol>';
}

/**
 * Order button text.
 *
 * @return string
 */
function saayeh_order_button_text() {
	return __( 'ثبت سفارش و پرداخت', 'saayeh' );
}
add_filter( 'woocommerce_order_button_text', 'saayeh_order_button_text' );
