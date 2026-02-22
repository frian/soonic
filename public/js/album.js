$(function() {

    const debug = false;
    let isLoadingAlbum = false;
    let currentAlbumRequest = null;

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

    if ($(".single-album-view").length) {
        adjustAlbumContainer();
    }

    $(document).on("click", ".img-wrapper", function(e) {
        e.preventDefault();

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


});
