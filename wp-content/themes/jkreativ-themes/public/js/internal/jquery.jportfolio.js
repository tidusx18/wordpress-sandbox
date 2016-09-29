/**
 * jquery.jportfolio.js
 */
(function ($) {
    "use strict";
    $.fn.jportfolio = function (options) {

        options = $.extend({
            adminurl: '',
            loadAnimation: 'seqfade', // normal | fade | seqfade | upfade | sequpfade | randomfade | randomupfade
            portfoliosize: 400,
            dimension: 0.6,
            margin: 0,
            expandtype: 'normal', // normal || theather || noexpand
            imgfsmode: 'fit', // fit || fitNoUpscale || zoom
            tiletype: 'normal', // normal || masonry
            hidetitle: "1"
        }, options);

        if (joption.ismobile) options.loadAnimation = 'fade';

        return $(this).each(function () {
            var element = $(this);
            var container = $(this).find('.isotopewrapper');
            var loader = $('.portfolioloader');
            var portfoliofilter = $(".portfoliofilter");
            var portfolioform = $(".portfolioinputfilter form");
            var currenturl = document.URL;
            var currentitle = document.title;
            var prevargs = null;
            var args = null;

            var is_history_exist = function(){
                if(window.history && window.history.pushState) {
                    return true;
                } else {
                    return false;
                }
            };

            var get_portfolio_column_number = function (ww) {
                ww = ( ww > 1920 ) ? 1920 : ww;
                return Math.round(ww / options.portfoliosize);
            };

            var resize_portfolio_content = function () {
                var ww = $(window).width();

                if (ww > 1024) {
                    $(".portfoliocontent").height($(window).height());
                }
                else {
                    $(".portfoliocontent").height($(window).height() - $(".responsiveheader").height());
                }

                if ($('body').hasClass('horizontalnav')) {
                    if (ww > 1024) {
                        $(".portfolioholderwrap").height($(".portfoliocontent").height() - $(".topnavigation").height());
                    } else {
                        $(".portfolioholderwrap").height($(".portfoliocontent").height() - $(".portfolionavbar").height());
                    }

                } else {
                    $(".portfolioholderwrap").height($(".portfoliocontent").height() - $(".portfolionavbar").height());
                }

            };

            // itemwidth, margin, thisheight
            var calc_normal_height = function (itemwidth, thisheight) {
                var imgwidth = itemwidth * options.dimension;
                var thisheight = imgwidth * thisheight;
                return {
                    'itemheight': thisheight
                };
            };

            var resize_portfolio_item_list = function () {
                var ww = $(window).width();
                var wrapperwidth = $('.portfoliocontentwrapper').width();
                var portfolionumber = get_portfolio_column_number(wrapperwidth);

                var itemwidth = Math.floor(wrapperwidth / portfolionumber);

                $(".portfolioitem", element).each(function (i) {
                    var thiswidth = parseFloat($(this).data('width'));
                    var thisheight = parseFloat($(this).data('height'));

                    // calc width
                    var curwidth = itemwidth * thiswidth;

                    if (ww < 768 || curwidth > $(container).width()) {
                        thiswidth = 1;
                    } else {
                        if (isNaN(thiswidth)) thiswidth = 1;
                    }

                    $(this).width(Math.floor(itemwidth * thiswidth) - ( options.margin * 2));

                    // calc height
                    if (options.tiletype === 'normal' && ww > 1024) {
                        var res = calc_normal_height(itemwidth, thisheight);
                        $(this).css({ height: res.itemheight });
                    } else {
                        $("a img", this).attr('style', '');
                        $(this).css({ 'height': '' });
                    }

                    // info height
                    var infoheight = $(".info", this).height();
                    var itemheight = $(this).height() - ( $(this).height() * 4 / 100 );

                    $(".info", this).css({
                        "margin-top": ( itemheight - infoheight ) / 2
                    });

                    var elementdim = $(this).height() / $(this).width(),
                        imagedim = $(this).find('img').height() / $(this).find('img').width();

                    $(this).find('img').removeClass('fixwidthportfolio');
                    if (elementdim > imagedim) {
                        $(this).find('img').addClass('fixwidthportfolio');
                    }

                });
            };

            var do_portfolio_resize = function () {
                resize_portfolio_content();
                resize_portfolio_theather();
                resize_portfolio_item_list();
            };

            var filterfloatclicked = function(){
                var portfoliofilterbutton = $(".filterfloatbutton");
                var portfoliofilter = $(".filterfloat");
                $(document).mouseup(function(e){
                    if($(e.target).parents('.filterfloatbutton').length > 0 || portfoliofilterbutton.is(e.target)) {
                        if ($(portfoliofilter).hasClass('active')) {
                            $(portfoliofilter).removeClass('active');
                        } else {
                            $(portfoliofilter).addClass('active');
                        }
                    } else {
                        $(portfoliofilter).removeClass('active');
                    }
                });
            };

            var normalfilterclicked = function(){
                var portfoliofilterbutton = $(".portfoliofilterbutton");
                var portfoliofilter = $(".portfoliofilter");
                $(document).mouseup(function(e){
                    if($(e.target).parents('.portfoliofilterbutton').length > 0 || portfoliofilterbutton.is(e.target)) {
                        if ($(portfoliofilter).hasClass('active')) {
                            $(portfoliofilter).removeClass('active');
                        } else {
                            $(portfoliofilter).addClass('active');
                        }
                    } else {
                        $(portfoliofilter).removeClass('active');
                    }
                });
            };

            var filterlistclicked = function () {
                filterfloatclicked();
                normalfilterclicked();
            };

            var doloadmorerequest = function(loadpaging, data){
                $(".isotopewrapper .portfolioitem", data).each(function (i) {
                    $(container).append(this);
                });

                if (loadpaging) {
                    $(".portfoliopagingwrapper").html($(".portfoliopagingwrapper", data));
                }

                initialize_portfolio();
            };

            var loadmorerequest = function (loadpaging) {
                $(loader).fadeIn();

                $(container).addClass('no-transition')
                    .isotope('destroy')
                    .attr('style', '');

                // do ajax request
                $.ajax({
                    url: options.adminurl,
                    type: "post",
                    dataType: "html",
                    data: $(portfolioform).serialize(),
                    success: function (data) {
                        $(container).removeClass('no-transition');
                        doloadmorerequest(loadpaging, data);
                    }
                });
            };

            var pagingclicked = function (event) {
                var li = $(event.currentTarget);
                if (!$(li).hasClass('active')) {
                    $(".pagedot li").removeClass('active');
                    $(li).addClass('active');

                    var pagenumber = $(li).data('page');
                    $("[name='page']", portfolioform).val(pagenumber);
                    $(".pagetext .curpage").text(pagenumber);

                    $.animate_hide(options.loadAnimation, container, $(container).find('.portfolioitem'), function () {
                        loadmorerequest(false);
                        $(".portfoliopagingwrapper").fadeOut();
                    });
                }
            };

            var changefilterwidth = function() {
                var portfoliofilterwidth = $(portfoliofilter).width();
                $(".portfoliofilterlist ul").css({ 'min-width': portfoliofilterwidth });
            };


            var filterclicked = function (event) {
                var li = $(event.currentTarget);
                var filterid = $(li).data('filter');

                // filter text
                var filtertext = $(li).text();
                var filterbuttontext = $(".portfoliofilterbutton").data('text');
                $(".portfoliofilterbutton").text(filterbuttontext + " : " + filtertext);
                $(".filterfloatbutton span").text(filterbuttontext + " : " + filtertext);

                // 	portfoliofilterwidth
                changefilterwidth();

                // modify form
                $("[name='category']", portfolioform).val(filterid);
                $("[name='page']", portfolioform).val(1);

                // hide portfolio paging
                $(".portfoliopagingwrapper").fadeOut();

                $.animate_hide(options.loadAnimation, container, $(container).find('.portfolioitem'), function () {
                    loadmorerequest(true);
                });
            };

            var initialize_portfolio = function () {
                $(container).imagesLoaded(function () {

                    // image loaded check on ie
                    $(".isotopewrapper").checkimageloaded();

                    // do portfolio resize
                    do_portfolio_resize();

                    $(container).isotope({
                        itemSelector: ".portfolioitem",
                        masonry: {
                            columnWidth: 1
                        }
                    });

                    setTimeout(function () {
                        $(loader).fadeOut("slow");
                        $.animate_load(options.loadAnimation, container, $(container).find('.portfolioitem'), function () {
                            $(container).isotope('layout');
                        });
                    }, 800);

                    $(".portfoliopagingwrapper").fadeIn().removeClass('hideme');
                });
            };

            var single_portfolio_resize = function () {
                $(".portfolio-content-holder").each(function () {
                    var curimg = $("img", this).get(0);

                    if ($(curimg).hasClass('loaded')) {
                        /** parameter crop data ***/
                        var size = $.new_get_image_container_size(curimg, $(".portfolio-slider-holder"), options.imgfsmode);
                        $(curimg).css({
                            'height': size[0],
                            'width': size[1],
                            'max-width': 'inherit'
                        });

                        doTranslate(curimg, size[2] + "px", size[3] + "px");
                    }
                });
            };

            var open_portfolio_video = function (event) {
                $(window).trigger('jmusicstop');
                var curobj = $(event.target).parents(".portfolio-content-holder");
                var type = $(curobj).attr('data-type');

                $(".portfoliovideoclose").fadeIn();
                $(".portfoliovideo-container").fadeIn();
                $(".portfolio-navigation, .portfoliobottombar").fadeOut();
                switch (type) {
                    case 'youtube' :
                        $.type_video_youtube(curobj, true, false);
                        break;
                    case 'vimeo' :
                        $.type_video_vimeo(curobj, true, false);
                        break;
                    case 'html5video' :
                        $.type_video_html5(curobj, true, {
                            enableAutosize: true,
                            videoWidth: '100%',
                            videoHeight: '100%',
                            followContainerHeight: true,
                            features: ['playpause', 'progress', 'current', 'duration', 'tracks', 'volume']
                        }, '.html5-video-container');
                        break;
                    case 'soundcloud' :
                        $.type_soundcloud(curobj);
                        break;
                    default :
                        break;
                }
                ;
            };

            var close_portfolio_video = function () {
                $(window).trigger('jmusicstart');
                $(".video-container").html("");
                $(".html5-video-container").html("");
                $(".portfoliovideoclose").unbind('click', close_portfolio_video).fadeOut("slow");
                $(".portfolio-navigation, .portfoliobottombar").fadeIn();
                setTimeout(function () {
                    $(".portfoliovideo-container").fadeOut("slow");
                }, 500);
            };

            var check_portfolio_type = function (args, prevargs) {
                var prevobj = prevargs.currentSlideObject;
                var type = $(prevobj).attr('data-type');

                if (type == 'youtube' || type == 'vimeo' || type === 'html5video') {
                    $(".videooverlay", prevobj).unbind('click', open_portfolio_video);
                    close_portfolio_video();
                }

                if (get_portfolio_type(args) !== 'image') {
                    // build current object
                    var curobj = args.currentSlideObject;
                    var type = $(curobj).attr('data-type');

                    /*** load image ***/
                    if ($("img", curobj).hasClass('notloaded')) {
                        var datasrc = $("img", curobj).data('src');
                        var img = new Image();

                        $(img).load(function () {
                            $("img", curobj).attr('src', datasrc);
                            var size = $.new_get_image_container_size($("img", curobj), $(".portfolio-slider-holder"), options.imgfsmode);
                            $("img", curobj).css({
                                'height': size[0],
                                'width': size[1],
                                'max-width': 'inherit'
                            });
                            doTranslate($("img", curobj), size[2] + "px", size[3] + "px");
                            $("img", curobj).removeClass('notloaded').addClass('loaded');

                            load_next_image(args.currentSlideNumber, args.sliderObject);
                            iphone_loaded_event_fix();
                        }).attr('src', datasrc);
                    } else {
                        var imgobj =  $("img", curobj);
                        var size = $.new_get_image_container_size(imgobj, $(".portfolio-slider-holder"), options.imgfsmode);
                        $(imgobj).css({
                            'height': size[0],
                            'width': size[1],
                            'max-width': 'inherit'
                        });
                        doTranslate(imgobj, size[2] + "px", size[3] + "px");
                    }

                    if (type == 'youtube' || type == 'vimeo' || type === 'html5video') {
                        $(".videooverlay", curobj).bind('click', open_portfolio_video);
                    } else if (type == 'soundcloud') {
                        $.type_soundcloud(curobj);
                    }
                }
            };

            var set_portfolio_item_title = function (args) {
                var obj = args.currentSlideObject;
                var title = $(obj).data('title');
                var sequence = args.currentSlideNumber;
                var total = $(".portfolio-content-holder").length;
                var itemtitle = title + " (" + sequence + "/" + total + ")";

                $(".portfoliobottombar .portfolionavtitle").text(itemtitle);
            };

            var get_portfolio_type = function (args) {
                var curobj = args.currentSlideObject;
                return $(curobj).attr('data-type');
            };

            var navigatoin_show_hide = function (args) {
                $(".portfolionavprev").fadeIn();
                $(".portfolionavnext").fadeIn();

                /** thumb **/
                var prevthumb = $($(args.currentSlideObject).prev()).data('thumb');
                var nextthumb = $($(args.currentSlideObject).next()).data('thumb');

                if (typeof prevthumb !== 'undefined') $(".pt-prev-bg").css({ backgroundImage: 'url(' + prevthumb + ')'});
                if (typeof nextthumb !== 'undefined') $(".pt-next-bg").css({ backgroundImage: 'url(' + nextthumb + ')'});

                /** navigation **/
                var total = $(".portfolio-content-holder").length;

                if (args.currentSlideNumber == 1) {
                    $(".portfolionavprev").stop().fadeOut();
                }

                if (args.currentSlideNumber == total) {
                    $(".portfolionavnext").stop().fadeOut();
                }
            };

            /** pan image functionality ***/
            var paneimage = function (event) {
                var holder = $(event.target).parent('.portfolio-content-holder');
                var image = $(event.target);

                if (holder.offset() !== undefined) {
                    var mouseX = (event.pageX - holder.offset().left);
                    var mouseY = (event.pageY - holder.offset().top);

                    var topposition = ( $(image).height() - $(holder).height() ) * ( mouseY / $(holder).height() );
                    var leftposition = ( $(image).width() - $(holder).width() ) * ( mouseX / $(holder).width() );

                    doTranslate(image, "-" + leftposition + "px", "-" + topposition + "px");
                }
            };

            var prevelement = null;
            var dopane = function (element, container) {
                prevelement = element;
                element.unbind('mousemove', paneimage);
                element.bind('mousemove', paneimage);
            };
            var unbindprevelement = function () {
                if (prevelement !== null) {
                    $(prevelement).unbind('mousemove', paneimage);
                }
            };

            var panetimeout = null;
            var panme = function (element, container) {
                if (options.imgfsmode === 'zoom' && !joption.ismobile) {
                    clearTimeout(panetimeout);
                    panetimeout = setTimeout(function () {
                        dopane(element, container.find('.item'));
                    }, 500);
                }
            };
            /** pan image end ***/

            var iphone_loaded_event_fix = function () {
                // ugly fix for iphone not listen for loaded event
                setTimeout(single_portfolio_resize, 100);
            };

            var load_next_image = function(currentslide, slidercontainer){
                var nextslide = ($(slidercontainer).find('.item').get(currentslide));
                var thumb = $(nextslide).data('thumb');
                var nextimage = $(nextslide).find('img');
                if($(nextimage).hasClass('notloaded')){
                    // load image
                    var fullimg = new Image();
                    $(fullimg).attr('src', $(nextimage).data('src'));
                    // load thumb
                    var thumbimg = new Image();
                    $(thumbimg).attr('src', thumb);
                }
            };

            var curslideobj = null;
            var single_portfolio_arrange = function (callback) {
                $('.portfolio-slider-holder').iosSlider({
                    snapToChildren: true,
                    desktopClickDrag: true,
                    autoSlide: false,

                    navNextSelector: $('.portfolionavnext'),
                    navPrevSelector: $('.portfolionavprev'),

                    onSliderResize: function () {
                        single_portfolio_resize();

                        if (get_portfolio_type(prevargs) === 'image') {
                            panme(prevargs.currentSlideObject, $(".portfolio-slider-holder"));
                        }
                    },
                    onSliderLoaded: function (args) {
                        curslideobj = args.currentSlideObject;

                        $(loader).fadeOut();
                        single_portfolio_resize();

                        // need to check first if element is image
                        if (get_portfolio_type(args) === 'image')
                            panme(args.currentSlideObject, $(".portfolio-slider-holder"));

                        // if in case portfolio is not an image
                        prevargs = args;

                        // set item title
                        set_portfolio_item_title(args);
                        navigatoin_show_hide(args);

                        // if in case portfolio is not an image
                        check_portfolio_type(args, prevargs);

                        load_next_image(args.currentSlideNumber, args.sliderObject);
                        callback.call();
                    },
                    onSlideChange: function (args) {
                        curslideobj = args.currentSlideObject;

                        if (get_portfolio_type(args) === 'image') {
                            var imgobj = $('img', curslideobj).get(0);

                            unbindprevelement();

                            if ($(imgobj).hasClass('notloaded')) {
                                var datasrc = $(imgobj).data('src');
                                var img = new Image();

                                $(img).load(function () {
                                    $(imgobj).attr('src', datasrc);
                                    var size = $.new_get_image_container_size(imgobj, $(".portfolio-slider-holder"), options.imgfsmode);
                                    $(imgobj).css({
                                        'height': size[0],
                                        'width': size[1],
                                        'max-width': 'inherit'
                                    });
                                    doTranslate(imgobj, size[2] + "px", size[3] + "px");
                                    $(imgobj).addClass('loaded').removeClass('notloaded');

                                    // attach panning method after item loaded
                                    panme(curslideobj, $(".portfolio-slider-holder"));
                                    load_next_image(args.currentSlideNumber, args.sliderObject);
                                    iphone_loaded_event_fix();
                                }).attr('src', datasrc);
                            } else {
                                var size = $.new_get_image_container_size(imgobj, $(".portfolio-slider-holder"), options.imgfsmode);
                                $(imgobj).css({
                                    'height': size[0],
                                    'width': size[1],
                                    'max-width': 'inherit'
                                });
                                doTranslate(imgobj, size[2] + "px", size[3] + "px");
                            }

                            // attach panning method after item loaded
                            panme(curslideobj, $(".portfolio-slider-holder"));
                        }

                        // if in case portfolio is not an image
                        check_portfolio_type(args, prevargs);

                        // set item title
                        prevargs = args;
                        set_portfolio_item_title(args);
                        navigatoin_show_hide(args);

                    }
                });
                zoom_icon_change();
            };

            var show_single_portfolio = function (data, url, from, portfolioid) {
                options.imgfsmode = $(data).attr('data-expand');
                options.hidetitle = $(data).attr('data-hide-title');

                var portfoliotitle = $(".portfolionavbar .portfolionavtitle", data).text();
                var bottomtitle = $(".portfoliobottombar .portfolionavtitle", data).text();
                var content = $(".portfolioholderwrap", data).html();

                var portfolionext = $(".portfolionext a", data).attr('href');
                var portfolionextid = $(".portfolionext a", data).attr('data-id');
                var portfolionexttype = $(".portfolionext a", data).attr('data-type');

                var portfolioprev = $(".portfolioprev a", data).attr('href');
                var portfolioprevid = $(".portfolioprev a", data).attr('data-id');
                var portfolioprevtype = $(".portfolioprev a", data).attr('data-type');

                var title = portfoliotitle;

                /** portfolio share parameter **/
                var sharetitle = $(".portfoliolove", data).data('title');
                $(".portfoliolove").attr('data-title', sharetitle);

                var shareurl = $(".portfoliolove", data).data('url');
                $(".portfoliolove").attr('data-url', shareurl);

                var sharecover = $(".portfoliolove", data).data('cover');
                $(".portfoliolove").attr('data-cover', sharecover);

                /** manage content **/
                $(".portfolionext a").attr('href', portfolionext);
                $(".portfolionext a").attr('data-id', portfolionextid);
                $(".portfolionext a").attr('data-type', portfolionexttype);

                $(".portfolioprev a").attr('href', portfolioprev);
                $(".portfolioprev a").attr('data-id', portfolioprevid);
                $(".portfolioprev a").attr('data-type', portfolioprevtype);

                $(".portfolionavbar .portfolionavtitle").text(portfoliotitle);
                $(".portfoliobottombar .portfolionavtitle").text(bottomtitle);
                $(".portfolioholderwrap").html(content).imagesLoaded(function () {

                    /** show portfolio holder first to fix bug zero width **/
                    if (from == 'left')    $(".portfolioholderwrap").css("margin-left", "-100%");
                    else $(".portfolioholderwrap").css("margin-left", "100%");
                    $(".portfolioholderwrap").show();

                    /** single portfolio IOS slider **/
                    single_portfolio_arrange(function () {
                        /** portfolio content animation */
                        $(".portfolioholderwrap").show().animate({ 'margin-left': '0%' }, 800, 'easeInOutQuart');

                        /** navigation bar **/
                        if ($("body").hasClass('horizontalnav')) {
                            $(".portfolionavbar").fadeIn();
                        } else {
                            $(".portfolionavbar").show().animate({ 'top': '0' }, 800, 'easeInOutQuart');
                        }

                        if (options.hidetitle !== "1") {
                            $(".portfoliobottombar").show().animate({ 'bottom': '0' }, 800, 'easeInOutQuart');
                        }
                    });
                });

                /** save to history url **/
                if (is_history_exist()) {
                    document.title = title;
                    history.pushState({
                        'title': title,
                        'url': url,
                        'from': 'from'
                    }, title, url);
                }
            };

            var show_password_form = function (data, url, from, portfolioid) {
                var portfoliotitle = $(".portfolionavbar .portfolionavtitle", data).text();
                $(".portfolionavbar .portfolionavtitle").text(portfoliotitle);
                var content = $(".portfolioholderwrap", data).html();

                /** content **/
                var portfolionext = $(".portfolionext a", data).attr('href');
                var portfolionextid = $(".portfolionext a", data).attr('data-id');
                var portfolionexttype = $(".portfolionext a", data).attr('data-type');

                var portfolioprev = $(".portfolioprev a", data).attr('href');
                var portfolioprevid = $(".portfolioprev a", data).attr('data-id');
                var portfolioprevtype = $(".portfolioprev a", data).attr('data-type');

                /** manage content **/
                $(".portfolionext a").attr('href', portfolionext);
                $(".portfolionext a").attr('data-id', portfolionextid);
                $(".portfolionext a").attr('data-type', portfolionexttype);

                $(".portfolioprev a").attr('href', portfolioprev);
                $(".portfolioprev a").attr('data-id', portfolioprevid);
                $(".portfolioprev a").attr('data-type', portfolioprevtype);

                $(".portfolioholderwrap").html(content).imagesLoaded(function () {
                    /** portfolio hide element **/
                    $(".portfolioinfo").hide();
                    $(".portfoliolove").hide();
                    $(".portfolionavprev").hide();
                    $(".portfolionavnext").hide();
                    $(".portfoliozoom").hide();

                    /** hide loader **/
                    $(loader).fadeOut();

                    /** show portfolio holder first to fix bug zero width **/
                    if (from == 'left')    $(".portfolioholderwrap").css("margin-left", "-100%");
                    else $(".portfolioholderwrap").css("margin-left", "100%");
                    $(".portfolioholderwrap").show();

                    /** portfolio content animation */
                    $(".portfolioholderwrap").show().animate({ 'margin-left': '0%' }, 800, 'easeInOutQuart', function () {
                        $(".portfolio-form-body [name='password']").focus();
                    });

                    /** navigation bar **/
                    if ($("body").hasClass('horizontalnav')) {
                        $(".portfolionavbar").fadeIn();
                    } else {
                        $(".portfolionavbar").show().animate({ 'top': '0' }, 800, 'easeInOutQuart');
                    }
                    $(".portfoliobottombar").show().animate({ 'bottom': '0' }, 800, 'easeInOutQuart');
                });

                /** save to history url **/
                if (is_history_exist()) {
                    document.title = currentitle;
                    history.pushState({
                        'title': currentitle,
                        'url': url,
                        'from': 'from'
                    }, currentitle, url);
                }
            };

            var password_keypress = function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (code == 13) {
                    submit_password();
                }
            };

            var submit_password = function (event) {
                $(".portfolioinfo").removeClass("opened");

                /** navigation bar **/
                if ($("body").hasClass('horizontalnav')) {
                    $(".portfolionavbar").fadeOut();
                } else {
                    $(".portfolionavbar").show().animate({ 'top': '-50px' }, 800, 'easeInOutQuart');
                }
                $(".portfoliobottombar").animate({ 'bottom': '-45px' }, 800, 'easeInOutQuart');

                var url = $(".portfolio-form-body [name='url']").val();
                var password = $(".portfolio-form-body [name='password']").val();
                var portfolioid = $(".portfolio-form-body [name='portfolioid']").val();

                $(".portfolioholderwrap").show().animate({ 'margin-left': '100%' }, 800, 'easeInOutQuart', function () {
                    single_portfolio_executed(url, portfolioid, 'left', password);
                });
            };

            var single_portfolio_executed = function (url, portfolioid, from, password) {
                // reset all element first
                portfolionormalcontentpos();
                $(loader).fadeIn();

                $.ajax({
                    url: options.adminurl,
                    type: "post",
                    data: {
                        'pageid': $(".portfolioinputfilter [name='portfolioid']").val(),
                        'portfolioid': portfolioid,
                        'category': $(".portfolioinputfilter [name='category']").val(),
                        'password': password,
                        'action': 'get_single_portfolio_page'
                    },
                    success: function (data) {
                        if ($(".portfolio-content-slider", data).length) {
                            // single portfolio with slider content
                            show_single_portfolio(data, url, from, portfolioid);
                        } else if ($(".portfolio-password-overlay", data).length) {
                            // single portfolio with protected password
                            show_password_form(data, url, from, portfolioid);
                        }
                    }
                });
            };

            // reset
            var portfolionormalcontentpos = function () {
                $(".portfolionavbar").hide();
                if (!$('body').hasClass('horizontalnav')) {
                    $(".portfolionavbar").css('top', '-50px');
                }
                $(".portfoliobottombar").hide().css('bottom', '-45px');
                $(".portfolioholderwrap").hide().css("margin-left", "-100%");
                $(".portfolioinfo").show().removeClass("opened");
                $(".portfoliolove").show();
                $(".portfolionavnext").show();
                $(".portfolionavprev").show();
            };


            // disable touch event
            var touchmoveevent = function (disable) {
                if (disable) {
                    $('body').addClass('single-portfolio');
                } else {
                    setTimeout(function () {
                        $('body').removeClass('single-portfolio');
                    }, 800);
                }
            };

            var portfolioclicked = function (event) {
                event.preventDefault();
                var url = $(event.currentTarget).attr('href');
                var portfolioid = $(event.currentTarget).data('id');
                portfolionormalcontentpos();

                // content mulai dari side
                $(".portfoliowrapper").animate({ 'left': '-100%' }, 800, 'easeInOutQuart');
                $(".portfoliocontent").animate({ 'left': '0%' }, 800, 'easeInOutQuart', function () {
                    // disable touchmove
                    touchmoveevent(true);
                    single_portfolio_executed(url, portfolioid, 'left', null);
                });

                setTimeout(function () {
                    $("html, body").animate({ scrollTop: "0"}, 0);
                    $(".headermenu").animate({ 'top': "-" + $(".headermenu").outerHeight() }, 800, 'easeInOutQuart');
                    $(".portfoliopagingwrapper").fadeOut();
                    $(".noheadermenu .filterfloat, .horizontalnav .filterfloat").fadeOut();
                }, 400);
            };

            var portfolioclose = function (event) {
                event.preventDefault();

                // reenable touchmove
                touchmoveevent(false);

                $(".portfoliocontent").animate({ 'left': '100%' }, 800, 'easeInOutQuart');
                $(".portfoliowrapper").show().animate({ 'left': '0%' }, 800, 'easeInOutQuart', function () {
                    setTimeout(function () {
                        $(".portfolioholderwrap").html('');
                        do_portfolio_resize();
                        $(container).isotope('layout');
                    }, 500);
                });


                $(".headermenu").animate({ 'top': 0 }, 1000, 'easeInOutQuart');
                setTimeout(function () {
                    $(".portfoliopagingwrapper").fadeIn();
                    $(".noheadermenu .filterfloat, .horizontalnav .filterfloat").fadeIn();
                }, 400);

                /** return url **/
                if (is_history_exist()) {
                    document.title = currentitle;
                    history.pushState({
                        'title': currentitle,
                        'url': currenturl
                    }, currentitle, currenturl);
                }
            };

            var portfolionextprev = function (dir, url, id) {
                var marginleft = ( dir == 'left' ) ? '100%' : '-100%';
                $(".portfolioinfo").removeClass("opened");

                /** navigation bar **/
                if ($("body").hasClass('horizontalnav')) {
                    $(".portfolionavbar").fadeOut();
                } else {
                    $(".portfolionavbar").show().animate({ 'top': '-50px' }, 800, 'easeInOutQuart');
                }
                $(".portfoliobottombar").animate({ 'bottom': '-45px' }, 800, 'easeInOutQuart');

                $(".portfolioholderwrap").show().animate({ 'margin-left': marginleft }, 800, 'easeInOutQuart', function () {
                    single_portfolio_executed(url, id, dir, null);
                });
            };

            var portfolioprev = function (event) {
                var url = $(event.currentTarget).attr('href');
                var id = $(event.currentTarget).attr('data-id');

                portfolionextprev('right', url, id);
                event.preventDefault();
            };

            var portfolionext = function (event) {
                var url = $(event.currentTarget).attr('href');
                var id = $(event.currentTarget).attr('data-id');

                portfolionextprev('left', url, id);
                event.preventDefault();
            };

            var portfolioinfosize = function () {
                var ww = $(window).width();
                if (ww <= 640) return 100;
                if (ww <= 800) return 70;
                if (ww <= 1280) return 50;
                if (ww <= 1600) return 40;
                return 35;
            };

            var portfolioinfo = function (event) {
                event.preventDefault();
                var element = $(event.currentTarget);
                var parent = $(element).parent();

                if ($(parent).hasClass('opened')) {
                    $(parent).removeClass('opened');
                    $(".portfolio-content-wrapper").animate({ 'left': '100%' }, 1000, 'easeInOutQuart');
                } else {
                    $(parent).addClass('opened');
                    var size = portfolioinfosize();
                    var widthsize = size + "%";
                    var leftpost = ( 100 - size ) + "%";

                    $(".portfolio-content-wrapper").css('width', widthsize).animate({ 'left': leftpost }, 1000, 'easeInOutQuart');
                }
            };

            var portfoliolove = function (event) {
                event.preventDefault();
                $(".portfolio-share-overlay").fadeIn();
            };

            var portfolioloveclose = function (event) {
                event.preventDefault();
                $(".portfolio-share-overlay").fadeOut();
            };

            var opensharing = function (event, parent) {
                var element = $(".portfoliolove", parent);
                var target = event.currentTarget;
                var type = $(target).data('id');
                var urlsharing = '';

                var sharetitle = $(element).data('title');
                var shareurl = $(element).data('url');
                var sharecover = $(element).data('cover');

                switch (type) {
                    case "facebook":
                        urlsharing = "http://www.facebook.com/sharer.php?u=" + encodeURIComponent(shareurl);
                        break;
                    case "twitter":
                        urlsharing = "http://twitter.com/intent/tweet?url=" + encodeURIComponent(shareurl) + "&text=" + encodeURIComponent(sharetitle);
                        break;
                    case "googleplus":
                        urlsharing = "https://plus.google.com/share?url=" + encodeURIComponent(shareurl);
                        break;
                    case "pinterest":
                        urlsharing = "http://pinterest.com/pin/create/button/?url=" + encodeURIComponent(shareurl)
                            + "&media=" + encodeURIComponent(sharecover)
                            + "&description=" + encodeURIComponent(sharetitle);
                        break;
                }

                $.open_in_new_tab(urlsharing);
                return false;
            };

            /******************************
             * theather portfolio
             ******************************/

            var theatheroverflowhide = function () {
                $('html, body').addClass("pthoverflow");
            };

            var theatheroverflowshow = function () {
                $('html, body').removeClass("pthoverflow");
            };

            var showtheatherloader = function () {
                $(".theatherloader").fadeIn('slow');
            };

            var hidetheatherloader = function () {
                $(".theatherloader").fadeOut();
            };

            var resize_portfolio_theather = function () {
                var wrapper = $(".ptwrapper");
                var margin = 30;
                var ww = $(window).width();
                var wh = $(window).height();

                var ptheight = wh - (margin * 2);
                var ptwidth = ww - (margin * 2);
                $(wrapper).css({
                    'height': ptheight,
                    'width': ptwidth,
                    'margin-top': margin,
                    'margin-left': margin
                });
            };

            var show_single_theather_portfolio = function (data, portfolioid, url, from) {
                options.imgfsmode = $(data).attr('data-expand');
                options.hidetitle = $(data).attr('data-hide-title');

                var portfoliotitle = $(".portfolionavbar .portfolionavtitle", data).text();
                var portfoliocontent = $(".portfolio-content-slider", data).html();
                var portfoliodescription = $(".portfolio-content-wrapper", data).html();

                var portfolionext = $(".portfolionext a", data).attr('href');
                var portfolionextid = $(".portfolionext a", data).attr('data-id');
                var portfolionexttype = $(".portfolionext a", data).attr('data-type');

                var portfolioprev = $(".portfolioprev a", data).attr('href');
                var portfolioprevid = $(".portfolioprev a", data).attr('data-id');
                var portfolioprevtype = $(".portfolioprev a", data).attr('data-type');

                var title = portfoliotitle;

                /** portfolio share parameter **/
                var sharetitle = $(".portfoliolove", data).data('title');
                var shareurl = $(".portfoliolove", data).data('url');
                var sharecover = $(".portfoliolove", data).data('cover');
                $(".portfoliolove")
                    .attr('data-title', sharetitle)
                    .attr('data-url', shareurl)
                    .attr('data-cover', sharecover);

                // append content
                $(".ptcontent-wrapper").html(portfoliocontent);
                $(".ptdescription-wrapper").html(portfoliodescription);

                // append portfolio next prev
                $(".portfolionext a").attr('href', portfolionext);
                $(".portfolionext a").attr('data-id', portfolionextid);
                $(".portfolionext a").attr('data-type', portfolionexttype);

                $(".portfolioprev a").attr('href', portfolioprev);
                $(".portfolioprev a").attr('data-id', portfolioprevid);
                $(".portfolioprev a").attr('data-type', portfolioprevtype);


                if (options.hidetitle !== "1") {
                    $(".portfoliobottombar").show();
                } else {
                    $(".portfoliobottombar").hide();
                }

                $(".ptcontainer").imagesLoaded(function () {
                    // hide theather loader
                    hidetheatherloader();

                    /** show portfolio holder first to fix bug zero width **/
                    if (from == 'left')    $(".ptwrapper").css("left", "-100%");
                    else $(".ptwrapper").css("left", "100%");

                    single_portfolio_arrange(function () {
                        // $('.ptdescription').jScrollPane({mouseWheelSpeed: 50});
                        $(".ptwrapper").show().animate({ 'left': '0%' }, 800, 'easeInOutQuart');
                    });
                });

                /** save to history url **/
                if (is_history_exist()) {
                    document.title = title;
                    history.pushState({
                        'title': title,
                        'url': url,
                        'from': 'from'
                    }, title, url);
                }
            };

            var ptnormalize = function () {
                var ptdescriptionjpane = $(".ptdescription").jScrollPane().data().jsp;
                ptdescriptionjpane.destroy();
                $(".ptdescription").animate({ scrollTop: "0"}, 0);
                $(".ptwrapper").css('left', '-100%');
                $(".ptcontent-wrapper").html('');
                $(".ptdescription-wrapper").html('');
                $(".ptdescription").attr('style', '');
                $(".portfolioinfo").removeClass("opened");
                $(".ptcontent").attr('style', '');
            };

            var show_password_theather_form = function (data, url, from, portfolioid) {
                /** show portfolio holder first to fix bug zero width **/
                if (from == 'left')    $(".ptwrapper").css("left", "-100%");
                else $(".ptwrapper").css("left", "100%");

                // change the url first
                $(".portfolio-form-body [name='url']").val(url);
                $(".portfolio-form-body [name='portfolioid']").val(portfolioid);

                /** pt wrapper **/
                $(".ptwrapper").show().animate({ 'left': '0%' }, 800, 'easeInOutQuart', function () {
                    $(".portfolio-form-body [name='password']").focus();
                });

                /** save to history url **/
                if (is_history_exist()) {
                    document.title = currentitle;
                    history.pushState({
                        'title': currentitle,
                        'url': url,
                        'from': 'from'
                    }, currentitle, url);
                }
            };

            var pt_password_keypress = function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (code == 13) {
                    pt_submit_password();
                }
            };

            var pt_submit_password = function (event) {
                var url = $(".portfolio-form-body [name='url']").val();
                var password = $(".portfolio-form-body [name='password']").val();
                var portfolioid = $(".portfolio-form-body [name='portfolioid']").val();

                // clear value
                $(".portfolio-form-body [name='password']").val('');

                $(".ptwrapper").show().animate({ 'left': '100%' }, 800, 'easeInOutQuart', function () {
                    theatherajax(url, portfolioid, 'left', password);
                });
            };

            var theatherajax = function (url, portfolioid, from, password) {
                showtheatherloader();
                ptnormalize();

                $.ajax({
                    url: options.adminurl,
                    type: "post",
                    data: {
                        'pageid': $(".portfolioinputfilter [name='portfolioid']").val(),
                        'portfolioid': portfolioid,
                        'category': $(".portfolioinputfilter [name='category']").val(),
                        'password': password,
                        'action': 'get_single_portfolio_page'
                    },
                    success: function (data) {
                        if ($(".portfolio-content-slider", data).length) {
                            $(".ptcontainer").removeClass('loadpassword').addClass('loadcontent');
                            show_single_theather_portfolio(data, portfolioid, url, from);
                        } else if ($(".portfolio-password-overlay", data).length) {
                            $(".ptcontainer").removeClass('loadcontent').addClass('loadpassword');
                            show_password_theather_form(data, url, from, portfolioid);
                        }
                    }
                });
            };

            var ptportfolioclose = function (event) {
                event.preventDefault();
                $(window).unbind('resize', ptportfolioclose);
                $("body").removeClass('hideoverflow');

                $(".portfoliooverflow").fadeOut(function () {
                    ptnormalize();
                });

                if (is_history_exist()) {
                    document.title = currentitle;
                    history.pushState({
                        'title': currentitle,
                        'url': currenturl
                    }, currentitle, currenturl);
                }
            };

            var ptportfolionextprev = function (dir, url, id) {
                var leftpos = ( dir == 'left' ) ? '100%' : '-100%';

                $(".ptwrapper").show().animate({ 'left': leftpos }, 800, 'easeInOutQuart', function () {
                    theatherajax(url, id, dir, null);
                });
            };

            var ptportfolioprev = function (event) {
                var url = $(event.currentTarget).attr('href');
                var id = $(event.currentTarget).attr('data-id');

                ptportfolionextprev('right', url, id);
                event.preventDefault();
            };

            var ptportfolionext = function (event) {
                var url = $(event.currentTarget).attr('href');
                var id = $(event.currentTarget).attr('data-id');

                ptportfolionextprev('left', url, id);
                event.preventDefault();
            };

            var ptportfoliolove = function (event) {
                event.preventDefault();
                $(".pt-portfolio-share-overlay").fadeIn();
            };

            var ptportfolioloveclose = function (event) {
                event.preventDefault();
                $(".pt-portfolio-share-overlay").fadeOut();
            };


            var ptportfolioinfo = function (event) {
                event.preventDefault();
                var element = $(event.currentTarget);
                var parent = $(element).parent();

                if ($(parent).hasClass('opened')) {
                    $(parent).removeClass('opened');
                    $(".ptdescription").animate({ 'left': '100%' }, 1000, 'easeInOutQuart');
                } else {
                    $(parent).addClass('opened');
                    var size = portfolioinfosize();
                    var widthsize = size + "%";
                    var leftpost = ( 100 - size ) + "%";

                    $(".ptdescription").css('width', widthsize).animate({ 'left': leftpost }, 1000, 'easeInOutQuart');
                }
            };

            var theatherportfolio = function (event) {
                event.preventDefault();
                var url = $(event.currentTarget).attr('href');
                var portfolioid = $(event.currentTarget).data('id');
                $("body").addClass('hideoverflow');

                // preparation
                $(".portfoliooverflow").fadeIn(function () {
                    showtheatherloader();
                    resize_portfolio_theather();
                    theatherajax(url, portfolioid, 'left', null);
                });
            };


            var doTranslate = function (ele, x, y) {
                $(ele).css({
                    "left": x,
                    "top": y
                });
            };

            /*** mulai lagi ***/
            var zoom_icon_change = function() {
                if(options.imgfsmode === 'zoom') {
                    $(".portfoliozoom").addClass('alt');
                } else {
                    $(".portfoliozoom").removeClass('alt');
                }
            };

            var change_zoom_mode = function (){
                options.imgfsmode = (options.imgfsmode === 'zoom') ? 'fit' : 'zoom';
                zoom_icon_change();

                var imgobj = $('img', curslideobj).get(0);
                var size = $.new_get_image_container_size(imgobj, $(".portfolio-slider-holder"), options.imgfsmode);
                $(imgobj).animate({
                    'height': size[0],
                    'width': size[1],
                    'max-width': 'inherit',
                    'left' : size[2],
                    'top' : size[3]
                }, function(){
                    doTranslate(imgobj, size[2] + "px", size[3] + "px");
                    panme(curslideobj, $(".portfolio-slider-holder"));
                });
            };

            /*** theather portfolio end ***/

                // binding element
            $(window).bind("load resize", function (event) {
                do_portfolio_resize();
                changefilterwidth();
            });

            $(".portfoliopagingwrapper").on("click", ".pagedot li", pagingclicked);
            $(".portfoliofilterlist li, .filterfloatlist li").bind("click", filterclicked);

            if (options.expandtype == 'normal') {
                $(element).on("click", ".portfolioitem a", function (e) {
                    if ($(this).attr('data-type') == 'ajax') portfolioclicked(e);
                    else return true;
                });

                // content portfolio clicked
                $(".portfoliocontent").on("click", ".portfolioclose a", portfolioclose);
                $(".portfoliocontent").on("click", ".portfolioprev a", function (e) {
                    if ($(this).attr('data-type') == 'ajax') portfolioprev(e);
                    else return true;
                });
                $(".portfoliocontent").on("click", ".portfolionext a", function (e) {
                    if ($(this).attr('data-type') == 'ajax') portfolionext(e);
                    else return true;
                });
                $(".portfoliocontent").on("click", ".portfolioinfo a", portfolioinfo);
                $(".portfoliocontent").on('click', ".portfoliovideoclose a", close_portfolio_video);

                /** portfolio share **/
                $(".portfoliocontent").on("click", ".portfoliolove a", portfoliolove);
                $(".portfoliocontent").on("click", ".portfoliozoom a", change_zoom_mode);
                $(".portfoliocontent").on("click", ".share-close", portfolioloveclose);
                $(".portfoliocontent").on("click", ".share-body > div", function (e) {
                    opensharing(e, $(".portfoliocontent"));
                });

                // password bind
                $(".portfoliocontent").on("keypress", ".portfolio-form-body [name='password']", password_keypress);
                $(".portfoliocontent").on("click", ".portfolio-form-body .slider-button", submit_password);

            } else if (options.expandtype == 'theather') {
                $(element).on("click", ".portfolioitem a", function (e) {
                    if ($(this).attr('data-type') == 'ajax') theatherportfolio(e);
                    else return true;
                });

                // overflow clicked than close
                $(".portfoliooverflow, .ptpasswordform").bind("click", function (e) {
                    if (e.target !== this)    return;
                    ptportfolioclose(e);
                });

                // content portfolio clicked
                $(".ptcontainer").on("click", ".portfolioclose a", ptportfolioclose);
                $(".ptcontainer").on("click", ".portfolioprev a", function (e) {
                    if ($(this).attr('data-type') == 'ajax') ptportfolioprev(e);
                    else return true;
                });
                $(".ptcontainer").on("click", ".portfolionext a", function (e) {
                    if ($(this).attr('data-type') == 'ajax') ptportfolionext(e);
                    else return true;
                });
                $(".ptcontainer").on("click", ".portfolioinfo a", ptportfolioinfo);
                $(".ptcontainer").on('click', ".portfoliovideoclose a", close_portfolio_video);

                /** portfolio share **/
                $(".ptcontainer").on("click", ".portfoliolove a", ptportfoliolove);
                $(".ptcontainer").on("click", ".portfoliozoom a", change_zoom_mode);
                $(".pt-portfolio-share-overlay").on("click", ".share-close", ptportfolioloveclose);
                $(".pt-portfolio-share-overlay").on("click", ".share-body > div", function (e) {
                    opensharing(e, $(".ptcontainer"));
                });

                // password bind
                $(".ptcontainer").on("keypress", ".portfolio-form-body [name='password']", pt_password_keypress);
                $(".ptcontainer").on("click", ".portfolio-form-body .slider-button", pt_submit_password);
            } else {
                // do nothing
            }

            // extra nav popup disabled  on mobile
            if (!joption.ismobile) $.portfolio_popup();
            initialize_portfolio();
            filterlistclicked();

            $(window).stop().keydown(function(e) {
                if(e.keyCode == 32) { // right
                    change_zoom_mode();
                }
            });




        });
    };
})(jQuery);