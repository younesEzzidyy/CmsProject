/****** Premium Fancy Text Handler ******/
(function ($) {
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

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/premium-addon-fancy-text.default', PremiumFancyTextHandler);
    });
})(jQuery);

