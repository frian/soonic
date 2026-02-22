$(function() {

    const debug = false;
    let isLoadingAlbum = false;
    let currentAlbumRequest = null;
    let pageScrollWasLocked = false;
    let bodyOverflowBeforeAlbum = "";
    let htmlOverflowBeforeAlbum = "";

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

        if ($albumView.height() < $albumContainer.height()) {
            $albumContainer
                .height($albumView.height() - 40)
                .css("overflow", "hidden")
                .css("overflow-y", "scroll");
        }
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

        $("body").css("overflow", "hidden");
        $("html").css("overflow", "hidden");
        pageScrollWasLocked = true;
    }

    function unlockPageScroll() {
        if (!pageScrollWasLocked) {
            return;
        }

        $("body").css("overflow", bodyOverflowBeforeAlbum || "");
        $("html").css("overflow", htmlOverflowBeforeAlbum || "");
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
