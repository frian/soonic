$(function() {

    var playerStatus = "paused";
    var statusClass = '';

    /**
     * Play / Pause currently loaded song
     */
    $(document).on("click", ".icon-play, .icon-pause", function(e) {

        var player = document.getElementById("player");

        var src = document.getElementById("mpegSource");

        // if (!document.getElementById("mpegSource")) {
        //     player.append('<source id="mpegSource" src="" type="audio/mpeg"/>');
        //     return;
        // }

        if (!$(src).attr('src')) {
            console.log("No source");
            return;
        }


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
    $(document).on("click", "tbody tr", function(e) {

        $("tbody .active").removeClass('active');

        loadSong($(this));

        playerStatus = "playing";

        $(this).addClass('active');
    });


    /**
     * Context menu
     */
    $(document).on("contextmenu", "#songslist tbody tr, #playlist tbody tr", function(e) {

        e.preventDefault();

        // -- if we right-clic two times, remove class and listener
        $("#songslist tbody tr.active").removeClass("active");
        $(document).off( "click", "body");

        var currentItem = $(this);
        currentItem.addClass("active");

        var contextMenu = '.songsContextMenu';
        var tableId = $(e.target).parent().parent().parent().attr('id');

        if (tableId === 'playlist') {
            contextMenu = '.playlistContextMenu';
        }

        $(contextMenu).css('display', 'block');
        $(contextMenu).css('top', e.pageY);
        $(contextMenu).css('left', e.pageX);

        setTimeout(function() {
            $(document).on( "click", "body", function(e) {
                e.preventDefault();

                if (e.target.id === 'addToPlaylist') {
                    var copy = currentItem.clone();
                    copy.removeClass("active");
                    var icon = copy.find(".icon-plus");
                    $(icon).attr('class', 'icon-minus');
                    updatePlaylistInfo(copy);
                    $("#playlist tbody").append(copy);
                    currentItem.removeClass("active");
                }
                else if (e.target.id === 'removeFromPlaylist') {
                    updatePlaylistInfo(currentItem, 'remove');
                    currentItem.remove();
                }

                $(contextMenu).css('display', 'none');
                $(document).off( "click", "body");
            });
        }, 100);
    });


    /**
     * play next song in songslist
     */
    $(document).on("click", ".icon-to-end", function(e) {

        playNext();
        playerStatus = "playing";
    });

    /**
     * play previous song in songslist
     */
    $(document).on("click", ".icon-to-start", function(e) {

        playNext('backward');
        playerStatus = "playing";
    });


    /**
     * show time elapsed
     */
    $("#player").on("timeupdate", function() {
        var player = document.getElementById("player");
        $("#currentTime").html(formatDuration(player.currentTime) + ' /');
    });


    /**
     * on song emd, play next song
     */
    $('#player').on('ended', function() {
       playNext();
    });


    /**
     * Add song to playlist
     */
    $(document).on("click", "#songslist .add", function(e) {
        e.stopPropagation();
        // -- add song
        var copy = $(this).parent().clone();
        var icon = copy.find(".icon-plus");
        $(icon).attr('class', 'icon-minus');
        $("#playlist tbody").append(copy);

        updatePlaylistInfo(copy);
    });


    /**
     * Remove song from playlist
     */
    $(document).on("click", "#playlist .add", function(e) {
        e.stopPropagation();
        updatePlaylistInfo($(this).parent(), 'remove');
        $(this).parent().remove();
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

    // path = cleanPath(path);

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

    if ($("tbody .active").length) {

        var current = $("tbody .active");

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
            current.removeClass('active');
            $(".icon-pause").attr("class", 'icon-play');
        }
    }
}


function toSeconds(str)  {

    var arr = str.split(':').map(Number);

    if (arr.length === 1) {
        return (arr[0]);
    }
    else if (arr.length === 2) {
        return (arr[0] * 60) + arr[1];
    }

    return (arr[0] * 3600) + (arr[1] * 60) + arr[2];
}

function toDuration(secs) {
    var hours = parseInt(secs / 3600, 10),
        minutes = parseInt((secs / 60) % 60, 10),
        seconds = parseInt(secs % 3600 % 60, 10);

    return [hours, minutes, seconds].map(function (i) { return i.toString().length === 2 ? i : '0' + i; }).join(':');
}


function updatePlaylistInfo(item, action) {

    action = action || 'add';

    var numFiles = document.getElementById("playlistNumFiles").textContent;

    var songDuration = $(item).data("duration");
    var playlistDuration = document.getElementById("playlistDuration").textContent;

    playlistDuration = toSeconds(playlistDuration);
    songDuration = toSeconds(songDuration);

    if (action === 'add') {
        ++numFiles;
        playlistDuration = toDuration(playlistDuration + songDuration);
    }
    else {
        --numFiles;
        playlistDuration = toDuration(playlistDuration - songDuration);
    }

    var fileInfoText = 'file';
    if (numFiles > 1) {
        fileInfoText = 'files';
    }

    document.getElementById("playlistFile").textContent = fileInfoText;
    document.getElementById("playlistNumFiles").textContent = numFiles;
    document.getElementById("playlistDuration").textContent = playlistDuration;

    var display = 'none';
    if (numFiles > 0) {
        display = 'initial';
    }
    document.getElementById("playlistInfos").style.display = display;
}
