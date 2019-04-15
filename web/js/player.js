$(function() {

    var playerStatus = "paused";
    var statusClass = '';


    // var audio = $("#player")[0];
    // $("#player").on("loadedmetadata", function() {
    //     alert(audio.duration);
    // });


    /**
     * Play / Pause currently loaded song
     */
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


    /**
     * load and play a song from the songs list
     */
     $(document).on("click", "#songslist tbody tr", function(e) {

         var path = $(this).data("path");

         var buff = path.split('/');
         path = '/' + buff.slice(4).join('/');

         console.log(path);

         var format = $(this).data("format");

         var values = $(this).find('td').map(function() {
             return $(this).text();
         }).get();

         var artist = values[1];
         var title = values[2];
         var duration = values[4];

         var mpegSource = document.getElementById("mpegSource");

         $(mpegSource).attr('src', path);

         var player = document.getElementById("player");

         player.load();

         $(".songInfo").html(title + ' - ' + artist);

         player.play();
         playerStatus = "playing";

         if ($("#startStopButton").attr("class") === 'icon-play') {
             $("#startStopButton").attr("class", "icon-pause");
         }

         console.log($("#startStopButton").attr("class"));
     });


});

/**
 * seconds to minutes:seconds
 */
function formatDuration(rawDuration) {

    rawDuration = Math.round(rawDuration);
    var durationSeconds = parseInt(rawDuration % 60);
    var durationMinutes = parseInt(rawDuration / 60) % 60;

    durationSeconds = durationSeconds < 10 ? '0' + durationSeconds : durationSeconds ;

    return durationMinutes + ':' + durationSeconds;
}
