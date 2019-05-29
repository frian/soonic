$(function() {

    /**
     * Returns a album list for an artist or remove album list (close)
     * Updates the navigation panel
     */
    $(document).on("click", ".artists-navigation a.artist", function(e) {

        e.preventDefault();

        var url = $(this).attr("href");

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

        var filter = this.value;

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
    });


    /**
     * Returns the songs from an album
     * Updates the songs panel
     */
    $(document).on("click", ".artists-navigation a.song", function(e) {

        e.preventDefault();

        var url = $(this).attr("href");

        $.get({
            url: url,
            cache: true,
            success: function(data) {
                $("#songs table tbody").remove();
                $("#songs table").append(data);
            }
        });
    });


    /**
     * Returns search results
     * Updates the songs panel
     */
    $(document).on("click", "#searchButton", function(e) {

        e.preventDefault();

        var form = $('#searchForm');

        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(),
            success: function(data) {
                $("#songs table tbody").remove();
                $("#songs table").append(data);
            },
            error: function(data) {
                console.log("error");
            }
        });
    });


    /**
     * Start scan
     */
    $(document).on("click", "#scanButton", function(e) {

        e.preventDefault();

        $.get({
            url: '/scan',
            cache: true,
            success: function(data) {
                // $("body").append(data);
            }
        });

        $("#numFiles").html("0");
        $("#scanStatus").html('scanning');

        var $loop = setInterval(myTimer, 1000);

        var value = 0;
        function myTimer() {
            $.get({
                url: '/scan/progress',
                cache: true,
                success: function(data) {
                    currentValue = data.data;
                    if (data.status == 'stopped') {
                        $("#scanStatus").html('done');
                        $("#numFiles").html(currentValue);
                        $("#fileCount").html('');
                        clearInterval($loop);
                        return;
                    }

                    value = currentValue;
                    $("#fileCount").html(data.data);
                }
            });
        }
    });


    $(document).on("click", ".input-reset", function(e) {
        var url = '/artist/filter/';
        $.get({
            url: url,
            cache: true,
            success: function(data) {
                $("#artists-nav").remove();
                $("nav.artists-navigation").append(data);
            }
        });
    });
});
