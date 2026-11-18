<?php
/**
 * Mini-cart used inside the slide-out cart drawer.
 *
 * This template overrides woocommerce/templates/cart/mini-cart.php.
 *
 * @package Saayeh
 * @version 11.2.2
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' );

if ( WC()->cart && ! WC()->cart->is_empty() ) :
	?>
	<div class="x-drawer__body woocommerce-mini-cart cart_list product_list_widget">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
			$visible    = apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key );

			if ( ! ( $_product instanceof WC_Product ) || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! $visible ) {
				continue;
			}
			$cart_item_name    = WC()->cart->get_item_product_name( $cart_item, $_product );
			$product_name      = apply_filters( 'woocommerce_cart_item_name', $cart_item_name, $cart_item, $cart_item_key );
			$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );
			$line_total        = WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] );
			$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
			$qty               = (int) $cart_item['quantity'];
			$max               = $_product->get_max_purchase_quantity();
			$sold_individually = $_product->is_sold_individually();
			?>
			<div class="x-line woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
				<?php if ( $product_permalink ) : ?>
					<a href="<?php echo esc_url( $product_permalink ); ?>" tabindex="-1" aria-hidden="true"><?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<?php else : ?>
					<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
				<div>
					<div class="x-line__row x-line__row--top">
						<h3>
							<?php if ( $product_permalink ) : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo wp_kses_post( $product_name ); ?></a>
							<?php else : ?>
								<?php echo wp_kses_post( $product_name ); ?>
							<?php endif; ?>
						</h3>
						<span class="x-line__price"><?php echo wp_kses_post( $line_total ); ?></span>
					</div>
					<?php echo wc_get_formatted_cart_item_data( $cart_item, false, $cart_item_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<div class="x-line__row">
						<?php if ( $sold_individually ) : ?>
							<span class="x-muted" style="font-size:13px"><?php echo esc_html( saayeh_num( 1 ) ); ?></span>
						<?php else : ?>
							<div class="x-step x-step--sm" role="group" aria-label="<?php esc_attr_e( 'تعداد', 'saayeh' ); ?>">
								<button type="button" data-cart-qty="<?php echo esc_attr( $qty + 1 ); ?>" aria-label="<?php esc_attr_e( 'افزایش', 'saayeh' ); ?>" <?php disabled( $max > 0 && $qty >= $max ); ?>>+</button>
								<span aria-live="polite"><?php echo esc_html( saayeh_num( $qty ) ); ?></span>
								<button type="button" data-cart-qty="<?php echo esc_attr( $qty - 1 ); ?>" aria-label="<?php esc_attr_e( 'کاهش', 'saayeh' ); ?>">−</button>
							</div>
						<?php endif; ?>
						<?php
						echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'woocommerce_cart_item_remove_link',
							sprintf(
								'<a role="button" href="%s" class="x-link remove_from_cart_saayeh" data-cart-qty="0" aria-label="%s" data-product_id="%s" data-cart_item_key="%s">%s</a>',
								esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
								/* translators: %s: product name */
								esc_attr( sprintf( __( 'حذف %s از سبد', 'saayeh' ), wp_strip_all_tags( $product_name ) ) ),
								esc_attr( $product_id ),
								esc_attr( $cart_item_key ),
								esc_html__( 'حذف', 'saayeh' )
							),
							$cart_item_key
						);
						?>
					</div>
				</div>
			</div>
			<?php
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</div>

	<div class="x-drawer__foot">
		<?php saayeh_free_shipping_bar(); ?>

		<?php if ( wc_coupons_enabled() ) : ?>
			<?php
			$saayeh_coupons = WC()->cart->get_applied_coupons();
			foreach ( $saayeh_coupons as $saayeh_code ) :
				?>
				<p class="x-ok x-coupon-tag">
					<span>
						<?php
						/* translators: %s: coupon code */
						echo esc_html( sprintf( __( 'کد %s اعمال شد', 'saayeh' ), strtoupper( $saayeh_code ) ) );
						?>
					</span>
					<button type="button" class="x-link" data-remove-coupon="<?php echo esc_attr( $saayeh_code ); ?>"><?php esc_html_e( 'حذف کد', 'saayeh' ); ?></button>
				</p>
			<?php endforeach; ?>
			<?php if ( ! $saayeh_coupons ) : ?>
				<form class="x-code" data-coupon-form novalidate>
					<label class="screen-reader-text" for="x-code-in"><?php esc_html_e( 'کد تخفیف', 'saayeh' ); ?></label>
					<input class="x-input" id="x-code-in" name="coupon_code" placeholder="<?php esc_attr_e( 'کد تخفیف', 'saayeh' ); ?>" autocomplete="off">
					<button class="x-btn x-btn--ghost" type="submit"><?php esc_html_e( 'اعمال', 'saayeh' ); ?></button>
				</form>
				<p class="x-err" data-coupon-msg aria-live="polite" style="margin-bottom:8px"></p>
			<?php endif; ?>
		<?php endif; ?>

		<div class="x-sum">
			<div><span><?php esc_html_e( 'جمع کالاها', 'saayeh' ); ?></span><span><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span></div>
			<?php foreach ( WC()->cart->get_coupons() as $saayeh_code => $saayeh_coupon ) : ?>
				<div>
					<span>
						<?php
						/* translators: %s: coupon code */
						echo esc_html( sprintf( __( 'تخفیف %s', 'saayeh' ), strtoupper( $saayeh_code ) ) );
						?>
					</span>
					<span>− <?php echo wp_kses_post( wc_price( WC()->cart->get_coupon_discount_amount( $saayeh_code, WC()->cart->display_cart_ex_tax ) ) ); ?></span>
				</div>
			<?php endforeach; ?>
			<div><span><?php esc_html_e( 'هزینه ارسال', 'saayeh' ); ?></span><small><?php esc_html_e( 'در مرحله بعد محاسبه می‌شود', 'saayeh' ); ?></small></div>
			<?php
			$saayeh_total = (float) WC()->cart->get_displayed_subtotal() - (float) WC()->cart->get_discount_total();
			if ( WC()->cart->display_prices_including_tax() ) {
				$saayeh_total -= (float) WC()->cart->get_discount_tax();
			}
			?>
			<div class="x-total"><span><?php esc_html_e( 'مبلغ قابل پرداخت', 'saayeh' ); ?></span><span><?php echo wp_kses_post( wc_price( max( 0, $saayeh_total ) ) ); ?></span></div>
		</div>

		<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>
		<div class="x-drawer__btns">
			<a class="x-btn x-btn--ghost" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'مشاهده سبد', 'saayeh' ); ?></a>
			<a class="x-btn checkout" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'تسویه حساب', 'saayeh' ); ?></a>
		</div>
		<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>
	</div>

<?php else : ?>

	<div class="x-drawer__body x-drawer__body--empty woocommerce-mini-cart__empty-message">
		<div>
			<p style="font-size:18px;margin:0 0 6px;color:var(--x-ink)"><?php esc_html_e( 'سبد خرید شما خالی است', 'saayeh' ); ?></p>
			<p class="x-muted" style="margin:0 0 24px"><?php esc_html_e( 'چیزی هنوز انتخاب نکرده‌اید.', 'saayeh' ); ?></p>
			<a class="x-btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'رفتن به فروشگاه', 'saayeh' ); ?></a>
		</div>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
