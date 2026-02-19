$(function() {
    'use strict';

    const debug = false;

    let playerStatus = "paused";
    let contextMenuClickTimer = null;

    /**
     * Play / Pause currently loaded song
     */
    $(document).on("click", "#play-pause-button", function(e) {

        if (debug) {
            console.log('clicked on Play / Pause');
        }
        playPause();
    });


    /**
     * load and play a song from the songs list or the playlist
     */
    $(document).on("click", "#songs tbody tr, #playlist tbody tr", function(e) {

        if (debug) {
            console.log('clicked on a song');
        }

        $("tbody .playing").removeClass('playing');
        loadSong($(this));
        playerStatus = "playing";
        $(this).addClass('playing');

        $('#play-pause-button').removeClass('icon-play').addClass('icon-pause');

        if ($(window).width() < 500) {
            $(".song-info").css('display', 'none');
        }
    });


    /**
     * Context menu
     */
    $(document).on("contextmenu", "#songs tbody tr, #playlist tbody tr", function(e) {

        e.preventDefault();

        const $currentItem = $(this);

        // -- if we right-clic two times, remove class and listener
        $("#songs tbody tr.selected, #playlist tbody tr.selected").removeClass("selected");
        $(document).off("click.playlistContext");
        if (contextMenuClickTimer) {
            clearTimeout(contextMenuClickTimer);
            contextMenuClickTimer = null;
        }

        $currentItem.addClass("selected");

        let contextMenu = '.songs-context-menu';
        const tableId = $currentItem.closest('table').attr('id');

        if (tableId === 'playlist') {
            contextMenu = '.playlist-context-menu';
        }

        $(contextMenu).css('display', 'block');
        $(contextMenu).css('top', e.pageY);
        $(contextMenu).css('left', e.pageX);

        contextMenuClickTimer = setTimeout(function() {
            $(document).one("click.playlistContext", function(e) {
                const $target = $(e.target).closest("#add-to-playlist, #remove-from-playlist");
                const $selected = $("#songs tbody tr.selected, #playlist tbody tr.selected").first();

                if ($target.length && $selected.length) {
                    if ($target.is("#add-to-playlist")) {
                        if (playlistContainsPath($selected.data("path"))) {
                            $(".songs-context-menu, .playlist-context-menu").css('display', 'none');
                            $("#songs tbody tr.selected, #playlist tbody tr.selected").removeClass("selected");
                            return;
                        }

                        const $copy = $selected.clone();
                        $copy.removeClass("selected");
                        const $icon = $copy.find(".icon-plus");
                        $icon.attr('class', 'icon-minus');
                        updatePlaylistInfo($copy);
                        $("#playlist tbody").append($copy);
                    }
                    else if ($target.is("#remove-from-playlist")) {
                        updatePlaylistInfo($selected, 'remove');
                        $selected.remove();
                    }
                }

                $(".songs-context-menu, .playlist-context-menu").css('display', 'none');
                $("#songs tbody tr.selected, #playlist tbody tr.selected").removeClass("selected");
            });
        }, 100);
    });


    /**
     * play next song in songslist
     */
    $(document).on("click", ".icon-to-end", function(e) {

        if (debug) {
            console.log('clicked on next song');
        }
        playNext();
    });


    /**
     * play previous song in songslist
     */
    $(document).on("click", ".icon-to-start", function(e) {

        if (debug) {
            console.log('clicked on previous song');
        }
        playNext('backward');
    });


    /**
     * move progress bar
     */
    $(document).on("click", ".progressbar", function(e) {

        if (debug) {
            console.log('clicked on progress bar');
        }

        const player = document.getElementById("player");
        if (!Number.isFinite(player.duration) || player.duration <= 0) {
            return;
        }
        const offset = $(this).offset();
        const xVal = e.pageX - offset.left;
        const percent = (xVal / $(this).width()) * 100;
        const jumpTime = player.duration * percent / 100;

        player.currentTime = jumpTime;

        $(".progress-indicator").width(percent + "%");

        if (debug) {
            console.log("jumpTime : " + toDuration(jumpTime));
        }
    });


    /**
     * show time elapsed
     */
    $("#player").on("timeupdate", function() {

        $("#current-time").text(toDuration(this.currentTime) + ' /');

        if (!Number.isFinite(this.duration) || this.duration <= 0) {
            return;
        }

        let percentagePlayed = (this.currentTime / this.duration);

        if (percentagePlayed > 1) {
            percentagePlayed = 1;
        } else if (percentagePlayed < 0) {
            percentagePlayed = 0;
        }

        percentagePlayed = percentagePlayed * 100;

        $(".progress-indicator").width(percentagePlayed + '%');
    });


    /**
     * on song end, play next song
     */
    $('#player').on('ended', function() {

        if (debug) {
            console.log('song ended');
            console.log("running playNext");
        }
        playNext();
    });


    /**
     * Add song to playlist
     */
    $(document).on("click", "#songs .add", function(e) {
        e.stopPropagation();
        const $button = $(this);

        // -- prevent duplicate inserts on very fast repeated clicks
        if ($button.data('adding') === true) {
            return;
        }
        $button.data('adding', true);

        // -- add song
        if (playlistContainsPath($button.parent().data("path"))) {
            setTimeout(function() {
                $button.removeData('adding');
            }, 120);
            return;
        }

        const $copy = $button.parent().clone();
        const $icon = $copy.find(".icon-plus");
        if ($copy.hasClass('playing')) {
            $copy.removeClass('playing');
        }
        $icon.attr('class', 'icon-minus');
        $("#playlist tbody").append($copy);

        if ($("#playlist").height() + 20 > $("#playlist-section").height()) {
            $('.playlist-container').scrollTop($('.playlist-container').prop("scrollHeight"));
        }

        updatePlaylistInfo($copy);

        if (debug) {
            console.log('clicked on add to playlist');
        }

        setTimeout(function() {
            $button.removeData('adding');
        }, 120);
    });


    /**
     * Remove song from playlist
     */
    $(document).on("click", "#playlist .add", function(e) {
        e.stopPropagation();
        updatePlaylistInfo($(this).parent(), 'remove');
        $(this).parent().remove();

        if (debug) {
            console.log('remove song from playlist');
        }
    });



    /**
     * play next song (forward or backward)
     */
    function playNext(direction) {

        if (debug) {
            console.log('- in playNext');
        }

        if ($("tbody .playing").length) {

            const current = $("tbody .playing");

            if (debug) {
                console.log('current : ' + current);
            }

            let next = null;

            if (!direction) {
                next = current.next('tr');

                if (debug) {
                    console.log('get next song');
                }
            }
            else {
                next = current.prev('tr');

                if (debug) {
                    console.log('get prev song');
                }
            }

            if (next.length) {
                current.removeClass('playing');
                next.addClass('playing');
                loadSong(next);
            }
            else {
                if (debug) {
                    console.log('NO next song');
                    console.log('playerStatus : ' + playerStatus);
                }

                $("#play-pause-button").removeClass('icon-pause').addClass('icon-play');
                playerStatus = "paused";
            }
        }
        else {
            if (debug) {
                console.log('NO next song');
            }

            $("#play-pause-button").removeClass('icon-pause').addClass('icon-play');
            playerStatus = "paused";

            if (debug) {
                console.log('playerStatus : ' + playerStatus);
            }
        }
    }

    /**
     * Play / Pause currently loaded song
     */
    function playPause() {

        const player = document.getElementById("player");
        const src = document.getElementById("audio-source");
        const $this = $("#play-pause-button");

        if (!$(src).attr('src')) {
            if (debug) {
                console.log("No source");
            }
            return;
        }

        if (playerStatus === "paused") {
            player.play();
            playerStatus = "playing";
            $this.removeClass('icon-play').addClass('icon-pause');
        } else {
            player.pause();
            playerStatus = "paused";
            $this.removeClass('icon-pause').addClass('icon-play');
        }

        if (debug) {
            console.log('in playPause');
            console.log("- playerStatus = " + playerStatus);
        }
    }

    /**
     * load song
     */
    function loadSong(song) {

        const path = song.data("path");
        const values = song.find('td').map(function() {
            return $(this).text();
        }).get();
        const artist = values[2];
        const title = values[3];
        const duration = values[5];
        const audioSource = document.getElementById("audio-source");
        const player = document.getElementById("player");

        $(audioSource).attr('src', path);
        player.load();

        $("#song-title").text(title);
        $("#song-artist").text(' by ' + artist);
        $("#duration").text(duration);

        player.play();

        if ($("#play-pause-button").attr("class") === 'icon-play') {
            $("#play-pause-button").attr("class", "icon-pause");
        }

        if (debug) {
            console.log('in loadSong');
        }
    }

    /**
     * 00:00:00, 00:00 to seconds
     */
    function toSeconds(str)  {

        const arr = str.split(':').map(Number);

        if (arr.length === 1) {
            return (arr[0]);
        }
        else if (arr.length === 2) {
            return (arr[0] * 60) + arr[1];
        }

        return (arr[0] * 3600) + (arr[1] * 60) + arr[2];
    }

    /**
     * seconds to 00:00:00, 00:00
     */
    function toDuration(secs) {

        secs = Math.round(secs);

        const hours = parseInt(secs / 3600, 10),
            minutes = parseInt((secs / 60) % 60, 10),
            seconds = parseInt(secs % 3600 % 60, 10);

        const durationParts = [];

        if (hours !== 0) {
            durationParts.push(hours);
            durationParts.push(minutes);
            durationParts.push(seconds);
        }
        else{
            durationParts.push(minutes);
            durationParts.push(seconds);
        }

        return durationParts.map(function (i) { return i.toString().length === 2 ? i : '0' + i; }).join(':');
    }

    /**
     * Update playlist num files and duration
     */
    function updatePlaylistInfo(item, action) {

        action = action || 'add';

        let numFiles = document.getElementById("playlist-num-files").textContent;
        let songDuration = $(item).data("duration");
        let playlistDuration = document.getElementById("playlist-duration").textContent;

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

        let fileInfoText = 'file';
        if (numFiles > 1) {
            fileInfoText = 'files';
        }

        document.getElementById("playlist-file").textContent = fileInfoText;
        document.getElementById("playlist-num-files").textContent = numFiles;
        document.getElementById("playlist-duration").textContent = playlistDuration;

        let display = 'none';
        if (numFiles > 0) {
            display = 'initial';
        }
        document.getElementById("playlist-infos").style.display = display;

        if (debug) {
            console.log('in updatePlaylistInfo');
        }
    }

    /**
     * true when a song with the same path already exists in playlist
     */
    function playlistContainsPath(path) {

        let found = false;

        $("#playlist tbody tr").each(function() {
            if ($(this).data("path") === path) {
                found = true;
                return false;
            }
        });

        return found;
    }
});
