$(function() {

    const debug = 1;

    if ($(".single-album-view")) {
        if ($(".single-album-view").height() < $(".single-album-container").height() ) {
            $(".single-album-container").height($(".single-album-view").height() - 40);
            $(".single-album-container").css("overflow", "hidden");
            $(".single-album-container").css("overflow-y", "scroll");
        }
    }

    $(document).on("click", ".img-wrapper", function(e) {

        if (debug === 1) {
            console.log('clicked on an album');
        }

        const url = "/album/" + $(this).parent().attr("data-album-id");
        if (debug === 1) {
            console.log("url : " + url);
        }

        $.ajax({
            url: url,
            cache: true,
            success: function(data) {
                $(document.body).append(data);

                if ($(".single-album-view").height() < $(".single-album-container").height() ) {
                    $(".single-album-container").height($(".single-album-view").height() - 40);
                    $(".single-album-container").css("overflow", "hidden");
                    $(".single-album-container").css("overflow-y", "scroll");
                }
                $(".single-album-view").css("top", $(window).scrollTop());
                console.log("scrollTop : " + $(window).scrollTop());
            },
            error: function(data) {
                console.log("error");
            }
        });
    });


});
