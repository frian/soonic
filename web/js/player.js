$(function() {
    console.log("loaded player");

    var playerStatus = "paused";
    var statusClass = '';

    $(document).on("click", ".icon-play, .icon-pause", function(e) {

        var player = document.getElementById("player");

        if (playerStatus === "paused") {
            player.play();
            playerStatus = "playing";
            statusClass = "icon-pause";
        }
        else {
            player.pause();
            playerStatus = "paused";
            statusClass = "icon-play";
        }

        $(this).attr("class", statusClass);
    });
});
