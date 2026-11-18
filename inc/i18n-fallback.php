<?php
/**
 * Persian fallback for the most visible WooCommerce strings.
 *
 * The theme is Persian-first. When the site language is not Persian (and so
 * WooCommerce's fa_IR translation is not loaded), these key storefront strings
 * are still shown in Persian. With the site language set to فارسی, WooCommerce's
 * own translation is used and this map is skipped.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the fallback should run.
 *
 * @return bool
 */
function saayeh_i18n_fallback_enabled() {
	static $on = null;
	if ( null === $on ) {
		$on = ! is_admin() && 0 !== strpos( (string) determine_locale(), 'fa' ) && (bool) apply_filters( 'saayeh_wc_persian_fallback', true );
	}
	return $on;
}

/**
 * Map of WooCommerce source strings → Persian.
 *
 * @return array
 */
function saayeh_i18n_map() {
	return array(
		'Billing details'                  => 'مشخصات و آدرس گیرنده',
		'Billing &amp; Shipping'           => 'مشخصات و آدرس گیرنده',
		'Ship to a different address?'     => 'ارسال به آدرس دیگر؟',
		'Additional information'           => 'اطلاعات تکمیلی',
		'Have a coupon?'                   => 'کد تخفیف دارید؟',
		'Click here to enter your code'    => 'برای وارد کردن کد کلیک کنید',
		'If you have a coupon code, please apply it below.' => 'اگر کد تخفیف دارید، آن را این‌جا وارد کنید.',
		'Coupon code'                      => 'کد تخفیف',
		'Coupon:'                          => 'کد تخفیف:',
		'Apply coupon'                     => 'اعمال کد',
		'Coupon: %s'                       => 'کد تخفیف: %s',
		'[Remove]'                         => '[حذف]',
		'Coupon code applied successfully.' => 'کد تخفیف با موفقیت اعمال شد.',
		'Coupon code removed successfully.' => 'کد تخفیف حذف شد.',
		'Coupon has been removed.'         => 'کد تخفیف حذف شد.',
		'Please enter a coupon code.'      => 'لطفاً کد تخفیف را وارد کنید.',
		'Coupon "%s" does not exist!'      => 'کد تخفیف «%s» معتبر نیست.',
		'Coupon "%s" cannot be applied because it does not exist.' => 'کد تخفیف «%s» معتبر نیست.',
		'Coupon code already applied!'     => 'این کد تخفیف قبلاً اعمال شده است.',
		'Subtotal'                         => 'جمع کالاها',
		'Subtotal:'                        => 'جمع کالاها:',
		'Total'                            => 'مبلغ نهایی',
		'Total:'                           => 'مبلغ نهایی:',
		'Shipping'                         => 'ارسال',
		'Shipment'                         => 'ارسال',
		'Shipment %d'                      => 'ارسال %d',
		'Shipping to %s.'                  => 'ارسال به %s.',
		'Change address'                   => 'تغییر آدرس',
		'Calculate shipping'               => 'محاسبه هزینه ارسال',
		'Product'                          => 'محصول',
		'Price'                            => 'قیمت',
		'Quantity'                         => 'تعداد',
		'Update cart'                      => 'به‌روزرسانی سبد',
		'Proceed to checkout'              => 'ادامه و تسویه حساب',
		'Cart totals'                      => 'جمع سبد خرید',
		'Your cart is currently empty.'    => 'سبد خرید شما خالی است.',
		'Return to shop'                   => 'بازگشت به فروشگاه',
		'Remove this item'                 => 'حذف این کالا',
		'You may be interested in&hellip;' => 'شاید این‌ها را هم بپسندید',
		'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.' => 'در حال حاضر روش پرداختی در دسترس نیست. برای راهنمایی با ما تماس بگیرید.',
		'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.' => 'اطلاعات شخصی شما برای پردازش سفارش و بهبود تجربه‌ی شما در این وب‌سایت، طبق %s استفاده می‌شود.',
		'privacy policy'                   => 'سیاست حفظ حریم خصوصی',
		'optional'                         => 'اختیاری',
		'Country / Region'                 => 'کشور',
		'Phone'                            => 'شماره موبایل',
		'Email address'                    => 'ایمیل',
		'Create an account?'               => 'ساخت حساب کاربری',
		'Choose an option'                 => 'انتخاب کنید',
		'Clear'                            => 'پاک کردن انتخاب',
		'Add to cart'                      => 'افزودن به سبد',
		'Select options'                   => 'انتخاب گزینه‌ها',
		'Read more'                        => 'بیشتر',
		'View cart'                        => 'مشاهده سبد',
		'Out of stock'                     => 'ناموجود',
		'In stock'                         => 'موجود',
		'%s in stock'                      => '%s عدد در انبار',
		'Only %s left in stock'            => 'فقط %s عدد باقی مانده',
		'SKU:'                             => 'شناسه کالا:',
		'Description'                      => 'توضیحات',
		'Reviews'                          => 'نظرات',
		'Your review'                      => 'نظر شما',
		'Your rating'                      => 'امتیاز شما',
		'Submit'                           => 'ارسال',
		'There are no reviews yet.'        => 'هنوز نظری ثبت نشده است.',
		'Only logged in customers who have purchased this product may leave a review.' => 'فقط مشتریانی که این محصول را خریده‌اند می‌توانند نظر بدهند.',
		'Login'                            => 'ورود',
		'Log in'                           => 'ورود',
		'Register'                         => 'ثبت‌نام',
		'Username or email address'        => 'نام کاربری یا ایمیل',
		'Password'                         => 'رمز عبور',
		'Remember me'                      => 'مرا به خاطر بسپار',
		'Lost your password?'              => 'رمز عبورتان را فراموش کرده‌اید؟',
		'Dashboard'                        => 'پیشخوان',
		'Orders'                           => 'سفارش‌های من',
		'Downloads'                        => 'دانلودها',
		'Addresses'                        => 'آدرس‌ها',
		'Account details'                  => 'جزئیات حساب',
		'Log out'                          => 'خروج',
		'Order details'                    => 'جزئیات سفارش',
		'Billing address'                  => 'آدرس صورتحساب',
		'Shipping address'                 => 'آدرس ارسال',
		'Payment method:'                  => 'روش پرداخت:',
		'Note:'                            => 'یادداشت:',
		'Order again'                      => 'سفارش دوباره',
		'Thank you. Your order has been received.' => 'سپاس! سفارش شما دریافت شد.',
		'Order'                            => 'سفارش',
		'Date'                             => 'تاریخ',
		'Status'                           => 'وضعیت',
		'Actions'                          => 'عملیات',
		'View'                             => 'مشاهده',
		'Pay'                              => 'پرداخت',
		'Cancel'                           => 'لغو',
		'Save address'                     => 'ذخیره آدرس',
		'Save changes'                     => 'ذخیره تغییرات',
		'Edit'                             => 'ویرایش',
		'Add'                              => 'افزودن',
		'No products were found matching your selection.' => 'محصولی با این مشخصات پیدا نشد.',
		'Pay for order'                    => 'پرداخت سفارش',
		'%s has been added to your cart.'  => '%s به سبد خرید اضافه شد.',
		'&ldquo;%s&rdquo; has been added to your cart.' => '«%s» به سبد خرید اضافه شد.',
		'Please choose product options by visiting <a href="%1$s" title="%2$s">%2$s</a>.' => 'لطفاً گزینه‌های محصول را در <a href="%1$s" title="%2$s">%2$s</a> انتخاب کنید.',
		'%s is a required field.'          => '%s الزامی است.',
		'Please read and accept the terms and conditions to proceed with your order.' => 'لطفاً شرایط و قوانین را بپذیرید.',
		'Invalid payment method.'          => 'روش پرداخت نامعتبر است.',
		'Free!'                            => 'رایگان',
		'Shipping:'                        => 'ارسال:',
		'via %s'                           => 'با %s',
		'Discount:'                        => 'تخفیف:',
		'Payment method'                   => 'روش پرداخت',
		'Free'                             => 'رایگان',
	);
}

