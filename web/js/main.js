$(function() {

    // -- show artist albums
    $(document).on("click", ".artists a.artist", function(e) {
        console.log("LOG 1");
        e.preventDefault();
        var url = $(this).attr("href");

console.log("in show albums : " + url);

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


    // -- filter artists list
    var lastval = "";
    var timeout = null;

    $("input[name=filter]").keyup(function() {

        var url = '/artist/filter/';

        // if input is cleared
        if (this.value.length === 0 && lastval.length > 0) {

            console.log("in filter artists 2 : " + url);

            $.get({
                url: url,
                cache: true,
                success: function(data){
                    $("#artists-nav").remove();
                    $("nav.artists").append(data);
                }
            });
        }

        if (this.value === lastval) {
            return;
        }

        lastval = this.value;

        if (this.value.length < 3) {
            return;
        }

        var filter = this.value;

        if (timeout) { clearTimeout(timeout); }

        timeout = setTimeout(function() {

console.log("in filter artists 1 : " + url + filter);

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


    // -- show album songs
    $(document).on("click", ".artists a.song", function(e) {
        console.log("LOG 2");
        e.preventDefault();

        var url = $(this).attr("href");

        console.log("in show songss : " + url);

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
