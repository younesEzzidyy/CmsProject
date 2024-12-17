(function ($) {

    var PremiumAdvCarouselHandler = function ($scope, $) {

        var $outerContainer = $scope.find('.premium-adv-carousel__container'),
            settings = $outerContainer.data('settings'),
            $carouselContainer = $scope.find('.premium-adv-carousel__inner-container');

        if (!settings) {
            return;
        }

        var animationType = settings.type;

        if ('infinite' === animationType) {

            var $mediaItemsContainer = $outerContainer.find('.premium-adv-carousel__items'),
                light_box = settings.light_box,
                lightbox_type = settings.lightbox_type;

            if ("load" === settings.renderEvent) {

                runInfiniteAnimation();

            } else {

                // unsing IntersectionObserverAPI.
                var wheelObserver = new IntersectionObserver(function (entries) {

                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            runInfiniteAnimation();
                            wheelObserver.unobserve(entry.target); // to only excecute the callback func once.
                        }
                    });
                });

                wheelObserver.observe($scope[0]);

                // elementorFrontend.waypoint($scope, function () {

                //     runInfiniteAnimation();

                // }, {
                //     offset: "100%",
                //     triggerOnce: true
                // });
            }

            $carouselContainer.css('visibility', 'inherit');

            if (light_box) {
                if ("default" === lightbox_type)
                    $scope.find(".premium-adv-carousel__inner-container a[data-rel^='prettyPhoto']").prettyPhoto(getPrettyPhotoSettings());
            }

            // We need to set the animation on reaching viewpoint.
            if (settings.pauseOnHover) {
                setInfiniteAnimeState();
            }

            if (settings.scroll) {

                setInfiniteAnimeState();

                if (settings.dir === 'horizontal') {

                    $outerContainer.find('.premium-adv-carousel__inner-container').mousewheel(function (e, delta) {
                        this.scrollLeft -= (delta * 30);
                        e.preventDefault();
                    });
                }
            } else {
                $outerContainer.find('.premium-adv-carousel__inner-container').css({ 'overflow': 'hidden' });
            }

        } else {

            // flipster animations.
            var $flipContainer = $scope.find('.premium-adv-carousel__items'),
                $flipItem = $scope.find('.premium-adv-carousel__item-outer-wrapper'),
                $buttonPrev = $scope.find('.premium-adv-carousel__prev-icon').html(),
                $buttonNext = $scope.find('.premium-adv-carousel__next-icon').html(),
                isSmallDevice = ['mobile', 'mobile_extra', 'tablet', 'tablet_extra'].includes(elementorFrontend.getCurrentDeviceMode());

            $scope.find('.premium-adv-carousel__icons-holder').remove();

            $carouselContainer.flipster({
                itemContainer: $flipContainer,
                itemSelector: $flipItem,
                style: settings.type,
                fadeIn: 0,
                start: settings.start,
                loop: settings.loop,
                autoplay: settings.autoPlay,
                scrollwheel: settings.scroll,
                pauseOnHover: settings.pauseOnHover,
                click: settings.loop ? false : settings.click,
                keyboard: settings.keyboard,
                touch: settings.touch,
                spacing: settings.spacing,
                buttons: settings.buttons ? 'custom' : false,
                buttonPrev: $buttonPrev,
                buttonNext: $buttonNext,
                onItemSwitch: function () {
                    resetVideos();
                }
            }).css('visibility', 'inherit');

            if (settings.keyboard && !isSmallDevice) {
                //Fix: keyboard nav won't start unless the elements is focused.
                // elementorFrontend.waypoint($carouselContainer, function () {
                //     $.fn.focusWithoutScrolling = function () {
                //         var x = window.scrollX, y = window.scrollY;
                //         this.focus();
                //         window.scrollTo(x, y);
                //     };

                //     $carouselContainer.focusWithoutScrolling();
                // });

                // unsing IntersectionObserverAPI.
                var eleObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {

                            $.fn.focusWithoutScrolling = function () {
                                var x = window.scrollX, y = window.scrollY;
                                this.focus();
                                window.scrollTo(x, y);
                            };

                            $carouselContainer.focusWithoutScrolling();

                            eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                        }
                    });
                });

                eleObserver.observe($carouselContainer[0]);
            }

            // Fix: item click event when loop option is enabled navigates to the wrong slide.
            if (settings.loop && settings.click) {
                $scope.find('.premium-adv-carousel__item-outer-wrapper').on('click.paFlipClick', function () {
                    var itemIndex = $(this).index();
                    $carouselContainer.flipster('jump', itemIndex);
                });
            }
        }

        // play video.
        if ("yes" !== settings.light_box || undefined == settings.light_box) {
            $scope.find('.premium-adv-carousel__item .premium-adv-carousel__video-wrap').each(function (index, item) {

                var type = $(item).data("type");

                $(item).closest(".premium-adv-carousel__item").on("click.paPlayVid" + index, function () {

                    var _this = $(this);

                    resetVideos();

                    _this.find(".premium-adv-carousel__media-wrap").css("background", "#000");

                    _this.find(".premium-adv-carousel__video-icon, .premium-adv-carousel__vid-overlay").css("visibility", "hidden");

                    if ("hosted" !== type) {
                        var $iframeWrap = _this.find(".premium-adv-carousel__iframe-wrap"),
                            src = $iframeWrap.data("src");

                        src = src.replace("&mute", "&autoplay=1&mute");

                        var $iframe = $("<iframe/>");

                        $iframe.attr({ "src": src, "frameborder": "0", "allowfullscreen": "1", "allow": "autoplay;encrypted-media;" });

                        $iframeWrap.html($iframe);

                        $iframe.css("visibility", "visible");
                    } else {
                        var $video = $(item).find("video");
                        $video.attr('pa-playing', 'true').get(0).play();
                        $video.css("visibility", "visible");
                    }
                });
            });
        }

        function setInfiniteAnimeState() {
            $outerContainer.on('mouseenter.paMediaWheel', function () {

                $mediaItemsContainer.css('animation-play-state', 'paused');
            }).on('mouseleave.paMediaWheel', function () {
                $mediaItemsContainer.css('animation-play-state', 'running');
            });
        }

        function resetVideos() {

            $scope.find('iframe').attr('src', ''); // reset youtube/vimeo videos
            // reset self hosted videos.
            $video = $scope.find("video[pa-playing='true']").each(function () {
                var media = $(this).get(0);
                media.pause();
                media.currentTime = 0;
            });

            $scope.find(".premium-adv-carousel__video-icon, .premium-adv-carousel__vid-overlay").css("visibility", "visible");
            $scope.find(".premium-adv-carousel__media-wrap").css("background", "unset");
        }

        function setHorizontalWidth() {
            var horAlignWidth = 0;

            $scope.find('.premium-adv-carousel__item').each(function () {
                horAlignWidth += $(this).outerWidth(true);
            });

            $mediaItemsContainer.css({ 'width': horAlignWidth });

            return horAlignWidth;
        }

        function runInfiniteAnimation() {

            var $mediaItem = $scope.find('.premium-adv-carousel__item'),
                direction = settings.dir,
                transformOffset = 'horizontal' === direction ? $carouselContainer.innerWidth() : $carouselContainer.innerHeight(),
                start = 'transform: translateX(' + 0 + 'px)',
                end = 'transform: translateX(-50%)',
                scrollDir = settings.reverse,
                verAlignWidth = 10,
                duration = settings.speed * 1000 + 'ms',
                animeName = 'pa-scroll-' + $scope.data('id'),
                containerHeight = $mediaItemsContainer.outerHeight();

            if ('horizontal' === direction) {

                var horAlignWidth = setHorizontalWidth();

                $mediaItemsContainer.css({
                    'height': containerHeight,
                    'position': 'relative'
                });

                $mediaItemsContainer.find('.premium-adv-carousel__item-outer-wrapper').css('position', 'absolute');

                if ('normal' === scrollDir) {
                    $mediaItemsContainer.find('.premium-adv-carousel__item-outer-wrapper').css('right', 0);
                } else {
                    $mediaItemsContainer.css('left', '-' + horAlignWidth / $mediaItem.length + 'px');
                    if ('rtl' === document.dir) {
                        $mediaItemsContainer.css('direction', 'ltr');
                    }
                }

                var slidesSpacing = getComputedStyle($scope[0]).getPropertyValue('--pa-wheel-spacing') || 0,
                    factor = 'normal' === scrollDir ? -1 : 1,
                    accumlativeWidth = 0;

                // clone the items till the width is equal to the viewport width
                while (horAlignWidth <= $scope.outerWidth(true) || (horAlignWidth - $scope.outerWidth(true) <= 400)) {

                    cloneItems();
                    // recalculate the full width.
                    horAlignWidth = setHorizontalWidth();
                };

                gsap.set($scope.find('.premium-adv-carousel__item-outer-wrapper'), { // animates the carousel.
                    x: function (i) {

                        transformVal = accumlativeWidth;

                        accumlativeWidth = accumlativeWidth + $scope.find('.premium-adv-carousel__item').eq(i).outerWidth(true) + parseFloat(slidesSpacing);

                        return (transformVal) * factor
                    }
                });


                var fullWidth = (horAlignWidth + ($scope.find('.premium-adv-carousel__item').length * parseFloat(slidesSpacing)));
                var animation = gsap.to($scope.find('.premium-adv-carousel__item-outer-wrapper'), {
                    duration: settings.speed,
                    ease: "none",
                    x: ('normal' === scrollDir ? '-=' : '+=') + fullWidth,
                    modifiers: {
                        x: gsap.utils.unitize(function (x) {

                            var remainder = parseFloat(x) % fullWidth,
                                clampedValue = Math.max(remainder, -fullWidth);

                            return 'normal' === scrollDir ? clampedValue : remainder;

                        })
                    },
                    repeat: -1
                });

            } else {

                $mediaItem.each(function () {
                    verAlignWidth += $(this).outerHeight(true);
                });

                $mediaItemsContainer.css({
                    'position': 'relative',
                    'height': verAlignWidth
                });

                $mediaItemsContainer.find('.premium-adv-carousel__item-outer-wrapper').css('position', 'absolute');

                if ('normal' === scrollDir) {
                    $mediaItemsContainer.find('.premium-adv-carousel__item-outer-wrapper').css('bottom', 0);
                } else {
                    $mediaItemsContainer.css('top', '-' + verAlignWidth / $mediaItem.length + 'px');
                }

                var slidesSpacing = getComputedStyle($scope[0]).getPropertyValue('--pa-wheel-spacing') || 0,
                    factor = 'normal' === scrollDir ? -1 : 1,
                    accumlativeHeight = 0;

                gsap.set($scope.find('.premium-adv-carousel__item-outer-wrapper'), { // animates the carousel.
                    y: function (i) {

                        transformVal = accumlativeHeight;

                        accumlativeHeight = accumlativeHeight + $scope.find('.premium-adv-carousel__item').eq(i).outerHeight(true);

                        return (transformVal) * factor
                    }
                });


                var fullHeight = (verAlignWidth + ($scope.find('.premium-adv-carousel__item').length * parseFloat(slidesSpacing)));

                var animation = gsap.to($scope.find('.premium-adv-carousel__item-outer-wrapper'), {
                    duration: settings.speed,
                    ease: "none",
                    y: ('normal' === scrollDir ? '-=' : '+=') + fullHeight,
                    modifiers: {
                        y: gsap.utils.unitize(function (y) {

                            var remainder = parseFloat(y) % fullHeight,
                                clampedValue = Math.max(remainder, -fullHeight);

                            return 'normal' === scrollDir ? clampedValue : remainder;

                        })
                    },
                    repeat: -1
                })

            }

            //Pause on hover
            if (settings.pauseOnHover) {
                $scope.hover(function () {
                    animation.pause();
                }, function () {
                    animation.play();
                })
            }

        }

        function cloneItems() {
            var itemLen = $mediaItemsContainer.children().length,
                docFragment = new DocumentFragment();

            $mediaItemsContainer.find('.premium-adv-carousel__item-outer-wrapper:lt(' + itemLen + ')').clone(true, true).appendTo(docFragment);

            $mediaItemsContainer.append(docFragment);
        }

        function getPrettyPhotoSettings() {

            return {
                theme: settings.theme,
                hook: "data-rel",
                opacity: 0.7,
                show_title: false,
                deeplinking: false,
                overlay_gallery: settings.overlay,
                custom_markup: "",
                default_width: 900,
                default_height: 506,
                social_tools: ""
            }
        }

    };

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/premium-media-wheel.default', PremiumAdvCarouselHandler);
    });

})(jQuery);
