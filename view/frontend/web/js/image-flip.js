/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 *
 * Image flip hover functionality
 * Supports: Category pages, Widgets, Search, Related Products, CMS Blocks, Page Builder
 */
define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    return function (config) {
        if (!config.enabled) {
            return;
        }

        var animationSpeed = config.animationSpeed || 300;
        var animationType = config.animationType || 'fade';
        var desktopOnly = config.desktopOnly || false;
        var initTimeout = null;
        var mobileBreakpoint = 768;

        /**
         * Check if current device is desktop based on screen width
         * @returns {boolean}
         */
        function isDesktop() {
            return window.innerWidth >= mobileBreakpoint;
        }

        /**
         * Check if flip should be enabled based on device type
         * @returns {boolean}
         */
        function shouldEnableFlip() {
            if (!desktopOnly) {
                return true;
            }
            return isDesktop();
        }

        /**
         * Initialize flip images
         */
        function initFlipImages() {
            // Skip initialization if desktop only and we're on mobile
            if (!shouldEnableFlip()) {
                return;
            }

            var $flipContainers = $('[data-flip-image="true"]:not(.flip-initialized)');

            if ($flipContainers.length === 0) {
                return;
            }

            $flipContainers.each(function () {
                var $container = $(this);
                var $flipImage = $container.find('.flip-image');
                var flipUrl = $container.data('flip-url');

                // Mark as initialized to prevent double binding
                $container.addClass('flip-initialized');

                // Add desktop-only class if configured
                if (desktopOnly) {
                    $container.addClass('desktop-only');
                }

                // Set animation speed as CSS variable
                $container.css('--flip-animation-speed', animationSpeed + 'ms');

                // Lazy load flip image on first hover
                if ($flipImage.length && flipUrl && !$flipImage.attr('src')) {
                    $container.one('mouseenter', function () {
                        $flipImage.attr('src', flipUrl);
                    });
                }
            });

            // Bind hover events
            bindHoverEvents($flipContainers);
        }

        /**
         * Bind hover events to flip containers
         *
         * @param {jQuery} $containers
         */
        function bindHoverEvents($containers) {
            $containers.on('mouseenter.flipImage', function () {
                var $this = $(this);
                var $flipImage = $this.find('.flip-image');
                var flipUrl = $this.data('flip-url');

                // Ensure flip image is loaded
                if ($flipImage.length && flipUrl && !$flipImage.attr('src')) {
                    $flipImage.attr('src', flipUrl);
                }

                $this.addClass('is-flipped');
            });

            $containers.on('mouseleave.flipImage', function () {
                $(this).removeClass('is-flipped');
            });
        }

        /**
         * Debounced initialization to prevent multiple rapid calls
         */
        function debouncedInit() {
            if (initTimeout) {
                clearTimeout(initTimeout);
            }
            initTimeout = setTimeout(function () {
                initFlipImages();
            }, 100);
        }

        /**
         * Re-initialize for dynamically loaded content
         * This handles AJAX-loaded product grids, widgets, infinite scroll, Page Builder, CMS blocks, etc.
         */
        function observeDynamicContent() {
            // Use MutationObserver to detect new product images
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function (mutations) {
                    var shouldReinit = false;

                    mutations.forEach(function (mutation) {
                        if (mutation.addedNodes.length) {
                            mutation.addedNodes.forEach(function (node) {
                                if (node.nodeType === 1) {
                                    var $node = $(node);
                                    // Check if new node contains flip images or is a flip image container
                                    if ($node.find('[data-flip-image="true"]:not(.flip-initialized)').length ||
                                        ($node.is('[data-flip-image="true"]') && !$node.hasClass('flip-initialized'))) {
                                        shouldReinit = true;
                                    }
                                    // Also check for common product container classes (Page Builder, CMS, widgets)
                                    if ($node.find('.product-item, .product-image-container, [data-content-type="products"]').length) {
                                        shouldReinit = true;
                                    }
                                    // Check for Page Builder content types
                                    if ($node.is('[data-content-type]') || $node.find('[data-content-type]').length) {
                                        shouldReinit = true;
                                    }
                                }
                            });
                        }
                    });

                    if (shouldReinit) {
                        debouncedInit();
                    }
                });

                // Observe the entire document for changes
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }

            // Magento's contentUpdated event (used by AJAX, Knockout, UI components)
            $(document).on('contentUpdated', function () {
                debouncedInit();
            });

            // Page Builder specific events
            $(document).on('pagebuilder:renderAfter', function () {
                debouncedInit();
            });

            // Slick slider events
            $(document).on('init reInit afterChange', '.slick-slider', function () {
                debouncedInit();
            });

            // Owl Carousel events
            $(document).on('initialized.owl.carousel refreshed.owl.carousel', function () {
                debouncedInit();
            });

            // Swiper events
            $(document).on('swiperInit swiperSlideChangeEnd', function () {
                debouncedInit();
            });

            // Generic AJAX completion
            $(document).on('ajaxComplete', function () {
                setTimeout(debouncedInit, 200);
            });

            // Support for Amasty, Mirasvit, and other popular extensions
            $(document).on('amscroll_after_load contentUpdate catalog_product_list_loaded', function () {
                debouncedInit();
            });

            // Magento UI component loaded
            if (typeof require !== 'undefined') {
                try {
                    require(['Magento_Ui/js/lib/view/utils/async'], function (async) {
                        if (async && async.async) {
                            async.async('.product-item', function () {
                                debouncedInit();
                            });
                        }
                    });
                } catch (e) {
                    // Async module not available, ignore
                }
            }
        }

        /**
         * Preload flip images for better UX (optional)
         *
         * @param {jQuery} $containers
         */
        function preloadFlipImages($containers) {
            if (!$containers) {
                $containers = $('[data-flip-image="true"]');
            }
            $containers.each(function () {
                var $container = $(this);
                var flipUrl = $container.data('flip-url');

                if (flipUrl) {
                    var img = new Image();
                    img.src = flipUrl;
                }
            });
        }

        /**
         * Initialize with IntersectionObserver for lazy preloading
         */
        function initLazyPreload() {
            if (typeof IntersectionObserver === 'undefined') {
                return;
            }

            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        var $container = $(entry.target);
                        var $flipImage = $container.find('.flip-image');
                        var flipUrl = $container.data('flip-url');

                        if ($flipImage.length && flipUrl && !$flipImage.attr('src')) {
                            // Preload when in viewport
                            $flipImage.attr('src', flipUrl);
                        }

                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '100px'
            });

            $('[data-flip-image="true"]').each(function () {
                observer.observe(this);
            });
        }

        // Initialize on document ready
        $(function () {
            initFlipImages();
            observeDynamicContent();

            // Optional: Enable lazy preloading for better UX
            // initLazyPreload();
        });

        // Re-initialize when required elements are rendered (for Knockout/UI components)
        if (typeof require !== 'undefined') {
            require(['mage/apply/main'], function () {
                setTimeout(initFlipImages, 500);
            });
        }

        // Public API for manual re-initialization
        window.rollpixImageFlip = {
            init: initFlipImages,
            preload: preloadFlipImages,
            lazyPreload: initLazyPreload
        };

        return {
            init: initFlipImages,
            preload: preloadFlipImages,
            lazyPreload: initLazyPreload
        };
    };
});
