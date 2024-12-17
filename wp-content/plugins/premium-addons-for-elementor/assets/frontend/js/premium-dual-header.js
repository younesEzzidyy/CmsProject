(function ($) {

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

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/premium-addon-dual-header.default', PremiumMaskHandler);
    });
})(jQuery);

