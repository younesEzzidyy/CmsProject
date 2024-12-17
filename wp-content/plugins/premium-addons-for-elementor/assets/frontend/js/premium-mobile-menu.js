
(function ($) {
    $(window).on('elementor/frontend/init', function () {

        var PremiumMobileMenuHandler = elementorModules.frontend.handlers.Base.extend({

            observer: null, // Reference to the IntersectionObserver
            isNotScrolling: false, // Flag to determine if the user is scrolling manually

            getDefaultSettings: function () {

                return {
                    slick: {
                        infinite: false,
                        rows: 0,
                        draggable: true,
                        pauseOnHover: true,
                        slidesToScroll: 1,
                        autoplay: false,
                    },
                    selectors: {
                        wrap: '.premium-mobile-menu__wrap',
                        list: '.premium-mobile-menu__list',
                        item: '.premium-mobile-menu__item',
                    }
                }
            },

            getDefaultElements: function () {
                var selectors = this.getSettings('selectors');

                return {
                    $wrap: this.$element.find(selectors.wrap),
                    $list: this.$element.find(selectors.list),
                    $items: this.$element.find(selectors.item),
                }

            },
            bindEvents: function () {
                this.run();
                this.handleItemClick();
                this.observeSections();
            },

            getSlickSettings: function () {

                var settings = this.getElementSettings(),
                    rtl = this.elements.$wrap.data("rtl"),
                    colsNumber = settings.items_to_show,
                    prevArrow = '<a type="button" data-role="none" class="carousel-arrow carousel-prev" aria-label="Previous" role="button" style=""><i class="fas fa-angle-left" aria-hidden="true"></i></a>',
                    nextArrow = '<a type="button" data-role="none" class="carousel-arrow carousel-next" aria-label="Next" role="button" style=""><i class="fas fa-angle-right" aria-hidden="true"></i></a>',
                    slides_tab = settings.items_to_show_tablet,
                    slides_mob = settings.items_to_show_mobile,
                    spacing_tab = settings.carousel_spacing_tablet,
                    spacing_mob = settings.carousel_spacing_mobile,
                    currentDeviceMode = elementorFrontend.getCurrentDeviceMode();

                if (-1 !== currentDeviceMode.indexOf('mobile') && 'yes' !== settings.carousel_arrows_mobile) {
                    prevArrow = '';
                    nextArrow = '';

                } else if (-1 !== currentDeviceMode.indexOf('tablet') && 'yes' !== settings.carousel_arrows_tablet) {
                    prevArrow = '';
                    nextArrow = '';
                }

                return Object.assign(this.getSettings('slick'), {

                    slidesToShow: colsNumber,
                    responsive: [{
                        breakpoint: 1025,
                        settings: {
                            slidesToShow: slides_tab,
                            centerPadding: spacing_tab + "px",
                            nextArrow: settings.carousel_arrows_tablet ? nextArrow : '',
                            prevArrow: settings.carousel_arrows_tablet ? prevArrow : '',
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: slides_mob,
                            centerPadding: spacing_mob + "px",
                            nextArrow: settings.carousel_arrows_mobile ? nextArrow : '',
                            prevArrow: settings.carousel_arrows_mobile ? prevArrow : '',
                        }
                    }
                    ],
                    rtl: rtl ? true : false,
                    speed: settings.carousel_speed,
                    prevArrow: settings.carousel_arrows ? prevArrow : '',
                    nextArrow: settings.carousel_arrows ? nextArrow : '',
                    centerMode: 'yes' === settings.carousel_center,
                    centerPadding: settings.carousel_spacing + "px",

                });


            },

            run: function () {

                var $list = this.elements.$list,
                    carousel = this.getElementSettings('carousel');

                if (carousel)
                    $list.slick(this.getSlickSettings());

            },

            handleItemClick: function () {
                var self = this,
                    $items = this.elements.$items;

                var currentPageURL = window.location.href;

                // //Loop through each menu item
                $items.each(function () {

                    var $item = $(this),
                        menuItemHref = $item.find('a').attr('href');

                    // Check if the menu item's link matches the current page URL
                    if (menuItemHref === currentPageURL) {
                        // Add the active class if it matches
                        $item.addClass('active-menu-item');
                    }

                });

                $items.on('click', function (e) {

                    var $this = $(this);

                    if (!$this.hasClass('active-menu-item')) {
                        $items.removeClass('active-menu-item');
                        $this.addClass('active-menu-item');
                    }

                    // Pause the IntersectionObserver
                    self.isNotScrolling = true;

                    // Scroll to the corresponding section
                    var targetId = $this.data('target');

                    if (targetId) {

                        var targetElement = $(targetId);

                        if (targetElement.length > 0) {

                            e.preventDefault();

                            $(window).animate({
                                scrollTop: targetElement.offset().top
                            }, 500, function () {

                                // enable the IntersectionObserver after scroll completes.
                                self.isNotScrolling = false;
                            });

                        }

                    }
                });
            },

            observeSections: function () {
                var self = this,
                    observerOptions = {
                        root: null,
                        rootMargin: '0px',
                        threshold: 0.5,
                    };

                var observerCallback = function (entries) {

                    if (!self.isNotScrolling) {
                        entries.forEach(function (entry) {
                            var $item = self.elements.$items.filter('[data-target="#' + entry.target.id + '"]');

                            if (entry.isIntersecting) {
                                self.elements.$items.removeClass('active-menu-item');
                                $item.addClass('active-menu-item');
                            }
                        });
                    }
                };

                self.observer = new IntersectionObserver(observerCallback, observerOptions);
                //Observe sections dynamically based on menu items' data-target attributes
                this.elements.$items.each(function () {
                    var targetId = $(this).data('target');
                    if (targetId) {
                        var targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            self.observer.observe(targetElement);
                        }
                    }
                });
            },


        });

        elementorFrontend.elementsHandler.attachHandler('premium-mobile-menu', PremiumMobileMenuHandler);
    });
})(jQuery);