/***
 * @author : jegbagus
 * jquery.jproduct.js
 */

(function ($, PhotoSwipe) {
    "use strict";
    $.fn.jproduct = function (options) {

        options = $.extend({
            loadAnimation: 'seqfade', // normal | fade | seqfade | upfade | sequpfade | randomfade | randomupfade
            portfoliosize: 400,
            dimension: 0.6,
            tiletype: 'normal' // normal || masonry
        }, options);

        return $(this).each(function () {
            var element = $(this);
            var container = $(this).find('.isotopewrapper');
            var loader = $('.productloader');
            var photoswipe = null;

            var get_image_column_number = function (ww) {
                ww = ( ww > 1920 ) ? 1920 : ww;
                return Math.round(ww / options.portfoliosize);
            };

            var calc_normal_height = function (itemwidth, margin, thiswidth) {
                var imgwidth = itemwidth - (margin * 2);
                var imgheight = imgwidth * options.dimension;
                var eleheight = imgheight + (margin * 2);
                var thisheight = eleheight * thiswidth;
                var thisimgheight = thisheight - (margin * 2);
                return {
                    'itemheight': thisheight,
                    'imageheight': thisimgheight,
                };
            };

            var resize_gallery_item_list = function () {
                var ww = $(window).width();
                var wrapperwidth = $('.product-content-wrapper').width();
                var portfolionumber = get_image_column_number(wrapperwidth);
                var margin = parseInt($($(".productitem .productcontent", element).get(0)).css('margin-top'), 10);

                var itemwidth = Math.floor(wrapperwidth / portfolionumber);

                $(".productitem", element).each(function () {
                    var thiswidth = parseFloat($(this).data('width'));
                    var curwidth = itemwidth * thiswidth;

                    if (ww < 768 || curwidth > $(container).width()) {
                        thiswidth = 1;
                        $("img", this).attr('style', '');
                        $(this).css({ 'height': '' });
                    } else {
                        if (isNaN(thiswidth)) thiswidth = 1;
                    }

                    if (options.tiletype === 'normal' && thiswidth !== 1) {
                        var res = calc_normal_height(itemwidth, margin, thiswidth);
                        $("img", this).css({ height: res.imageheight });
                        $(this).css({ height: res.itemheight });
                    }

                    $(this).width(Math.floor(itemwidth * thiswidth));
                });

            };

            var initialize_gallery = function () {
                resize_gallery_item_list();
                $(container).imagesLoaded(function () {

                     // image loaded check on ie
                    $(".isotopewrapper").checkimageloaded();

                    $(container).isotope({
                        itemSelector: ".productitem",
                        masonry: {
                            columnWidth: 1
                        }
                    });

                    setTimeout(function () {
                        $(loader).fadeOut("slow");
                        $.animate_load(options.loadAnimation, container, $(container).find('.productitem'), function () {
                        });
                    }, 1000);
                });
            };

            initialize_gallery();
            $(window).bind("resize load", function (event) {
                resize_gallery_item_list();
            });
        });
    };
})(jQuery, window.Code.PhotoSwipe);