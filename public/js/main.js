$(function() {
    'use strict';

    const debug = false;
    let screenWidth = $(window).width();
    let mobileMenuState = 'closed';
    let openView = null;
    let scanLoop = null;
    let isHistoryNavigation = false;

    function logDebug(message) {
        if (debug) {
            console.log(message);
        }
    }

    function pushHistoryIfNeeded(url) {
        if (isHistoryNavigation || !window.history || !window.history.pushState || !url) {
            return;
        }

        const currentUrl = window.location.pathname + window.location.search;
        if (url === currentUrl) {
            return;
        }

        window.history.pushState({ url: url }, "", url);
    }

    function upsertLibraryView(data) {
        const $incoming = $('<div>').html(data).find('.library-view').first();
        if (!$incoming.length) {
            return;
        }

        if ($('.library-view').length) {
            $('.library-view').replaceWith($incoming);
        } else {
            $(document.body).append($incoming);
        }
    }

    function upsertSingleView(data, selector) {
        const $incoming = $('<div>').html(data).find(selector).first();
        if (!$incoming.length) {
            return false;
        }

        if ($(selector).length) {
            $(selector).replaceWith($incoming);
        } else {
            $(document.body).append($incoming);
        }

        return true;
    }

    function setRadioListNavState() {
        $('#navigation-library, #navigation-albums, #navigation-radio-new, #navigation-settings').css('display', 'list-item');
        $('#navigation-radios, #navigation-random, #navigation-search-form').css('display', 'none');
    }

    function setRadioFormNavState() {
        $('#navigation-library, #navigation-albums, #navigation-radios, #navigation-settings').css('display', 'list-item');
        $('#navigation-random, #navigation-radio-new, #navigation-search-form').css('display', 'none');
    }

    function hideRadioViews() {
        $('.radios-view, .radio-new-view, .radio-show-view, .radio-edit-view').css('display', 'none');
    }

    function activateRadioSubview(selector) {
        $(document).trigger("soonic:closeAlbumOverlay");
        $(openView).css('display', 'none');
        $('.library-view').css('display', 'none');
        hideRadioViews();
        $(selector).css('display', 'block');
        openView = selector;
        setSongInfoSize();
    }

    function loadRadioPage(url) {
        $.ajax({
            url: url,
            cache: false,
            success: function(data) {
                upsertRadiosView(data);
                activateRadioSubview('.radios-view');
            },
            error: function() {
                logDebug("radios pagination error");
            }
        });
    }

    function loadRadioSubview(url, selector) {
        $.ajax({
            url: url,
            cache: false,
            success: function(data) {
                if (!upsertSingleView(data, selector)) {
                    return;
                }
                activateRadioSubview(selector);
            },
            error: function() {
                logDebug("radio subview load error");
            }
        });
    }

    function restoreViewFromLocation() {
        const path = window.location.pathname;
        const fullUrl = path + window.location.search;

        isHistoryNavigation = true;

        if (path === '/') {
            $('#library-button').trigger('click');
        } else if (path === '/album/') {
            $('#albums-button').trigger('click');
        } else if (/^\/album\/\d+$/.test(path)) {
            $('#albums-button').trigger('click');
        } else if (path === '/radio/') {
            loadRadioPage(fullUrl);
            setRadioListNavState();
        } else if (path === '/radio/new') {
            $('#radio-new-button').trigger('click');
        } else if (/^\/radio\/\d+\/edit$/.test(path)) {
            loadRadioSubview(fullUrl, '.radio-edit-view');
            setRadioFormNavState();
        } else if (/^\/radio\/\d+$/.test(path)) {
            loadRadioSubview(fullUrl, '.radio-show-view');
            setRadioFormNavState();
        } else if (path === '/settings/') {
            $('#settings-button').trigger('click');
        }

        isHistoryNavigation = false;
    }

    _init();
    if (window.history && window.history.replaceState) {
        window.history.replaceState({ url: window.location.pathname + window.location.search }, "", window.location.pathname + window.location.search);
    }

    // Recompute after full load/fonts: prevents occasional wrong topbar layout on cached reloads.
    $(window).on('load', function() {
        setSongInfoSize();
    });

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(function() {
            setSongInfoSize();
        });
    }

    $(window).on("popstate", function() {
        restoreViewFromLocation();
    });

    // Accessibility: make custom role=button controls keyboard-activable.
    $(document).on("keydown", '[role="button"]', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).trigger('click');
        }
    });

    // -- on resize
    let resizeTimer;
    $(window).on("resize", function() {
        if(resizeTimer) {
            window.clearTimeout(resizeTimer);
        }
        resizeTimer = window.setTimeout(function() {
            screenWidth = $(window).width();
            _init();
        }, 30);
    });


    /**
     * -- Ajax navigation -----------------------------------------------------
     */

    /**
     * Load library page
     */
    $(document).on("click", "#library-button", function(e) {

        e.preventDefault();
        $(document).trigger("soonic:closeAlbumOverlay");

        $(openView).css('display', 'none');
        openView = null;

        if ($('.library-view').length) {
            $('.library-view').css('display', 'block');
        } else {
            $.ajax({
                url: '/',
                cache: true,
                success: function(data) {
                    upsertLibraryView(data);
                    $('.library-view').css('display', 'block');
                    setSongInfoSize();
                },
                error: function() {
                    logDebug("library load error");
                }
            });
        }

        $('#navigation-random, #navigation-albums, #navigation-radios, #navigation-settings, #navigation-search-form' ).css('display', 'list-item');
        $('#navigation-library, #navigation-radio-new').css('display', 'none');
        setSongInfoSize();
        pushHistoryIfNeeded($(this).attr('href') || '/');

        if (debug) {
            console.log('clicked on library');
            console.log("- openView = " + openView);
        }
    });

    /**
     * Load albums page
     */
    const observer = lozad();
    observer.observe();

    // $(document).on("click", ".album-container-content", function(e) {
    //     $(this).children(".lozad").fadeOut('fast').delay(3000).fadeIn('fast');
    // });

    $(document).on("click", "#albums-button", function(e) {

        e.preventDefault();
        $(document).trigger("soonic:closeAlbumOverlay");

        $(openView).css('display', 'none');
        $('.library-view').css('display', 'none');

        if ($('.albums-view').length) {
            $('.albums-view').css('display', '');
        } else {
            const url = "/album/";
            $.ajax({
                url: url,
                cache: true,
                success: function(data) {
                    $('.library-view').css('display', 'none');
                    $(document.body).append(data);
                    observer.observe();
                },
                error: function() {
                    logDebug("albums load error");
                }
            });
        }
        $('#navigation-library, #navigation-radios, #navigation-settings').css('display', 'list-item');
        $('#navigation-albums, #navigation-radio-new, #navigation-search-form, #navigation-random').css('display', 'none');
        openView = '.albums-view';
        pushHistoryIfNeeded($(this).attr('href') || '/album/');

        if (debug) {
            console.log('clicked on albums');
            console.log("- openView = " + openView);
        }
    });


    /**
     * Load radios page
     */
    $(document).on("click", "#radio-button", function(e) {

        e.preventDefault();
        $(document).trigger("soonic:closeAlbumOverlay");

        $(openView).css('display', 'none');
        $('.library-view').css('display', 'none');

        if ($('.radios-view').length) {
            activateRadioSubview('.radios-view');
        } else {
            const url = "/radio/";

            $.ajax({
                url: url,
                cache: true,
                success: function(data) {
                    upsertRadiosView(data);
                    activateRadioSubview('.radios-view');
                },
                error: function() {
                    logDebug("radios load error");
                }
            });
        }
        setRadioListNavState();
        pushHistoryIfNeeded($(this).attr('href') || '/radio/');

        if (debug) {
            console.log('clicked on radio');
            console.log("- openView = " + openView);
        }
    });

    /**
     * Load one radios pagination page
     */
    $(document).on("click", ".radios-pagination a", function(e) {
        e.preventDefault();

        const url = $(this).attr('href');
        if (!url) {
            return;
        }

        loadRadioPage(url);
        pushHistoryIfNeeded(url);
    });

    $(document).on("click", ".radios-view .radio-edit-link", function(e) {
        const url = $(this).attr('href');
        if (!url) {
            return;
        }

        e.preventDefault();
        loadRadioSubview(url, '.radio-edit-view');
        setRadioFormNavState();
        pushHistoryIfNeeded(url);
    });

    $(document).on("click", ".radio-show-view a, .radio-edit-view a, .radio-new-view a", function(e) {
        const url = $(this).attr('href');
        if (!url || !/^\/radio(?:\/\d+(?:\/edit)?|\/new|\/)?(?:\?.*)?$/.test(url)) {
            return;
        }

        e.preventDefault();

        if (/^\/radio\/(?:\?.*)?$/.test(url) || /^\/radio\/\?/.test(url)) {
            loadRadioPage(url);
            setRadioListNavState();
        } else if (/^\/radio\/new$/.test(url)) {
            loadRadioSubview(url, '.radio-new-view');
            setRadioFormNavState();
        } else if (/^\/radio\/\d+\/edit$/.test(url)) {
            loadRadioSubview(url, '.radio-edit-view');
            setRadioFormNavState();
        } else if (/^\/radio\/\d+$/.test(url)) {
            loadRadioSubview(url, '.radio-show-view');
            setRadioFormNavState();
        } else {
            return;
        }

        pushHistoryIfNeeded(url);
    });


    /**
     * Load new radio page
     */
    $(document).on("click", "#radio-new-button", function(e) {

        e.preventDefault();
        $(document).trigger("soonic:closeAlbumOverlay");

        $(openView).css('display', 'none');
        $('.library-view').css('display', 'none');

        if ($('.radio-new-view').length) {
            activateRadioSubview('.radio-new-view');
        } else {
            const url = "/radio/new";

            $.ajax({
                url: url,
                cache: true,
                success: function(data) {
                    upsertSingleView(data, '.radio-new-view');
                    activateRadioSubview('.radio-new-view');
                },
                error: function() {
                    logDebug("radio new load error");
                }
            });
        }
        setRadioFormNavState();
        pushHistoryIfNeeded($(this).attr('href') || '/radio/new');

        if (debug) {
            console.log('clicked on new radio');
            console.log("- openView = " + openView);
        }
    });


    /**
     * Load settings page
     */
    $(document).on("click", "#settings-button", function(e) {

        e.preventDefault();
        $(document).trigger("soonic:closeAlbumOverlay");

        $(openView).css('display', 'none');
        $('.library-view').css('display', 'none');

        if ($('.settings-view').length) {
            $('.settings-view').css('display', 'block');
        } else {
            const url = "/settings/";

            $.ajax({
                url: url,
                cache: true,
                success: function(data) {
                    $(document.body).append(data);
                },
                error: function() {
                    logDebug("settings load error");
                }
            });
        }
        $('#navigation-settings, #navigation-random, #navigation-search-form, #navigation-radio-new').css('display', 'none');
        $('#navigation-library, #navigation-albums, #navigation-radios').css('display', 'list-item');
        setSongInfoSize();
        openView = '.settings-view';
        pushHistoryIfNeeded($(this).attr('href') || '/settings/');

        if (debug) {
            console.log('clicked on settings');
            console.log("- openView = " + openView);
        }
    });


    /**
     * Load random songs
     * Updates the songs panel
     */
    $(document).on("click", "#random-button", function(e) {

        e.preventDefault();

        const url = "/songs/random";

        $.ajax({
            url: url,
            cache: true,
            success: loadSongPanel,
            error: function() {
                logDebug("random songs load error");
            }
        });

        if (debug) {
            console.log('clicked on random songs');
            console.log("- openView = " + openView);
        }
    });


    /**
     * Returns a album list for an artist or remove album list (close)
     * Updates the navigation panel
     */
    $(document).on("click", ".artists-navigation a.artist", function(e) {

        e.preventDefault();

        const url = $(this).attr("href");

        if ($(this).next('ul').length) {
            $(this).next().remove();
        } else {
            $.get({
                url: url,
                context: this,
                cache: true,
                success: function(data) {
                    $(this).after(data);
                }
            });
        }
        $(".active").removeClass("active");
        $(this).addClass('active');

        if (debug) {
            console.log('clicked on an artist in artist nav');
        }
    });


    /**
     * Filters the artists list
     * Updates the navigation panel
     */
    let lastval = "";
    let timeout = null;

    $(document).on("keyup", "input[name=filter]", function() {

        const url = '/artist/filter/';

        // -- if input is cleared
        if (this.value.length === 0 && lastval.length > 0) {

            $.get({
                url: url,
                cache: true,
                success: function(data) {
                    $("#artists-nav").remove();
                    $("nav.artists-navigation").append(data);
                }
            });
        }

        // -- if input has not changed
        if (this.value === lastval) {
            return;
        }

        lastval = this.value;

        // -- if input has less than 3 chars
        if (this.value.length < 3) {
            return;
        }

        const filter = this.value;

        if (timeout) {
            clearTimeout(timeout);
        }

        timeout = setTimeout(function() {

            $.get({
                url: url + filter,
                cache: true,
                success: function(data) {
                    $("#artists-nav").remove();
                    $("nav.artists-navigation").append(data);
                }
            });
        }, 300);

        if (debug) {
            console.log('filetered artists');
        }
    });


    /**
     * Returns the songs from an album
     * Updates the songs panel
     */
    $(document).on("click", ".artists-navigation a.song", function(e) {

        e.preventDefault();

        const url = $(this).attr("href");

        $.get({
            url: url,
            cache: true,
            success: function(data) {
                $("#songs tbody").remove();
                $("#songs").append(data);
            }
        });
        $(".active").removeClass("active");
        $(this).addClass('active');

        if (screenWidth < 1024) {
            $(".artists-navigation").css('display', 'none');
            $(".songs").css('display', 'block');
            $(".songs").css('width', '100%');
            $(".playlist").css('display', 'none');
            $(".mobile-songs-to-artists-button").css('display', 'initial');
            $(".mobile-songs-to-playlist-button").css('display', 'block');
        }

        if (debug) {
            console.log('clicked on an album in artist nav');
        }
    });


    /**
     * Returns search results
     * Updates the songs panel
     * TODO: search on smallscreen
     */
    $(document).on("click", "#search-button", function(e) {

        e.preventDefault();

        if ($("#form-keyword").val().length < 3) {
            $("#form-keyword").val('');
            return;
        }

        const form = $('#search-form');

        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(),
            success: loadSongPanel,
            error: function() {
                logDebug("search error");
            }
        });

        if (debug) {
            console.log('clicked on search');
        }
    });


    /**
     * Helper for the two methods above
     */
    function loadSongPanel(data) {

        $("#songs tbody").remove();
        $("#songs").append(data);
        // if ($("#top-bar-nav").hasClass('is-active')) {
        //     $("#top-bar-nav").toggleClass('is-active');
        //     $(".top-nav").toggleClass('is-active');
        //     $(".songs").css('display', 'initial');
        //     $(".playlist").css('display', 'none');
        //     $(".artists-navigation").css('display', 'none');
        //     $(".mobile-songs-to-artists-button").css('display', 'initial');
        //     $(".mobile-songs-to-playlist-button").css('display', 'initial');
        //     hamburger.toggleClass("is-active");
        //
        //     if (debug) {
        //         console.log('after hamburger');
        //     }
        //
        //     mobileMenuState = mobileMenuState == 'closed' ? 'open' : 'closed';
        // }

        if (debug) {
            console.log('in loadSongPanel');
        }
    }

    function upsertRadiosView(data) {
        const $incoming = $('<div>').html(data).find('.radios-view').first();
        if (!$incoming.length) {
            return;
        }

        if ($('.radios-view').length) {
            $('.radios-view').replaceWith($incoming);
        } else {
            $(document.body).append($incoming);
        }
    }


    /**
     * Start scan
     */
    $(document).on("click", "#scan-button", function(e) {

        e.preventDefault();
        const $button = $(this);
        const scanUrl = $button.attr('href') || '/scan/';
        const csrfToken = $button.data('csrf-token') || '';
        const initialLabel = $button.text();
        $button.data('initial-label', initialLabel);

        if ($button.hasClass('running')) {
            return;
        }

        $button.addClass('running');

        $.ajax({
            type: 'POST',
            url: scanUrl,
            cache: true,
            dataType: 'json',
            headers: csrfToken ? { 'X-CSRF-Token': csrfToken } : {},
            success: function(data) {
                if (data && data.status === 'already_running') {
                    $button.addClass('running');
                }

                if (data && (data.status === 'running' || data.status === 'already_running')) {
                    if (!scanLoop) {
                        scanLoop = setInterval(scanTimer, 1000);
                    }
                } else {
                    $button.removeClass('running');
                    $button.text($button.data('initial-label') || initialLabel);
                }
            },
            error: function() {
                $button.removeClass('running');
                $button.text($button.data('initial-label') || initialLabel);
            }
        });

        $("#num-files").text("0");
        $("#num-artists").text("0");
        $("#num-albums").text("0");
        $button.text('scanning');

        if (debug) {
            console.log('clicked on scan');
        }
    });


    /**
     * Submit setting form
     */
    $(document).on("click", "#settings-form-button", function(e) {
        e.preventDefault();
        $('#settings-form').trigger('submit');
    });

    $(document).on("submit", "#settings-form", function(e) {

        e.preventDefault();

        const form = $(this);

        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function(data) {

                let href = "";
                const cacheBuster = Date.now();
                if ($('#screen-theme-css').length) {
                    href = "/css/themes/" + data.config.theme + "/screen.css?v=" + cacheBuster;
                    $('#screen-theme-css').attr('href', href );
                }
                if ($('#layout-theme-css').length) {
                    href = "/css/themes/" + data.config.theme + "/layout.css?v=" + cacheBuster;
                    $('#layout-theme-css').attr('href', href );
                }

                // Refresh translated server-rendered fragments without full-page navigation.
                $.get({
                    url: '/settings/',
                    cache: false,
                    success: function(html) {
                        const $html = $('<div>').html(html);
                        const $newTopbar = $html.find('.topbar').first();
                        const $newSettings = $html.find('.settings-view').first();

                        if ($newTopbar.length) {
                            $('.topbar').replaceWith($newTopbar);
                        }

                        if ($newSettings.length) {
                            $('.settings-view').replaceWith($newSettings);
                            openView = '.settings-view';
                        }

                        setSongInfoSize();
                    }
                });
            },
            error:function() {
                logDebug("settings submit error");
            }
        });

        if (debug) {
            console.log('submitted settings form');
        }
    });


    /**
     * reload artist artist on clear filter form
     */
    $(document).on("click", ".filter-form .input-reset", function(e) {
        const url = '/artist/filter/';
        $.get({
            url: url,
            cache: true,
            success: function(data) {
                $("#artists-nav").remove();
                $("nav.artists-navigation").append(data);
            }
        });
        $('.filter-input').focus();

        if (debug) {
            console.log('cleared artist filter');
        }
    });


    /**
     * set focus on search input on clear
     */
    $(document).on("click", "#search-form .input-reset", function(e) {
        $('#form-keyword').focus();
    });


    /**
     * empty playlist
     */
    $(document).on("click", ".icon-trash", function(e) {
        if ($("#playlist tbody tr").length) {
            $("#playlist tbody tr").remove();
            $("#playlist-num-files").text(0);
            $("#playlist-file").text('file');
            $("#playlist-duration").text('00:00');
            $("#playlist-infos").css('display', 'none');
        }

        if (debug) {
            console.log('clicked on empty palylist');
        }
    });


    /**
     * Forward to songs list
     */
    $(document).on("click", ".mobile-artists-to-songs-button", function(e) {

        $(".songs").css('display', 'block');
        $(".playlist").css('display', 'none');
        $(".artists-navigation").css('display', 'none');
        $(".mobile-artists-to-songs-button").css('display', 'none');
        $(".mobile-songs-to-artists-button").css('display', 'initial');
        $(".mobile-songs-to-playlist-button").css('display', 'initial');

        if (debug) {
            console.log('show songs list (forward)');
        }
    });


    /**
     * Back to artists list
     */
    $(document).on("click", ".mobile-songs-to-artists-button", function(e) {

        $(".songs, .playlist").css('display', 'none');
        $(".artists-navigation").css('display', 'block');
        $(".mobile-songs-to-artists-button").css('display', 'none');
        $(".mobile-songs-to-playlist-button").css('display', 'none');
        $(".mobile-artists-to-songs-button").css('display', 'initial');

        if (debug) {
            console.log('show artists list');
        }
    });


    /**
     * Forward to playlist
     */
    $(document).on("click", ".mobile-songs-to-playlist-button", function(e) {
        $(".songs").css('display', 'none');
        $(".playlist").css('display', 'initial');
        $(".mobile-artists-to-songs-button").css('display', 'none');
        $(".mobile-songs-to-playlist-button").css('display', 'none');
        $(".mobile-playlist-to-songs-button").css('display', 'initial');
        logDebug('show playlist');
    });


    /**
     * Back to songs list
     */
    $(document).on("click", ".mobile-playlist-to-songs-button", function(e) {
        $(".songs").css('display', 'initial');
        $(".playlist").css('display', 'none');
        $(".mobile-songs-to-playlist-button").css('display', 'initial');

        if (debug) {
            console.log('show songs list (backward)');
        }
    });


    /**
     * handle mobile menu
     */
    $(".hamburger").on("click", function(e) {
        e.preventDefault();

        $(".topbar-nav, .top-nav, .hamburger").toggleClass("is-active");
        mobileMenuState = mobileMenuState === 'closed' ? 'open' : 'closed';

        if (mobileMenuState === 'open') {
            setTimeout(function() {
                $(document).one("click.mobileMenu", function(event) {
                    const $target = $(event.target);
                    if ($target.closest(".topbar-nav, .top-nav, .hamburger").length) {
                        return;
                    }

                    $(".topbar-nav, .top-nav, .hamburger").removeClass("is-active");
                    mobileMenuState = 'closed';
                });
            }, 100);
        }

        if (debug) {
            console.log('clicked on mobile menu');
        }
    });


    /**
     * check if we are scanning
     */
    if (window.location.pathname === '/settings/') {
        $.get({
            url: '/scan/progress',
            cache: true,
            success: function(data) {
                if (data.status === 'running') {
                    const $scanButton = $("#scan-button");
                    if (!$scanButton.hasClass('running')) {
                        $scanButton.toggleClass('running');
                    }
                    $scanButton.data('initial-label', $scanButton.data('initial-label') || $scanButton.text());
                    $scanButton.text('scanning');
                    if (!scanLoop) {
                        scanLoop = setInterval(scanTimer, 1000);
                    }
                }
            }
        });

        if (debug) {
            console.log('check if we are scanning');
        }
    }

    if (window.location.pathname !== '/') {
        restoreViewFromLocation();
    }


    /**
     * update library infos while we are scanning
     */
    function scanTimer() {
        $.get({
            url: '/scan/progress',
            cache: true,
            success: function(data) {
                if (data.status === 'stopped') {
                    if (scanLoop) {
                        clearInterval(scanLoop);
                        scanLoop = null;
                    }
                    const $scanButton = $("#scan-button");
                    if ($scanButton.hasClass('running')) {
                        $scanButton.toggleClass('running');
                    }
                    $scanButton.text($scanButton.data('initial-label') || 'scan');
                }
                $("#num-files").text(data.data.song);
                $("#num-artists").text(data.data.artist);
                $("#num-albums").text(data.data.album);
            }
        });
    }

    function _init() {
        setSongInfoSize();
        setFilterInputSize();
    }

    function setSongInfoSize() {
        const $songInfo = $('.song-info');
        if (!$songInfo.length) {
            return;
        }

        const logoWidth = getElementOuterWidth($('.logo').first());
        const playerWidth = getElementOuterWidth($('#player-container'));
        const sideWidth = screenWidth < 1024
            ? getElementOuterWidth($('.hamburger').first())
            : getElementOuterWidth($('.topbar-nav').first());

        const rawWidth = screenWidth - (logoWidth + playerWidth + sideWidth + 50);
        const width = Math.max(140, Math.floor(rawWidth));

        $songInfo.css({
            width: 'auto',
            maxWidth: width + 'px'
        });
    }

    function setFilterInputSize() {
        let width;
        if (screenWidth < 1024) {
            const buttonWidth = getElementOuterWidth($('#search-button'));
            width = (screenWidth - buttonWidth );
        }
        $('.form-element-container').width(width);
    }

    function getElementOuterWidth($element) {
        if (!$element.length) {
            return 0;
        }

        // jQuery 4 safe: avoid dependency on legacy jquery.actual plugin.
        if ($element.is(':visible')) {
            return $element.outerWidth() || 0;
        }

        var element = $element.get(0);
        var style = element.style;
        var original = {
            display: style.display,
            visibility: style.visibility,
            position: style.position
        };

        style.visibility = 'hidden';
        style.display = 'block';
        style.position = 'absolute';

        var width = $element.outerWidth() || 0;

        style.display = original.display;
        style.visibility = original.visibility;
        style.position = original.position;

        return width;
    }
});
