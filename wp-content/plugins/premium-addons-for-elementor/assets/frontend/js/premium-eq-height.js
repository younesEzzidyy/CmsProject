(function ($) {

    $(window).on('elementor/frontend/init', function () {

        var PremiumEqualHeightHandler = function ($scope) {

            if (!$scope.hasClass("premium-equal-height-yes"))
                return;

            var timeToWait = $scope.find('.premium-carousel-inner, ul.products').length ? 1500 : 10;

            setTimeout(function () {
                premiumEqHeightHandler($scope);
            }, timeToWait);
        }

        function premiumEqHeightHandler($scope) {

            var section = $scope,
                editMode = elementorFrontend.isEditMode(),
                dataHolder = (editMode) ? section.find('#premium-temp-equal-height-' + section.data('id')) : section,
                addonSettings = dataHolder.data('pa-eq-height');

            if (!addonSettings)
                return;

            var enableOn = addonSettings.enableOn;

            if (0 === Object.keys(addonSettings).length) {
                return false;
            }

            if ('scroll' === addonSettings.trigger) {
                // elementorFrontend.waypoint($scope, function () {
                //     triggerEqualHeight();
                // });
                var eleObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            triggerEqualHeight();
                            eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                        }
                    });
                });

                eleObserver.observe($scope[0]);
            } else {
                triggerEqualHeight();
            }

            function matchHeight(selector) {
                var $targets = section.find(selector),
                    heights = [];

                section.find(selector).css('minHeight', 'unset');

                jQuery.each($targets, function (key, valueObj) {
                    heights.push($(valueObj).outerHeight(true));
                });

                section.find(selector).css('minHeight', Math.max.apply(null, heights));
            }

            function triggerEqualHeight() {
                if (enableOn.includes(elementorFrontend.getCurrentDeviceMode()) && 0 !== addonSettings.target.length) {

                    addonSettings.target.forEach(function (target) {
                        matchHeight(target);
                    });
                } else {
                    section.find(addonSettings.target).css('minHeight', 'unset');
                }
            }

            window.onresize = triggerEqualHeight;
        };

        elementorFrontend.hooks.addAction("frontend/element_ready/section", PremiumEqualHeightHandler);
        elementorFrontend.hooks.addAction("frontend/element_ready/container", PremiumEqualHeightHandler);

    });

})(jQuery);