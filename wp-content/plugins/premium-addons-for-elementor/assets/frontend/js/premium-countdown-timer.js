(function ($) {

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

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/premium-countdown-timer.default', PremiumCountDownHandler);
    });
})(jQuery);

