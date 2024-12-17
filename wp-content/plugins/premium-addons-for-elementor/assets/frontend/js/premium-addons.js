(function ($) {
    $(window).on('elementor/frontend/init', function () {

        var ModuleHandler = elementorModules.frontend.handlers.Base;

        /****** Premium Progress Bar Handler ******/
        var PremiumProgressBarWidgetHandler = function ($scope, trigger) {

            var $progressbarElem = $scope.find(".premium-progressbar-container"),
                settings = $progressbarElem.data("settings"),
                length = settings.progress_length,
                speed = settings.speed,
                type = settings.type,
                mScroll = settings.mScroll;

            if ("line" === type) {

                var $progressbar = $progressbarElem.find(".premium-progressbar-bar");

                if (settings.gradient)
                    $progressbar.css("background", "linear-gradient(-45deg, " + settings.gradient + ")");

                if ('yes' !== mScroll) {
                    $progressbar.animate({
                        width: length + "%"
                    }, speed);
                }

            } else if ("circle" === type || "half-circle" === type) {
                if (length > 100)
                    length = 100;

                var degreesFactor = 1.8 * (elementorFrontend.config.is_rtl ? -1 : 1);

                if ('yes' !== mScroll) {
                    $progressbarElem.find(".premium-progressbar-hf-circle-progress").css({
                        transform: "rotate(" + length * degreesFactor + "deg)",
                    });
                }

                $progressbarElem.prop({
                    'counter': 0
                }).animate({
                    counter: length
                }, {
                    duration: speed,
                    easing: 'linear',
                    step: function (counter) {
                        var rotate = (counter * 3.6);

                        if ('yes' !== mScroll) {

                            $progressbarElem.find(".premium-progressbar-right-label").text(Math.ceil(counter) + "%");

                            $progressbarElem.find(".premium-progressbar-circle-left").css('transform', "rotate(" + rotate + "deg)");
                        }

                        if ('circle' === type && rotate > 180) {

                            $progressbarElem.find(".premium-progressbar-circle").css({
                                '-webkit-clip-path': 'inset(0)',
                                'clip-path': 'inset(0)',
                            });

                            $progressbarElem.find(".premium-progressbar-circle-right").css('visibility', 'visible');
                        }
                    }
                });

            } else {

                var $progressbar = $progressbarElem.find(".premium-progressbar-bar-wrap"),
                    width = $progressbarElem.outerWidth(),
                    dotSize = settings.dot || 25,
                    dotSpacing = settings.spacing || 10,
                    numberOfCircles = Math.ceil(width / (dotSize + dotSpacing)),
                    circlesToFill = numberOfCircles * (length / 100),
                    numberOfTotalFill = Math.floor(circlesToFill),
                    fillPercent = 100 * (circlesToFill - numberOfTotalFill);

                $progressbar.attr('data-circles', numberOfCircles);
                $progressbar.attr('data-total-fill', numberOfTotalFill);
                $progressbar.attr('data-partial-fill', fillPercent);

                var className = "progress-segment";
                for (var i = 0; i < numberOfCircles; i++) {
                    className = "progress-segment";
                    var innerHTML = '';

                    if (i < numberOfTotalFill) {
                        innerHTML = "<div class='segment-inner'></div>";
                    } else if (i === numberOfTotalFill) {

                        innerHTML = "<div class='segment-inner'></div>";
                    }

                    $progressbar.append("<div class='" + className + "'>" + innerHTML + "</div>");

                }

                if ("frontend" !== trigger) {
                    PremiumProgressDotsHandler($scope);
                }

            }

        };

        var PremiumProgressDotsHandler = function ($scope) {

            var $progressbarElem = $scope.find(".premium-progressbar-container"),
                settings = $progressbarElem.data("settings"),
                $progressbar = $scope.find(".premium-progressbar-bar-wrap"),
                data = $progressbar.data(),
                speed = settings.speed,
                increment = 0;

            var numberOfTotalFill = data.totalFill,
                numberOfCircles = data.circles,
                fillPercent = data.partialFill;

            dotIncrement(increment);

            function dotIncrement(inc) {

                var $dot = $progressbar.find(".progress-segment").eq(inc),
                    dotWidth = 100;

                if (inc === numberOfTotalFill)
                    dotWidth = fillPercent

                $dot.find(".segment-inner").animate({
                    width: dotWidth + '%'
                }, speed / numberOfCircles, function () {
                    increment++;
                    if (increment <= numberOfTotalFill) {
                        dotIncrement(increment);
                    }

                });
            }
        };

        /****** Premium Progress Bar Scroll Handler *****/
        var PremiumProgressBarScrollWidgetHandler = function ($scope, $) {

            var $progressbarElem = $scope.find(".premium-progressbar-container"),
                settings = $progressbarElem.data("settings"),
                type = settings.type;

            if ("dots" === type) {
                PremiumProgressBarWidgetHandler($scope, "frontend");
            }

            // unsing IntersectionObserverAPI.
            var eleObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {

                        if ("dots" !== type) {
                            PremiumProgressBarWidgetHandler($(entry.target));
                        } else {
                            PremiumProgressDotsHandler($(entry.target));
                        }

                        eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                    }
                });
            });

            eleObserver.observe($scope[0]);
        };

        /****** Premium Video Box Handler ******/
        var PremiumVideoBoxWidgetHandler = function ($scope, $) {

            var $videoBoxElement = $scope.find(".premium-video-box-container"),
                $videoListElement = $scope.find(".premium-video-box-playlist-container"),
                $videoContainer = $videoBoxElement.find(".premium-video-box-video-container"), //should be clicked
                $videoInnerContainer = $videoBoxElement.find('.premium-video-box-inner-wrap'),
                $videoImageContainer = $videoInnerContainer.find('.premium-video-box-image-container'),
                type = $videoBoxElement.data("type"),
                thumbnail = $videoBoxElement.data("thumbnail"),
                sticky = $videoBoxElement.data('sticky'),
                stickyOnPlay = $videoBoxElement.data('sticky-play'),
                hoverEffect = $videoBoxElement.data('hover'),
                $lighboxContainer = $videoListElement.length ? $videoListElement : $videoBoxElement,
                lightBox = $lighboxContainer.data('lightbox') ? $lighboxContainer.data('lightbox') : false,
                video, vidSrc;

            if (lightBox) {

                if ('prettyphoto' === lightBox.type) {
                    $lighboxContainer.find(".premium-vid-lightbox-container[data-rel^='prettyPhoto']").prettyPhoto(getPrettyPhotoSettings(lightBox.theme));
                }

                $lighboxContainer.find('.premium-video-box-image-container, .premium-video-box-play-icon-container').on('click', function (e) {
                    triggerLightbox($(this).closest('.premium-video-box-container'), lightBox.type);
                });

            } else {
                // Youtube playlist option.
                if ($videoListElement.length) {

                    //Make sure that video were pulled from the API.
                    if (!$videoContainer.length)
                        return;

                    $videoContainer.each(function (index, item) {

                        var vidSrc,
                            $videoContainer = $(item),
                            $videoBoxElement = $videoContainer.closest(".premium-video-box-container"),
                            $trigger = $videoContainer.closest(".premium-video-box-trigger");

                        vidSrc = $videoContainer.data("src");
                        vidSrc = vidSrc + "&autoplay=1";

                        $trigger.on("click", function () {

                            var $iframe = $("<iframe/>");

                            $iframe.attr({
                                "src": vidSrc,
                                "frameborder": "0",
                                "allowfullscreen": "1",
                                "allow": "autoplay;encrypted-media;"
                            });
                            $videoContainer.css("background", "#000");
                            $videoContainer.html($iframe);

                            $videoBoxElement.find(
                                ".premium-video-box-image-container, .premium-video-box-play-icon-container"
                            ).remove();

                        });

                    });

                    return;
                }

                if ("self" === type) {

                    video = $videoContainer.find("video");
                    vidSrc = video.attr("src");

                    if ($videoBoxElement.data("play-viewport")) {

                        var eleObserver = new IntersectionObserver(function (entries) {
                            entries.forEach(function (entry) {
                                if (entry.isIntersecting) {
                                    if ($videoBoxElement.data("play-reset")) {
                                        if ('up' === window.paDirection) {
                                            restartVideo();
                                        } else {
                                            playVideo();
                                        }

                                    } else {
                                        playVideo();
                                    }
                                }
                            });
                        });


                        eleObserver.observe($videoBoxElement[0]);
                    }

                } else {

                    vidSrc = $videoContainer.data("src");

                    if (!thumbnail || -1 !== vidSrc.indexOf("autoplay=1")) {

                        //Check if Autoplay on viewport option is enabled
                        if ($videoBoxElement.data("play-viewport")) {

                            var eleObserver = new IntersectionObserver(function (entries) {
                                entries.forEach(function (entry) {
                                    if (entry.isIntersecting) {
                                        playVideo();
                                    }
                                });
                            }, {
                                threshold: "0.5"
                            });

                            eleObserver.observe($videoBoxElement[0]);
                        } else {
                            playVideo();
                        }

                    } else {
                        vidSrc = vidSrc + "&autoplay=1";
                    }

                }

                $videoBoxElement.on("click", function () {
                    playVideo();
                });

                if ("yes" !== sticky || "yes" === stickyOnPlay)
                    return;

                stickyOption();
            }

            function playVideo() {

                if ($videoBoxElement.hasClass("playing")) return;

                $videoBoxElement.addClass("playing");

                if (stickyOnPlay === 'yes')
                    stickyOption();

                if ("self" === type) {

                    $(video).get(0).play();

                    $videoContainer.css({
                        opacity: "1",
                        visibility: "visible"
                    });

                } else {

                    var $iframe = $("<iframe/>");

                    $iframe.attr({
                        "src": vidSrc,
                        "frameborder": "0",
                        "allowfullscreen": "1",
                        "allow": "autoplay;encrypted-media;"
                    });
                    $videoContainer.css("background", "#000");
                    $videoContainer.html($iframe);
                }

                $videoBoxElement.find(
                    ".premium-video-box-image-container, .premium-video-box-play-icon-container, .premium-video-box-description-container"
                ).remove();

                if ("vimeo" === type)
                    $videoBoxElement.find(".premium-video-box-vimeo-wrap").remove();
            }

            function restartVideo() {
                $videoBoxElement.removeClass("playing");
                $(video).get(0).currentTime = 0;
            }

            function triggerLightbox($container, type) {
                if ('elementor' === type) {
                    $container.find('.premium-video-box-video-container').trigger('click');
                } else {
                    $container.find(".premium-vid-lightbox-container[data-rel^='prettyPhoto']").trigger('click');
                }
            }

            function stickyOption() {
                var stickyDesktop = $videoBoxElement.data('hide-desktop'),
                    stickyTablet = $videoBoxElement.data('hide-tablet'),
                    stickyMobile = $videoBoxElement.data('hide-mobile'),
                    stickyMargin = $videoBoxElement.data('sticky-margin');

                $videoBoxElement.off('click').on('click', function (e) {
                    // if ('yes' === sticky) {
                    var stickyTarget = e.target.className;
                    if ((stickyTarget.toString().indexOf('premium-video-box-sticky-close') >= 0) || (stickyTarget.toString().indexOf('premium-video-box-sticky-close') >= 0)) {
                        return false;
                    }
                    // }
                    playVideo();

                });

                var stickyWaypoint = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {

                            if ('down' === window.paDirection) {
                                $videoBoxElement.removeClass('premium-video-box-sticky-hide').addClass('premium-video-box-sticky-apply premium-video-box-filter-sticky');

                                //Fix conflict with Elementor motion effects
                                if ($scope.hasClass("elementor-motion-effects-parent")) {
                                    $scope.removeClass("elementor-motion-effects-perspective").find(".elementor-widget-container").addClass("premium-video-box-transform");
                                }

                                if ($videoBoxElement.data("mask")) {
                                    //Fix Sticky position issue when drop-shadow is applied
                                    $scope.find(".premium-video-box-mask-filter").removeClass("premium-video-box-mask-filter");

                                    $videoBoxElement.find(':first-child').removeClass('premium-video-box-mask-media');

                                    $videoImageContainer.removeClass(hoverEffect).removeClass('premium-video-box-mask-media').css({
                                        'transition': 'width 0.2s, height 0.2s',
                                        '-webkit-transition': 'width 0.2s, height 0.2s'
                                    });
                                }

                                $(document).trigger('premium_after_sticky_applied', [$scope]);

                                // Entrance Animation Option
                                if ($videoInnerContainer.data("video-animation") && " " != $videoInnerContainer.data("video-animation")) {
                                    $videoInnerContainer.css("opacity", "0");
                                    var animationDelay = $videoInnerContainer.data('delay-animation');
                                    setTimeout(function () {

                                        $videoInnerContainer.css("opacity", "1").addClass("animated " + $videoInnerContainer.data("video-animation"));

                                    }, animationDelay * 1000);
                                }
                            } else {
                                $videoBoxElement.removeClass('premium-video-box-sticky-apply  premium-video-box-filter-sticky').addClass('premium-video-box-sticky-hide');

                                //Fix conflict with Elementor motion effects
                                if ($scope.hasClass("elementor-motion-effects-parent")) {
                                    $scope.addClass("elementor-motion-effects-perspective").find(".elementor-widget-container").removeClass("premium-video-box-transform");
                                }

                                if ($videoBoxElement.data("mask")) {
                                    //Fix Sticky position issue when drop-shadow is applied
                                    $videoBoxElement.parent().addClass("premium-video-box-mask-filter");

                                    $videoBoxElement.find(':first-child').eq(0).addClass('premium-video-box-mask-media');
                                    $videoImageContainer.addClass('premium-video-box-mask-media');
                                }

                                $videoImageContainer.addClass(hoverEffect).css({
                                    'transition': 'all 0.2s',
                                    '-webkit-transition': 'all 0.2s'
                                });

                                $videoInnerContainer.removeClass("animated " + $videoInnerContainer.data("video-animation"));

                            }
                        }
                    });
                }, {
                    threshold: 1
                });

                stickyWaypoint.observe($videoBoxElement[0]);

                var closeBtn = $scope.find('.premium-video-box-sticky-close');

                closeBtn.off('click.closetrigger').on('click.closetrigger', function (e) {
                    e.stopPropagation();
                    stickyWaypoint.unobserve($videoBoxElement[0]);

                    $videoBoxElement.removeClass('premium-video-box-sticky-apply premium-video-box-sticky-hide');

                    //Fix conflict with Elementor motion effects
                    if ($scope.hasClass("elementor-motion-effects-parent")) {
                        $scope.addClass("elementor-motion-effects-perspective").find(".elementor-widget-container").removeClass("premium-video-box-transform");
                    }

                    if ($videoBoxElement.data("mask")) {
                        //Fix Sticky position issue when drop-shadow is applied
                        $videoBoxElement.parent().addClass("premium-video-box-mask-filter");

                        //Necessary classes for mask shape option
                        $videoBoxElement.find(':first-child').eq(0).addClass('premium-video-box-mask-media');
                        $videoImageContainer.addClass('premium-video-box-mask-media');
                    }
                });

                checkResize(stickyWaypoint);

                checkScroll();

                window.addEventListener("scroll", checkScroll);

                $(window).resize(function (e) {
                    checkResize(stickyWaypoint);
                });

                function checkResize(stickyWaypoint) {

                    var currentDeviceMode = elementorFrontend.getCurrentDeviceMode();

                    if ([stickyDesktop, stickyTablet, stickyMobile].includes(currentDeviceMode)) {
                        disableSticky(stickyWaypoint);
                    } else {
                        stickyWaypoint.observe($videoBoxElement[0])
                    }

                    // if ('' !== stickyDesktop && currentDeviceMode == stickyDesktop) {
                    //     disableSticky(stickyWaypoint);
                    // } else if ('' !== stickyTablet && currentDeviceMode == stickyTablet) {
                    //     disableSticky(stickyWaypoint);
                    // } else if ('' !== stickyMobile && currentDeviceMode == stickyMobile) {
                    //     disableSticky(stickyWaypoint);
                    // } else {
                    //     stickyWaypoin[0].enable();
                    // }
                }

                function disableSticky(stickyWaypoint) {
                    stickyWaypoint.unobserve($videoBoxElement[0]);
                    $videoBoxElement.removeClass('premium-video-box-sticky-apply premium-video-box-sticky-hide');
                }

                function checkScroll() {
                    if ($videoBoxElement.hasClass('premium-video-box-sticky-apply')) {
                        $videoInnerContainer.draggable({
                            start: function () {
                                $(this).css({
                                    transform: "none",
                                    top: $(this).offset().top + "px",
                                    left: $(this).offset().left + "px"
                                });
                            },
                            containment: 'window'
                        });
                    }
                }

                $(document).on('premium_after_sticky_applied', function (e, $scope) {
                    var infobar = $scope.find('.premium-video-box-sticky-infobar');

                    if (0 !== infobar.length) {
                        var infobarHeight = infobar.outerHeight();

                        if ($scope.hasClass('premium-video-sticky-center-left') || $scope.hasClass('premium-video-sticky-center-right')) {
                            infobarHeight = Math.ceil(infobarHeight / 2);
                            $videoInnerContainer.css('top', 'calc( 50% - ' + infobarHeight + 'px )');
                        }

                        if ($scope.hasClass('premium-video-sticky-bottom-left') || $scope.hasClass('premium-video-sticky-bottom-right')) {
                            if ('' !== stickyMargin) {
                                infobarHeight = Math.ceil(infobarHeight);
                                var stickBottom = infobarHeight + stickyMargin;
                                $videoInnerContainer.css('bottom', stickBottom);
                            }
                        }
                    }
                });
            }

            function getPrettyPhotoSettings(theme) {
                return {
                    theme: theme,
                    hook: "data-rel",
                    opacity: 0.7,
                    show_title: false,
                    deeplinking: false,
                    overlay_gallery: true,
                    custom_markup: "",
                    default_width: 900,
                    default_height: 506,
                    social_tools: ""
                };
            }
        };

        /****** Premium Media Grid Handler ******/
        var PremiumGridWidgetHandler = ModuleHandler.extend({

            settings: {},

            getDefaultSettings: function () {
                return {
                    selectors: {
                        galleryElement: '.premium-gallery-container',
                        filters: '.premium-gallery-cats-container li',
                        gradientLayer: '.premium-gallery-gradient-layer',
                        loadMore: '.premium-gallery-load-more',
                        loadMoreDiv: '.premium-gallery-load-more div',
                        vidWrap: '.premium-gallery-video-wrap',
                    }
                }
            },

            getDefaultElements: function () {

                var selectors = this.getSettings('selectors'),
                    elements = {
                        $galleryElement: this.$element.find(selectors.galleryElement),
                        $filters: this.$element.find(selectors.filters),
                        $gradientLayer: this.$element.find(selectors.gradientLayer),
                        $vidWrap: this.$element.find(selectors.vidWrap)
                    };

                elements.$loadMore = elements.$galleryElement.parent().find(selectors.loadMore)
                elements.$loadMoreDiv = elements.$galleryElement.parent().find(selectors.loadMoreDiv)

                return elements;
            },

            bindEvents: function () {
                this.getGlobalSettings();
                this.run();

                var self = this;
                $(document).on('elementor/popup/show', function () {
                    self.run();
                });
            },

            getGlobalSettings: function () {
                var $galleryElement = this.elements.$galleryElement,
                    settings = $galleryElement.data('settings');

                this.settings = {
                    layout: settings.img_size,
                    loadMore: settings.load_more,
                    columnWidth: null,
                    filter: null,
                    isFilterClicked: false,
                    minimum: settings.minimum,
                    imageToShow: settings.click_images,
                    counter: settings.minimum,
                    ltrMode: settings.ltr_mode,
                    shuffle: settings.shuffle,
                    active_cat: settings.active_cat,
                    theme: settings.theme,
                    overlay: settings.overlay,
                    sort_by: settings.sort_by,
                    light_box: settings.light_box,
                    flag: settings.flag,
                    lightbox_type: settings.lightbox_type
                }
            },

            updateCounter: function () {

                if (this.settings.isFilterClicked) {

                    this.settings.counter = this.settings.minimum;

                    this.settings.isFilterClicked = false;

                } else {
                    this.settings.counter = this.settings.counter;
                }

                this.settings.counter = this.settings.counter + this.settings.imageToShow;
            },

            updateGrid: function (gradHeight, $isotopeGallery, $loadMoreDiv) {
                $.ajax({
                    url: this.appendItems(this.settings.counter, gradHeight, $isotopeGallery),
                    beforeSend: function () {
                        $loadMoreDiv.removeClass("premium-gallery-item-hidden");
                    },
                    success: function () {
                        $loadMoreDiv.addClass("premium-gallery-item-hidden");
                    }
                });
            },

            loadMore: function (gradHeight, $isotopeGallery) {

                var $galleryElement = this.elements.$galleryElement,
                    $loadMoreDiv = this.elements.$loadMoreDiv,
                    $loadMore = this.elements.$loadMore,
                    _this = this;

                $loadMoreDiv.addClass("premium-gallery-item-hidden");

                if ($galleryElement.find(".premium-gallery-item").length > this.settings.minimum) {

                    $loadMore.removeClass("premium-gallery-item-hidden");

                    $galleryElement.parent().on("click", ".premium-gallery-load-less", function () {
                        _this.settings.counter = _this.settings.counter - _this.settings.imageToShow;
                    });

                    $galleryElement.parent().on("click", ".premium-gallery-load-more-btn:not(.premium-gallery-load-less)", function () {
                        _this.updateCounter();
                        _this.updateGrid(gradHeight, $isotopeGallery, $loadMoreDiv);
                    });

                }

            },

            getItemsToHide: function (instance, imagesToShow) {
                var items = instance.filteredItems.slice(imagesToShow, instance
                    .filteredItems.length).map(function (item) {
                        return item.element;
                    });

                return items;
            },

            appendItems: function (imagesToShow, gradHeight, $isotopeGallery) {

                var $galleryElement = this.elements.$galleryElement,
                    $gradientLayer = this.elements.$gradientLayer,
                    instance = $galleryElement.data("isotope"),
                    itemsToHide = this.getItemsToHide(instance, imagesToShow);

                $gradientLayer.outerHeight(gradHeight);

                $galleryElement.find(".premium-gallery-item-hidden").removeClass("premium-gallery-item-hidden");

                $galleryElement.parent().find(".premium-gallery-load-more").removeClass("premium-gallery-item-hidden");

                $(itemsToHide).addClass("premium-gallery-item-hidden");

                $isotopeGallery.isotope("layout");

                if (0 == itemsToHide) {

                    $gradientLayer.addClass("premium-gallery-item-hidden");

                    $galleryElement.parent().find(".premium-gallery-load-more").addClass("premium-gallery-item-hidden");
                }
            },

            triggerFilerTabs: function (url) {
                var filterIndex = url.searchParams.get(this.settings.flag),
                    $filters = this.elements.$filters;

                if (filterIndex) {

                    var $targetFilter = $filters.eq(filterIndex).find("a");

                    $targetFilter.trigger('click');

                }
            },

            onReady: function ($isotopeGallery) {
                var _this = this;

                $isotopeGallery.isotope("layout");

                // $isotopeGallery.isotope({
                //     filter: _this.settings.active_cat
                // });

                var url = new URL(window.location.href);

                if (url)
                    _this.triggerFilerTabs(url);

                //Show the widget after making sure everything is ready.
                _this.$element.find(".category.active").trigger('click');
                _this.$element.find(".elementor-invisible").removeClass("elementor-invisible");

            },

            onResize: function ($isotopeGallery) {
                var _this = this;

                _this.setMetroLayout();

                $isotopeGallery.isotope({
                    itemSelector: ".premium-gallery-item",
                    masonry: {
                        columnWidth: _this.settings.columnWidth
                    },
                });

            },

            lightBoxDisabled: function () {
                var _this = this,
                    $vidWrap = this.elements.$vidWrap;

                $vidWrap.each(function (index, item) {
                    var type = $(item).data("type");

                    $(".pa-gallery-video-icon").keypress(function () {
                        $(this).closest(".premium-gallery-item").trigger('click');
                    });

                    $(item).closest(".premium-gallery-item").on("click", function () {

                        var $this = $(this);

                        $this.find(".pa-gallery-img-container").css("background", "#000");

                        $this.find("img, .pa-gallery-icons-caption-container, .pa-gallery-icons-wrapper").css("visibility", "hidden");

                        if ("style3" !== _this.settings.skin)
                            $this.find(".premium-gallery-caption").css("visibility", "hidden");

                        if ("hosted" !== type) {
                            _this.playVid($this);
                        } else {
                            _this.playHostedVid(item);
                        }
                    });
                });

            },

            playVid: function ($this) {
                var $iframeWrap = $this.find(".premium-gallery-iframe-wrap"),
                    src = $iframeWrap.data("src");

                src = src.replace("&mute", "&autoplay=1&mute");

                var $iframe = $("<iframe/>");

                $iframe.attr({
                    "src": src,
                    "frameborder": "0",
                    "allowfullscreen": "1",
                    "allow": "autoplay;encrypted-media;"
                });

                $iframeWrap.html($iframe);

                $iframe.css("visibility", "visible");
            },

            playHostedVid: function (item) {
                var $video = $(item).find("video");

                $video.get(0).play();
                $video.css("visibility", "visible");
            },

            run: function () {

                var $galleryElement = this.elements.$galleryElement,
                    $vidWrap = this.elements.$vidWrap,
                    $filters = this.elements.$filters,
                    _this = this;

                if ('metro' === this.settings.layout) {

                    this.setMetroLayout();

                    this.settings.layout = "masonry";

                    $(window).resize(function () { _this.onResize($isotopeGallery); });
                }

                var $isotopeGallery = $galleryElement.isotope(this.getIsoTopeSettings());

                $isotopeGallery.imagesLoaded().progress(function () {
                    $isotopeGallery.isotope("layout");
                });

                $(document).ready(function () { _this.onReady($isotopeGallery); });

                if (this.settings.loadMore) {

                    var $gradientLayer = this.elements.$gradientLayer,
                        gradHeight = null;

                    setTimeout(function () {
                        gradHeight = $gradientLayer.outerHeight();
                    }, 200);

                    this.loadMore(gradHeight, $isotopeGallery);
                }

                if ("yes" !== this.settings.light_box)
                    this.lightBoxDisabled();

                $filters.find("a").click(function (e) {
                    e.preventDefault();

                    _this.isFilterClicked = true;

                    $filters.find(".active").removeClass("active");

                    $(this).addClass("active");

                    _this.settings.filter = $(this).attr("data-filter");

                    $isotopeGallery.isotope({
                        filter: _this.settings.filter
                    });

                    if (_this.settings.shuffle) $isotopeGallery.isotope("shuffle");

                    if (_this.settings.loadMore) _this.appendItems(_this.settings.minimum, gradHeight, $isotopeGallery);

                    return false;
                });

                if ("default" === this.settings.lightbox_type)
                    this.$element.find(".premium-img-gallery a[data-rel^='prettyPhoto']").prettyPhoto(this.getPrettyPhotoSettings());
            },

            getPrettyPhotoSettings: function () {
                return {
                    theme: this.settings.theme,
                    hook: "data-rel",
                    opacity: 0.7,
                    show_title: false,
                    deeplinking: false,
                    overlay_gallery: this.settings.overlay,
                    custom_markup: "",
                    default_width: 900,
                    default_height: 506,
                    social_tools: ""
                }
            },

            getIsoTopeSettings: function () {
                return {
                    itemSelector: '.premium-gallery-item',
                    percentPosition: true,
                    animationOptions: {
                        duration: 750,
                        easing: 'linear'
                    },
                    filter: this.settings.active_cat,
                    layoutMode: this.settings.layout,
                    originLeft: this.settings.ltrMode,
                    masonry: {
                        columnWidth: this.settings.columnWidth
                    },
                    sortBy: this.settings.sort_by
                }
            },

            getRepeaterSettings: function () {
                return this.getElementSettings('premium_gallery_img_content');
            },

            setMetroLayout: function () {

                var $galleryElement = this.elements.$galleryElement,
                    gridWidth = $galleryElement.width(),
                    cellSize = Math.floor(gridWidth / 12),
                    deviceType = elementorFrontend.getCurrentDeviceMode(),
                    suffix = 'desktop' === deviceType ? '' : '_' + deviceType,
                    repeater = this.getRepeaterSettings();

                $galleryElement.find(".premium-gallery-item").each(function (index, item) { //should be added to selectors and elements

                    var cells = repeater[index]['premium_gallery_image_cell' + suffix].size,
                        vCells = repeater[index]['premium_gallery_image_vcell' + suffix].size;

                    if ("" === cells || undefined == cells) {
                        cells = repeater[index].premium_gallery_image_cell;
                    }

                    if ("" === vCells || undefined == vCells) {
                        vCells = repeater[index].premium_gallery_image_vcell;
                    }

                    $(item).css({
                        width: Math.ceil(cells * cellSize),
                        height: Math.ceil(vCells * cellSize)
                    });
                });

                this.settings.columnWidth = cellSize;
            }

        });

        /****** Premium Counter Handler ******/
        var PremiumCounterHandler = function ($scope, $) {

            var $counterElement = $scope.find(".premium-counter"),
                isHScrollWidget = $counterElement.closest(".premium-hscroll-temp"),
                counterSettings = $counterElement.data(),
                incrementElement = $counterElement.find(".premium-counter-init"),
                iconElement = $counterElement.find(".icon");

            if (!isHScrollWidget.length) {
                // unsing IntersectionObserverAPI.
                var eleObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            $(incrementElement).numerator(counterSettings);

                            $(iconElement).addClass("animated " + iconElement.data("animation"));

                            eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                        }
                    });
                });

                eleObserver.observe($counterElement[0]);
            } else {

                $(window).on("scroll", function () {

                    if ($(window).scrollTop() >= isHScrollWidget.data("scroll-offset")) {
                        $(incrementElement).numerator(counterSettings);

                        $(iconElement).addClass("animated " + iconElement.data("animation"));
                    }

                });

            }

        };

        /****** Premium Fancy Text Handler ******/
        var PremiumFancyTextHandler = function ($scope, $) {

            var $elem = $scope.find(".premium-atext__wrapper"),
                settings = $elem.data("settings"),
                loadingSpeed = settings.delay || 2500,
                $animatedText = $elem.find('.premium-atext__text'),
                startEffectOn = $elem.data('start-effect');

            function escapeHtml(unsafe) {
                return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(
                    /"/g, "&quot;");
            }

            if ('switch' === settings.style) {

                var itemCount = $elem.find('.premium-fancy-list-items').length,
                    loopCount = ('' === settings.count && !['typing', 'slide', 'autofade'].includes(settings.effect)) ? 'infinite' : (settings.count * itemCount);

                function triggerSwitchedEffect() {

                    if ("typing" === settings.effect) {

                        var fancyStrings = [];

                        settings.strings.forEach(function (item) {
                            fancyStrings.push(escapeHtml(item));
                        });

                        var uniqueID = 'animated-text-' + Math.floor(Math.random() * 100);
                        $animatedText.attr('id', uniqueID);

                        var fancyTextID = '#' + uniqueID;
                        var typedInstance = new Typed(fancyTextID, {
                            strings: fancyStrings,
                            typeSpeed: settings.typeSpeed,
                            backSpeed: settings.backSpeed,
                            startDelay: settings.startDelay,
                            backDelay: settings.backDelay,
                            showCursor: settings.showCursor,
                            loop: settings.loop
                        });

                        //To start/stop programmatically.
                        if ($scope.hasClass("fancy-text-stop")) {
                            typedInstance.stop();
                        }

                        $(".fancy-text-stop").bind("fancy-text-start", function () {
                            typedInstance.start();
                        });


                    } else if ("slide" === settings.effect) {
                        loadingSpeed = settings.pause;

                        $animatedText.vTicker({
                            speed: settings.speed,
                            showItems: settings.showItems,
                            pause: settings.pause,
                            mousePause: settings.mousePause,
                            direction: "up"
                        });

                    } else {

                        setFancyAnimation();

                        function setFancyAnimation() {

                            var $item = $elem.find(".premium-fancy-list-items"),
                                $stringsWrap = $elem.find('.premium-atext__items-wrapper'),
                                current = 1;

                            //Get effect settings
                            var delay = settings.delay || 2500,
                                loopCount = settings.count;

                            //If Loop Count option is set
                            if (loopCount) {
                                var currentLoop = 1,
                                    fancyStringsCount = $elem.find(".premium-fancy-list-items").length;
                            }

                            var loopInterval = setInterval(function () {

                                if ('clip' === settings.effect) {

                                    $stringsWrap.animate({
                                        width: 0
                                    }, (settings.speed / 2) || 1000, function () {

                                        //Show current active item
                                        $item.eq(current).addClass("premium-fancy-item-visible").removeClass("premium-fancy-item-hidden");

                                        var $inactiveItems = $item.filter(function (index) {
                                            return index !== current;
                                        });

                                        //Hide inactive items
                                        $inactiveItems.addClass("premium-fancy-item-hidden").removeClass("premium-fancy-item-visible");

                                        var visibleTextWidth = $stringsWrap.find('.premium-fancy-item-visible').outerWidth();

                                        $stringsWrap.animate({
                                            width: visibleTextWidth + 10
                                        }, (settings.speed / 2) || 1000, function () {

                                            current++;

                                            //Restart loop
                                            if ($item.length === current)
                                                current = 0;

                                            //Increment interval and check if loop count is reached
                                            if (loopCount) {
                                                currentLoop++;

                                                if ((fancyStringsCount * loopCount) === currentLoop)
                                                    clearInterval(loopInterval);
                                            }

                                        });

                                    });
                                } else {

                                    var animationClass = "";

                                    //Add animation class
                                    if (settings.effect === "custom")
                                        animationClass = "animated " + settings.animation;

                                    if (
                                        (settings.effect === 'custom') &&
                                        (settings.animation !== 'slideInUp' &&
                                            settings.animation !== 'slideInDown' &&
                                            settings.animation !== 'fadeInUp' &&
                                            settings.animation !== 'fadeInDown')
                                    ) {
                                        $stringsWrap.css('transition', 'width 0.5s');
                                    } else if (settings.effect === 'rotate') {
                                        $stringsWrap.css('transition', 'width 0.2s  0.5s')
                                    }

                                    //Show current active item
                                    $item.eq(current).addClass("premium-fancy-item-visible " + animationClass).removeClass("premium-fancy-item-hidden");

                                    var $inactiveItems = $item.filter(function (index) {
                                        return index !== current;
                                    });

                                    //Hide inactive items
                                    $inactiveItems.addClass("premium-fancy-item-hidden").removeClass("premium-fancy-item-visible " + animationClass);

                                    var visibleTextWidth = $stringsWrap.find('.premium-fancy-item-visible').outerWidth();

                                    $stringsWrap.css('width', visibleTextWidth);

                                    current++;

                                    //Restart loop
                                    if ($item.length === current)
                                        current = 0;

                                    //Increment interval and check if loop count is reached
                                    if (loopCount) {
                                        currentLoop++;

                                        if ((fancyStringsCount * loopCount) === currentLoop)
                                            clearInterval(loopInterval);
                                    }

                                }

                            }, delay);

                        }
                    }
                }

                if (startEffectOn === 'viewport') {

                    var observer = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                triggerSwitchedEffect();
                                observer.unobserve(entry.target);
                            }
                        });
                    });
                    observer.observe($elem[0]);

                } else {
                    triggerSwitchedEffect();
                }

                //Show the strings after the layout is set.
                if ("typing" !== settings.effect) {
                    setTimeout(function () {
                        $animatedText.css('opacity', '1');
                    }, 500);

                }

                if (settings.loading && 'typing' !== settings.effect) {
                    $scope.find('.premium-atext__text').append('<span class="premium-loading-bar"></span>');
                    $scope.find('.premium-loading-bar').css({
                        'animation-iteration-count': loopCount,
                        'animation-duration': loadingSpeed + 'ms'
                    });
                }
            } else {

                var highlightEffect = settings.effect;

                if (['tilt', 'flip', 'wave', 'pop'].includes(highlightEffect)) {

                    var textArray = $animatedText.text().trim().split("");


                    // element.firstChild.remove();

                    var elArray = textArray.map(function (letter, index) {

                        if (letter == " ")
                            letter = '&nbsp;';

                        var el = document.createElement("span");

                        el.className = "premium-atext__letter";
                        el.innerHTML = letter;
                        el.style.animationDelay = index / (textArray.length) + "s";

                        return el;
                    });

                    $animatedText.html(elArray);
                    setTimeout(function () {
                        $elem.css('opacity', 1);
                    }, 1000);


                } else if ('shape' === highlightEffect) {

                    var animationDelay = settings.delay || 4,
                        animationSpeed = settings.duration || 1.2;

                    var eleObserver = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                $elem.addClass('draw-shape');
                                setInterval(function () {
                                    $elem.addClass('hide-shape');

                                    setTimeout(function () {
                                        $elem.removeClass('hide-shape');
                                    }, 1000);
                                }, 1000 * (animationSpeed + animationDelay));

                                eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                            }
                        });
                    });

                    eleObserver.observe($elem[0]);

                    // elementorFrontend.waypoint($elem, function () {

                    //     $elem.addClass('draw-shape');

                    //     setInterval(function () {
                    //         $elem.addClass('hide-shape');

                    //         setTimeout(function () {
                    //             $elem.removeClass('hide-shape');
                    //         }, 1000);
                    //     }, 1000 * (animationSpeed + animationDelay));

                    // });
                }
            }

        };

        /****** Premium Countdown Handler ******/
        var PremiumCountDownHandler = function ($scope, $) {

            var $countDownElement = $scope.find(".premium-countdown"),
                $countDown = $countDownElement.find('.countdown'),
                settings = $countDownElement.data("settings"),
                timerType = settings.timerType,
                until = 'evergreen' === timerType ? settings.until.date.replace(/ /g, "T") : settings.until,
                layout = '',
                computedStyle = getComputedStyle($scope[0]);

            function escapeHTML(str) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                };

                return str.replace(/[&<>"']/g, function (m) { return map[m]; });
            }

            if (settings.separator) {
                settings.separator = escapeHTML(settings.separator);
            }

            var currentDate = new Date().getTime(),
                untilDate = new Date(until).getTime();

            if ('' !== settings.serverSync) {
                currentDate = new Date(settings.serverSync).getTime();
            }

            // Calculate the difference in seconds between the future and current date
            var diff = Math.round(untilDate / 1000 - currentDate / 1000);

            if ('flipping' === settings.style) {

                var clock;

                // Run countdown timer
                clock = $countDown.FlipClock(diff, {
                    clockFace: "DailyCounter",
                    countdown: true,
                    timeSeparator: settings.separator || '',
                    language: settings.lang,
                    callbacks: {
                        stop: function () {

                            triggerExpirationAction();
                        }
                    }
                });

            } else {

                // var single = settings.single.split(","),
                //     plural = settings.plural.split(",");

                $countDownElement.find('.countdown').countdown({
                    $countDown: $countDown,
                    layout: layout,
                    // labels: single,
                    // labels1: plural,
                    until: diff,
                    format: settings.format,
                    style: settings.style,
                    timeSeparator: settings.separator || '',
                    unitsPos: settings.unitsPos,
                    id: $scope.data('id'),
                    circleStrokeWidth: computedStyle.getPropertyValue('--pa-countdown-stroke-width'),
                    unitsInside: $scope.hasClass('premium-countdown-uinside-yes'),
                    onExpiry: function () {

                        triggerExpirationAction();

                    },
                });

                //To unify digit unit width.
                if ($scope.hasClass('premium-countdown-block')) {
                    var currentValueWidth = $countDown.find('.countdown-amount').last().outerWidth();

                    $countDown.find('.countdown-period span').css('width', currentValueWidth);
                }

                //For evergreen timer reset.
                if (settings.reset) {
                    $countDownElement.find('.premium-countdown-init').countdown('option', 'until', new Date(until));
                }

                if ('featured' === settings.style) {
                    var $targetUnit = $countDownElement.find('.countdown-section-' + settings.featuredUnit);
                    $targetUnit.parent().prepend($targetUnit);
                }

            }

            if ('.' === settings.separator) {
                $countDown.find('.countdown_separator span').addClass('countdown-separator-circle').text('');
            }

            if (diff < 0)
                triggerExpirationAction();

            function triggerExpirationAction() {

                if ('default' === settings.event && 'flipping' !== settings.style) {
                    setTimeout(function () {
                        if ('dash' === settings.changeTo) {
                            $countDown.find('.countdown-amount > span').text('-');
                        } else if ('done' === settings.changeTo && $countDown.find('.countdown-show4').length > 0) {
                            var characters = ['D', 'O', 'N', 'E'];

                            characters.map(function (char, index) {
                                $countDown.find('.countdown-amount > span').eq(index).text(char);
                            });

                        }

                    }, 1000);
                } else if ('text' === settings.event) {
                    $countDown.remove();
                    $scope.find(".premium-addons__v-hidden").removeClass('premium-addons__v-hidden');
                } else if ('url' === settings.event && !elementorFrontend.isEditMode()) {

                    if ('' !== settings.text) {
                        var urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.-]{2,})([\/\w \u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF%.-]*)*\/?$/i;

                        // Test the string against the regular expression
                        if (urlPattern.test(settings.text))
                            window.location.href = settings.text;
                    }

                } else if ('restart' === settings.event) {

                    if ('flipping' === settings.style) {
                        setTimeout(function () {
                            clock.setTime(diff); // Restart with the same target seconds.
                        }, 1000);

                        setTimeout(function () {
                            clock.start(); // Restart with the same target seconds.
                        }, 2000);

                    } else {
                        setTimeout(function () {
                            $countDownElement.find('.premium-countdown-init').countdown('option', { until: diff });
                        }, 1000);
                    }


                }

            }


        };

        /****** Premium Carousel Handler ******/
        var PremiumCarouselHandler = function ($scope, $) {

            var $carouselElem = $scope.find(".premium-carousel-wrapper"),
                settings = $($carouselElem).data("settings"),
                computedStyle = getComputedStyle($scope[0]),
                widgetID = $scope.data('id'),
                currentSlides = 0;

            //Fix conflict with Stretch Section option
            $carouselElem.find('.elementor-section-stretched').removeClass('elementor-section-stretched');

            var $progressbar = $carouselElem.find(".premium-carousel-nav-progress-fill");

            if ($carouselElem.find(".item-wrapper").length < 1)
                return;

            function slideToShow(slick) {

                var slidesToShow = slick.options.slidesToShow,
                    windowWidth = $(window).width();
                if (windowWidth > settings.tabletBreak) {
                    slidesToShow = settings.slidesDesk;
                }
                if (windowWidth <= settings.tabletBreak) {
                    slidesToShow = settings.slidesTab;
                }
                if (windowWidth <= settings.mobileBreak) {
                    slidesToShow = settings.slidesMob;
                }
                return slidesToShow;

            }

            $carouselElem.on("init", function (event) {

                event.preventDefault();

                setTimeout(function () {
                    window.carouselTrigger = true;
                    $scope.trigger("paCarouselLoaded");
                    resetAnimations("init");
                }, 500);

                $(this).find("item-wrapper.slick-active").each(function () {
                    var $this = $(this);
                    $this.addClass($this.data("animation"));
                });

                $(".slick-track").addClass("translate");

            });

            $carouselElem.find(".premium-carousel-inner").slick({
                vertical: settings.vertical,
                slidesToScroll: settings.slidesToScroll,
                slidesToShow: settings.slidesToShow,
                responsive: [{
                    breakpoint: settings.tabletBreak,
                    settings: {
                        slidesToShow: settings.slidesTab,
                        slidesToScroll: settings.slidesTab,
                        swipe: settings.touchMove,
                    }
                },
                {
                    breakpoint: settings.mobileBreak,
                    settings: {
                        slidesToShow: settings.slidesMob,
                        slidesToScroll: settings.slidesMob,
                        swipe: settings.touchMove,
                    }
                }
                ],
                useTransform: true,
                fade: settings.fade,
                infinite: settings.infinite,
                speed: settings.speed,
                autoplay: settings.autoplay,
                autoplaySpeed: settings.autoplaySpeed,
                rows: 0,
                draggable: settings.draggable,
                rtl: settings.rtl,
                adaptiveHeight: settings.adaptiveHeight,
                pauseOnHover: settings.pauseOnHover,
                centerMode: settings.centerMode,
                centerPadding: computedStyle.getPropertyValue('--pa-carousel-center-padding') + 'px',
                arrows: settings.arrows,
                prevArrow: $carouselElem.find(".premium-carousel-nav-arrow-prev").html(),
                nextArrow: $carouselElem.find(".premium-carousel-nav-arrow-next").html(),
                dots: settings.dots,
                variableWidth: settings.variableWidth,
                cssEase: settings.cssEase,
                customPaging: function () {
                    var customDot = $carouselElem.find(".premium-carousel-nav-dot").html();
                    return customDot;
                },
                carouselNavigation: settings.carouselNavigation,
                templatesNumber: settings.templatesNumber,
            });

            function runProgress() {
                $progressbar.animate({ 'width': "+=100%" }, settings.autoplaySpeed, runProgress);
            }

            if (settings.carouselNavigation === "progressbar") {
                var progress = (currentSlides + 1) / settings.templatesNumber;
                $carouselElem.find(".premium-carousel-nav-progressbar-fill").css("transform", "translate3d(0px, 0px, 0px) scaleX(" + progress + ") scaleY(1)");
            }

            if (settings.carouselNavigation === "progress") {
                runProgress();
            }

            $scope.find(".premium-carousel-hidden").removeClass("premium-carousel-hidden");
            $carouselElem.find(".premium-carousel-nav-arrow-prev").remove();
            $carouselElem.find(".premium-carousel-nav-arrow-next").remove();
            // $carouselElem.find(".premium-carousel-nav-dot").remove();

            if (settings.variableWidth) {
                $carouselElem.find(".elementor-container").css("flex-wrap", "nowrap");
            }

            function resetAnimations(event) {

                var $slides = $carouselElem.find(".slick-slide");
                $progressbar.stop(true).animate({ "width": 0 }, 0);

                if ("init" === event)
                    $slides = $slides.not(".slick-current");

                $slides.find(".animated").each(function (index, elem) {

                    var settings = $(elem).data("settings");

                    if (!settings)
                        return;

                    if (!settings._animation && !settings.animation)
                        return;

                    var animation = settings._animation || settings.animation;

                    $(elem).removeClass("animated " + animation).addClass("elementor-invisible");
                });

                if (settings.carouselNavigation === "progress") {
                    runProgress();
                }
            };

            function triggerAnimation() {

                $carouselElem.find(".slick-active .elementor-invisible").each(function (index, elem) {

                    var settings = $(elem).data("settings");

                    if (!settings)
                        return;

                    if (!settings._animation && !settings.animation)
                        return;

                    var delay = settings._animation_delay ? settings._animation_delay : 0,
                        animation = settings._animation || settings.animation;

                    setTimeout(function () {
                        $(elem).removeClass("elementor-invisible").addClass(animation +
                            ' animated');
                    }, delay);
                });
            }

            $carouselElem.on("afterChange", function (event, slick, currentSlide) {

                var slidesScrolled = slick.options.slidesToScroll,
                    slidesToShow = slideToShow(slick),
                    centerMode = slick.options.centerMode,
                    slideToAnimate = currentSlide + slidesToShow - 1;

                //Trigger Aniamtions for the current slide
                triggerAnimation();

                if (slidesScrolled === 1) {
                    if (!centerMode === true) {
                        var $inViewPort = $(this).find("[data-slick-index='" + slideToAnimate +
                            "']");
                        if ("null" != settings.animation) {
                            $inViewPort.find("p, h1, h2, h3, h4, h5, h6, span, a, img, i, button")
                                .addClass(settings.animation).removeClass(
                                    "premium-carousel-content-hidden");
                        }
                    }
                } else {
                    for (var i = slidesScrolled + currentSlide; i >= 0; i--) {
                        $inViewPort = $(this).find("[data-slick-index='" + i + "']");
                        if ("null" != settings.animation) {
                            $inViewPort.find("p, h1, h2, h3, h4, h5, h6, span, a, img, i, button")
                                .addClass(settings.animation).removeClass(
                                    "premium-carousel-content-hidden");
                        }
                    }
                }

                //Fix carousel continues to work after last slide if autoplay is true and infinite is false.
                if (slick.$slides.length - 1 == currentSlide && !settings.infinite) {
                    $carouselElem.find(".premium-carousel-inner").slick('slickSetOption', 'autoplay', false, false);
                }

                if (slick.options.carouselNavigation === "fraction") {
                    $carouselElem.find("#currentSlide").text((currentSlide || 0) + 1);
                }

                if (slick.options.carouselNavigation === "progressbar") {
                    var progress = (currentSlide + 1) / slick.options.templatesNumber;
                    $carouselElem.find(".premium-carousel-nav-progressbar-fill").css("transform", "translate3d(0px, 0px, 0px) scaleX(" + progress + ") scaleY(1)");
                }

            });

            $carouselElem.on("beforeChange", function (event, slick, currentSlide) {

                //Reset Aniamtions for the other slides
                resetAnimations();

                var $inViewPort = $(this).find("[data-slick-index='" + currentSlide + "']");

                if ("null" != settings.animation) {
                    $inViewPort.siblings().find(
                        "p, h1, h2, h3, h4, h5, h6, span, a, img, i, button").removeClass(
                            settings.animation).addClass(
                                "premium-carousel-content-hidden");
                }
            });

            if (settings.vertical) {

                var maxHeight = -1;

                elementorFrontend.elements.$window.on('load', function () {
                    $carouselElem.find(".slick-slide").each(function () {
                        if ($(this).height() > maxHeight) {
                            maxHeight = $(this).height();
                        }
                    });
                    $carouselElem.find(".slick-slide").each(function () {
                        if ($(this).height() < maxHeight) {
                            $(this).css("margin", Math.ceil(
                                (maxHeight - $(this).height()) / 2) + "px 0");
                        }
                    });
                });
            }
            var marginFix = {
                element: $("a.ver-carousel-arrow"),
                getWidth: function () {
                    var width = this.element.outerWidth();
                    return width / 2;
                },
                setWidth: function (type) {
                    type = type || "vertical";
                    if (type == "vertical") {
                        this.element.css("margin-left", "-" + this.getWidth() + "px");
                    } else {
                        this.element.css("margin-top", "-" + this.getWidth() + "px");
                    }
                }
            };
            marginFix.setWidth();
            marginFix.element = $("a.carousel-arrow");
            marginFix.setWidth("horizontal");

            $(document).ready(function () {

                settings.navigation.map(function (item, index) {

                    if (item) {

                        $(item).on("click", function () {

                            var currentActive = $carouselElem.find(".premium-carousel-inner").slick("slickCurrentSlide");

                            if (index !== currentActive) {
                                $carouselElem.find(".premium-carousel-inner").slick("slickGoTo", index)
                            }

                        })
                    }

                });

                $('.carousel-next-' + widgetID).on('click', function () {
                    $carouselElem.find(".premium-carousel-inner").slick("slickNext");
                });

                $('.carousel-prev-' + widgetID).on('click', function () {
                    $carouselElem.find(".premium-carousel-inner").slick("slickPrev");
                });

            })

        };

        var PremiumBannerHandler = ModuleHandler.extend({

            getDefaultSettings: function () {

                return {
                    selectors: {
                        bannerImgWrap: '.premium-banner-ib',
                        bannerImg: 'img',
                    }
                }

            },

            getDefaultElements: function () {

                var selectors = this.getSettings('selectors');

                return {
                    $bannerImgWrap: this.$element.find(selectors.bannerImgWrap),
                    $bannerImg: this.$element.find(selectors.bannerImg)
                }

            },

            bindEvents: function () {

                var _this = this;

                _this.elements.$bannerImgWrap.hover(function () {
                    _this.elements.$bannerImg.addClass("active");
                }, function () {
                    _this.elements.$bannerImg.removeClass("active");
                });

                this.run();
            },

            run: function () {

                var $bannerElement = this.$element;

                if ($bannerElement.hasClass("premium-banner-tilt-yes")) {

                    var reverse = $bannerElement.hasClass("premium-banner-tilt-rev-yes");

                    UniversalTilt.init({
                        elements: $bannerElement.closest(".elementor-widget"),
                        settings: {
                            reverse: reverse
                        },
                        callbacks: {
                            onMouseLeave: function (el) {
                                el.style.boxShadow = "0 45px 100px rgba(255, 255, 255, 0)";
                            },
                            onDeviceMove: function (el) {
                                el.style.boxShadow = "0 45px 100px rgba(255, 255, 255, 0.3)";
                            }
                        }
                    });

                }
            }

        });

        /****** Premium Modal Box Handler ******/
        var PremiumModalBoxHandler = function ($scope, $) {

            var $modalElem = $scope.find(".premium-modal-box-container"),
                settings = $modalElem.data("settings"),
                $modal = $modalElem.find(".premium-modal-box-modal-dialog");

            if (!settings) {
                return;
            }

            if (settings.trigger === "pageload") {
                $(document).ready(function ($) {
                    setTimeout(function () {
                        $modalElem.find(".premium-modal-box-modal").modal();
                    }, settings.delay * 1000);
                });
            }

            if ($modal.data("modal-animation") && " " != $modal.data("modal-animation")) {
                var animationDelay = $modal.data('delay-animation');

                // unsing IntersectionObserverAPI.
                var eleObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            setTimeout(function () {
                                $modal.css("opacity", "1").addClass("animated " + $modal.data("modal-animation"));
                            }, animationDelay * 1000);

                            eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                        }
                    });
                }, {
                    threshold: 0.25
                });

                eleObserver.observe($modal[0]);
            }
        };

        /****** Premium Blog Handler ******/
        var PremiumBlogHandler = ModuleHandler.extend({

            settings: {},

            getDefaultSettings: function () {
                return {
                    selectors: {
                        user: '.fa-user',
                        activeCat: '.category.active',
                        loading: '.premium-loading-feed',
                        blogElement: '.premium-blog-wrap',
                        blogFilterTabs: '.premium-blog-filter',
                        contentWrapper: '.premium-blog-content-wrapper',
                        blogPost: '.premium-blog-post-outer-container',
                        metaSeparators: '.premium-blog-meta-separator',
                        filterLinks: '.premium-blog-filters-container li a',
                        currentPage: '.premium-blog-pagination-container .page-numbers.current',
                        activeElememnt: '.premium-blog-filters-container li .active',
                    }
                }
            },

            getDefaultElements: function () {
                var selectors = this.getSettings('selectors'),
                    elements = {
                        $blogElement: this.$element.find(selectors.blogElement),
                        $blogFilterTabs: this.$element.find(selectors.blogFilterTabs),
                        $activeCat: this.$element.find(selectors.activeCat),
                        $filterLinks: this.$element.find(selectors.filterLinks),
                        $blogPost: this.$element.find(selectors.blogPost),
                        $contentWrapper: this.$element.find(selectors.contentWrapper)
                    };

                return elements;
            },

            bindEvents: function () {
                this.setLayoutSettings();
                this.removeMetaSeparators();
                this.run();
            },

            setLayoutSettings: function () {

                var settings = this.getElementSettings(),
                    $blogPost = this.elements.$blogPost;

                var layoutSettings = {
                    pageNumber: 1,
                    isLoaded: true,
                    count: 2,
                    equalHeight: settings.force_height,
                    layout: settings.premium_blog_layout,
                    carousel: 'yes' === settings.premium_blog_carousel ? true : false,
                    infinite: 'yes' === settings.premium_blog_infinite_scroll ? true : false,
                    scrollAfter: 'yes' === settings.scroll_to_offset ? true : false,
                    grid: 'yes' === settings.premium_blog_grid ? true : false,
                    total: $blogPost.data('total'),
                };


                if (layoutSettings.carousel) {

                    layoutSettings.slidesToScroll = settings.slides_to_scroll;
                    layoutSettings.spacing = parseInt(settings.premium_blog_carousel_spacing);
                    layoutSettings.autoPlay = 'yes' === settings.premium_blog_carousel_play ? true : false;
                    layoutSettings.arrows = 'yes' === settings.premium_blog_carousel_arrows ? true : false;
                    layoutSettings.fade = 'yes' === settings.premium_blog_carousel_fade ? true : false;
                    layoutSettings.center = 'yes' === settings.premium_blog_carousel_center ? true : false;
                    layoutSettings.dots = 'yes' === settings.premium_blog_carousel_dots ? true : false;
                    layoutSettings.speed = '' !== settings.carousel_speed ? parseInt(settings.carousel_speed) : 300;
                    layoutSettings.autoplaySpeed = '' !== settings.premium_blog_carousel_autoplay_speed ? parseInt(settings.premium_blog_carousel_autoplay_speed) : 5000;

                }

                this.settings = layoutSettings;

            },

            removeMetaSeparators: function () {

                var selectors = this.getSettings('selectors'),
                    $blogPost = this.$element.find(selectors.blogPost);

                var $metaSeparators = $blogPost.first().find(selectors.metaSeparators),
                    $user = $blogPost.find(selectors.user);

                if (1 === $metaSeparators.length) {
                    //If two meta only are enabled. One of them is author meta.
                    if (!$user.length) {
                        $blogPost.find(selectors.metaSeparators).remove();
                    }

                } else {
                    if (!$user.length) {
                        $blogPost.each(function (index, post) {
                            $(post).find(selectors.metaSeparators).first().remove();
                        });
                    }
                }

            },
            run: function () {

                var _this = this,
                    $blogElement = this.elements.$blogElement,
                    $activeCategory = this.elements.$activeCat.data('filter'),
                    $filterTabs = this.elements.$blogFilterTabs.length,
                    pagination = $blogElement.data("pagination");

                this.settings.activeCategory = $activeCategory;
                this.settings.filterTabs = $filterTabs;



                if (this.settings.filterTabs) {
                    this.filterTabs();
                }

                if ("masonry" === this.settings.layout && !this.settings.carousel) {

                    $blogElement.imagesLoaded(function () {

                        if ("*" === _this.settings.activeCategory) {
                            $blogElement.isotope(_this.getIsoTopeSettings());
                        } else {
                            $blogElement.isotope({
                                itemSelector: ".premium-blog-post-outer-container",
                                animate: false,

                            });
                        }

                    });
                    //
                }

                if (this.settings.carousel) {
                    $blogElement.slick(this.getSlickSettings());

                    $blogElement.removeClass("premium-carousel-hidden");
                }

                if ("even" === this.settings.layout && this.settings.equalHeight) {
                    $blogElement.imagesLoaded(function () {
                        _this.forceEqualHeight();
                    });
                }

                if (pagination) {
                    this.paginate();
                }

                if (this.settings.infinite && $blogElement.is(":visible")) {
                    this.getInfiniteScrollPosts();
                }

            },

            paginate: function () {
                var _this = this,
                    $scope = this.$element,
                    selectors = this.getSettings('selectors');

                $scope.on('click', '.premium-blog-pagination-container .page-numbers', function (e) {

                    e.preventDefault();

                    if ($(this).hasClass("current")) return;

                    var currentPage = parseInt($scope.find(selectors.currentPage).html());

                    if ($(this).hasClass('next')) {
                        _this.settings.pageNumber = currentPage + 1;
                    } else if ($(this).hasClass('prev')) {
                        _this.settings.pageNumber = currentPage - 1;
                    } else {
                        _this.settings.pageNumber = $(this).html();
                    }

                    _this.getPostsByAjax(_this.settings.scrollAfter);

                })
            },

            forceEqualHeight: function () {
                var heights = new Array(),
                    contentWrapper = this.getSettings('selectors').contentWrapper,
                    $blogWrapper = this.$element.find(contentWrapper);

                $blogWrapper.each(function (index, post) {

                    var height = $(post).outerHeight();

                    heights.push(height);
                });

                var maxHeight = Math.max.apply(null, heights);

                $blogWrapper.css("height", maxHeight + "px");
            },

            getSlickSettings: function () {

                var settings = this.settings,
                    slickCols = settings.grid ? this.getSlickCols() : null,
                    cols = settings.grid ? slickCols.cols : 1,
                    colsTablet = settings.grid ? slickCols.colsTablet : 1,
                    colsMobile = settings.grid ? slickCols.colsMobile : 1,
                    prevArrow = settings.arrows ? '<a type="button" data-role="none" class="carousel-arrow carousel-prev" aria-label="Previous" role="button" style=""><i class="fas fa-angle-left" aria-hidden="true"></i></a>' : '',
                    nextArrow = settings.arrows ? '<a type="button" data-role="none" class="carousel-arrow carousel-next" aria-label="Next" role="button" style=""><i class="fas fa-angle-right" aria-hidden="true"></i></a>' : '';

                return {
                    infinite: true,
                    slidesToShow: cols,
                    slidesToScroll: settings.slidesToScroll || cols,
                    responsive: [{
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
                    autoplay: settings.autoPlay,
                    rows: 0,
                    speed: settings.speed,
                    autoplaySpeed: settings.autoplaySpeed,
                    nextArrow: nextArrow,
                    prevArrow: prevArrow,
                    fade: settings.fade,
                    centerMode: settings.center,
                    centerPadding: settings.spacing + "px",
                    draggable: true,
                    dots: settings.dots,
                    customPaging: function () {
                        return '<i class="fas fa-circle"></i>';
                    }
                }

            },

            getSlickCols: function () {
                var slickCols = this.getElementSettings(),
                    cols = slickCols.premium_blog_columns_number,
                    colsTablet = slickCols.premium_blog_columns_number_tablet,
                    colsMobile = slickCols.premium_blog_columns_number_mobile;

                return {
                    cols: parseInt(100 / cols.substr(0, cols.indexOf('%'))),
                    colsTablet: parseInt(100 / colsTablet.substr(0, colsTablet.indexOf('%'))),
                    colsMobile: parseInt(100 / colsMobile.substr(0, colsMobile.indexOf('%'))),
                }

            },

            getIsoTopeSettings: function () {
                return {
                    itemSelector: ".premium-blog-post-outer-container",
                    percentPosition: true,
                    filter: this.settings.activeCategory,
                    animationOptions: {
                        duration: 750,
                        easing: "linear",
                        queue: false
                    }
                }
            },

            filterTabs: function () {

                var _this = this,
                    selectors = this.getSettings('selectors'),
                    $filterLinks = this.elements.$filterLinks;

                $filterLinks.click(function (e) {

                    e.preventDefault();

                    _this.$element.find(selectors.activeElememnt).removeClass("active");

                    $(this).addClass("active");

                    //Get clicked tab slug
                    _this.settings.activeCategory = $(this).attr("data-filter");

                    _this.settings.pageNumber = 1;

                    if (_this.settings.infinite) {
                        _this.getPostsByAjax(false);
                        _this.settings.count = 2;
                        _this.getInfiniteScrollPosts();
                    } else {
                        //Make sure to reset pagination before sending our AJAX request
                        _this.getPostsByAjax(_this.settings.scrollAfter);
                    }

                });
            },

            getPostsByAjax: function (shouldScroll) {

                //If filter tabs is not enabled, then always set category to all.
                if ('undefined' === typeof this.settings.activeCategory) {
                    this.settings.activeCategory = '*';
                }

                var _this = this,
                    $blogElement = this.elements.$blogElement,
                    selectors = this.getSettings('selectors');

                $.ajax({
                    url: PremiumSettings.ajaxurl,
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        action: 'pa_get_posts',
                        page_id: $blogElement.data('page'),
                        widget_id: _this.$element.data('id'),
                        page_number: _this.settings.pageNumber,
                        category: _this.settings.activeCategory,
                        nonce: PremiumSettings.nonce,
                    },
                    beforeSend: function () {

                        $blogElement.append('<div class="premium-loading-feed"><div class="premium-loader"></div></div>');

                        var stickyOffset = 0;
                        if ($('.elementor-sticky').length > 0)
                            stickyOffset = 100;

                        if (shouldScroll) {
                            $('html, body').animate({
                                scrollTop: (($blogElement.offset().top) - 50 - stickyOffset)
                            }, 'slow');
                        }

                    },
                    success: function (res) {
                        if (!res.data)
                            return;

                        $blogElement.find(selectors.loading).remove();

                        var posts = res.data.posts,
                            paging = res.data.paging;

                        if (_this.settings.infinite) {
                            _this.settings.isLoaded = true;
                            if (_this.settings.filterTabs && _this.settings.pageNumber === 1) {
                                $blogElement.html(posts);
                            } else {
                                $blogElement.append(posts);
                            }
                        } else {
                            //Render the new markup into the widget
                            $blogElement.html(posts);

                            _this.$element.find(".premium-blog-footer").html(paging);
                        }

                        _this.removeMetaSeparators();

                        //Make sure grid option is enabled.
                        if (_this.settings.layout) {
                            if ("even" === _this.settings.layout) {
                                if (_this.settings.equalHeight)
                                    _this.forceEqualHeight();

                            } else {

                                $blogElement.imagesLoaded(function () {

                                    $blogElement.isotope('reloadItems');
                                    $blogElement.isotope({
                                        itemSelector: ".premium-blog-post-outer-container",
                                        animate: false
                                    });
                                });
                            }
                        }

                    },
                    error: function (err) {
                        console.log(err);
                    }

                });
            },

            getInfiniteScrollPosts: function () {
                var windowHeight = jQuery(window).outerHeight() / 1.25,
                    _this = this;

                $(window).scroll(function () {

                    if (_this.settings.filterTabs) {
                        $blogPost = _this.elements.$blogElement.find(".premium-blog-post-outer-container");
                        _this.settings.total = $blogPost.data('total');
                    }

                    if (_this.settings.count <= _this.settings.total) {
                        if (($(window).scrollTop() + windowHeight) >= (_this.$element.find('.premium-blog-post-outer-container:last').offset().top)) {
                            if (true == _this.settings.isLoaded) {
                                _this.settings.pageNumber = _this.settings.count;
                                _this.getPostsByAjax(false);
                                _this.settings.count++;
                                _this.settings.isLoaded = false;
                            }

                        }
                    }
                });
            },

        });

        /****** Premium Image Scroll Handler ******/
        var PremiumImageScrollHandler = function ($scope, $) {
            var scrollElement = $scope.find(".premium-image-scroll-container"),
                scrollOverlay = scrollElement.find(".premium-image-scroll-overlay"),
                scrollVertical = scrollElement.find(".premium-image-scroll-vertical"),
                dataElement = scrollElement.data("settings"),
                imageScroll = scrollElement.find("img"),
                direction = dataElement["direction"],
                reverse = dataElement["reverse"],
                transformOffset = null;

            function startTransform() {
                imageScroll.css("transform", (direction === "vertical" ? "translateY" : "translateX") + "( -" +
                    transformOffset + "px)");
            }

            function endTransform() {
                imageScroll.css("transform", (direction === "vertical" ? "translateY" : "translateX") + "(0px)");
            }

            function setTransform() {
                if (direction === "vertical") {
                    transformOffset = imageScroll.height() - scrollElement.height();
                } else {
                    transformOffset = imageScroll.width() - scrollElement.width();
                }
            }
            if (dataElement["trigger"] === "scroll") {
                scrollElement.addClass("premium-container-scroll");
                if (direction === "vertical") {
                    scrollVertical.addClass("premium-image-scroll-ver");
                } else {
                    scrollElement.imagesLoaded(function () {
                        scrollOverlay.css({
                            width: imageScroll.width(),
                            height: imageScroll.height()
                        });
                    });
                }
            } else {
                if (reverse === "yes") {
                    scrollElement.imagesLoaded(function () {
                        scrollElement.addClass("premium-container-scroll-instant");
                        setTransform();
                        startTransform();
                    });
                }
                if (direction === "vertical") {
                    scrollVertical.removeClass("premium-image-scroll-ver");
                }
                scrollElement.mouseenter(function () {
                    scrollElement.removeClass("premium-container-scroll-instant");
                    setTransform();
                    reverse === "yes" ? endTransform() : startTransform();
                });
                scrollElement.mouseleave(function () {
                    reverse === "yes" ? startTransform() : endTransform();
                });
            }
        };


        /****** Premium Contact Form 7 Handler ******/
        var PremiumContactFormHandler = function ($scope, $) {

            var $contactForm = $scope.find(".premium-cf7-container"),
                $input = $contactForm.find(
                    'input[type="text"], input[type="email"], textarea, input[type="password"], input[type="date"], input[type="number"], input[type="tel"], input[type="file"], input[type="url"]'
                );

            $input.wrap("<span class='wpcf7-span'>");

            $contactForm.find('.wpcf7-submit').closest('p').addClass('premium-cf-submit');

            $input.on("focus blur", function () {
                $(this).closest(".wpcf7-span").toggleClass("is-focused");
            });

            if ($scope.hasClass('premium-cf-anim-label') || $scope.hasClass('premium-cf-anim-css-filters') || $scope.hasClass('premium-cf-anim-label-pos-back')) {
                $contactForm.find('p').each(function (index, elem) {

                    if ($(elem).find('input[type!="radio"], textarea').length > 0) {
                        $(elem).find('label').addClass('cf7-text-input-label');

                        $(elem).find('input, textarea').on("focus", function () {
                            $(elem).addClass('input-focused');
                        });

                        $(elem).find('input, textarea').on("blur", function () {
                            if ('' == $(this).val())
                                $(elem).removeClass('input-focused');
                        });
                    }

                })
            }

            if ($scope.hasClass('premium-cf-anim-label-letter')) {

                $contactForm.find('p').each(function (index, elem) {

                    $(elem).find('input, textarea').on("focus", function () {
                        var letterSpacing = parseFloat($(elem).find('label').css('letter-spacing').replace('px', ''));

                        $(elem).find('label').css('letter-spacing', (letterSpacing + 3) + 'px');
                    });

                    $(elem).find('input, textarea').on("blur", function () {
                        var letterSpacing = parseFloat($(elem).find('label').css('letter-spacing').replace('px', ''));

                        $(elem).find('label').css('letter-spacing', (letterSpacing - 3) + 'px');
                    });

                });

            }

        };

        /****** Premium Team Members Handler ******/
        var PremiumTeamMembersHandler = ModuleHandler.extend({

            getDefaultSettings: function () {

                return {
                    slick: {
                        infinite: true,
                        rows: 0,
                        prevArrow: '<a type="button" data-role="none" class="carousel-arrow carousel-prev" aria-label="Previous" role="button" style=""><i class="fas fa-angle-left" aria-hidden="true"></i></a>',
                        nextArrow: '<a type="button" data-role="none" class="carousel-arrow carousel-next" aria-label="Next" role="button" style=""><i class="fas fa-angle-right" aria-hidden="true"></i></a>',
                        draggable: true,
                        pauseOnHover: true,
                    },
                    selectors: {
                        multiplePersons: '.multiple-persons',
                        person: '.premium-person-container',
                        personCarousel: '.premium-person-container.slick-active',
                        personImg: '.premium-person-image-container img',

                    }
                }
            },

            getDefaultElements: function () {

                var selectors = this.getSettings('selectors');

                return {
                    $multiplePersons: this.$element.find(selectors.multiplePersons),
                    $persons: this.$element.find(selectors.person),
                    $personImg: this.$element.find(selectors.personImg),
                }

            },
            bindEvents: function () {
                this.run();
            },
            getSlickSettings: function () {

                var settings = this.getElementSettings(),
                    rtl = this.elements.$multiplePersons.data("rtl"),
                    colsNumber = settings.persons_per_row,
                    colsTablet = settings.persons_per_row_tablet,
                    colsMobile = settings.persons_per_row_mobile;

                return Object.assign(this.getSettings('slick'), {

                    slidesToShow: parseInt(100 / colsNumber.substr(0, colsNumber.indexOf('%'))),
                    slidesToScroll: parseInt(100 / colsNumber.substr(0, colsNumber.indexOf('%'))),
                    responsive: [{
                        breakpoint: 1025,
                        settings: {
                            slidesToShow: parseInt(100 / colsTablet.substr(0, colsTablet.indexOf('%'))),
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: parseInt(100 / colsMobile.substr(0, colsMobile.indexOf('%'))),
                            slidesToScroll: 1
                        }
                    }
                    ],
                    autoplay: settings.carousel_play,
                    rtl: rtl ? true : false,
                    autoplaySpeed: settings.speed || 5000,

                });


            },

            runEqualHeight: function () {

                var $persons = this.elements.$persons,
                    $personImg = this.elements.$personImg;

                var selectors = this.getSettings('selectors'),
                    carousel = this.getElementSettings('carousel'),
                    heights = new Array();

                if (carousel) {
                    $persons = this.$element.find(selectors.personCarousel);
                }

                $persons.each(function (index, person) {
                    $(person).imagesLoaded(function () { }).done(function () {

                        var imageHeight = $(person).find(selectors.personImg).outerHeight();

                        heights.push(imageHeight);
                    });
                });

                $persons.imagesLoaded(function () { }).done(function () {
                    var maxHeight = Math.max.apply(null, heights);
                    $personImg.css("height", maxHeight + "px");
                });

            },

            run: function () {

                var $multiplePersons = this.elements.$multiplePersons,
                    _this = this;

                if (!$multiplePersons.length) return;

                if ("yes" === $multiplePersons.data("persons-equal")) {
                    this.runEqualHeight();
                }

                var carousel = this.getElementSettings('carousel');

                if (carousel)
                    $multiplePersons.slick(this.getSlickSettings());

            }

        });

        /****** Premium Title Handler ******/
        var PremiumTitleHandler = function ($scope, $) {

            var $titleContainer = $scope.find(".premium-title-container"),
                $titleElement = $titleContainer.find('.premium-title-text');

            if ($titleContainer.hasClass('style9')) {
                var $style9 = $scope.find(".premium-title-style9");

                $style9.each(function () {
                    var elm = $(this);
                    var holdTime = elm.attr('data-blur-delay') * 1000;
                    elm.attr('data-animation-blur', 'process')
                    elm.find('.premium-title-style9-letter').each(function (index, letter) {
                        index += 1;
                        var animateDelay;
                        if ($('body').hasClass('rtl')) {
                            animateDelay = 0.2 / index + 's';
                        } else {
                            animateDelay = index / 20 + 's';
                        }
                        $(letter).css({
                            '-webkit-animation-delay': animateDelay,
                            'animation-delay': animateDelay
                        });
                    })
                    setInterval(function () {
                        elm.attr('data-animation-blur', 'done')
                        setTimeout(function () {
                            elm.attr('data-animation-blur', 'process')
                        }, 150);
                    }, holdTime);
                });
            }


            if ($titleContainer.hasClass('style8')) {

                var holdTime = $titleElement.attr('data-shiny-delay') * 1000,
                    duration = $titleElement.attr('data-shiny-dur') * 1000;

                function shinyEffect() {
                    $titleElement.get(0).setAttribute('data-animation', 'shiny');
                    setTimeout(function () {
                        $titleElement.removeAttr('data-animation')
                    }, duration);
                }

                (function repeat() {
                    shinyEffect();
                    setTimeout(repeat, holdTime);
                })();
            }

        };

        /****** Premium Bullet List Handler ******/
        var PremiumBulletListHandler = ModuleHandler.extend({

            getDefaultSettings: function () {

                return {
                    selectors: {
                        listItems: '.premium-bullet-list-box',
                        items: '.premium-bullet-list-content',
                    }
                }

            },

            getDefaultElements: function () {

                var selectors = this.getSettings('selectors'),
                    elements = {
                        $listItems: this.$element.find(selectors.listItems),
                        $items: this.$element.find(selectors.items)
                    };

                return elements;
            },

            bindEvents: function () {
                this.run();

                this.addRandomBadges();

                var self = this;
                if (!this.$element.is(':visible') && this.$element.closest('.premium-mega-nav-item').length > 0)
                    this.$element.closest('.premium-mega-nav-item').find('.premium-menu-link').on('click', function () {
                        self.addRandomBadges();
                    });

                $(window).on('resize', self.handleAlignment);

            },

            run: function () {

                this.handleAlignment();

                var $listItems = this.elements.$listItems,
                    $items = this.elements.$items,
                    $scope = this.$element;

                var devices = ['widescreen', 'desktop', 'laptop', 'tablet', 'tablet_extra', 'mobile', 'mobile_extra'].filter(function (ele) { return ele != elementorFrontend.getCurrentDeviceMode(); });

                devices.map(function (device) {
                    device = ('desktop' !== device) ? device + '-' : '';
                    $scope.removeClass(function (index, selector) {
                        return (selector.match(new RegExp("(^|\\s)premium-" + device + "type\\S+", 'g')) || []).join(' ');
                    });
                });

                var typeRow = $scope.filter('*[class*="type-row"]');

                if (typeRow.length > 0) {
                    $items.addClass('premium-bullet-list-content-inline');
                }

                var eleObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {

                            var element = $(entry.target),
                                delay = element.data('delay');

                            setTimeout(function () {
                                element.next('.premium-bullet-list-divider , .premium-bullet-list-divider-inline').css("opacity", "1");
                                element.next('.premium-bullet-list-divider-inline , .premium-bullet-list-divider').addClass("animated " + $listItems.data("list-animation"));

                                element.css("opacity", "1").addClass("animated " + $listItems.data("list-animation"));
                            }, delay);

                            eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                        }
                    });
                });

                $items.each(function (index, item) {

                    if ($listItems.data("list-animation") && " " != $listItems.data("list-animation")) {

                        eleObserver.observe($(item)[0]); // we need to apply this on each item

                    }

                });
            },

            handleAlignment: function () {

                var $element = this.$element,
                    computedStyle = getComputedStyle($element[0]),
                    listAlignment = computedStyle.getPropertyValue('--pa-bullet-align');

                $element.addClass('premium-bullet-list-' + listAlignment);

            },

            addRandomBadges: function () {

                var settings = this.getElementSettings();

                if (settings.rbadges_repeater.length < 1)
                    return;

                var $currentList = $('.elementor-element-' + this.$element.data('id'));

                if (!$currentList.is(':visible') || this.$element.hasClass('randomb-applied'))
                    return;

                var randomBadges = settings.rbadges_repeater;

                randomBadges.forEach(function (badge, index) {

                    if ('' != badge.rbadge_selector) {

                        var notBadgedItems = $(badge.rbadge_selector).find('.premium-bullet-list-text').filter(':not(:has(+ .premium-bullet-list-badge))');

                        var badgeText = '<div class="premium-bullet-list-badge elementor-repeater-item-' + badge._id + '"><span>' + badge.badge_title + '</span></div>';

                        var numOfApplies = Math.floor(Math.random() * (badge.rbadge_max - badge.rbadge_min + 1)) + badge.rbadge_min;

                        // Get a random number of elements from the list.
                        for (var i = 0; i < numOfApplies; i++) {

                            // notBadgedItems = $(badge.rbadge_selector).find('.premium-bullet-list-text').filter(':not(:has(+ .premium-bullet-list-badge))');

                            var randomIndex = Math.floor(Math.random() * notBadgedItems.length),
                                wasBadgedBefore = $(notBadgedItems[randomIndex]).siblings('.premium-bullet-list-badge').length > 0;


                            if (!wasBadgedBefore) {
                                $(notBadgedItems[randomIndex]).after(badgeText);
                            }


                        }

                    }
                })

                this.$element.addClass('randomb-applied');
            }

        });

        /****** Premium Grow Effect Handler ******/
        var PremiumButtonHandler = function ($scope, $) {

            var $btnGrow = $scope.find('.premium-button-style6-bg');

            if ($btnGrow.length !== 0 && $scope.hasClass('premium-mouse-detect-yes')) {
                $scope.on('mouseenter mouseleave', '.premium-button-style6', function (e) {

                    var parentOffset = $(this).offset(),
                        left = e.pageX - parentOffset.left,
                        top = e.pageY - parentOffset.top;

                    $btnGrow.css({
                        top: top,
                        left: left,
                    });

                });
            }

        };

        var PremiumMaskHandler = function ($scope, $) {

            var txtShowcaseElem = $scope.find('.pa-txt-sc__effect-min-mask .pa-txt-sc__main-item.pa-txt-sc__item-text'),
                mask = $scope.hasClass('premium-mask-yes') || txtShowcaseElem.length;

            if (!mask) return;

            if ('premium-addon-title.default' === $scope.data('widget_type')) {
                var target = '.premium-title-header';
                $scope.find(target).find('.premium-title-icon, .premium-title-img').addClass('premium-mask-span');

            } else if ('premium-textual-showcase.default' === $scope.data('widget_type')) {
                var target = '.pa-txt-sc__effect-min-mask';

            } else {
                var target = '.premium-dual-header-first-header';
            }

            $scope.find(target).find('span:not(.premium-title-style7-stripe-wrap):not(.premium-title-img):not(.pa-txt-sc__hov-item)').each(function (index, span) {
                var html = '';

                $(this).text().split(' ').forEach(function (item) {
                    if ('' !== item) {
                        html += ' <span class="premium-mask-span">' + item + '</span>';
                    }
                });

                $(this).text('').append(html);
            });

            // unsing IntersectionObserverAPI.
            var eleObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {

                        if (txtShowcaseElem.length) {

                            $(txtShowcaseElem).addClass('premium-mask-active');

                        } else {
                            $($scope).addClass('premium-mask-active');
                        }

                        eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                    }
                });
            });

            eleObserver.observe($scope[0]);
        };

        var PremiumSVGDrawerHandler = ModuleHandler.extend({

            bindEvents: function () {
                this.run();
            },

            run: function () {

                gsap.registerPlugin(ScrollTrigger);

                var $scope = this.$element;

                $scope.find(".elementor-invisible").removeClass("elementor-invisible");

                //remove title HTML tag
                $scope.find("title").remove();

                if (!$scope.hasClass("premium-svg-animated-yes"))
                    return;

                var elemID = $scope.data("id"),
                    settings = this.getElementSettings(),
                    scrollAction = settings.scroll_action,
                    scrollTrigger = null;


                if ('automatic' === scrollAction) {

                    scrollTrigger = 'custom' !== settings.animate_trigger ? settings.animate_trigger : settings.animate_offset.size + "%";

                    var animRev = settings.anim_rev ? 'pause play reverse' : 'none',
                        timeLine = new TimelineMax({
                            repeat: settings.loop ? -1 : 0,
                            yoyo: settings.yoyo ? true : false,
                            scrollTrigger: {
                                trigger: '.elementor-element-' + elemID,
                                toggleActions: "play " + animRev,
                                start: "top " + scrollTrigger, //when the top of the element hits that offset of the viewport.
                            }
                        });


                } else {

                    var timeLine = new TimelineMax({
                        repeat: ('hover' === scrollAction && settings.loop) ? -1 : 0,
                        yoyo: ('hover' === scrollAction && settings.yoyo) ? true : false,
                    });

                    if ('viewport' === scrollAction)
                        scrollTrigger = settings.animate_offset.size / 100;
                }

                var fromOrTo = !$scope.hasClass("premium-svg-anim-rev-yes") ? 'from' : 'to',
                    $paths = $scope.find("path, circle, rect, square, ellipse, polyline, polygon, line"),
                    lastPathIndex = 0,
                    startOrEndPoint = 'from' === fromOrTo ? settings.animate_start_point.size : settings.animate_end_point.size;

                $paths.each(function (pathIndex, path) {

                    var $path = $(path);

                    $path.attr("fill", "transparent");

                    if ($scope.hasClass("premium-svg-sync-yes"))
                        pathIndex = 0;

                    lastPathIndex = pathIndex;

                    timeLine[fromOrTo]($path, 1, {
                        PaSvgDrawer: (startOrEndPoint || 0) + "% 0",
                    }, pathIndex);

                });

                if ('yes' === settings.svg_fill) {
                    if (lastPathIndex == 0)
                        lastPathIndex = 1;

                    timeLine.to($paths, 1, {
                        fill: settings.svg_color,
                        stroke: settings.svg_stroke
                    }, lastPathIndex);
                }

                if ('viewport' === scrollAction) {

                    var controller = new ScrollMagic.Controller(),
                        scene = new ScrollMagic.Scene({
                            triggerElement: '.elementor-element-' + elemID,
                            triggerHook: scrollTrigger,
                            duration: settings.draw_speed ? settings.draw_speed.size * 1000 : "150%"
                        })

                    scene.setTween(timeLine).addTo(controller);

                } else {

                    if (settings.frames)
                        timeLine.duration(settings.frames);

                    if ('hover' === scrollAction) {
                        timeLine.pause();

                        $scope.find("svg").hover(
                            function () {
                                timeLine.play();
                            },
                            function () {
                                timeLine.pause();
                            });
                    }

                }

            }


        });

        var PremiumTermsCloud = ModuleHandler.extend({

            getDefaultSettings: function () {

                return {
                    selectors: {
                        container: '.premium-tcloud-container',
                        canvas: '.premium-tcloud-canvas',
                        termWrap: '.premium-tcloud-term',
                    },
                }

            },

            getDefaultElements: function () {

                var selectors = this.getSettings('selectors');

                return {
                    $container: this.$element.find(selectors.container),
                    $canvas: this.$element.find(selectors.canvas),
                    $termWrap: this.$element.find(selectors.termWrap),
                }

            },

            bindEvents: function () {
                this.run();
            },

            run: function () {

                var widgetSettings = this.getElementSettings(),
                    $container = this.elements.$container,
                    _this = this,
                    $canvas = this.elements.$canvas;



                if (['shape', 'sphere'].includes(widgetSettings.words_order)) {

                    var computedStyle = getComputedStyle($canvas[0]);

                    $canvas.attr({
                        width: computedStyle.getPropertyValue('--pa-tcloud-width'),
                        height: computedStyle.getPropertyValue('--pa-tcloud-height')
                    });
                }

                setTimeout(function () {

                    if ('shape' === widgetSettings.words_order) {
                        // unsing IntersectionObserverAPI.
                        var eleObserver = new IntersectionObserver(function (entries) {
                            entries.forEach(function (entry) {
                                if (entry.isIntersecting) {
                                    _this.renderWordCloud();
                                    eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                                }
                            });
                        });

                        eleObserver.observe($canvas[0]);

                    } else if ('sphere' === widgetSettings.words_order) {

                        _this.renderWordSphere();

                    } else {
                        _this.handleTermsGrid();
                    }

                    $container.removeClass('premium-tcloud-hidden');
                }, 500);

            },

            renderWordSphere: function () {

                var widgetID = this.getID(),
                    widgetSettings = this.getElementSettings(),
                    $termWrap = this.elements.$termWrap,
                    _this = this;

                var colorScheme = widgetSettings.colors_select;

                if ('custom' === colorScheme && widgetSettings.words_colors) {
                    var colors = widgetSettings.words_colors.split("\n");
                }

                $termWrap.map(function (index, term) {

                    var generatedColor = null;


                    if ('custom' !== colorScheme) {

                        generatedColor = _this.genRandomColor(colorScheme);

                    } else if (widgetSettings.words_colors) {
                        generatedColor = Math.floor(Math.random() * colors.length);

                        generatedColor = colors[generatedColor];
                    }

                    if (generatedColor) {
                        $(term).find('.premium-tcloud-term-link').css(('background' === widgetSettings.colors_target ? 'background-' : '') + 'color', generatedColor);
                    }


                })

                setTimeout(function () {

                    $('#premium-tcloud-canvas-' + widgetID).tagcanvas({

                        decel: 'yes' === widgetSettings.stop_onDrag ? 0.95 : 1,

                        overlap: false,
                        textColour: null,

                        weight: 'yes' === widgetSettings.sphere_weight,
                        weightFrom: 'data-weight',
                        weightSizeMin: 'yes' === widgetSettings.sphere_weight ? widgetSettings.weight_min.size : 10,
                        weightSizeMax: 'yes' === widgetSettings.sphere_weight ? widgetSettings.weight_max.size : 20,

                        textHeight: widgetSettings.text_height || 15,
                        textFont: widgetSettings.font_family,
                        textWeight: widgetSettings.font_weight,

                        wheelZoom: 'yes' === widgetSettings.wheel_zoom,
                        reverse: 'yes' === widgetSettings.reverse,
                        dragControl: 'yes' === widgetSettings.drag_control,
                        initial: [widgetSettings.start_xspeed.size, widgetSettings.start_yspeed.size],

                        bgColour: 'tag',

                        padding: 'background' === widgetSettings.colors_target ? widgetSettings.sphere_term_padding.size : 0,
                        bgRadius: 'background' === widgetSettings.colors_target ? widgetSettings.sphere_term_radius.size : 0,

                        outlineColour: 'rgba(2,2,2,0)',
                        maxSpeed: 0.03,
                        depth: 0.75

                    }, 'premium-tcloud-terms-container-' + widgetID);
                }, 100);


            },

            handleTermsGrid: function () {

                var widgetSettings = this.getElementSettings(),
                    $termWrap = this.elements.$termWrap,
                    _this = this;

                var colorScheme = widgetSettings.colors_select;

                if ('custom' === colorScheme && widgetSettings.words_colors) {
                    var colors = widgetSettings.words_colors.split("\n");
                }

                $termWrap.map(function (index, term) {

                    var generatedColor = null,
                        fontSize = $(term).find('.premium-tcloud-term-link').css('font-size').replace('px', '');

                    if (widgetSettings.fsize_scale.size > 0)
                        fontSize = parseFloat(fontSize) + ($(term).find('.premium-tcloud-term-link').data('weight') * widgetSettings.fsize_scale.size);

                    if ('custom' !== colorScheme) {
                        generatedColor = _this.genRandomColor(colorScheme, 'grid');

                        var opacities = {
                            original: 'random-light' === colorScheme ? '0.15)' : '80%)',
                            replaced: 'random-light' === colorScheme ? '0.3)' : '100%)'
                        };

                        $(term).get(0).style.setProperty("--tag-hover-color", generatedColor.replace(opacities.original, opacities.replaced));
                        $(term).get(0).style.setProperty("--tag-text-color", 'random-dark' === colorScheme ? '#fff' : generatedColor.replace('42%,0.15)', '35%,100%)'));

                    } else if (widgetSettings.words_colors) {

                        generatedColor = Math.floor(Math.random() * colors.length);

                        generatedColor = colors[generatedColor];

                        $(term).get(0).style.setProperty("--tag-hover-color", generatedColor);

                    }

                    $(term).get(0).style.setProperty("--tag-color", generatedColor);

                    if (widgetSettings.fsize_scale.size > 0)
                        $(term).find('.premium-tcloud-term-link').css('font-size', Math.ceil(fontSize) + 'px');


                    if ('ribbon' === widgetSettings.words_order) {
                        $(term).get(0).style.setProperty("--tag-ribbon-size", (Math.ceil($(term).outerHeight(false)) / 2) + 'px');
                    }


                })



            },

            renderWordCloud: function () {

                var widgetID = this.getID(),
                    widgetSettings = this.getElementSettings(),
                    $container = this.elements.$container,
                    settings = $container.data("chart");

                var wordsArr = settings.wordsArr,
                    colors = [],
                    rotationRatio = rotationSteps = null,
                    minRot = -90 * (Math.PI / 180),
                    maxRot = 90 * (Math.PI / 180);


                switch (widgetSettings.rotation_select) {

                    case 'horizontal':
                        rotationRatio = 0;
                        rotationSteps = 0;
                        break;

                    case 'vertical':
                        rotationRatio = 1;
                        rotationSteps = 2;
                        break;

                    case 'hv':
                        rotationRatio = 0.5;
                        rotationSteps = 2;
                        break;

                    case 'custom':
                        rotationRatio = widgetSettings.rotation.size || 0.3;

                        minRot = widgetSettings.degrees.size * (Math.PI / 180) || 45;
                        maxRot = widgetSettings.degrees.size * (Math.PI / 180) || 45;

                        break;

                    case 'random':
                        rotationRatio = Math.random();
                        rotationSteps = 0;

                        break;

                    default:
                        rotationRatio = 0.3;
                        break;
                }


                if ('custom' === widgetSettings.colors_select) {
                    colors = widgetSettings.words_colors.split("\n");
                }

                WordCloud(document.getElementById('premium-tcloud-canvas-' + widgetID),
                    {

                        backgroundColor: 'rgba(0, 0, 0, 0)',
                        shuffle: false,

                        list: wordsArr,
                        shape: widgetSettings.shape,
                        color: widgetSettings.colors_select,
                        wordsColors: colors,

                        wait: (widgetSettings.interval.size * 1000) || 0,

                        gridSize: widgetSettings.grid_size.size || 8,

                        weightFactor: widgetSettings.weight_scale || 5,

                        minRotation: minRot,
                        maxRotation: maxRot,

                        rotateRatio: rotationRatio,
                        rotationSteps: rotationSteps,

                        fontFamily: widgetSettings.font_family || 'Arial',
                        fontWeight: widgetSettings.font_weight,

                        click: function (item) {

                            if (!elementorFrontend.isEditMode()) {
                                var link = item[2];

                                window.open(link, 'yes' === widgetSettings.new_tab ? '_blank' : '_top');
                            }
                        }

                        // minSize: 10
                        // rotationSteps: 90
                    }
                );



            },

            genRandomColor: function (scheme, shape) {

                var min = 50,
                    max = 90;

                if ('random-dark' === scheme) {
                    min = 10;
                    max = 50;
                }

                var lightandOpacity = (Math.random() * (max - min) + min).toFixed() + '%, 100%';
                if (shape) {
                    lightandOpacity = '42%,' + ('random-dark' === scheme ? '80%' : '0.15');
                }


                return 'hsla(' +
                    (Math.random() * 360).toFixed() + ',' +
                    '100%,' +
                    lightandOpacity + ')';

            },

            genRandomRotate: function () {

                return Math.floor(Math.random() * 361);

            },


        });

        var PremiumWorldClockHandler = function ($scope, $) {

            var _ = luxon,
                id = $scope.data('id'),
                settings = $scope.find('.premium-world-clock__clock-wrapper').data('settings'),
                isParentHotspot = $scope.closest('#tooltip_content').length > 0,
                analogclocks = ['skin-1', 'skin-5', 'skin-6', 'skin-7'],
                inc = isParentHotspot ? 300 : 1000;

            if (!settings)
                return;

            window['clockInterval-' + id];

            if (window['clockInterval-' + id]) {
                clearInterval(window['clockInterval-' + id]);
            }

            if (analogclocks.includes(settings.skin) && settings.showClockNum) {
                window['clockNumbers-' + id] = false;
                drawClockNumbers($scope);
            }

            window['clockInterval-' + id] = setInterval(clockInit, inc, settings, $scope, id);

            function clockInit(settings, $scope, id) {

                var isInHotspots = $('.elementor-element-' + id).closest('.premium-tooltipster-base').length > 0;

                if (isInHotspots) {
                    $scope = $('.elementor-element-' + id);

                    settings = $scope.find('.premium-world-clock__clock-wrapper').data('settings');

                    if (!window['clockNumbers-' + id] && analogclocks.includes(settings.skin) && settings.showClockNum) {
                        drawClockNumbers($scope);
                        window['clockNumbers-' + id] = true;
                    }
                }

                var time = getTimeComponents(settings);

                if (!time) {
                    var htmlNotice = '<div class="premium-error-notice">This Is An Invalid Timezone Name. Please Enter a Valid Timezone Name</div>';
                    $scope.find('.premium-world-clock__clock-wrapper').html(htmlNotice);
                }

                if (['skin-1', 'skin-5', 'skin-6', 'skin-7'].includes(settings.skin)) {

                    $scope.find('.premium-world-clock__hours').css('transform', 'rotate(' + ((time.hours * 30) + (time.minutes * 6 / 12)) + 'deg)').text('');
                    $scope.find('.premium-world-clock__minutes').css('transform', 'rotate(' + time.minutes * 6 + 'deg)').text('');
                    $scope.find('.premium-world-clock__seconds').css('transform', 'rotate(' + time.seconds * 6 + 'deg)').text('');

                    if (settings.showMeridiem) {
                        $scope.find('.premium-world-clock__meridiem').text(time.meridiem);
                    }

                } else {

                    $scope.find('.premium-world-clock__hours').text(time.hours);
                    $scope.find('.premium-world-clock__minutes').text(time.minutes);

                    if (settings.showSeconds) {
                        $scope.find('.premium-world-clock__seconds').text(time.seconds);
                    }

                    if (settings.showMeridiem) {
                        var type = settings.meridiemType;

                        if ('text' === type) {

                            $scope.find('.premium-world-clock__meridiem').text(time.meridiem);

                        } else {
                            var meridiemIcons = {
                                'AM': '<svg id="Weather_Icons" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><defs><style>.cls-1{fill:#333;}</style></defs><g id="Clear_Sky"><circle class="cls-1" cx="12" cy="12" r="5.5"/><path class="cls-1" d="m21.76,12.74h-1.95c-.98,0-.98-1.47,0-1.47h1.95c.98,0,.98,1.47,0,1.47Z"/><path class="cls-1" d="m19.39,5.62l-1.38,1.38c-.29.29-.75.29-1.04,0-.29-.29-.29-.75,0-1.04l1.38-1.38c.29-.28.75-.28,1.04,0,.28.29.28.75,0,1.04Z"/><path class="cls-1" d="m12.74,2.24v1.95c0,.4-.33.73-.73.73s-.74-.33-.74-.73v-1.95c0-.41.33-.74.74-.74s.73.33.73.74Z"/><path class="cls-1" d="m5.96,7.03l-1.38-1.38c-.32-.31-.29-.75,0-1.04s.72-.31,1.03,0l1.38,1.38c.69.69-.34,1.73-1.03,1.04Z"/><path class="cls-1" d="m4.19,12.74h-1.95c-.98,0-.98-1.47,0-1.47h1.95c.98,0,.98,1.47,0,1.47Z"/><path class="cls-1" d="m7.02,18.04l-1.38,1.38c-.31.31-.75.29-1.04,0s-.31-.72,0-1.03l1.38-1.38c.32-.31.75-.29,1.04,0,.29.28.31.72,0,1.03Z"/><path class="cls-1" d="m12.74,19.82v1.95c0,.98-1.47.98-1.47,0v-1.95c0-.98,1.47-.98,1.47,0Z"/><path class="cls-1" d="m19.43,19.4c-.29.28-.73.31-1.04,0l-1.38-1.39c-.31-.31-.29-.75,0-1.03.28-.29.72-.31,1.03,0l1.39,1.38c.31.31.28.75,0,1.04Z"/></g></svg>',
                                'PM': '<svg id="Weather_Icons" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><defs><style>.cls-1{fill:#333;}</style></defs><path id="Moon" class="cls-1" d="m21.93,17.23c-1.89,3.24-5.4,5.27-9.26,5.27-5.89,0-10.67-4.7-10.67-10.51S6.37,1.87,11.95,1.5c.4-.02.67.41.46.76-.83,1.42-1.28,3.04-1.28,4.73,0,5.25,4.33,9.51,9.68,9.51.22,0,.44,0,.65-.02.4-.03.67.4.47.75Z"/></svg>'
                            },
                                currentIcon = (6 <= time.hoursNotPadded && time.hoursNotPadded <= 17) ? meridiemIcons['AM'] : meridiemIcons['PM'];

                            $scope.find('.premium-world-clock__meridiem').html(currentIcon);
                        }
                    }

                    if (settings.equalWidth) {
                        equalWidth();
                    }
                }

                if (settings.date) {

                    if ('skin-3' === settings.skin) {

                        var daysNum = settings.daysNum,
                            currentDay = time.date.d.toLowerCase(),
                            $daysWrapper = $scope.find('.premium-world-clock__days-wrapper'),
                            daysMap = {
                                0: 'mon',
                                1: 'tue',
                                2: 'wed',
                                3: 'thu',
                                4: 'fri',
                                5: 'sat',
                                6: 'sun',
                            },
                            currentDayOrder = parseInt(time.date.order) - 1;

                        $daysWrapper.html('<span class="premium-world-clock__day-name current-day">' + currentDay + '</span>');

                        for (var i = 1; i <= parseInt(daysNum); i++) {

                            var dayBefore = currentDayOrder - i,
                                dayAfter = currentDayOrder + i;

                            if (dayBefore < 0) {
                                dayBefore += 7;
                            }

                            if (dayAfter > 6) {
                                dayAfter -= 7;
                            }

                            $daysWrapper.prepend('<span class="premium-world-clock__day-name">' + daysMap[dayBefore] + '</span>');
                            $daysWrapper.append('<span class="premium-world-clock__day-name">' + daysMap[dayAfter] + '</span>');
                        }

                        $scope.find('.premium-world-clock__month').text(time.date.m);
                        $scope.find('.premium-world-clock__day').text(time.date.dn);

                    } else if ('skin-4' === settings.skin) {

                        $scope.find('.premium-world-clock__date-wrapper').html('');

                        settings.dateFormat.forEach(function (format) {
                            if (time.date[format] !== undefined && time.date[format] !== null) {
                                var html = '<span class="premium-world-clock__date-segment">' + time.date[format] + '</span>';
                                $scope.find('.premium-world-clock__date-wrapper').append(html);
                            }
                        });

                    } else {
                        $scope.find('.premium-world-clock__date').text(time.date);
                    }
                }

                if (settings.gmtOffset) {
                    var offset = 'Z' === settings.offsetFormat ? time.offset + 'HRS' : time.offset;
                    $scope.find('.premium-world-clock__gmt-offset').text(offset);
                }

                $scope.find('.premium-addons__v-hidden').removeClass('premium-addons__v-hidden');
            }

            function getTimeComponents(settings) {

                var skin = settings.skin,
                    showDate = settings.date,
                    showGmtOffset = settings.gmtOffset,
                    time = {
                        hours: '',
                        minutes: '',
                        seconds: '',
                        meridiem: '',
                        date: '',
                    },
                    dateTime = _.DateTime.local().setLocale(settings.language || 'en').setZone(settings.timezone);

                if (!dateTime.isValid) {
                    return false;
                }

                time.hours = dateTime.toFormat(settings.format);
                time.minutes = dateTime.toFormat('mm');
                time.seconds = dateTime.toFormat('ss');

                if (showDate) {
                    if ('skin-3' === skin || 'skin-4' === skin) {
                        time.date = {
                            d: dateTime.toFormat('ccc'),
                            dn: dateTime.toFormat('dd'),
                            m: dateTime.toFormat('LLL'),
                            order: dateTime.toFormat('c')
                        };

                    } else {
                        time.date = dateTime.toFormat(settings.dateFormat);
                    }
                }

                if (showGmtOffset) {
                    time.offset = dateTime.toFormat(settings.offsetFormat);
                }

                if (settings.showMeridiem) {
                    time.meridiem = dateTime.toFormat('a');
                    time.hoursNotPadded = parseInt(dateTime.toFormat('H'));
                }

                return time;
            }

            function equalWidth(skin) {
                var width = 0,
                    selector = 'skin-3' === skin ? '.premium-world-clock__hand:not(.premium-world-clock__seconds)' : '.premium-world-clock__hand';

                $scope.find(selector).each(function (index, slot) {
                    if (width < $(slot).outerWidth()) {
                        width = $(slot).outerWidth();
                    }
                });

                $scope.find(selector).css('min-width', width);
            }

            function drawClockNumbers($scope) {

                var $clockNumbers = $scope.find('.premium-world-clock__clock-number');

                for (var i = 0; i < 12; i++) {
                    var point = getCirclePoint(50, i * 30, 50, 50);

                    $($clockNumbers[i]).css('left', point.x + '%');
                    $($clockNumbers[i]).css('top', point.y + '%');
                }
            }

            function getCirclePoint(r, degrees, cx, cy) {
                var angleInRadians = degrees * (Math.PI / 180);
                var xp = cx + r * Math.cos(angleInRadians);
                var yp = cy + r * Math.sin(angleInRadians);

                return {
                    x: xp,
                    y: yp
                };
            }
        };

        var PremiumPostTickerHandler = function ($scope, $) {

            var timer = null,
                $postsWrapper = $scope.find('.premium-post-ticker__posts-wrapper'),
                settings = $scope.find('.premium-post-ticker__outer-wrapper').data('ticker-settings');

            if (!settings)
                return;

            if ('' !== settings.animation && 'layout-4' !== settings.layout) {
                $postsWrapper.on("init", function (event) {
                    resetAnimations("init");
                });
            }

            if (settings.typing) {
                $postsWrapper.on('init', function (event, slick) {
                    var $currentTyping = $postsWrapper.find('[data-slick-index="' + slick.currentSlide + '"] .premium-post-ticker__post-title a');

                    typeTitle($currentTyping);
                });

                $postsWrapper.on('beforeChange', function (event, slick, currentSlide, nextSlide) {

                    var $typedItem = $postsWrapper.find('[data-slick-index="' + currentSlide + '"] .premium-post-ticker__post-title'),
                        $currentTyping = $postsWrapper.find('[data-slick-index="' + currentSlide + '"] .premium-post-ticker__post-title a'),
                        $nextTyping = $postsWrapper.find('[data-slick-index="' + nextSlide + '"] .premium-post-ticker__post-title a');

                    clearInterval(timer);
                    $typedItem.removeClass('premium-text-typing');
                    $currentTyping.text('');

                    typeTitle($nextTyping);
                });
            }

            if ($postsWrapper.find('.premium-post-ticker__post-wrapper').length <= 6) {

                $postsWrapper.find('.premium-post-ticker__post-wrapper').map(function (index, post) {
                    $postsWrapper.append($(post).clone());
                })

            }

            $postsWrapper.slick(getSlickSettings());

            if ('' !== settings.animation && 'layout-4' !== settings.layout) {

                $postsWrapper.on("beforeChange", function () {
                    resetAnimations();
                });

                $postsWrapper.on("afterChange", function () {
                    triggerAnimation();
                });
            }

            if (settings.arrows) {

                $scope.find('.premium-post-ticker__arrows a').on('click.paTickerNav', function () {

                    if ($(this).hasClass('prev-arrow')) {

                        $postsWrapper.slick('slickPrev');

                    } else if ($(this).hasClass('next-arrow')) {

                        $postsWrapper.slick('slickNext');

                    }
                });
            }

            $scope.find('.premium-post-ticker__outer-wrapper').removeClass('premium-post-ticker__hidden');

            function getSlickSettings() {

                $postsWrapper.off('mouseenter.paTickerPause');

                var closestTab = $scope.closest('.premium-tabs-content-section'),
                    autoPlay = settings.autoPlay;

                //If there is a parent tab and it's not active, then autoplay should not be true.
                if (closestTab.length > 0) {
                    if (!closestTab.hasClass('content-current'))
                        autoPlay = false;
                }

                var slickSetting = {
                    infinite: true,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: false,
                    autoplay: autoPlay,
                    rows: 0,
                    speed: settings.speed,
                    fade: settings.fade,
                    draggable: true,
                    pauseOnHover: settings.pauseOnHover,
                    vertical: settings.vertical
                };

                if (settings.autoPlay) {
                    slickSetting.autoplaySpeed = settings.autoplaySpeed;
                }

                if (settings.infinite) {
                    slickSetting.autoplaySpeed = 0;
                    slickSetting.cssEase = 'linear';

                    slickSetting.useCSS = false;

                    if ('layout-4' !== settings.layout && !settings.vertical) {
                        slickSetting.variableWidth = true; // this only is required if the slider is horizontal
                    }
                }

                if ('layout-4' === settings.layout) {
                    slickSetting.vertical = true;
                    slickSetting.slidesToShow = settings.slidesToShow || 1;
                }

                if ($scope.hasClass('premium-reversed-yes') && 'layout-4' !== settings.layout && !settings.vertical && !settings.typing && !settings.fade) {

                    slickSetting.rtl = true;
                }

                return slickSetting;
            }

            function resetAnimations() {

                var $slides = $postsWrapper.find(".slick-slide").not(".slick-current");

                $slides.each(function (index, elem) {
                    $(elem).removeClass("animated " + settings.animation).addClass("elementor-invisible");
                });
            };

            function triggerAnimation() {

                $postsWrapper.find(".slick-active.elementor-invisible").each(function (index, elem) {

                    $(elem).removeClass("elementor-invisible").addClass(settings.animation + ' animated');

                });
            }

            function typeTitle($tickerItem) {

                if (!$tickerItem.length) {
                    return;
                }

                var typingCounter = 0,
                    $typedItem = $tickerItem.closest('.premium-post-ticker__post-title'),
                    typingText = $tickerItem.data('typing'),
                    typingTextLength = typingText.length;

                $typedItem.addClass('premium-text-typing');
                $tickerItem.text(typingText.substr(0, typingCounter++));

                timer = setInterval(function () {
                    if (typingCounter <= typingTextLength) {
                        $tickerItem.text(typingText.substr(0, typingCounter++));
                    } else {
                        clearInterval(timer);
                        $typedItem.removeClass('premium-text-typing'); // have the '_' after.
                    }
                }, 40);
            }
        };

        var PremiumWeatherHandler = function ($scope, $) {

            var id = $scope.data('id'),
                isInHotspots = $('.elementor-element-' + id).closest('.premium-tooltipster-base').length > 0;

            if (isInHotspots) {
                $scope = $('.elementor-element-' + id);
            }

            if (!elementorFrontend.isEditMode()) {
                $scope.find('.premium-weather__outer-wrapper').css({ visibility: 'visible', opacity: 1 });
            }

            var settings = $scope.find('.premium-weather__outer-wrapper').data('pa-weather-settings');

            if (!settings) {
                return;
            }

            var forecastHeight = $scope.find('.premium-weather__outer-wrapper').data('pa-height'),
                $forecastSlider = 'layout-2' === settings.layout ? $scope.find('.premium-weather__extra-outer-wrapper') : $scope.find('.premium-weather__hourly-forecast-wrapper'),
                forecastTabs = $scope.hasClass('premium-forecast-tabs-yes') ? true : false,
                dailyForecastCarousel = $scope.hasClass('premium-forecast-carousel-yes') ? true : false,
                dailyEqWidth = !forecastTabs && !dailyForecastCarousel & !$scope.hasClass('premium-daily-forecast__style-4') ? true : false;


            if ($forecastSlider.length) {
                $forecastSlider.addClass('premium-addons__v-hidden').slick(getSlickSettings(settings, settings.layout, false));
            }

            if (dailyForecastCarousel) {
                var dailyCarouselSettings = $scope.find('.premium-weather__outer-wrapper').data('pa-daily-settings');

                $scope.find('.premium-weather__forecast').slick(getSlickSettings(dailyCarouselSettings, '', true));
            }

            // append the arrows here.
            if ('layout-2' !== settings.layout && 'vertical' === settings.hourlyLayout) {
                var prevArrow = '<a type="button" data-role="none" class="carousel-arrow carousel-prev" aria-label="Previous" role="button" style=""><i class="fas fa-chevron-left" aria-hidden="true"></i></a>',
                    nextArrow = '<a type="button" data-role="none" class="carousel-arrow carousel-next" aria-label="Next" role="button" style=""><i class="fas fa-chevron-right" aria-hidden="true"></i></a>';

                $forecastSlider.append(prevArrow + nextArrow);

                $scope.find('a.carousel-arrow').on('click.paWeatherNav', function () {

                    if ($(this).hasClass('carousel-prev')) {
                        $forecastSlider.slick('slickPrev');
                    } else if ($(this).hasClass('carousel-next')) {
                        $forecastSlider.slick('slickNext');
                    }

                });
            }

            if ($forecastSlider.length) {
                $forecastSlider.removeClass('premium-addons__v-hidden');
            }

            $(window).trigger('resize');

            if (forecastTabs) {

                var $tabs_headers = $scope.find('.premium-weather__tab-header');

                $tabs_headers.on('click.paWeatherTabs', function () {
                    $scope.find('.current').removeClass('current');

                    $(this).addClass('current');
                    $scope.find($(this).data('content-id')).addClass('current');
                });
            }

            if ('' !== forecastHeight) {
                $scope.find('.premium-weather__forecast').slimScroll({
                    color: '#00000033',
                    height: forecastHeight,
                });

                $scope.find('.slimScrollDiv').css('overflow', '');
            }

            if (dailyEqWidth) {
                equalWidth($scope);
            }

            function getSlickSettings(settings, widgetLayout, dailyForecast) {

                var slickSetting = {
                    infinite: false,
                    arrows: true,
                    autoplay: false,
                    draggable: true,
                };

                if (!dailyForecast && 'layout-2' !== widgetLayout && 'vertical' === settings.hourlyLayout) {
                    slickSetting.slidesToShow = 1;
                    slickSetting.slidesToScroll = 1;
                    slickSetting.rows = settings.slidesToShow;
                    slickSetting.arrows = false;
                    slickSetting.responsive = [
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                rows: settings.slidesToShowTab || 1
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                rows: settings.slidesToShowMobile || 1
                            }
                        }
                    ];
                } else {

                    var prevArrow = '<a type="button" data-role="none" class="carousel-arrow carousel-prev" aria-label="Previous" role="button" style=""><i class="fas fa-chevron-left" aria-hidden="true"></i></a>',
                        nextArrow = '<a type="button" data-role="none" class="carousel-arrow carousel-next" aria-label="Next" role="button" style=""><i class="fas fa-chevron-right" aria-hidden="true"></i></a>';

                    slickSetting.slidesToScroll = settings.slidesToScroll || 1;
                    slickSetting.slidesToShow = settings.slidesToShow;
                    slickSetting.rows = 0;
                    slickSetting.nextArrow = nextArrow;
                    slickSetting.prevArrow = prevArrow;

                    slickSetting.responsive = [
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: settings.slidesToShowTab || 1,
                                slidesToScroll: settings.slidesToScrollTab || 1
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: settings.slidesToShowMobile || 1,
                                slidesToScroll: settings.slidesToScrollMobile || 1
                            }
                        }
                    ];
                }

                return slickSetting;
            }

            function equalWidth($scope) {
                var width = 0,
                    selector = '.premium-weather__forecast .premium-weather__forecast-item';

                $scope.find(selector).each(function (index, slot) {
                    if (width < $(slot).outerWidth()) {
                        width = $(slot).outerWidth();
                    }
                });

                $scope.find(selector).css('min-width', width);
            }
        };

        var PremiumTiktokHandler = function ($scope, $) {

            var widgetID = $scope.data('id'),
                $outerWrapper = $scope.find('.premium-tiktok-feed__outer-wrapper'),
                settings = $outerWrapper.data('pa-tiktok-settings');

            if (!settings) {
                return;
            }

            var $videosWrapper = $scope.find('.premium-tiktok-feed__videos-wrapper');

            if ("masonry" === settings.layout && !settings.carousel) {
                $videosWrapper.imagesLoaded(function () {
                    $videosWrapper.isotope(getIsoTopeSettings());
                });
            }

            if (settings.loadMore && !settings.carousel) {

                window['paLoadMoreBookmark' + widgetID] = $outerWrapper.data('pa-load-bookmark');
                window['paHiddenPins' + widgetID] = $scope.find('.premium-tiktok-feed__video-outer-wrapper.premium-display-none').length;

                $scope.find('.premium-tiktok-feed__load-more-btn').on('click.paLoadMoreVids', function (e) {

                    var bookmark = window['paLoadMoreBookmark' + widgetID],
                        count = settings.loadMoreCount;

                    for (var i = 0; i < count; i++) {
                        // $scope.find('.premium-tiktok-feed__video-outer-wrapper').eq( bookmark + i ).show().addClass('premium-pin-shown');
                        $scope.find('.premium-tiktok-feed__video-outer-wrapper').eq(bookmark + i).show(0, function () {
                            var _this = this;
                            setTimeout(function () {
                                $(_this).removeClass('premium-display-none'); // fix: share menu not fully displayed.
                            }, 400);
                        });
                    }

                    window['paLoadMoreBookmark' + widgetID] = bookmark + count;
                    window['paHiddenPins' + widgetID] -= count;

                    if (0 >= window['paHiddenPins' + widgetID]) {
                        $scope.find('.premium-tiktok-feed__load-more-btn').remove();
                    }
                });
            }

            if (settings.carousel) {

                var carouselSettings = $outerWrapper.data('pa-carousel');

                $videosWrapper.addClass('premium-addons__v-hidden').slick(getSlickSettings(carouselSettings));

                $videosWrapper.removeClass('premium-addons__v-hidden');
            }

            // share links
            $scope.find('.premium-copy-link').on('click.paTiktokCopyLink', function () {
                $scope.find('.premium-tiktok-share-menu').css('visibility', 'hidden')

                var txt = $(this).data('pa-link');
                navigator.clipboard.writeText(txt);
            });

            $scope.find('.premium-tiktok-share-item:not(.premium-copy-link)').on('click.paTiktokShare', function () {

                var link = $(this).data('pa-link');
                window.open(link, 'popup', 'width=600,height=600');
            });

            if ('lightbox' === settings.onClick) {
                initFeedPopupEvents($scope);
            } else if ('play' === settings.onClick) {

                if ($scope.hasClass('premium-tiktok-feed__vid-layout-2')) {
                    $scope.find('.premium-tiktok-feed__vid-meta-wrapper').on('click', function () {
                        $(this).next().trigger('click');
                    });
                }

                $scope.find('.premium-tiktok-feed__video-media').on('click', function () {
                    var $video = $(this).find('video');

                    $(this).find('.premium-tiktok-feed__play-icon').toggleClass('premium-addons__v-hidden');

                    if (!$video.hasClass('video-playing')) {
                        $video.get(0).play();
                    } else {
                        $video.get(0).pause();
                    }

                    $video.toggleClass('video-playing');

                });
            }

            if (settings.playOnHover) {

                $scope.find('.premium-tiktok-feed__video-media').hover(function () {

                    $scope.find('.premium-tiktok-feed__play-icon').removeClass('premium-addons__v-hidden');
                    $scope.find('video').get(0).pause();

                    $(this).find('.premium-tiktok-feed__play-icon').addClass('premium-addons__v-hidden');
                    $(this).find('video').get(0).play();

                }, function () {
                    $(this).find('.premium-tiktok-feed__play-icon').removeClass('premium-addons__v-hidden');
                    $(this).find('video').get(0).pause();
                })
            }


            function closeModal() {
                $('.premium-tiktok-feed-modal-iframe-modal').css('display', 'none');

                $(".premium-tiktok-feed-modal-iframe-modal #pa-tiktok-vid-control-iframe").attr({
                    'src': ''
                });
            }

            function initFeedPopupEvents($scope) {

                var isBanner = $scope.hasClass('premium-tiktok-feed__vid-layout-2');

                if (isBanner) {
                    $scope.find('.premium-tiktok-feed__vid-meta-wrapper').on('click.paTriggerModal', function () {
                        $scope.find('.premium-tiktok-feed__video-media').trigger('click');
                    });
                }

                $scope.find('.premium-tiktok-feed__video-media').on('click.paTiktokModal', function () {
                    var embedLink = $(this).data('pa-tiktok-embed'),
                        $modalContainer = $('.premium-tiktok-feed-modal-iframe-modal'),
                        paIframe = $modalContainer.find("#pa-tiktok-vid-control-iframe");

                    $modalContainer.css('display', 'flex');
                    paIframe.css("z-index", "-1");

                    paIframe.attr("src", 'https://www.tiktok.com/embed/v2/' + embedLink);
                    paIframe.show();
                    paIframe.css("z-index", "1");
                });

                // close model events.
                $('.eicon-close').on('click.paClosePopup', closeModal);

                $(document).on('click.paClosePopup', '.premium-tiktok-feed-modal-iframe-modal', function (e) {
                    if ($(e.target).closest(".premium-tiktok-feed__video-content").length < 1) {
                        closeModal();
                    }
                });
            }

            function getSlickSettings(settings) {

                var prevArrow = settings.arrows ? '<a type="button" data-role="none" class="carousel-arrow carousel-prev" aria-label="Previous" role="button" style=""><i class="fas fa-angle-left" aria-hidden="true"></i></a>' : '',
                    nextArrow = settings.arrows ? '<a type="button" data-role="none" class="carousel-arrow carousel-next" aria-label="Next" role="button" style=""><i class="fas fa-angle-right" aria-hidden="true"></i></a>' : '';

                return {
                    infinite: true,
                    draggable: true,
                    rows: 0,
                    slidesToShow: settings.slidesToShow,
                    slidesToScroll: settings.slidesToScroll || 1,
                    responsive: [
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: settings.slidesToShowTab,
                                slidesToScroll: 1
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: settings.slidesToShowMobile,
                                slidesToScroll: 1
                            }
                        }
                    ],
                    autoplay: settings.autoPlay,
                    speed: settings.speed || 300,
                    autoplaySpeed: settings.autoplaySpeed || 5000,
                    fade: settings.fade,
                    centerMode: settings.centerMode,
                    centerPadding: settings.centerPadding + "px",
                    nextArrow: nextArrow,
                    prevArrow: prevArrow,
                    dots: settings.dots,
                    customPaging: function () {
                        return '<i class="fas fa-circle"></i>';
                    }
                };
            };

            function getIsoTopeSettings() {

                return {
                    itemSelector: ".premium-tiktok-feed__video-outer-wrapper",
                    percentPosition: true,
                    animationOptions: {
                        duration: 750,
                        easing: "linear",
                        queue: false
                    }
                }
            };
        };

        var PremiumPinterestHandler = function ($scope, $) {

            var $outerWrapper = $scope.find('.premium-pinterest-feed__outer-wrapper'),
                settings = $outerWrapper.data('pa-pinterest-settings');

            if (!settings) {
                return;
            }

            var isBoardQuery = 'boards/' === settings.query,
                loadBoardPins = isBoardQuery && 'pins' === settings.onClick,
                $pinsWrapper = $scope.find('.premium-pinterest-feed__pins-wrapper'),
                $boardWrapper = $scope.find('.premium-pinterest-feed__board-wrapper');

            if (loadBoardPins) {

                $boardWrapper.on('click.paLoadPins', function () {

                    var id = $(this).data('board-id');
                    $boardWrapper.hide();

                    $('#premium-board-content-' + id).show();

                    if ("masonry" === settings.layout) {
                        setTimeout(function () {
                            $('#premium-board-content-' + id + ' .premium-pinterest-feed__pins-wrapper').isotope(getIsoTopeSettings());
                        }, 100);
                    }
                });

                $scope.find('.premium-pinterest-feed__board-trigger').on('click.paHidePins', function () {
                    $boardWrapper.show();
                    $scope.find('.premium-pinterest-feed__content-wrapper').hide();
                });
            }

            if (!isBoardQuery) {

                if ("masonry" === settings.layout && !settings.carousel) {

                    $pinsWrapper.imagesLoaded(function () { }).done(function () {
                        $pinsWrapper.isotope(getIsoTopeSettings());
                    });
                }

                if (settings.loadMore && !settings.carousel) {

                    window.paLoadMoreBookmark = $outerWrapper.data('pa-load-bookmark');
                    window.paHiddenPins = $scope.find('.premium-pinterest-feed__pin-outer-wrapper.premium-display-none').length;

                    $scope.find('.premium-pinterest-feed__load-more-btn').on('click.paLoadMorePins', function (e) {

                        var bookmark = window.paLoadMoreBookmark,
                            count = settings.loadMoreCount;

                        for (var i = 0; i < count; i++) {
                            $scope.find('.premium-pinterest-feed__pin-outer-wrapper').eq(bookmark + i).show().addClass('premium-pin-shown');
                        }

                        window.paLoadMoreBookmark = bookmark + count;
                        window.paHiddenPins -= count;

                        if (0 >= window.paHiddenPins) {
                            $scope.find('.premium-pinterest-feed__load-more-btn').remove();
                        }
                    });
                }

                if (settings.carousel) {

                    var carouselSettings = $outerWrapper.data('pa-carousel');
                    $pinsWrapper.addClass('premium-addons__v-hidden').slick(getSlickSettings(carouselSettings));

                    $pinsWrapper.removeClass('premium-addons__v-hidden');
                }
            }

            if ('layout-2' === settings.pinLayout) {
                $scope.find('.premium-pinterest-feed__pin-meta-wrapper').on('click.paPinTrigger', function (e) {

                    if (e.target === this) {
                        $(this).siblings('.premium-pinterest-feed__overlay')[0].click();
                    }
                });
            }

            // copy to clipbaord
            $scope.find('.premium-copy-link').on('click.paCopyLink', function () {
                $scope.find('.premium-pinterest-share-menu').css('visibility', 'hidden')

                var txt = $(this).data('pa-link');
                navigator.clipboard.writeText(txt);
            });

            $scope.find('.premium-pinterest-share-item:not(.premium-copy-link)').on('click.paShare', function () {

                var link = $(this).data('pa-link');
                window.open(link, 'popup', 'width=600,height=600');
            });

            function getSlickSettings(settings) {

                var prevArrow = settings.arrows ? '<a type="button" data-role="none" class="carousel-arrow carousel-prev" aria-label="Previous" role="button" style=""><i class="fas fa-angle-left" aria-hidden="true"></i></a>' : '',
                    nextArrow = settings.arrows ? '<a type="button" data-role="none" class="carousel-arrow carousel-next" aria-label="Next" role="button" style=""><i class="fas fa-angle-right" aria-hidden="true"></i></a>' : '';

                return {
                    infinite: true,
                    draggable: true,
                    rows: 0,
                    slidesToShow: settings.slidesToShow,
                    slidesToScroll: settings.slidesToScroll || 1,
                    responsive: [
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: settings.slidesToShowTab,
                                slidesToScroll: 1
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: settings.slidesToShowMobile,
                                slidesToScroll: 1
                            }
                        }
                    ],
                    autoplay: settings.autoPlay,
                    speed: settings.speed || 300,
                    autoplaySpeed: settings.autoplaySpeed || 5000,
                    fade: settings.fade,
                    centerMode: settings.centerMode,
                    centerPadding: settings.centerPadding + "px",
                    nextArrow: nextArrow,
                    prevArrow: prevArrow,
                    dots: settings.dots,
                    customPaging: function () {
                        return '<i class="fas fa-circle"></i>';
                    }
                };
            };

            function getIsoTopeSettings() {

                return {
                    itemSelector: ".premium-pinterest-feed__pin-outer-wrapper",
                    percentPosition: true,
                    animationOptions: {
                        duration: 750,
                        easing: "linear",
                        queue: false
                    }
                }
            };
        };

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

        var PremiumTestimonialsHandler = ModuleHandler.extend({

            getDefaultSettings: function () {

                return {
                    slick: {
                        infinite: true,
                        rows: 0,
                        prevArrow: '<a type="button" data-role="none" class="carousel-arrow carousel-prev" aria-label="Previous" role="button" style=""><i class="fas fa-angle-left" aria-hidden="true"></i></a>',
                        nextArrow: '<a type="button" data-role="none" class="carousel-arrow carousel-next" aria-label="Next" role="button" style=""><i class="fas fa-angle-right" aria-hidden="true"></i></a>',
                        draggable: true,
                        pauseOnHover: true,
                    },
                    selectors: {
                        multipleTestimonials: '.multiple-testimonials',
                        testimonials: '.premium-testimonial-container',
                        testimonialCarousel: '.premium-testimonial-container.slick-active',
                        testimonialImg: '.premium-testimonial-img-wrapper img',

                    }
                }
            },

            getDefaultElements: function () {

                var selectors = this.getSettings('selectors');

                return {
                    $multipleTestimonials: this.$element.find(selectors.multipleTestimonials),
                    $testimonials: this.$element.find(selectors.testimonials),
                    $testimonialImg: this.$element.find(selectors.testimonialImg),
                }

            },
            bindEvents: function () {
                this.run();
            },
            getSlickSettings: function () {

                var settings = this.getElementSettings(),
                    rtl = this.elements.$multipleTestimonials.data("rtl"),
                    colsNumber = 'skin4' !== settings.skin ? parseInt(100 / settings.testimonials_per_row.substr(0, settings.testimonials_per_row.indexOf('%'))) : 1,
                    colsTablet = 'skin4' !== settings.skin ? parseInt(100 / settings.testimonials_per_row_tablet.substr(0, settings.testimonials_per_row_tablet.indexOf('%'))) : 1,
                    colsMobile = 'skin4' !== settings.skin ? parseInt(100 / settings.testimonials_per_row_mobile.substr(0, settings.testimonials_per_row_mobile.indexOf('%'))) : 1;

                return Object.assign(this.getSettings('slick'), {

                    slide: '.premium-testimonial-container',
                    slidesToShow: colsNumber,
                    slidesToScroll: colsNumber,
                    responsive: [{
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
                    autoplay: settings.carousel_play,
                    autoplaySpeed: settings.speed || 5000,
                    rtl: ('skin4' !== settings.skin && rtl) ? true : false,
                    speed: 500,
                    arrows: 'skin4' !== settings.skin ? true : false,
                    fade: 'skin4' === settings.skin ? true : false

                });


            },

            runEqualHeight: function () {

                var $testimonials = this.elements.$testimonials;

                var heights = new Array();

                $testimonials.each(function (index, testimonial) {

                    var height = $(testimonial).outerHeight();

                    heights.push(height);

                });

                var maxHeight = Math.max.apply(null, heights);
                $testimonials.find('.premium-testimonial-content-wrapper').css("height", maxHeight + "px");

            },

            run: function () {

                var $multipleTestimonials = this.elements.$multipleTestimonials;

                if (!$multipleTestimonials.length) return;

                if (this.$element.hasClass("premium-testimonial__equal-yes")) {
                    this.runEqualHeight();
                }

                var settings = this.getElementSettings(),
                    skin = settings.skin,
                    carousel = 'skin4' !== skin ? settings.carousel : true;

                if (carousel) {

                    // var $testimonials = this.elements.$testimonials;

                    // if ('skin1' === skin) {
                    //     var imgPosition = settings.img_position;

                    //     if ('absolute' === imgPosition) {
                    //         $testimonials.css('margin-top', '80px')
                    //     }
                    // } else if ('skin4' === skin) {
                    //     $testimonials.css('margin-bottom', '20px');
                    // }

                    var slickSettings = this.getSlickSettings();

                    if ('skin4' === skin)
                        slickSettings.infinite = false;

                    $multipleTestimonials.slick(slickSettings);

                    if ('skin4' === skin) {

                        var $skinCarousel = this.$element.find('.premium-testimonial__carousel');

                        $skinCarousel.slick({
                            slidesToScroll: 1,
                            slidesToShow: 3,
                            arrows: false,
                            centerMode: true,
                            centerPadding: 0,
                            infinite: false,
                            speed: 500,
                            autoplay: settings.carousel_play,
                            autoplaySpeed: settings.speed || 5000,
                            rtl: false
                        });

                        $multipleTestimonials.slick('slickGoTo', 1);
                        $skinCarousel.slick('slickGoTo', 1);

                        this.$element.find('.premium-testimonial__carousel-img').on('click', function () {

                            var slideIndex = $(this).data("index");

                            $multipleTestimonials.slick('slickGoTo', slideIndex);
                            $skinCarousel.slick('slickGoTo', slideIndex);
                        });

                        this.$element.hover(function () {
                            $skinCarousel.slick('slickPause');
                            $multipleTestimonials.slick('slickPause');
                        }, function () {
                            $skinCarousel.slick('slickPlay');
                            $multipleTestimonials.slick('slickPlay');
                        });
                    }

                }

            }

        });

        var PremiumTextualShowcaseHandler = function ($scope, $) {

            var trigger = $scope.find('.pa-txt-sc__outer-container').hasClass('pa-trigger-on-viewport') ? 'viewport' : 'hover',
                hasGrowEffect = $scope.find('.pa-txt-sc__effect-grow').length;

            // unsing IntersectionObserverAPI.
            $scope.off('.PaTextualHandler');

            var eleObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {

                        if (hasGrowEffect) {// grow always triggered on viewport.
                            $scope.find('.pa-txt-sc__effect-grow').css('clip-path', 'inset(0 0 0 0)');
                        }

                        if ('viewport' === trigger) {
                            triggerItemsEffects();
                        }

                        eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                    }
                });
            });

            eleObserver.observe($scope[0]);

            if ('viewport' !== trigger) {
                $scope.on("mouseenter.PaTextualHandler mouseleave.PaTextualHandler", function () {
                    triggerItemsEffects();
                });
            }

            function triggerItemsEffects() {
                $scope.find('.pa-txt-sc__item-container:not(.pa-txt-sc__effect-none)').each(function () {

                    var effectName = this.className.match(/pa-txt-sc__effect-\S+/)[0].replace('pa-txt-sc__effect-', '');
                    if ('grow' === effectName) {
                        return true;
                    }

                    if (['outline', 'curly', 'circle', 'x', 'h-underline', 'underline-zigzag', 'double-underline', 'diagonal', 'strikethrough'].includes(effectName)) {
                        $(this).find('svg').toggleClass('outline');
                    } else {
                        $(this).toggleClass(effectName);
                    }
                });
            }
        };

        var PremiumSearchHandler = function ($scope, $) {

            var widgetID = $scope.data('id');

            $scope = $('.elementor-element-' + widgetID);

            var $container = $scope.find('.premium-search__container'),
                settings = $container.data('settings'),
                $search = $scope.find('.premium-search__input'),
                $spinnerWrap = $scope.find('.premium-search__spinner'),
                queryType = settings.query,
                buttonAction = settings.buttonAction,
                lastSearchQuery = null;

            if ('post' === queryType) {
                var $resultsContainer = $scope.find('.premium-search__query-wrap'),
                    pageNumber = 1;

                if (settings.hideOnClick) {

                    $("body").on("click", function (event) {

                        var widgetContent = ".premium-search__container, .premium-search__container *, .premium-search__query-wrap, .premium-search__query-wrap *";

                        if (!$(event.target).is($scope.find(widgetContent))) {
                            $resultsContainer.html('').addClass('query-hidden');
                        }
                    });
                }
            }

            if ('onpage' === buttonAction) {

                $container.on('click', '.premium-search__btn', handleSearch);

                $search.on('keyup', function () {
                    var searchQuery = $search.val();

                    //Hide the current query everytime a keyboard is clicked. But only if query string is changed.
                    if (searchQuery !== lastSearchQuery && 'post' === queryType)
                        $resultsContainer.html('').addClass('query-hidden');

                    if ('' === searchQuery) {
                        var $textElems = $(settings.target).find('li,h1,h2,h3,h4,h5,h6,p,span,i');
                        $textElems.css('filter', 'blur(0px)');

                        var $fadeElems = $(settings.fadeout_target).find('li,h1,h2,h3,h4,h5,h6,p,span,i')
                        $fadeElems.css('opacity', '1');
                    }

                });

            } else {
                $container.on('click', '.premium-search__btn', function () {

                    var searchQuery = $search.val(),
                        postType = $container.find('.premium-search__type-select').val() || [];

                    window.location.href = settings.search_link + '?s=' + searchQuery + '&post_type[]=' + postType;
                });
            }

            //On click remove button.
            $container.on('click', '.premium-search__remove-wrap', function () {
                $search.val('');
                handleSearch('remove');
            });

            //On change post type select.
            $container.find('.premium-search__type-select').on('change', function () {
                lastSearchQuery = '';
                handleSearch();
            });

            //We want the search to be triggered all time.
            $search.on('keyup', handleSearch);

            //For enter button.
            document.addEventListener("keydown", function (event) {

                if ($container.hasClass("is-focused")) {
                    if (13 === event.keyCode)
                        handleSearch();
                }

            });

            $search.on("focus blur", function (e) {
                $container.toggleClass("is-focused");

                if ('focus' === e.type) {
                    lastSearchQuery = '';
                    handleSearch();
                }

            });

            if ($scope.hasClass('premium-search-anim-label-letter')) {

                $search.on("focus", function () {
                    var letterSpacing = parseFloat($container.find('label').css('letter-spacing').replace('px', ''));

                    $container.find('label').css('letter-spacing', (letterSpacing + 3) + 'px');
                });

                $search.on("blur", function () {
                    var letterSpacing = parseFloat($container.find('label').css('letter-spacing').replace('px', ''));

                    $container.find('label').css('letter-spacing', (letterSpacing - 3) + 'px');
                });



            }

            function handleSearch(trigger) {

                var timeToWait = 'remove' === trigger ? 10 : ('elements' === queryType ? 100 : 1000);

                setTimeout(function () {

                    var searchQuery = $search.val();

                    if (!$container.hasClass('fetching') && searchQuery !== lastSearchQuery) {

                        if ('post' === queryType)
                            $resultsContainer.html('').addClass('query-hidden');

                        if ('elements' !== queryType) {

                            if ('' !== searchQuery) {

                                $.ajax({
                                    url: PremiumSettings.ajaxurl,
                                    dataType: 'json',
                                    type: 'POST',
                                    data: {
                                        action: 'premium_get_search_results',
                                        nonce: PremiumSettings.nonce,
                                        page_id: $container.data('page'),
                                        page_number: 1,
                                        widget_id: $scope.data('id'),
                                        query: searchQuery,
                                        post_type: $container.find('.premium-search__type-select').val()
                                    },
                                    beforeSend: function () {

                                        $container.find('.premium-search__remove-wrap').addClass('premium-addons__v-hidden');

                                        $spinnerWrap.append('<div class="premium-loading-feed"><div class="premium-loader"></div></div>');

                                        $container.addClass('fetching');
                                    },
                                    success: function (res) {

                                        if (!res.data)
                                            return;

                                        var posts = res.data.posts,
                                            pagination = res.data.pagination;

                                        $spinnerWrap.find(".premium-loading-feed").remove();

                                        $container.find('.premium-search__remove-wrap').removeClass('premium-addons__v-hidden');

                                        $resultsContainer.html(posts).removeClass('query-hidden');

                                        if (settings.carousel)
                                            $resultsContainer.find('.premium-search__posts-wrap').slick(getSlickSettings());

                                        // if ($container.hasClass('premium-search__skin-banner'))
                                        forceEqualHeight();

                                        if ('' !== pagination) {
                                            $resultsContainer.append("<div class='premium-search__footer'></div>");
                                            $resultsContainer.find(".premium-search__footer").html(pagination);
                                        }

                                        $container.removeClass('fetching');

                                    },
                                    error: function (err) {
                                        console.log(err);

                                        $spinnerWrap.find(".premium-loading-feed").remove();
                                        $container.removeClass('fetching');
                                    },
                                });
                            }

                        } else {

                            var $textElems = $(settings.target).find('li,h1,h2,h3,h4,h5,h6,p,span,i'),
                                $fadeElems = $(settings.fadeout_target).find('li,h1,h2,h3,h4,h5,h6,p,span,i');


                            $textElems.css('transition', 'filter 0.3s ease-in-out');
                            $fadeElems.css('transition', 'opacity 0.3s ease-in-out');

                            $textElems.find('.pa-highlighted-text-' + widgetID).removeClass('pa-highlighted-text-' + widgetID);
                            $textElems.css('filter', 'blur(0px)');
                            $fadeElems.css('opacity', '1');

                            if ('' !== searchQuery) {

                                searchQuery = searchQuery.trim().toLowerCase();

                                // Filter elements that don't contain the specified text (case-sensitive)
                                var $queriedElems = $textElems.filter(function () {
                                    return $(this).text().toLowerCase().indexOf(searchQuery) == -1;
                                });

                                $queriedElems.css('filter', 'blur(3px)');
                                $fadeElems.css('opacity', '0.4');

                                var $matchedElems = $textElems.filter(function () {
                                    return $(this).text().toLowerCase().indexOf(searchQuery) !== -1;
                                });

                                $matchedElems.map(function (index, textElem) {

                                    if ($(this).is(':visible') && 'LI' !== $(this).prop('tagName')) {

                                        textElem = $(this).text().toLowerCase();

                                        textElem = textElem.replace(new RegExp(searchQuery, 'g'), '<span class="pa-highlighted-text pa-highlighted-text-' + widgetID + '">' + searchQuery + '</span>');

                                        $(this).html(textElem);
                                    }
                                });

                            } else {
                                $textElems.css('filter', 'blur(0px)');

                                $textElems.find('.pa-highlighted-text-' + widgetID).removeClass('pa-highlighted-text-' + widgetID);
                                $fadeElems.css('opacity', '1');
                            }


                        }

                        lastSearchQuery = searchQuery;
                    }

                }, timeToWait);

            }

            function forceEqualHeight() {

                var heights = new Array(),
                    $contentWrapper = $container.find('.premium-search__post-content');

                $contentWrapper.each(function (index, post) {

                    var height = $(post).outerHeight();

                    heights.push(height);
                });

                var maxHeight = Math.max.apply(null, heights);

                $contentWrapper.css("height", maxHeight + "px");
            }

            function getSlickSettings() {

                var cols = getComputedStyle($scope[0]).getPropertyValue('--pa-search-carousel-slides'),
                    prevArrow = settings.arrows ? '<a type="button" data-role="none" class="carousel-arrow carousel-prev" aria-label="Previous" role="button" style=""><i class="fas fa-angle-left" aria-hidden="true"></i></a>' : '',
                    nextArrow = settings.arrows ? '<a type="button" data-role="none" class="carousel-arrow carousel-next" aria-label="Next" role="button" style=""><i class="fas fa-angle-right" aria-hidden="true"></i></a>' : '';

                cols = parseInt(100 / cols.substr(0, cols.indexOf('%')))

                return {
                    infinite: false,
                    draggable: true,
                    autoplay: false,
                    slidesToShow: cols,
                    slidesToScroll: settings.slidesToScroll || cols,

                    rows: settings.rows,
                    speed: settings.speed,
                    nextArrow: nextArrow,
                    prevArrow: prevArrow,
                    fade: settings.fade,
                    centerMode: settings.center,
                    centerPadding: settings.spacing + "px",

                    dots: settings.dots,
                    customPaging: function () {
                        return '<i class="fas fa-circle"></i>';
                    }
                }

            }

            $scope.on('click', '.premium-search-form__pagination-container .page-numbers', function (e) {

                e.preventDefault();

                if ($(this).hasClass("current")) return;

                var currentPage = parseInt($scope.find('.page-numbers.current').html());

                if ($(this).hasClass('next')) {
                    pageNumber = currentPage + 1;
                } else if ($(this).hasClass('prev')) {
                    pageNumber = currentPage - 1;
                } else {
                    pageNumber = $(this).html();
                }

                $.ajax({
                    url: PremiumSettings.ajaxurl,
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        action: 'premium_get_search_results',
                        nonce: PremiumSettings.nonce,
                        page_id: $container.data('page'),
                        page_number: pageNumber,
                        widget_id: $scope.data('id'),
                        query: lastSearchQuery,
                        post_type: $container.find('.premium-search__type-select').val()
                    },
                    beforeSend: function () {

                        $container.find('.premium-search__remove-wrap').addClass('premium-addons__v-hidden');

                        $resultsContainer.append('<div class="premium-loading-feed"><div class="premium-loader"></div></div>');

                        $container.addClass('fetching');
                    },
                    success: function (res) {

                        if (!res.data)
                            return;

                        var posts = res.data.posts,
                            pagination = res.data.pagination;

                        $resultsContainer.find(".premium-loading-feed").remove();

                        $container.find('.premium-search__remove-wrap').removeClass('premium-addons__v-hidden');

                        $resultsContainer.html(posts).removeClass('query-hidden');

                        forceEqualHeight();

                        $resultsContainer.append("<div class='premium-search__footer'></div>");

                        $resultsContainer.find(".premium-search__footer").html(pagination);

                        $container.removeClass('fetching');

                    },
                    error: function (err) {
                        console.log(err);

                        $spinnerWrap.find(".premium-loading-feed").remove();

                        $container.removeClass('fetching');
                    },
                });

            });

        };

        var PremiumMobileMenuHandler = ModuleHandler.extend({

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

        var functionalHandlers = {
            'premium-search-form.default': PremiumSearchHandler,
            'premium-addon-dual-header.default': PremiumMaskHandler,
            'premium-addon-video-box.default': PremiumVideoBoxWidgetHandler,
            'premium-addon-fancy-text.default': PremiumFancyTextHandler,
            'premium-counter.default': PremiumCounterHandler,
            'premium-addon-title.default': [PremiumTitleHandler, PremiumMaskHandler],
            'premium-countdown-timer.default': PremiumCountDownHandler,
            'premium-carousel-widget.default': PremiumCarouselHandler,
            'premium-addon-modal-box.default': PremiumModalBoxHandler,
            'premium-image-scroll.default': PremiumImageScrollHandler,
            'premium-contact-form.default': PremiumContactFormHandler,
            'premium-addon-button.default': PremiumButtonHandler,
            'premium-addon-image-button.default': PremiumButtonHandler,
            'premium-world-clock.default': PremiumWorldClockHandler,
            'premium-post-ticker.default': PremiumPostTickerHandler,
            'premium-weather.default': PremiumWeatherHandler,
            'premium-pinterest-feed.default': PremiumPinterestHandler,
            'premium-tiktok-feed.default': PremiumTiktokHandler,
            'premium-media-wheel.default': PremiumAdvCarouselHandler,
            'premium-textual-showcase.default': [PremiumTextualShowcaseHandler, PremiumMaskHandler]
        };

        var classHandlers = {
            'premium-addon-person': PremiumTeamMembersHandler,
            'premium-addon-blog': PremiumBlogHandler,
            'premium-img-gallery': PremiumGridWidgetHandler,
            'premium-addon-banner': PremiumBannerHandler,
            'premium-svg-drawer': PremiumSVGDrawerHandler,
            'premium-tcloud': PremiumTermsCloud,
            'premium-icon-list': PremiumBulletListHandler,
            'premium-addon-testimonials': PremiumTestimonialsHandler,
            'premium-mobile-menu': PremiumMobileMenuHandler,
        };

        $.each(functionalHandlers, function (elemName, func) {
            if ('object' === typeof func) {
                $.each(func, function (index, handler) {
                    elementorFrontend.hooks.addAction('frontend/element_ready/' + elemName, handler);
                })
            } else {
                elementorFrontend.hooks.addAction('frontend/element_ready/' + elemName, func);
            }

        });

        $.each(classHandlers, function (elemName, clas) {
            elementorFrontend.elementsHandler.attachHandler(elemName, clas);
        });


        if (elementorFrontend.isEditMode()) {
            elementorFrontend.hooks.addAction("frontend/element_ready/premium-addon-progressbar.default", PremiumProgressBarWidgetHandler);
        } else {
            elementorFrontend.hooks.addAction("frontend/element_ready/premium-addon-progressbar.default", PremiumProgressBarScrollWidgetHandler);
        }
    });
})(jQuery);