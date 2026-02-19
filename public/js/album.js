$(function() {

    const debug = false;
    let isLoadingAlbum = false;

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

        if (debug) {
            console.log("clicked on an album");
        }

        const albumId = $(this).closest("[data-album-id]").data("album-id");
        if (!albumId) {
            isLoadingAlbum = false;
            return;
        }

        const url = "/album/" + albumId;
        if (debug) {
            console.log("url : " + url);
        }

        $.ajax({
            url: url,
            cache: true,
            success: function(data) {
                $(".single-album-view").remove();
                $(document.body).append(data);

                adjustAlbumContainer();
                $(".single-album-view").css("top", $(window).scrollTop());
                if (debug) {
                    console.log("scrollTop : " + $(window).scrollTop());
                }
            },
            error: function() {
                if (debug) {
                    console.log("error");
                }
            },
            complete: function() {
                isLoadingAlbum = false;
            }
        });
    });


});
