(function ($) {

    var PremiumMiniCartHandler = function ($scope, $) {

        var settings = $scope.find('.pa-woo-mc__outer-container').data('settings');

        if (!settings) {
            return;
        }

        if(elementorFrontend.isEditMode()) {
            $(document.body).trigger('wc_fragment_refresh');
        }

        // using the same classes in the off-canvas widget.
        $('html').addClass('msection-html');

        var $bodyInnerWrap = $("body .premium-magic-section-body-inner"),
            triggerEvent = settings.trigger,
            isHidden = true,
            type = settings.type,
            id = $scope.data('id'),
            style = settings.style,
            hoverTimeout;

            // shouldn't this be if it's a slide menu only?
        if ($(".premium-magic-section-body-inner").length < 1)
            $("body").wrapInner('<div class="premium-magic-section-body-inner" />');

        //Put the overlay on top and make sure it only one overlay per widget is added.
        $('.premium-magic-section-body-inner > .pa-woo-mc__overlay-' + id).remove();
        $('.premium-magic-section-body-inner').prepend($scope.find('.pa-woo-mc__overlay'));

        $scope.find('.pa-woo-mc__inner-container').off('click.paToggleMiniCart mouseenter.paToggleMiniCart mouseleave.paToggleMiniCart');

        getWraptoOrg(10);

        initWidgetEvents();

        initCartContentEvents();

        updateCartDynamicText();

        // Reinitialize the event listeners after the mini cart is refreshed
        $(document.body).on('wc_fragments_refreshed', function() {
            initCartContentEvents();
            updateCartDynamicText();
        });

        /**Helper Function */

        /**Restores the body to its initial state */
        function getWraptoOrg(duration) {

            if (!duration)
                duration = 500;

            $('body').addClass('animating');

            $bodyInnerWrap.css('transform', 'none');

            $('html').css('height', 'auto');

            setTimeout(function () {

                $('html').removeClass('offcanvas-open');
                $('body').removeClass('animating');

                //If the off canvas content is showing under content, then it should be hidden again after everything gets back to the initial state.
                // if (['slidealong', 'rotate'].includes(style))
                //     $magicElem.addClass('premium-addons__v-hidden');

            }, duration);

        }

        /** Handles Mini Cart Display */
        function toggleMiniCart(e) {
            if ( 'hover' === triggerEvent ) {
                e.stopPropagation();

                clearTimeout(hoverTimeout);
                $scope.find('.pa-woo-mc__content-wrapper-' + id).removeClass('premium-addons__v-hidden').addClass('pa-woo-mc__open');
            } else {

                if ( 'menu' === type ) {
                    $scope.find('.pa-woo-mc__content-wrapper-' + id).removeClass('premium-addons__v-hidden').toggleClass('pa-woo-mc__open');

                } else {
                    if ( isHidden ) {
                        $scope.find('.pa-woo-mc__content-wrapper-' + id).css('display', 'flex');

                        $('html').css({
                            'height': '100%',
                            // 'overflow-y': 'scroll'
                        });

                        $('html').addClass('offcanvas-open');

                        //Show overlay
                        $(".pa-woo-mc__overlay-" + id).removeClass("premium-addons__v-hidden");

                        //Show the content if reveal or similar effects.
                        $scope.find('.pa-woo-mc__content-wrapper-' + id).removeClass('premium-addons__v-hidden');

                        $scope.find('.pa-woo-mc__content-wrapper-' + id).removeClass('pa-woo-mc__anim-' + style);

                        setTimeout(function () {
                            isHidden = false;
                        }, 550);
                    }
                }
            }
        }

        /**
         * Updates Cart Footer Text if the user has the {{count}} keyword in it.
         * We add the text here as it gets replaced with only the items' count when
         * WC Fragments are refreshed.
         */
        function updateCartDynamicText() {
            var footerTxt = $scope.find('.pa-woo-mc__cart-footer').data('pa-footer-txt');

            if ( footerTxt && footerTxt.includes('{{count}}') ) {

                var itemCount = $scope.find('.pa-woo-mc__cart-footer .pa-woo-mc__cart-count').text(),
                    newTxt = footerTxt.replace("{{count}}", '<span class="pa-woo-mc__cart-count">' + itemCount + '</span>');

                $scope.find('.pa-woo-mc__cart-footer .pa-woo-mc__subtotal-heading').html(newTxt);
            }

            if ( settings.removeTxt ) {
                $scope.find('.pa-woo-mc__remove-item span').text( settings.removeTxt );
            }
        }

        /** Adds Cart Items' Evenents
         * Updating the item quantity, or deleting it.
        */
        function initCartContentEvents() {

            $('.pa-woo-mc__qty-btn').on('click', function(e) {
                e.stopPropagation();

                var $input = $(this).parent().find('.pa-woo-mc__input')[0],
                    itemStock = parseInt( $( $input ).attr('max') ),
                    currentVal = parseInt( $($input ).val());

                if ( $(this).hasClass('plus') ) {
                    if ( currentVal >= itemStock ) {
                        $(this).parents('.pa-woo-mc__item-wrapper').find('.pa-woo-mc__item-notice').text('*The current stock is only ' + itemStock);
                    } else {
                        $input.stepUp();
                        $($input).trigger('change');
                    }

                } else {

                    $input.stepDown();
                    $($input).trigger('change');
                }
            });

            // update item quantity
            $scope.find('.pa-woo-mc__input').on('change', function() {

                var itemKey = $(this).attr('name').replace('cart-', ''),
                    newQty = $(this).val();

                if (  '1' === newQty ) {
                    $(this).siblings('.pa-woo-mc__qty-btn.minus').addClass('disabled');
                } else {
                    $(this).siblings('.pa-woo-mc__qty-btn.minus').removeClass('disabled');
                }

                sendCartAjax('pa_update_mc_qty', itemKey, newQty);
            });

            // delete cart item.
            $scope.find('.pa-woo-mc__remove-item').on('click.paRemoveCartItem', function(e) {
                e.stopPropagation();
                var itemKey = $(this).data('pa-item-key').replace('cart-', '');
                sendCartAjax('pa_delete_cart_item', itemKey, false);
            });

            $scope.find('.pa-woo-mc__input').on('click', function(e){
                e.stopPropagation();
            });
        }

        /**
         * Sends an ajax request to update/delete a cart item.
         *
         * @param {String} action Request action.
         * @param {String} itemKey Items's key.
         * @param {String|Boolean} qty false|item quantity.
         */
        function sendCartAjax(action, itemKey, qty) {

            var data = {
                action: action,
                itemKey: itemKey,
                nonce: PremiumWooSettings.mini_cart_nonce,
            };

            if ( qty ) {
                data.quantity = qty;
            }

            $.ajax({
                url: PremiumWooSettings.ajaxurl,
                dataType: 'JSON',
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $scope.find('.pa-woo-mc__widget-shopping-outer-wrapper').append('<div class="premium-loading-feed"><div class="premium-loader"></div></div>');
                },
                success: function(res) {
                    $(document.body).trigger('wc_fragment_refresh');
                },
                error: function (err) {
                    console.log(err);
                },
                complete: function (res) {
                    $scope.find('.premium-loading-feed').remove();
                }
            });
        }

        /** Add the widget's basic events */
        function initWidgetEvents() {

            if ('click' === triggerEvent ) {
                $scope.find('.pa-woo-mc__inner-container').on('click.paToggleMiniCart', toggleMiniCart);
            } else {
                // hover => mini window
                $scope.find('.pa-woo-mc__inner-container').on('mouseenter.paToggleMiniCart', toggleMiniCart);
                $scope.on('mouseleave.paToggleMiniCart', function (e) {

                    hoverTimeout = setTimeout(function () {
                        $scope.find('.pa-woo-mc__content-wrapper-' + id).removeClass('pa-woo-mc__open');
                    }, 300);
                });
            }

            //On Click outside, close everything.
            if (settings.clickOutside) {

                $("body").on("click", function (event) {
                    // we need to recheck this
                    var mcContent = ".premium-tabs-nav-list-item, .pa-woo-mc__content-wrapper, .pa-woo-mc__content-wrapper *, .pa-woo-mc__inner-container, .pa-woo-mc__inner-container *";

                    if (!$(event.target).is($(mcContent)) ) {
                        if ( 'menu' === type ) {
                            $scope.find('.pa-woo-mc__content-wrapper-' + id).removeClass('pa-woo-mc__open');
                        } else {
                            !isHidden && $scope.find(".pa-woo-mc__close-button").trigger("click");
                        }
                    }
                });
            }

            /**
             * Events: Closing the slide menu.
             */
            $scope.find(".pa-woo-mc__close-button").on("click", function () {
                $(".pa-woo-mc__overlay-" + id).addClass("premium-addons__v-hidden");

                //Add the default styling again.
                $scope.find('.pa-woo-mc__content-wrapper-' + id).addClass('pa-woo-mc__anim-' + style);

                //We don't want to trigger this for each close action.
                // if (!$('body').hasClass('animating'))
                    // getWraptoOrg();

                setTimeout(function () {
                    isHidden = true;
                    // if ('morph' !== style)
                    $scope.find('.pa-woo-mc__content-wrapper-' + id).css('display', 'none');
                }, 500);

            });

        }
    };

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/premium-mini-cart.default', PremiumMiniCartHandler);
    });
})(jQuery);