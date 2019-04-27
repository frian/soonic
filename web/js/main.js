$(function() {

    /**
     * Returns a album list for an artist or remove album list (close)
     * Updates the navigation panel
     */
    $(document).on("click", ".artists a.artist", function(e) {

        e.preventDefault();

        var url = $(this).attr("href");

        if ($(this).next('ul').length) {
            $(this).next().remove();
        }
        else {
            $.get({
                url: url,
                context: this,
                cache: true,
                success: function(data){
                    $(this).after(data);
                }
            });
        }
    });


    /**
     * Filters the artists list
     * Updates the navigation panel
     */
    var lastval = "";
    var timeout = null;

    $("input[name=filter]").keyup(function() {

        var url = '/artist/filter/';

        // -- if input is cleared
        if (this.value.length === 0 && lastval.length > 0) {

            $.get({
                url: url,
                cache: true,
                success: function(data){
                    $("#artists-nav").remove();
                    $("nav.artists").append(data);
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

        var filter = this.value;

        if (timeout) { clearTimeout(timeout); }

        timeout = setTimeout(function() {

            $.get({
                url: url + filter,
                cache: true,
                success: function(data){
                    $("#artists-nav").remove();
                    $("nav.artists").append(data);
                }
            });

        },300);

    });


    /**
     * Returns the songs from an album
     * Updates the songs panel
     */
    $(document).on("click", ".artists a.song", function(e) {
console.log("clicked");
        e.preventDefault();

        var url = $(this).attr("href");
console.log(url);
        $.get({
            url: url,
            cache: true,
            success: function(data){
                $("#songs table tbody").remove();
                $("#songs table").append(data);
            }
        });
    });

});
