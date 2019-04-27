$(function() {

    var playerStatus = "paused";
    var statusClass = '';

    /**
     * Play / Pause currently loaded song
     */
    $(document).on("click", ".icon-play, .icon-pause", function(e) {

        var player = document.getElementById("player");

        if (playerStatus === "paused") {
            player.play();
            playerStatus = "playing";
            statusClass = "icon-pause";
        } else {
            player.pause();
            playerStatus = "paused";
            statusClass = "icon-play";
        }

        $(this).attr("class", statusClass);
    });


    /**
     * load and play a song from the songs list
     */
    $(document).on("click", "#songslist tbody tr", function(e) {

        $("#songslist tbody .active").removeClass('active');

        loadSong($(this));

        playerStatus = "playing";

        $(this).addClass('active');
    });


    /**
     * play next song in songslist
     */
    $(document).on("click", ".icon-to-end", function(e) {

        playNext();
        playerStatus = "playing";
    });

    /**
     * play next song in songslist
     */
    $(document).on("click", ".icon-to-start", function(e) {

        playNext('backward');
        playerStatus = "playing";
    });



    $("#player").on("timeupdate", function() {
        var player = document.getElementById("player");
        $("#currentTime").html(formatDuration(player.currentTime) + ' /');
    });


    $('#player').on('ended', function() {
       playNext();
    });
});

/**
 * seconds to minutes:seconds
 */
function formatDuration(rawDuration) {

    rawDuration = Math.round(rawDuration);
    var durationSeconds = parseInt(rawDuration % 60);
    var durationMinutes = parseInt(rawDuration / 60) % 60;

    durationSeconds = durationSeconds < 10 ? '0' + durationSeconds : durationSeconds;
    durationMinutes = durationMinutes < 10 ? '0' + durationMinutes : durationMinutes;

    return durationMinutes + ':' + durationSeconds;
}

/**
 * song path hack
 */
function cleanPath(path) {
    var buff = path.split('/');
    return '/' + buff.slice(4).join('/');
}

/**
 * load song
 */
function loadSong(song) {

    var path = song.data("path");

    path = cleanPath(path);

    var format = song.data("format");

    var values = song.find('td').map(function() {
        return $(this).text();
    }).get();

    var artist = values[1];
    var title = values[2];
    var duration = values[4];

    var mpegSource = document.getElementById("mpegSource");

    $(mpegSource).attr('src', path);

    var player = document.getElementById("player");

    player.load();

    $("#songTitle").html(title);
    $("#songArtist").html(' by ' + artist);
    $("#duration").html(duration);

    player.play();

    if ($("#startStopButton").attr("class") === 'icon-play') {
        $("#startStopButton").attr("class", "icon-pause");
    }

}

/**
 * play next song (forward or backward)
 */
function playNext(direction) {

    if ($("#songslist tbody .active").length) {

        var current = $("#songslist tbody .active");

        var next = null;

        if (!direction) {
            next = current.next('tr');
        }
        else {
            next = current.prev('tr');
        }

        if (next.length) {

            current.removeClass('active');
            next.addClass('active');

            loadSong(next);
        }
        else {
            
        }
    }
}
