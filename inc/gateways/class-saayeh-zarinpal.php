<?php
/**
 * Zarinpal payment gateway (REST API v4) for WooCommerce.
 *
 * Flow:
 *  1. process_payment()  → POST /pg/v4/payment/request.json → redirect to /pg/StartPay/{authority}
 *  2. Zarinpal redirects the customer to the callback (WC API endpoint) with Authority & Status
 *  3. handle_callback()  → POST /pg/v4/payment/verify.json → complete order (code 100/101)
 *
 * No merchant IDs or keys are stored in code; the merchant ID is entered in
 * WooCommerce → Settings → Payments → Zarinpal.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'Saayeh_Gateway_Zarinpal' ) || ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

/**
 * Zarinpal gateway.
 */
class Saayeh_Gateway_Zarinpal extends WC_Payment_Gateway {

	const META_AUTHORITY = '_saayeh_zarinpal_authority';
	const META_REF_ID    = '_saayeh_zarinpal_ref_id';
	const META_CARD_PAN  = '_saayeh_zarinpal_card_pan';
	const META_AMOUNT    = '_saayeh_zarinpal_amount';

	/**
	 * Merchant ID (UUID, 36 chars).
	 *
	 * @var string
	 */
	protected $merchant_id = '';

	/**
	 * Sandbox mode.
	 *
	 * @var bool
	 */
	protected $sandbox = false;

