<?php
/**
 * Woocommerce Mini Cart Custom Template.
 */

defined( 'ABSPATH' ) || exit;
if ( ! function_exists( 'pa_render_cart_item' ) ) {
	function pa_render_cart_item( $cart_item_key, $cart_item ) {

		$_product           = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		$is_product_visible = ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) );

		if ( ! $is_product_visible ) {
			return;
		}

		$product_id        = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
		$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
		$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
		$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
		$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

        $rendered_layout = get_option('pa_mc_layout', 'layout-1');

        if( 'layout-1' === $rendered_layout ) {

            pa_render_first_layout( $_product, $cart_item_key, $cart_item, $thumbnail, $product_permalink, $product_name, $product_price );
        } else {
            pa_render_second_layout( $_product, $cart_item_key, $cart_item, $thumbnail, $product_permalink, $product_name, $product_price );
        }

	}
}

if ( ! function_exists( 'pa_render_first_layout' ) ) {
	function pa_render_first_layout( $_product, $cart_item_key, $cart_item, $thumbnail, $product_permalink, $product_name, $product_price ) {

		$disabled_cls = '1' === $cart_item['quantity'] ? 'disabled' : '';
		?>
		<div class="pa-woo-mc__item-wrapper pa-woo-mc__layout-1 <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>" style="display: none">
			<div class="pa-woo-mc__product-thumbnail">
				<?php
					$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

				if ( ! $product_permalink ) {
					echo wp_kses_post( $thumbnail );

				} else {
					?>
						<a href="<?php echo esc_url( $product_permalink ); ?>"> <?php echo wp_kses_post( $thumbnail ); ?></a>
						<?php
				}
				?>
			</div>
			<div class="pa-woo-mc__product-data">
				<div class="pa-woo-mc__title-row">
					<a class="pa-woo-mc__title" href="<?php echo esc_url( $product_permalink ); ?>">
						<?php

							$words = explode( ' ', $product_name, 11 );

						if ( count( $words ) > 10 ) {
							array_pop( $words );
							array_push( $words, '…' );
						}

							$product_name = implode( ' ', $words );

							echo wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</a>
					<span class="pa-woo-mc__remove-item" data-pa-item-key="cart-<?php echo esc_attr( $cart_item_key ); ?>">
						<svg style="display: none" xmlns="http://www.w3.org/2000/svg" width="21.5" height="21.51" viewBox="0 0 21.5 21.51"><defs><style>.pa-trash-1{fill:#1a1a1a;}</style></defs><g id="Trash"><path class="pa-trash-1" d="M19.82,3.98c-1.61-.16-3.22-.29-4.83-.37h0s-.22-1.31-.22-1.31C14.62,1.39,14.39,0,12.06,0h-2.62C7.11,0,6.89,1.33,6.73,2.29l-.21,1.28c-.94.05-1.87.12-2.8.21l-2.04.2c-.42.04-.72.41-.68.82s.4.72.82.68l2.05-.2c5.23-.53,10.51-.33,15.81.2h.07c.38,0,.71-.29.75-.68.04-.41-.26-.78-.68-.82ZM8.05,3.52l.16-.98c.15-.89.17-1.03,1.23-1.03h2.62c1.06,0,1.09.18,1.23,1.04l.17,1c-1.81-.08-3.61-.09-5.41-.03Z"/><path class="pa-trash-1" d="M13.96,21.51h-6.42c-3.49,0-3.63-1.93-3.74-3.49l-.65-10.07c-.03-.41.29-.77.7-.8.42-.02.77.29.8.7l.65,10.07c.11,1.52.15,2.09,2.24,2.09h6.42c2.09,0,2.13-.57,2.24-2.09l.65-10.07c.03-.41.41-.72.8-.7.41.03.73.38.7.8l-.65,10.07c-.11,1.56-.25,3.49-3.74,3.49Z"/><path class="pa-trash-1" d="M12.41,16.01h-3.33c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h3.33c.41,0,.75.34.75.75s-.34.75-.75.75Z"/><path class="pa-trash-1" d="M13.25,12.01h-5c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h5c.41,0,.75.34.75.75s-.34.75-.75.75Z"/></g></svg>
						<span style="display: none" class="pa-woo-mc__remove-txt"></span>
					</span>
				</div>
				<div class="pa-woo-mc__price-row">
					<div class="pa-woo-mc__item-qty">
						<svg class="pa-woo-mc__qty-btn minus <?php echo esc_attr( $disabled_cls ); ?>" xmlns="http://www.w3.org/2000/svg" width="21.5" height="21.5" viewBox="0 0 21.5 21.5"><defs><style>.pa-qty-minus{fill:#333;}</style></defs><g id="Minus"><path class="pa-qty-minus" d="M16.75,11.5H4.75c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h12c.41,0,.75.34.75.75s-.34.75-.75.75Z"/></g></svg>
						<input class="pa-woo-mc__input" type="number" name="cart-<?php echo $cart_item_key; ?>" value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" min="1" step="1" max="<?php echo esc_attr( $_product->get_stock_quantity() ); ?>">
						<svg class="pa-woo-mc__qty-btn plus" xmlns="http://www.w3.org/2000/svg" width="21.5" height="21.5" viewBox="0 0 21.5 21.5"><defs><style>.pa-qty-plus{fill:#333;}</style></defs><g id="Plus"><path class="pa-qty-plus" d="M17.5,10.75c0,.41-.34.75-.75.75h-5.25v5.25c0,.41-.34.75-.75.75s-.75-.34-.75-.75v-5.25h-5.25c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h5.25v-5.25c0-.41.34-.75.75-.75s.75.34.75.75v5.25h5.25c.41,0,.75.34.75.75Z"/></g></svg>
					</div>

					<span class="pa-woo-mc__item-price"><?php echo wp_kses_post( $product_price ); ?></span>
				</div>
			</div>
			<span class="pa-woo-mc__item-notice"></span>
		</div>

		<?php
	}
}


if ( ! function_exists( 'pa_render_second_layout' ) ) {

	function pa_render_second_layout( $_product, $cart_item_key, $cart_item, $thumbnail, $product_permalink, $product_name, $product_price ) {
		$disabled_cls = '1' === $cart_item['quantity'] ? 'disabled' : '';

		?>
		<div class="pa-woo-mc__item-wrapper pa-woo-mc__layout-2 <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>" style="display: none">
			<div class="pa-woo-mc__product-thumbnail">
				<?php
					$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

				if ( ! $product_permalink ) {
					echo wp_kses_post( $thumbnail );

				} else {
					?>
						<a href="<?php echo esc_url( $product_permalink ); ?>"> <?php echo wp_kses_post( $thumbnail ); ?></a>
						<?php
				}
				?>
			</div>
			<div class="pa-woo-mc__product-data">

				<a class="pa-woo-mc__title" href="<?php echo esc_url( $product_permalink ); ?>">
					<?php

						$words = explode( ' ', $product_name, 11 );

					if ( count( $words ) > 10 ) {
						array_pop( $words );
						array_push( $words, '…' );
					}

						$product_name = implode( ' ', $words );

						echo wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</a>

				<span class="pa-woo-mc__item-price"><?php echo wp_kses_post( $product_price ); ?></span>

				<div class="pa-woo-mc__qty-ctrls-row">
					<div class="pa-woo-mc__item-qty">
						<svg class="pa-woo-mc__qty-btn minus <?php echo esc_attr( $disabled_cls ); ?>" xmlns="http://www.w3.org/2000/svg" width="21.5" height="21.5" viewBox="0 0 21.5 21.5"><defs><style>.pa-qty-minus{fill:#333;}</style></defs><g id="Minus"><path class="pa-qty-minus" d="M16.75,11.5H4.75c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h12c.41,0,.75.34.75.75s-.34.75-.75.75Z"/></g></svg>
						<input class="pa-woo-mc__input" type="number" name="cart-<?php echo esc_attr( $cart_item_key ); ?>" value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" min="1" step="1" max="<?php echo esc_attr( $_product->get_stock_quantity() ); ?>">
						<svg class="pa-woo-mc__qty-btn plus" xmlns="http://www.w3.org/2000/svg" width="21.5" height="21.5" viewBox="0 0 21.5 21.5"><defs><style>.pa-qty-plus{fill:#333;}</style></defs><g id="Plus"><path class="pa-qty-plus" d="M17.5,10.75c0,.41-.34.75-.75.75h-5.25v5.25c0,.41-.34.75-.75.75s-.75-.34-.75-.75v-5.25h-5.25c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h5.25v-5.25c0-.41.34-.75.75-.75s.75.34.75.75v5.25h5.25c.41,0,.75.34.75.75Z"/></g></svg>
					</div>

					<span class="pa-woo-mc__remove-item" data-pa-item-key="cart-<?php echo esc_attr( $cart_item_key ); ?>">
						<svg style="display: none" xmlns="http://www.w3.org/2000/svg" width="21.5" height="21.51" viewBox="0 0 21.5 21.51"><defs><style>.pa-trash-1{fill:#1a1a1a;}</style></defs><g id="Trash"><path class="pa-trash-1" d="M19.82,3.98c-1.61-.16-3.22-.29-4.83-.37h0s-.22-1.31-.22-1.31C14.62,1.39,14.39,0,12.06,0h-2.62C7.11,0,6.89,1.33,6.73,2.29l-.21,1.28c-.94.05-1.87.12-2.8.21l-2.04.2c-.42.04-.72.41-.68.82s.4.72.82.68l2.05-.2c5.23-.53,10.51-.33,15.81.2h.07c.38,0,.71-.29.75-.68.04-.41-.26-.78-.68-.82ZM8.05,3.52l.16-.98c.15-.89.17-1.03,1.23-1.03h2.62c1.06,0,1.09.18,1.23,1.04l.17,1c-1.81-.08-3.61-.09-5.41-.03Z"/><path class="pa-trash-1" d="M13.96,21.51h-6.42c-3.49,0-3.63-1.93-3.74-3.49l-.65-10.07c-.03-.41.29-.77.7-.8.42-.02.77.29.8.7l.65,10.07c.11,1.52.15,2.09,2.24,2.09h6.42c2.09,0,2.13-.57,2.24-2.09l.65-10.07c.03-.41.41-.72.8-.7.41.03.73.38.7.8l-.65,10.07c-.11,1.56-.25,3.49-3.74,3.49Z"/><path class="pa-trash-1" d="M12.41,16.01h-3.33c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h3.33c.41,0,.75.34.75.75s-.34.75-.75.75Z"/><path class="pa-trash-1" d="M13.25,12.01h-5c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h5c.41,0,.75.34.75.75s-.34.75-.75.75Z"/></g></svg>
						<span style="display: none" class="pa-woo-mc__remove-txt"></span>
					</span>
				</div>
			</div>

			<span class="pa-woo-mc__item-notice"></span>
		</div>

		<?php
	}
}

$has_cart = is_a( WC()->cart, 'WC_Cart' );
if ( ! $has_cart ) {
	return; }

$cart_items = WC()->cart->get_cart();

?>
	<div class="pa-woo-mc__items-wrapper">
		<?php if ( empty( $cart_items ) ) { ?>
		<div class="pa-woo-mc__empty-msg"><?php echo __('Your cart is currently empty!', 'woocommerce'); ?></div>
			<?php
		} else {

			do_action( 'woocommerce_before_mini_cart_contents' );
			$item_count = count( $cart_items );
			$counter    = 1;

			foreach ( $cart_items as $cart_item_key => $cart_item ) {
				pa_render_cart_item( $cart_item_key, $cart_item );

				if ( $counter < $item_count ) {
					?>
					<hr class="pa-woo-mc__item-divider" style="display: none">
					<?php
				}

				++$counter;
			}

			do_action( 'woocommerce_mini_cart_contents' );
		}
		?>

	</div>


<?php
