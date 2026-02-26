$(function() {

    const debug = false;
    let isLoadingAlbum = false;
    let currentAlbumRequest = null;
    let pageScrollWasLocked = false;
    let pageScrollTopBeforeAlbum = 0;
    let bodyOverflowBeforeAlbum = "";
    let htmlOverflowBeforeAlbum = "";
    let bodyPositionBeforeAlbum = "";
    let bodyTopBeforeAlbum = "";
    let bodyWidthBeforeAlbum = "";

    function logDebug(message) {
        if (debug) {
            console.log(message);
        }
    }

    function isAlbumShowPath(pathname) {
        return /^\/album\/\d+$/.test(pathname || window.location.pathname);
    }

    function pushAlbumHistory(url) {
        if (!window.history || !window.history.pushState || !url) {
            return;
        }

        const currentUrl = window.location.pathname + window.location.search;
        if (url === currentUrl) {
            return;
        }

        window.history.pushState({ url: url }, "", url);
    }

    function adjustAlbumContainer() {
        const $albumView = $(".single-album-view");
        const $albumContainer = $(".single-album-container");

        if (!$albumView.length || !$albumContainer.length) {
            return;
        }

        // Layout/scroll is handled by CSS (container max-height + scrollable songs wrapper).
        // Clear any inline styles from previous logic.
        $albumContainer.css({
            height: "",
            overflow: "",
            "overflow-y": ""
        });
    }

    function closeSingleAlbumView(options) {
        const opts = options || {};

        if (!opts.fromHistory && isAlbumShowPath() && $(".albums-view").length && window.history && window.history.back) {
            window.history.back();
            return;
        }

        if (!opts.fromHistory && isAlbumShowPath() && !$(".albums-view").length) {
            window.location.href = "/album/";
            return;
        }

        $(".single-album-view").remove();
        unlockPageScroll();
    }

    function lockPageScroll() {
        if (pageScrollWasLocked) {
            return;
        }

        bodyOverflowBeforeAlbum = $("body").css("overflow");
        htmlOverflowBeforeAlbum = $("html").css("overflow");
        bodyPositionBeforeAlbum = $("body").css("position");
        bodyTopBeforeAlbum = $("body").css("top");
        bodyWidthBeforeAlbum = $("body").css("width");
        pageScrollTopBeforeAlbum = window.scrollY || $(window).scrollTop() || 0;

        $("html").css("overflow", "hidden");
        $("body").css({
            position: "fixed",
            top: (-pageScrollTopBeforeAlbum) + "px",
            width: "100%"
        });
        pageScrollWasLocked = true;
    }

    function unlockPageScroll() {
        if (!pageScrollWasLocked) {
            return;
        }

        $("body").css("overflow", bodyOverflowBeforeAlbum || "");
        $("html").css("overflow", htmlOverflowBeforeAlbum || "");
        $("body").css({
            position: bodyPositionBeforeAlbum || "",
            top: bodyTopBeforeAlbum || "",
            width: bodyWidthBeforeAlbum || ""
        });
        window.scrollTo(0, pageScrollTopBeforeAlbum);
        pageScrollWasLocked = false;
    }

    if ($(".single-album-view").length) {
        adjustAlbumContainer();
        lockPageScroll();
        if (window.history && window.history.replaceState) {
            window.history.replaceState({ url: window.location.pathname + window.location.search }, "", window.location.pathname + window.location.search);
        }
    }

    function openAlbumOverlay(albumId, options) {
        const opts = options || {};

        if (!albumId) {
            return;
        }

        if (isLoadingAlbum) {
            return;
        }
        isLoadingAlbum = true;

        const url = "/album/" + albumId;
        logDebug("url : " + url);

        if (currentAlbumRequest && currentAlbumRequest.readyState !== 4) {
            currentAlbumRequest.abort();
        }

        currentAlbumRequest = $.ajax({
            url: url,
            cache: true,
            success: function(data) {
                $(".single-album-view").remove();
                $(document.body).append(data);

                adjustAlbumContainer();
                lockPageScroll();
                if (!opts.fromHistory) {
                    pushAlbumHistory(url);
                }
                logDebug("scrollTop : " + $(window).scrollTop());
            },
            error: function() {
                logDebug("album load error");
            },
            complete: function() {
                isLoadingAlbum = false;
                currentAlbumRequest = null;
            }
        });
    }


    /**
     * show album
     */
    $(document).on("click", ".img-wrapper", function(e) {
        e.preventDefault();

        if ($(this).closest(".single-album-view").length) {
            return;
        }

        if (isLoadingAlbum) {
            return;
        }

        logDebug("clicked on an album");

        const albumId = $(this).closest("[data-album-id]").data("album-id");
        if (!albumId) {
            return;
        }

        openAlbumOverlay(albumId);
    });

    $(document).on("click", ".single-album-view", function(e) {
        if ($(e.target).closest(".single-album-container").length) {
            return;
        }

        closeSingleAlbumView();
    });

    $(document).on("keydown", function(e) {
        if (e.key === "Escape") {
            closeSingleAlbumView();
        }
    });

    $(document).on("soonic:closeAlbumOverlay", function() {
        closeSingleAlbumView();
    });

    $(window).on("popstate", function() {
        const pathname = window.location.pathname;

        if (isAlbumShowPath(pathname) && $(".albums-view").length) {
            const match = pathname.match(/^\/album\/(\d+)$/);
            if (match) {
                openAlbumOverlay(match[1], { fromHistory: true });
            }
        } else if (!isAlbumShowPath(pathname) && $(".single-album-view").length) {
            closeSingleAlbumView({ fromHistory: true });
        }
    });


});
