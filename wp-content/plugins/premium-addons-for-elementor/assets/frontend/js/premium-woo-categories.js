

(function ($) {
    $(window).on('elementor/frontend/init', function () {

        var PremiumWooCategoriesHandler = elementorModules.frontend.handlers.Base.extend({

            getDefaultSettings: function () {

                return {
                    selectors: {
                        container: '.premium-woo-cats__container',
                        listWrap: '.premium-woo-cats__list-wrap',
                        list: 'ul.products'
                    }
                }

            },

            getDefaultElements: function () {

                var selectors = this.getSettings('selectors');

                return {
                    $container: this.$element.find(selectors.container),
                    $listWrap: this.$element.find(selectors.listWrap),
                    $list: this.$element.find(selectors.list)
                }

            },

            bindEvents: function () {

                this.run();
            },

            run: function () {

                var settings = this.getElementSettings();

                if ('masonry' === settings.layout) {
                    this.handleGridMasonry();

                    $(window).on("resize", this.handleGridMasonry);

                } else if ('carousel' === settings.layout) {
                    this.handleCarousel();
                }

            },

            handleGridMasonry: function () {

                var $list = this.elements.$list;

                $list
                    .imagesLoaded(function () { })
                    .done(
                        function () {
                            $list.isotope({
                                itemSelector: "li.product-category",
                                percentPosition: true,
                                animationOptions: {
                                    duration: 750,
                                    easing: "linear",
                                    queue: false
                                },
                                layoutMode: "masonry",
                            });
                        });
            },

            handleCarousel: function () {

                var $list = this.elements.$list,
                    $container = this.elements.$container,
                    settings = this.getElementSettings(),
                    prevArrow = settings.arrows ? '<a type="button" data-role="none" class="carousel-arrow carousel-prev" aria-label="Previous" role="button" style=""><i class="fas fa-angle-left" aria-hidden="true"></i></a>' : '',
                    nextArrow = settings.arrows ? '<a type="button" data-role="none" class="carousel-arrow carousel-next" aria-label="Next" role="button" style=""><i class="fas fa-angle-right" aria-hidden="true"></i></a>' : '';

                var cols = parseInt(100 / settings.columns.substr(0, settings.columns.indexOf('%'))),
                    colsTablet = parseInt(100 / settings.columns_tablet.substr(0, settings.columns_tablet.indexOf('%'))),
                    colsMobile = parseInt(100 / settings.columns_mobile.substr(0, settings.columns_mobile.indexOf('%')));

                $list.on("init", function (event) {

                    setTimeout(function () {
                        $container.removeClass("premium-addons__v-hidden");
                    }, 100);

                });

                $list.slick({
                    infinite: true,
                    draggable: true,
                    slidesToShow: cols,
                    slidesToScroll: settings.products_to_scroll || 1,
                    responsive: [
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: colsTablet,
                                slidesToScroll: 1
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: colsMobile,
                                slidesToScroll: 1
                            }
                        }
                    ],
                    autoplay: 'yes' === settings.autoplay_slides,
                    speed: settings.speed || 500,
                    autoplaySpeed: settings.autoplay_speed || 5000,
                    nextArrow: nextArrow,
                    prevArrow: prevArrow,
                    dots: 'yes' === settings.dots,
                    customPaging: function () {
                        return '<i class="fas fa-circle"></i>';
                    }
                });



            }

        });

        elementorFrontend.elementsHandler.attachHandler('premium-woo-categories', PremiumWooCategoriesHandler);
    });

})(jQuery);
