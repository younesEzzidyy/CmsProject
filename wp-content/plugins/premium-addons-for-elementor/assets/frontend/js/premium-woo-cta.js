
(function ($) {

    var PremiumWooCTAHandler = function ($scope, $) {

        var $container = $scope.find(".premium-wrapper-woo-cta"),
            $button = $container.find(".premium-woo-cta-button"),
            inputQuantity = $container.find('.product-quantity'),
            grouped_products = $container.find('.grouped_product_qty'),
            minusButton = $container.find('.quantity-button.minus'),
            plusButton = $container.find('.quantity-button.plus'),
            redirectToCart = $button.data('redirect-to-cart'),
            connectToMC = $container.data('pa-mc-id');


        minusButton.on('click', function () {
            var value = parseInt(inputQuantity.val());
            if (!isNaN(value) && value > 1) {
                inputQuantity.val(value - 1);
            }
        });

        plusButton.on('click', function () {
            var value = parseInt(inputQuantity.val());
            if (!isNaN(value)) {
                inputQuantity.val(value + 1);
            }
        });

        var GplusButtons = $container.find('.quantity-button.g-plus');
        var GminusButtons = $container.find('.quantity-button.g-minus');

        GplusButtons.each(function () {
            $(this).on('click', function () {
                var input = $(this).closest('.quantity-grouped-wrapper').find('.grouped_product_qty');
                var value = parseInt(input.val());
                if (!isNaN(value)) {
                    input.val(value + 1);
                }
            });
        });

        GminusButtons.each(function () {
            $(this).on('click', function () {
                var input = $(this).closest('.quantity-grouped-wrapper').find('.grouped_product_qty');
                var value = parseInt(input.val());
                if (!isNaN(value) && value > 1) {
                    input.val(value - 1);
                }
            });
        });

        var btn_text = $button.find('.premium-woo-btn-text'),
            wishlistRemoveText = $button.attr('data-wishlist-remove-text'),
            wishlistButtonText = $button.attr('data-wishlist-button-text'),
            compareRemoveText = $button.attr('data-compare-remove-text'),
            compareButtonText = $button.attr('data-compare-button-text'),
            productType = $button.attr('data-product-type');

        if ($button.attr('data-icon-visible') === 'yes') {

            var btn_icon = $button.find('.premium-woo-btn-icon'),
                btnTextTrimmed = $button.find('.premium-woo-btn-text').text().trim();

            if (btnTextTrimmed === wishlistRemoveText) {
                btn_icon.hide();
            } else {
                btn_icon.show();
                $button.remove('.premium-woo-icon-hidden');
            }

        }

        if (productType !== 'external') {

            $button.on('click', function (e) {
                e.preventDefault();

                var product_id = $(this).attr('data-product-id'),
                    $spinnerWrap = $(this).siblings('.premium-woo-cta__spinner');

                wishlist_message = $(this).attr('data-product-added');
                quantity = $container.find('.product-quantity').val(); // Select by class

                attributes = {};
                quantities = {};


                $spinnerWrap.addClass('loader-visible').append('<div class="premium-loading-feed"><div class="premium-loader"></div></div>');

                $messageBox = $container.find('.premium-cta-message-box');

                // Remove any previous messages.
                $messageBox.remove();

                grouped_products.each(function () {
                    var childId = $(this).attr('name').match(/\d+/)[0];
                    quantity = parseInt($(this).val(), 10);
                    if (quantity > 0 && !isNaN(quantity)) {
                        quantities[childId] = quantity;
                    }
                });

                $container.find('.product-attribute').each(function () {
                    var attrName = $(this).data('attribute_name');
                    attrValue = $(this).val();
                    if (attrValue) {
                        attributes[attrName] = attrValue;
                    }
                });

                var dataAction = $(this).attr('data-actions');
                var ajaxAction;

                switch (dataAction) {
                    case 'add_to_wishlist':
                        ajaxAction = 'custom_add_to_wishlist';
                        break;
                    case 'add_to_compare':
                        ajaxAction = 'custom_add_to_compare';
                        break;
                    default:
                        ajaxAction = 'custom_add_to_cart';
                }

                $.ajax({
                    url: PremiumWooSettings.ajaxurl,
                    dataType: 'JSON',
                    type: 'POST',
                    data: {
                        action: 'handle_pa_woo_cta_actions',
                        ajaxAction: ajaxAction,
                        product_id: product_id,
                        quantity: quantity,
                        grouped_product_qty: quantities,
                        attributes: attributes,
                        security: PremiumWooSettings.cta_nonce, // Ensure nonce matches
                    },
                    success: function (response) {

                        if (response.success) {

                            $spinnerWrap.removeClass('loader-visible').find(".premium-loading-feed").remove();

                            if (ajaxAction === 'custom_add_to_wishlist') {
                                if (response.data.in_wishlist === true) {
                                    btn_text.text(wishlistRemoveText);
                                    btn_icon.hide();
                                    $button.after('<div class="pro-wish">' + wishlist_message + '</div>');
                                } else {
                                    btn_text.text(wishlistButtonText);
                                    btn_icon.show();
                                    $add_message = $button.siblings('.pro-wish');
                                    $add_message.remove();
                                }

                            } else if (ajaxAction === 'custom_add_to_compare') {
                                if (response.data.in_compare === true) {
                                    btn_text.text(compareRemoveText);
                                } else {
                                    btn_text.text(compareButtonText);
                                }
                            } else {

                                if (redirectToCart === 'yes') {

                                    window.location.href = response.data.cart_url;

                                } else {

                                    $button.siblings('.view-cart-button').remove();

                                    $button.after('<a href="' + response.data.cart_url + '" target="_blank" class="view-cart-button" style="margin-left: 10px;">' + PremiumWooSettings.view_cart + '</a>');

                                }
                            }

                            // check if it is connected to premium mc
                            if ( connectToMC ) {
                                $(connectToMC + '.elementor-widget-premium-mini-cart').find('.pa-woo-mc__inner-container').trigger('click.paToggleMiniCart');
                                $(document.body).trigger('wc_fragment_refresh');
                            }
                        } else {

                            $spinnerWrap.removeClass('loader-visible').find(".premium-loading-feed").remove();

                            if (response.data.qty_message) {
                                $container.append('<div class="premium-cta-message-box">' + response.data.qty_message + '</div>');
                            }
                        }

                    },

                });
            });
        } else {
            $button.on('click', function (e) {
                var external_url = $button.attr('data-external-url');
                if (external_url) {
                    e.preventDefault();
                    window.location.href = external_url;
                }
            });
        }

    }

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/premium-woo-cta.default', PremiumWooCTAHandler);
    });
})(jQuery);