/**
 * Translate a single string.
 *
 * @param string $translation Current translation.
 * @param string $text        Source.
 * @return string
 */
function saayeh_i18n_gettext( $translation, $text ) {
	if ( ! saayeh_i18n_fallback_enabled() ) {
		return $translation;
	}
	static $map = null;
	if ( null === $map ) {
		$map = saayeh_i18n_map();
	}
	return isset( $map[ $text ] ) ? $map[ $text ] : $translation;
}
add_filter( 'gettext_woocommerce', 'saayeh_i18n_gettext', 10, 2 );

/**
 * Strings with context (e.g. _x( 'Shipping', 'shipping packages' )).
 *
 * @param string $translation Translation.
 * @param string $text        Source.
 * @return string
 */
function saayeh_i18n_gettext_context( $translation, $text ) {
	return saayeh_i18n_gettext( $translation, $text );
}
add_filter( 'gettext_with_context_woocommerce', 'saayeh_i18n_gettext_context', 10, 2 );

/**
 * Plural strings.
 *
 * @param string $translation Translation.
 * @param string $single      Singular.
 * @param string $plural      Plural.
 * @return string
 */
function saayeh_i18n_ngettext( $translation, $single, $plural ) {
	if ( ! saayeh_i18n_fallback_enabled() ) {
		return $translation;
	}
	$map = array(
		'Category:' => 'دسته‌بندی:',
		'Tag:'      => 'برچسب:',
		'%s item'   => '%s کالا',
		'%s review for %s' => '%s نظر برای %s',
	);
	return isset( $map[ $single ] ) ? $map[ $single ] : $translation;
}
add_filter( 'ngettext_woocommerce', 'saayeh_i18n_ngettext', 10, 3 );
