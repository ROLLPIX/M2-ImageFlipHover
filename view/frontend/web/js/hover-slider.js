/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 *
 * Hover Slider - PLP gallery navigation (Luma/jQuery version)
 *
 * Slide transition: track approach (images side by side, translateX moves the strip)
 * Fade/instant transition: overlay approach (images stacked, opacity toggles)
 * Overlays/track are created on first hover (not on page load).
 */
define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    return function (config) {
        if (!config.enabled || config.mode !== 'slider') {
            return;
        }

        var isTouchDevice = ('ontouchstart' in window) || window.matchMedia('(hover: none)').matches;
        var desktopOnly = config.desktopOnly || false;
        var initTimeout = null;

        function shouldEnable() {
            if (!desktopOnly) return true;
            return window.innerWidth >= 768;
        }

        function initSliders() {
            if (!shouldEnable()) return;
            $('[data-hover-slider="true"]:not(.slider-initialized)').each(function () {
                createSliderInstance($(this));
            });
        }

        function createSliderInstance($container) {
            var galleryData = $container.data('gallery');
            var sliderConfig = $container.data('slider-config');
            if (!galleryData || !sliderConfig) return;

            var images = typeof galleryData === 'string' ? JSON.parse(galleryData) : galleryData;
            var cfg = typeof sliderConfig === 'string' ? JSON.parse(sliderConfig) : sliderConfig;
            if (images.length < 2) return;

            var deviceCfg = isTouchDevice ? cfg.mobile : cfg.desktop;
            var isFlipOnly = images.length === 2 && cfg.hoverFlip;

            var state = {
                $container: $container,
                $viewport: $container.find('.hover-slider-viewport'),
                images: images,
                currentIndex: 0,
                cfg: cfg,
                deviceCfg: deviceCfg,
                transition: cfg.transition || 'fade',
                isFlipOnly: isFlipOnly,
                $slides: [],
                slidesCreated: false
            };

            $container.addClass('slider-initialized');

            // Preload all images into browser cache
            for (var i = 1; i < images.length; i++) {
                var img = new Image();
                img.src = images[i];
            }

            if (isFlipOnly) {
                bindHoverEvents(state);
                return;
            }

            if (deviceCfg.nav && deviceCfg.nav.indexOf('arrows') !== -1) createArrows(state);
            if (deviceCfg.nav && deviceCfg.nav.indexOf('mouse_tracking') !== -1 && !isTouchDevice) {
                $container.addClass('nav-mouse-tracking');
                initMouseTracking(state);
            }
            if (deviceCfg.nav && deviceCfg.nav.indexOf('swipe') !== -1) initSwipe(state);

            createIndicators(state);
            bindHoverEvents(state);
        }

        // =============================================
        // SLIDES — created on first interaction
        // =============================================

        function ensureSlidesCreated(state) {
            if (state.slidesCreated) return;
            state.slidesCreated = true;

            var $viewport = state.$viewport;
            var $baseImg = $viewport.find('img').first();
            var alt = $baseImg.attr('alt') || '';
            var isSlide = state.transition === 'slide';

            if (isSlide) {
                // TRACK approach: all images in a flex row
                var vpWidth = $viewport[0].offsetWidth;
                var vpHeight = $viewport[0].offsetHeight;
                var count = state.images.length;

                var $track = $('<div class="hover-slider-track"></div>').css({
                    display: 'flex',
                    width: (vpWidth * count) + 'px',
                    height: vpHeight + 'px',
                    transition: 'transform ' + (state.cfg.speed || 250) + 'ms ease-out'
                });

                // Move base image into track
                $baseImg.css({ width: vpWidth + 'px', height: vpHeight + 'px', objectFit: 'contain', flex: 'none' });
                $track.append($baseImg);
                state.$slides.push($baseImg);

                // Create additional slides
                for (var i = 1; i < count; i++) {
                    var $slide = $('<img/>').attr('src', state.images[i]).attr('alt', alt)
                        .css({ width: vpWidth + 'px', height: vpHeight + 'px', objectFit: 'contain', flex: 'none' });
                    $track.append($slide);
                    state.$slides.push($slide);
                }

                $viewport.empty().append($track);
                state.$track = $track;
            } else {
                // OVERLAY approach for fade/instant
                state.$slides.push($baseImg);
                for (var j = 1; j < state.images.length; j++) {
                    var $overlay = $('<img class="hover-slider-overlay"/>').attr('src', state.images[j]).attr('alt', alt)
                        .addClass('hover-slider-transition-' + state.transition);
                    $viewport.append($overlay);
                    state.$slides.push($overlay);
                }
            }
        }

        function goToSlide(state, index) {
            var count = state.images.length;
            if (state.cfg.loop) {
                index = ((index % count) + count) % count;
            } else {
                index = Math.max(0, Math.min(count - 1, index));
            }
            if (index === state.currentIndex && state.slidesCreated) return;

            ensureSlidesCreated(state);
            state.currentIndex = index;

            if (state.transition === 'slide' && state.$track) {
                var vpWidth = state.$viewport[0].offsetWidth;
                state.$track.css('transform', 'translateX(-' + (index * vpWidth) + 'px)');
            } else {
                // Fade/instant: show only target overlay
                state.$viewport.find('.hover-slider-overlay').removeClass('visible');
                if (index > 0 && state.$slides[index]) {
                    state.$slides[index].addClass('visible');
                }
            }

            updateIndicators(state);
            updateArrows(state);
        }

        // =============================================
        // HOVER
        // =============================================

        function bindHoverEvents(state) {
            state.$container.on('mouseenter.hoverSlider', function () {
                if (state.cfg.hoverFlip && state.currentIndex === 0) goToSlide(state, 1);
            });
            state.$container.on('mouseleave.hoverSlider', function () {
                if (state.cfg.autoReturn) goToSlide(state, 0);
            });
        }

        // =============================================
        // ARROWS
        // =============================================

        function createArrows(state) {
            var prevSvg = '<svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>';
            var nextSvg = '<svg viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>';

            var $prev = $('<button class="hover-slider-arrow hover-slider-arrow--prev" type="button">' + prevSvg + '</button>');
            var $next = $('<button class="hover-slider-arrow hover-slider-arrow--next" type="button">' + nextSvg + '</button>');

            $prev.on('click.hoverSlider', function (e) { e.preventDefault(); e.stopPropagation(); goToSlide(state, state.currentIndex - 1); });
            $next.on('click.hoverSlider', function (e) { e.preventDefault(); e.stopPropagation(); goToSlide(state, state.currentIndex + 1); });

            state.$container.append($prev).append($next);
            state.$arrows = { $prev: $prev, $next: $next };
            updateArrows(state);
        }

        function updateArrows(state) {
            if (!state.$arrows || state.cfg.loop) return;
            state.$arrows.$prev.toggleClass('disabled', state.currentIndex === 0);
            state.$arrows.$next.toggleClass('disabled', state.currentIndex === state.images.length - 1);
        }

        // =============================================
        // MOUSE TRACKING
        // =============================================

        function initMouseTracking(state) {
            var rafId = null;
            state.$container.on('mousemove.hoverSlider', function (e) {
                if (rafId) return;
                rafId = requestAnimationFrame(function () {
                    var rect = state.$container[0].getBoundingClientRect();
                    var relativeX = (e.clientX - rect.left) / rect.width;
                    var idx = Math.min(state.images.length - 1, Math.max(0, Math.floor(relativeX * state.images.length)));
                    goToSlide(state, idx);
                    rafId = null;
                });
            });
        }

        // =============================================
        // SWIPE
        // =============================================

        function initSwipe(state) {
            var startX = 0, startY = 0, dragging = false;
            var el = state.$container[0];
            el.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; startY = e.touches[0].clientY; dragging = true; }, { passive: true });
            el.addEventListener('touchmove', function (e) {
                if (!dragging) return;
                if (Math.abs(e.touches[0].clientX - startX) > Math.abs(e.touches[0].clientY - startY) && Math.abs(e.touches[0].clientX - startX) > 10) e.preventDefault();
            }, { passive: false });
            el.addEventListener('touchend', function (e) {
                if (!dragging) return;
                dragging = false;
                var dx = e.changedTouches[0].clientX - startX;
                if (Math.abs(dx) > 30) goToSlide(state, state.currentIndex + (dx < 0 ? 1 : -1));
            }, { passive: true });
        }

        // =============================================
        // INDICATORS
        // =============================================

        function createIndicators(state) {
            var type = state.deviceCfg.indicator;
            var pos = state.deviceCfg.indicatorPos;
            if (type === 'none') return;

            if (type === 'counter') {
                var cls = pos === 'top' ? 'hover-slider-counter--top' : 'hover-slider-counter--bottom';
                state.$counter = $('<span class="hover-slider-counter ' + cls + '">1/' + state.images.length + '</span>');
                state.$container.append(state.$counter);
                return;
            }

            var $w = $('<span class="hover-slider-indicators indicator-' + type + ' hover-slider-indicators--' + pos + '"></span>');
            var clickable = state.deviceCfg.nav && state.deviceCfg.nav.indexOf('dots_click') !== -1;

            for (var i = 0; i < state.images.length; i++) {
                var $item;
                if (type === 'bars') { $item = $('<span class="hover-slider-bar"></span>').css('width', (100 / state.images.length) + '%'); }
                else if (type === 'dots') { $item = $('<span class="hover-slider-dot"></span>'); }
                else if (type === 'pills') { $item = $('<span class="hover-slider-pill"></span>'); }
                if (i === 0) $item.addClass('active');
                if (clickable) { $item.addClass('clickable').data('index', i); }
                $w.append($item);
            }

            if (clickable) $w.on('click', '.clickable', function () { goToSlide(state, $(this).data('index')); });
            state.$container.append($w);
            state.$indicators = $w;
        }

        function updateIndicators(state) {
            if (state.$indicators) {
                state.$indicators.children().removeClass('active');
                state.$indicators.children().eq(state.currentIndex).addClass('active');
            }
            if (state.$counter) state.$counter.text((state.currentIndex + 1) + '/' + state.images.length);
        }

        // =============================================
        // DYNAMIC CONTENT
        // =============================================

        function debouncedInit() {
            if (initTimeout) clearTimeout(initTimeout);
            initTimeout = setTimeout(initSliders, 100);
        }

        function observeDynamicContent() {
            if (typeof MutationObserver !== 'undefined') {
                new MutationObserver(function (muts) {
                    for (var i = 0; i < muts.length; i++) {
                        if (muts[i].addedNodes.length) { debouncedInit(); return; }
                    }
                }).observe(document.body, { childList: true, subtree: true });
            }
            $(document).on('contentUpdated pagebuilder:renderAfter', debouncedInit);
            $(document).on('init reInit afterChange', '.slick-slider', debouncedInit);
            $(document).on('initialized.owl.carousel refreshed.owl.carousel swiperInit swiperSlideChangeEnd', debouncedInit);
            $(document).on('ajaxComplete', function () { setTimeout(debouncedInit, 200); });
            $(document).on('amscroll_after_load contentUpdate catalog_product_list_loaded', debouncedInit);
        }

        $(function () { initSliders(); observeDynamicContent(); });
        if (typeof require !== 'undefined') { require(['mage/apply/main'], function () { setTimeout(initSliders, 500); }); }

        window.rollpixHoverSlider = { init: initSliders };
        return { init: initSliders };
    };
});
