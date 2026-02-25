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

    function closeSingleAlbumView() {
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

        $("body").css("overflow", "hidden");
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
        isLoadingAlbum = true;

        logDebug("clicked on an album");

        const albumId = $(this).closest("[data-album-id]").data("album-id");
        if (!albumId) {
            isLoadingAlbum = false;
            return;
        }

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


});
