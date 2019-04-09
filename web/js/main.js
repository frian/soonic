$(function() {

    $(document).on("click", ".artists a", function(e) {
        e.preventDefault();
        url = $(this).attr("href");

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


    // filter artists list
    var lastval = "";
    var timeout = null;
    var url = '/artist/filter/';

    $("input[name=filter]").keyup(function() {

        // if input is cleared
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

});
