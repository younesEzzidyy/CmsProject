(function ($) {
    $(window).on('elementor/frontend/init', function () {

        var scrollPos = 0;
        window.addEventListener('scroll', function() {
            // detects new state and compares it with the new one.
            window.paDirection = (document.body.getBoundingClientRect()).top > scrollPos ? 'up': 'down';

            scrollPos = (document.body.getBoundingClientRect()).top; // saves the new position for iteration.
        });
    });
})(jQuery);
