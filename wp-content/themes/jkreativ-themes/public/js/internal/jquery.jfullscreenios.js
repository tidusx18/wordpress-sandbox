/** jquery.jfullscreenios.js **/
(function ($, window) {
    "use strict";
    $.fn.jfullscreenios = function (options) {

        $(this).each(function () {
            var element = $(this);

            function slideContentComplete(args) {
                // slide dot changed
                $('.slide-dot', element).removeClass('selected');
                $('.slide-dot:eq(' + (args.currentSlideNumber - 1) + ')', element).addClass('selected');

                // switch active slide
                $('.item', args.sliderObject).removeClass('activeslide');
                $(args.currentSlideObject).addClass('activeslide');
            }

            function slideContentLoaded(args) {
                $(".sliderloader").fadeOut();
                $(".sliderContainer").animate({ 'opacity': 1 }, "slow");
                slideContentComplete(args);
            }

            /*** IOS FULL SCREEN ***/

            function get_image_size(img, wh, ww) {
                var nh, nw, nt, nl;
                var h = $(img).height();
                var w = $(img).width();

                if (h === 0) {
                    h = img.height;
                    w = img.width;
                }

                var r = (h / w).toFixed(2);
                var wr = wh / ww.toFixed(2);

                var resizeWidth = function () {
                    nw = ww;
                    nh = ww * r;
                    nl = (ww - nw) / 2;
                    nt = (wh - nh) / 2;

                    return [nh, nw, nl, nt];
                };

                var resizeHeight = function () {
                    nw = wh / r;
                    nh = wh;
                    nl = (ww - nw) / 2;
                    nt = (wh - nh) / 2;
                    return [nh, nw, nl, nt];
                };

                if (wr > r) {
                    return resizeHeight();
                }
                return resizeWidth();
            }

            function windowResize(container) {
                $('.sliderContainer img').each(function () {
                    var size = get_image_size($(this), $(container).height() + 10, $(container).width());
                    $(this).css('height', size[0])
                        .css('width', size[1])
                        .css('left', size[2])
                        .css('top', size[3])
                        .css('max-width', 'inherit')
                        .css('position', 'absolute');
                });
            }

            function containerChange() {
                windowResize($(".sliderContainer"));
            }

            function initialize() {
                $(element).iosSlider({
                    snapToChildren: true,
                    desktopClickDrag: true,
                    snapSlideCenter: true,
                    autoSlide: ($(element).attr('data-autoplay') === "1") ? true : false,
                    autoSlideTimer: parseInt($(element).attr('data-delay'), 10),
                    navNextSelector: $('.next-slide', element),
                    navPrevSelector: $('.prev-slide', element),
                    navSlideSelector: $('.slide-dot', element),
                    onSlideComplete: slideContentComplete,
                    onSliderLoaded: slideContentLoaded
                });

                $(window).bind('resize', containerChange);
                containerChange();
            }

            $(window).bind('load', initialize);

        });
    };
})(jQuery, window);