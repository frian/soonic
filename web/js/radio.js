$(function() {
    console.log("loaded radio.js");

    var playerStatus = "paused";

    /**
     * Play / Pause currently loaded song
     */
    $(document).on("click", ".icon-play, .icon-pause", function(e) {

        var player = $(this).next()[0];

        if (playerStatus === "paused") {
            player.play();
            playerStatus = "playing";
            $(this).removeClass("icon-play");
            $(this).addClass("icon-pause");
            $(this).addClass("active");
        } else {
            player.pause();
            playerStatus = "paused";
            $(this).removeClass("icon-pause");
            $(this).addClass("icon-play");
            $(this).removeClass("active");
        }

    });
});
