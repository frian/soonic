$(function() {
    console.log("loaded");

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
});