	/**
	 * Debug logging.
	 *
	 * @var bool
	 */
	protected $debug = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                 = 'saayeh_zarinpal';
		$this->method_title       = __( 'زرین‌پال', 'saayeh' );
		$this->method_description = __( 'پرداخت آنلاین با همه‌ی کارت‌های عضو شتاب از طریق درگاه زرین‌پال (API نسخه ۴).', 'saayeh' );
		$this->has_fields         = false;
		$this->supports           = array( 'products' );
		$this->icon               = apply_filters( 'saayeh_zarinpal_icon', '' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );
		$this->merchant_id = trim( (string) $this->get_option( 'merchant_id' ) );
		$this->sandbox     = 'yes' === $this->get_option( 'sandbox' );
		$this->debug       = 'yes' === $this->get_option( 'debug' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_' . $this->id, array( $this, 'handle_callback' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_ref' ) );
	}

	/**
	 * Settings fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'فعال‌سازی', 'saayeh' ),
				'type'    => 'checkbox',
				'label'   => __( 'فعال کردن درگاه زرین‌پال', 'saayeh' ),
				'default' => 'no',
			),
			'title'       => array(
				'title'       => __( 'عنوان', 'saayeh' ),
				'type'        => 'text',
				'description' => __( 'عنوانی که مشتری هنگام پرداخت می‌بیند.', 'saayeh' ),
				'default'     => __( 'درگاه بانکی (زرین‌پال)', 'saayeh' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'   => __( 'توضیحات', 'saayeh' ),
				'type'    => 'textarea',
				'default' => __( 'پرداخت آنلاین با همه‌ی کارت‌های عضو شتاب.', 'saayeh' ),
			),
			'merchant_id' => array(
				'title'       => __( 'مرچنت کد', 'saayeh' ),
				'type'        => 'text',
				'description' => __( 'کد ۳۶ کاراکتری درگاه که در پنل زرین‌پال (بخش درگاه‌ها) دریافت می‌کنید. در حالت آزمایشی هر شناسه‌ی ۳۶ کاراکتری (UUID) پذیرفته می‌شود.', 'saayeh' ),
				'default'     => '',
				'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
				'custom_attributes' => array( 'dir' => 'ltr', 'autocomplete' => 'off' ),
			),
			'sandbox'     => array(
				'title'       => __( 'حالت آزمایشی (سندباکس)', 'saayeh' ),
				'type'        => 'checkbox',
				'label'       => __( 'استفاده از sandbox.zarinpal.com برای تست؛ هیچ پولی جابه‌جا نمی‌شود.', 'saayeh' ),
				'default'     => 'no',
			),
			'debug'       => array(
				'title'       => __( 'گزارش خطا', 'saayeh' ),
				'type'        => 'checkbox',
				'label'       => __( 'ثبت درخواست‌ها و پاسخ‌ها در گزارش ووکامرس (وضعیت → گزارش‌ها)', 'saayeh' ),
				'default'     => 'no',
			),
		);
	}

	/**
	 * Available only with a merchant ID and a Toman/Rial currency.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( ! parent::is_available() ) {
			return false;
		}
		if ( '' === $this->merchant_id ) {
			return false;
		}
		return null !== $this->rial_multiplier( get_woocommerce_currency() );
	}

	/**
	 * Admin notice in settings when currency is unsupported.
	 */
	public function admin_options() {
		if ( null === $this->rial_multiplier( get_woocommerce_currency() ) ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'زرین‌پال فقط با واحد پول تومان (IRT) یا ریال (IRR) کار می‌کند. واحد پول فروشگاه را در تنظیمات ووکامرس تغییر دهید.', 'saayeh' ) . '</p></div>';
		}
		parent::admin_options();
	}

	/**
	 * Multiplier from store currency to Rial.
	 *
	 * @param string $currency Currency code.
	 * @return int|null
	 */
	protected function rial_multiplier( $currency ) {
		$map = (array) apply_filters(
			'saayeh_zarinpal_currency_multipliers',
			array(
				'IRT'  => 10,    // Toman.
				'IRR'  => 1,     // Rial.
				'IRHT' => 10000, // Thousand Toman (used by some Persian plugins).
				'IRHR' => 1000,  // Thousand Rial.
			)
		);
		$currency = strtoupper( (string) $currency );
		return isset( $map[ $currency ] ) ? (int) $map[ $currency ] : null;
	}

	/**
	 * Order total in Rial (integer).
	 *
	 * @param WC_Order $order Order.
	 * @return int
	 */
	protected function amount_in_rial( $order ) {
		$mult = $this->rial_multiplier( $order->get_currency() );
		$mult = null === $mult ? 10 : $mult;
		return (int) round( (float) $order->get_total() * $mult );
	}

	/**
	 * API host.
	 *
	 * @return string
	 */
	protected function api_host() {
		return $this->sandbox ? 'https://sandbox.zarinpal.com' : 'https://api.zarinpal.com';
	}

	/**
	 * StartPay URL.
	 *
	 * @param string $authority Authority.
	 * @return string
	 */
	protected function startpay_url( $authority ) {
		$host = $this->sandbox ? 'https://sandbox.zarinpal.com' : 'https://www.zarinpal.com';
		return $host . '/pg/StartPay/' . rawurlencode( $authority );
	}

	/**
	 * Merchant ID to send (in sandbox mode any UUID-shaped value is accepted by Zarinpal).
	 *
	 * @return string
	 */
	protected function merchant() {
		return $this->merchant_id;
	}

	/**
	 * POST JSON to Zarinpal.
	 *
	 * @param string $path Path (e.g. /pg/v4/payment/request.json).
	 * @param array  $body Body.
	 * @return array{ok:bool,data:array,errors:array,message:string}
	 */
	protected function api( $path, $body ) {
		$url      = $this->api_host() . $path;
		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 30,
				'headers' => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
					'User-Agent'   => 'Saayeh-WooCommerce/' . ( defined( 'SAAYEH_VERSION' ) ? SAAYEH_VERSION : '1.0' ),
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		$this->log( 'POST ' . $url . ' ' . wp_json_encode( $this->redact( $body ) ) );

		if ( is_wp_error( $response ) ) {
			$this->log( 'HTTP error: ' . $response->get_error_message(), 'error' );
			return array(
				'ok'      => false,
				'data'    => array(),
				'errors'  => array(),
				'message' => __( 'ارتباط با درگاه زرین‌پال برقرار نشد. لطفاً چند لحظه بعد دوباره تلاش کنید.', 'saayeh' ),
			);
		}

		$raw  = wp_remote_retrieve_body( $response );
		$json = json_decode( $raw, true );
		$this->log( 'Response ' . wp_remote_retrieve_response_code( $response ) . ': ' . $raw );

		$data   = isset( $json['data'] ) && is_array( $json['data'] ) ? $json['data'] : array();
		$errors = isset( $json['errors'] ) && is_array( $json['errors'] ) ? $json['errors'] : array();
		$code   = isset( $data['code'] ) ? (int) $data['code'] : ( isset( $errors['code'] ) ? (int) $errors['code'] : 0 );

		return array(
			'ok'      => in_array( $code, array( 100, 101 ), true ),
			'code'    => $code,
			'data'    => $data,
			'errors'  => $errors,
			'message' => $this->error_message( $code, $errors ),
		);
	}

	/**
	 * Persian error messages for Zarinpal codes.
	 *
	 * @param int   $code   Code.
	 * @param array $errors Raw errors.
	 * @return string
	 */
	protected function error_message( $code, $errors = array() ) {
		$messages = array(
			-9  => __( 'اطلاعات ارسال‌شده به درگاه معتبر نیست (خطای اعتبارسنجی).', 'saayeh' ),
			-10 => __( 'آی‌پی یا مرچنت کد پذیرنده صحیح نیست.', 'saayeh' ),
			-11 => __( 'مرچنت کد فعال نیست؛ با پشتیبانی زرین‌پال تماس بگیرید.', 'saayeh' ),
			-12 => __( 'تلاش‌های بیش از حد در مدت کوتاه؛ کمی بعد دوباره تلاش کنید.', 'saayeh' ),
			-15 => __( 'درگاه پرداخت به حالت تعلیق درآمده است.', 'saayeh' ),
			-16 => __( 'سطح تأیید پذیرنده پایین‌تر از سطح نقره‌ای است.', 'saayeh' ),
			-17 => __( 'محدودیت پذیرنده در سطح آبی.', 'saayeh' ),
			-30 => __( 'پذیرنده اجازه‌ی دسترسی به سرویس تسویه اشتراکی شناور را ندارد.', 'saayeh' ),
			-31 => __( 'حساب بانکی تسویه را به پنل اضافه کنید.', 'saayeh' ),
			-33 => __( 'درصدهای واردشده صحیح نیست.', 'saayeh' ),
			-34 => __( 'مبلغ از کل تراکنش بیشتر است.', 'saayeh' ),
			-35 => __( 'تعداد افراد دریافت‌کننده تسهیم بیش از حد مجاز است.', 'saayeh' ),
			-40 => __( 'پارامترهای اضافی نامعتبر است.', 'saayeh' ),
			-50 => __( 'مبلغ پرداخت‌شده با مبلغ سفارش یکسان نیست.', 'saayeh' ),
			-51 => __( 'پرداخت ناموفق بود.', 'saayeh' ),
			-52 => __( 'خطای غیرمنتظره در درگاه؛ با پشتیبانی تماس بگیرید.', 'saayeh' ),
			-53 => __( 'این پرداخت متعلق به این پذیرنده نیست.', 'saayeh' ),
			-54 => __( 'شناسه‌ی پرداخت (Authority) نامعتبر است.', 'saayeh' ),
			-55 => __( 'تراکنش مورد نظر یافت نشد.', 'saayeh' ),
			101 => __( 'این تراکنش قبلاً تأیید شده است.', 'saayeh' ),
		);
		if ( isset( $messages[ $code ] ) ) {
			return $messages[ $code ];
		}
		if ( ! empty( $errors['message'] ) ) {
			/* translators: 1: code, 2: message */
			return sprintf( __( 'خطای درگاه (%1$s): %2$s', 'saayeh' ), $code, sanitize_text_field( $errors['message'] ) );
		}
		/* translators: %s: code */
		return $code ? sprintf( __( 'خطای ناشناخته از درگاه (کد %s).', 'saayeh' ), $code ) : __( 'پاسخ نامعتبر از درگاه دریافت شد.', 'saayeh' );
	}

	/**
	 * Step 1: request a payment authority and redirect to StartPay.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wc_add_notice( __( 'سفارش پیدا نشد.', 'saayeh' ), 'error' );
			return array( 'result' => 'failure' );
		}

		$amount = $this->amount_in_rial( $order );
		if ( $amount < 10000 ) {
			wc_add_notice( __( 'حداقل مبلغ قابل پرداخت با زرین‌پال ۱۰۰۰ تومان است.', 'saayeh' ), 'error' );
			return array( 'result' => 'failure' );
		}

		$callback = add_query_arg(
			array(
				'order_id' => $order->get_id(),
				'key'      => $order->get_order_key(),
			),
			WC()->api_request_url( $this->id )
		);

		$metadata = array_filter(
			array(
				'mobile'   => $this->normalize_mobile( $order->get_billing_phone() ),
				'email'    => $order->get_billing_email(),
				'order_id' => (string) $order->get_id(),
			)
		);

		/* translators: 1: order id, 2: site name */
		$description = sprintf( __( 'پرداخت سفارش %1$s - %2$s', 'saayeh' ), $order->get_id(), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );

		$body = apply_filters(
			'saayeh_zarinpal_request_body',
			array(
				'merchant_id'  => $this->merchant(),
				'amount'       => $amount,
				'currency'     => 'IRR',
				'callback_url' => $callback,
				'description'  => $description,
				'metadata'     => (object) $metadata,
			),
			$order,
			$this
		);

		$res = $this->api( '/pg/v4/payment/request.json', $body );

		if ( 100 === (int) ( isset( $res['data']['code'] ) ? $res['data']['code'] : 0 ) && ! empty( $res['data']['authority'] ) ) {
			$authority = sanitize_text_field( $res['data']['authority'] );
			$order->update_meta_data( self::META_AUTHORITY, $authority );
			$order->update_meta_data( self::META_AMOUNT, $amount );
			if ( $this->sandbox ) {
				$order->update_meta_data( '_saayeh_zarinpal_sandbox', 'yes' );
			}
			/* translators: %s: authority */
			$order->add_order_note( sprintf( __( 'انتقال مشتری به زرین‌پال. شناسه پرداخت (Authority): %s', 'saayeh' ), $authority ) );
			$order->save();

			return array(
				'result'   => 'success',
				'redirect' => $this->startpay_url( $authority ),
			);
		}

		/* translators: %s: error */
		$order->add_order_note( sprintf( __( 'خطا در ایجاد تراکنش زرین‌پال: %s', 'saayeh' ), $res['message'] ) );
		wc_add_notice( $res['message'], 'error' );
		return array( 'result' => 'failure' );
	}

	/**
	 * Step 2: callback from Zarinpal (WC API endpoint).
	 */
	public function handle_callback() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- external redirect; verified via order key + authority.
		$order_id  = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
		$key       = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';
		$authority = isset( $_GET['Authority'] ) ? sanitize_text_field( wp_unslash( $_GET['Authority'] ) ) : '';
		$status    = isset( $_GET['Status'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['Status'] ) ) ) : '';
		// phpcs:enable

		$order = $order_id ? wc_get_order( $order_id ) : false;
		if ( ! $order || ! hash_equals( $order->get_order_key(), (string) $key ) ) {
			wc_add_notice( __( 'سفارش معتبر نیست.', 'saayeh' ), 'error' );
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}

		// Already paid (e.g. page refresh) → thank you page.
		if ( $order->is_paid() ) {
			wp_safe_redirect( $this->get_return_url( $order ) );
			exit;
		}

		$stored = (string) $order->get_meta( self::META_AUTHORITY );
		if ( '' === $authority || ( '' !== $stored && ! hash_equals( $stored, $authority ) ) ) {
			$this->fail( $order, __( 'شناسه پرداخت با سفارش هم‌خوانی ندارد.', 'saayeh' ) );
		}

		if ( 'OK' !== $status ) {
			$order->add_order_note( __( 'پرداخت توسط مشتری لغو شد یا ناموفق بود (Status=NOK).', 'saayeh' ) );
			$this->fail( $order, __( 'پرداخت لغو شد یا ناموفق بود. می‌توانید دوباره تلاش کنید یا روش پرداخت دیگری را انتخاب کنید.', 'saayeh' ), false );
		}

		$amount = (int) $order->get_meta( self::META_AMOUNT );
		if ( $amount <= 0 ) {
			$amount = $this->amount_in_rial( $order );
		}

		$res = $this->api(
			'/pg/v4/payment/verify.json',
			array(
				'merchant_id' => $this->merchant(),
				'amount'      => $amount,
				'authority'   => $authority,
			)
		);

		$code = isset( $res['data']['code'] ) ? (int) $res['data']['code'] : ( isset( $res['code'] ) ? (int) $res['code'] : 0 );
		if ( in_array( $code, array( 100, 101 ), true ) ) {
			$ref_id   = isset( $res['data']['ref_id'] ) ? sanitize_text_field( (string) $res['data']['ref_id'] ) : '';
			$card_pan = isset( $res['data']['card_pan'] ) ? sanitize_text_field( (string) $res['data']['card_pan'] ) : '';

			$order->update_meta_data( self::META_REF_ID, $ref_id );
			if ( $card_pan ) {
				$order->update_meta_data( self::META_CARD_PAN, $card_pan );
			}
			$order->add_order_note(
				sprintf(
					/* translators: 1: ref id, 2: card, 3: code */
					__( 'پرداخت زرین‌پال موفق بود. کد پیگیری: %1$s | کارت: %2$s | کد پاسخ: %3$s', 'saayeh' ),
					$ref_id ? $ref_id : '—',
					$card_pan ? $card_pan : '—',
					$code
				)
			);
			$order->save();
			$order->payment_complete( $ref_id );
			if ( WC()->cart ) {
				WC()->cart->empty_cart();
			}
			do_action( 'saayeh_zarinpal_payment_complete', $order, $res['data'] );
			wp_safe_redirect( $this->get_return_url( $order ) );
			exit;
		}

		/* translators: %s: error */
		$order->add_order_note( sprintf( __( 'تأیید پرداخت زرین‌پال ناموفق بود: %s', 'saayeh' ), $res['message'] ) );
		$this->fail( $order, $res['message'] );
	}

	/**
	 * Mark failed, add notice, redirect to the pay page, exit.
	 *
	 * @param WC_Order $order   Order.
	 * @param string   $message Message.
	 * @param bool     $failed  Set status to failed (otherwise keep pending for retry).
	 */
	protected function fail( $order, $message, $failed = true ) {
		if ( $failed && ! $order->has_status( 'failed' ) ) {
			$order->update_status( 'failed', $message );
		}
		wc_add_notice( $message, 'error' );
		wp_safe_redirect( $order->get_checkout_payment_url() );
		exit;
	}

	/**
	 * Show tracking code on the thank-you page.
	 *
	 * @param int $order_id Order.
	 */
	public function thankyou_ref( $order_id ) {
		$order = wc_get_order( $order_id );
		$ref   = $order ? $order->get_meta( self::META_REF_ID ) : '';
		if ( $ref ) {
			echo '<p class="x-muted">' . esc_html__( 'کد پیگیری پرداخت:', 'saayeh' ) . ' <b dir="ltr">' . esc_html( $ref ) . '</b></p>';
		}
	}

	/**
	 * Normalise an Iranian mobile number to 09xxxxxxxxx.
	 *
	 * @param string $mobile Mobile.
	 * @return string
	 */
	protected function normalize_mobile( $mobile ) {
		$m = preg_replace( '/\D/', '', function_exists( 'saayeh_en_digits' ) ? saayeh_en_digits( (string) $mobile ) : (string) $mobile );
		if ( preg_match( '/^(?:98|0098)?0?(9\d{9})$/', $m, $mm ) ) {
			return '0' . $mm[1];
		}
		return '';
	}

	/**
	 * Remove the merchant ID from logs.
	 *
	 * @param array $body Body.
	 * @return array
	 */
	protected function redact( $body ) {
		if ( isset( $body['merchant_id'] ) ) {
			$body['merchant_id'] = substr( (string) $body['merchant_id'], 0, 4 ) . '…';
		}
		return $body;
	}

	/**
	 * Log when debug is on.
	 *
	 * @param string $message Message.
	 * @param string $level   Level.
	 */
	protected function log( $message, $level = 'info' ) {
		if ( $this->debug && function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->log( $level, $message, array( 'source' => $this->id ) );
		}
	}
}
