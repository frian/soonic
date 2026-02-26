$(function() {
    'use strict';

    const debug = false;

    let playerStatus = "paused";
    let contextMenuClickTimer = null;
    let playlistFlashTimer = null;

    function logDebug(message) {
        if (debug) {
            console.log(message);
        }
    }

    function showPlaylistFlash(action, options) {
        options = options || {};

        if (!options.force && $(window).width() >= 1024) {
            return;
        }

        const fallbackMessages = {
            add: 'Song added to playlist',
            remove: 'Song removed from playlist',
            albumAdd: 'Album added to playlist'
        };
        const translationKeys = {
            add: 'player.playlist.flash.add',
            remove: 'player.playlist.flash.remove',
            albumAdd: 'player.playlist.flash.albumAdd'
        };
        const translationKey = translationKeys[action] || translationKeys.add;
        const message = typeof window.t === 'function'
            ? window.t(translationKey, fallbackMessages[action])
            : fallbackMessages[action];

        if (!message) {
            return;
        }

        let $flash = $("#playlist-flash-message");
        if (!$flash.length) {
            $flash = $("<div>", { id: "playlist-flash-message" });
            $("body").append($flash);
        }

        if (playlistFlashTimer) {
            clearTimeout(playlistFlashTimer);
            playlistFlashTimer = null;
        }

        $flash.stop(true, true).text(message).fadeIn(80);
        playlistFlashTimer = setTimeout(function() {
            $flash.fadeOut(120);
            playlistFlashTimer = null;
        }, 1000);
    }

    function buildPlaylistRowFromAlbumSong($row) {
        const $copy = $("<tr>", {
            "data-path": $row.data("path"),
            "data-duration": $row.data("duration")
        });

        $copy.append(
            $("<td>", { "class": "add" }).append(
                $("<i>", {
                    "class": "icon-minus",
                    role: "button",
                    tabindex: 0
                })
            )
        );
        $copy.append($("<td>").text($row.data("track-number") || ""));
        $copy.append($("<td>").text($row.data("artist") || ""));
        $copy.append($("<td>").text($row.data("title") || ""));
        $copy.append($("<td>").text($row.data("album") || ""));
        $copy.append($("<td>").text($row.data("duration") || ""));
        $copy.append($("<td>").text($row.data("year") || ""));
        $copy.append($("<td>").text($row.data("genre") || ""));

        return $copy;
    }

    function buildSongsRowFromAlbumSong($row) {
        const $copy = $("<tr>", {
            "data-path": $row.data("path"),
            "data-duration": $row.data("duration")
        });

        $copy.append(
            $("<td>", { "class": "add" }).append(
                $("<i>", {
                    "class": "icon-plus",
                    role: "button",
                    tabindex: 0
                })
            )
        );
        $copy.append($("<td>").text($row.data("track-number") || ""));
        $copy.append($("<td>").text($row.data("artist") || ""));
        $copy.append($("<td>").text($row.data("title") || ""));
        $copy.append($("<td>").text($row.data("album") || ""));
        $copy.append($("<td>").text($row.data("duration") || ""));
        $copy.append($("<td>").text($row.data("year") || ""));
        $copy.append($("<td>").text($row.data("genre") || ""));

        return $copy;
    }

    function addSongToPlaylist($sourceRow, options) {
        options = options || {};
        const path = $sourceRow.data("path");

        if (!path || playlistContainsPath(path)) {
            return false;
        }

        let $copy = null;

        if ($sourceRow.closest(".album-songs").length) {
            $copy = buildPlaylistRowFromAlbumSong($sourceRow);
        } else {
            $copy = $sourceRow.clone();
            const $icon = $copy.find(".icon-plus");
            if ($copy.hasClass('playing')) {
                $copy.removeClass('playing');
            }
            $copy.removeClass("selected");
            $icon.attr('class', 'icon-minus');
        }

        $("#playlist tbody").append($copy);

        if ($("#playlist").height() + 20 > $("#playlist-section").height()) {
            $('.playlist-container').scrollTop($('.playlist-container').prop("scrollHeight"));
        }

        updatePlaylistInfo($copy);
        if (options.suppressFlash !== true) {
            showPlaylistFlash('add', { force: options.forceFlash === true });
        }

        return true;
    }

    function addAlbumToPlaylist($albumView) {
        let addedCount = 0;

        $albumView.find(".album-songs tbody tr").each(function() {
            if (addSongToPlaylist($(this), { suppressFlash: true })) {
                addedCount++;
            }
        });

        if (addedCount > 0) {
            showPlaylistFlash('albumAdd', { force: true });
        }
    }

    function playAlbumFromOverlay($albumView) {
        const $tbody = $("<tbody>");

        $albumView.find(".album-songs tbody tr").each(function() {
            const $row = $(this);
            if (!$row.data("path")) {
                return;
            }
            $tbody.append(buildSongsRowFromAlbumSong($row));
        });

        if (!$tbody.children().length) {
            return;
        }

        $(document).trigger("soonic:updateSongPanel", [{ tbody: $tbody }]);

        const $firstSong = $tbody.find("tr").first();
        if ($firstSong.length) {
            $firstSong.trigger("click");
        }
    }

    /**
     * Play / Pause currently loaded song
     */
    $(document).on("click", "#play-pause-button", function(e) {

        logDebug('clicked on Play / Pause');
        playPause();
    });


    /**
     * load and play a song from the songs list or the playlist
     */
    $(document).on("click", "#songs tbody tr, #playlist tbody tr", function(e) {

        logDebug('clicked on a song');

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
                        if (!addSongToPlaylist($selected)) {
                            $(".songs-context-menu, .playlist-context-menu").css('display', 'none');
                            $("#songs tbody tr.selected, #playlist tbody tr.selected").removeClass("selected");
                            return;
                        }
                    }
                    else if ($target.is("#remove-from-playlist")) {
                        updatePlaylistInfo($selected, 'remove');
                        $selected.remove();
                        showPlaylistFlash('remove');
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

        logDebug('clicked on next song');
        playNext();
    });


    /**
     * play previous song in songslist
     */
    $(document).on("click", ".icon-to-start", function(e) {

        logDebug('clicked on previous song');
        playNext('backward');
    });


    /**
     * move progress bar
     */
    $(document).on("click", ".progressbar", function(e) {

        logDebug('clicked on progress bar');

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

        logDebug("jumpTime : " + toDuration(jumpTime));
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

        logDebug('song ended');
        logDebug("running playNext");
        playNext();
    });

    $("#player").on("error stalled abort", function() {
        playerStatus = "paused";
        $("#play-pause-button").removeClass('icon-pause').addClass('icon-play');
        logDebug('player error/stalled');
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
        if (!addSongToPlaylist($button.parent())) {
            setTimeout(function() {
                $button.removeData('adding');
            }, 120);
            return;
        }

        logDebug('clicked on add to playlist');

        setTimeout(function() {
            $button.removeData('adding');
        }, 120);
    });

    $(document).on("click", ".album-songs .icon-plus", function(e) {
        e.preventDefault();
        e.stopPropagation();
        addSongToPlaylist($(this).closest("tr"), { forceFlash: true });
    });

    $(document).on("click", ".add-album-to-playlist", function(e) {
        e.preventDefault();
        e.stopPropagation();
        addAlbumToPlaylist($(this).closest(".single-album-view"));
    });

    $(document).on("click", ".play-album", function(e) {
        e.preventDefault();
        e.stopPropagation();
        playAlbumFromOverlay($(this).closest(".single-album-view"));
    });


    /**
     * Remove song from playlist
     */
    $(document).on("click", "#playlist .add", function(e) {
        e.stopPropagation();
        updatePlaylistInfo($(this).parent(), 'remove');
        $(this).parent().remove();
        showPlaylistFlash('remove');

        logDebug('remove song from playlist');
    });



    /**
     * play next song (forward or backward)
     */
    function playNext(direction) {

        logDebug('- in playNext');

        if ($("tbody .playing").length) {

            const current = $("tbody .playing");

            logDebug('current : ' + current);

            let next = null;

            if (!direction) {
                next = current.next('tr');

                logDebug('get next song');
            }
            else {
                next = current.prev('tr');

                logDebug('get prev song');
            }

            if (next.length) {
                current.removeClass('playing');
                next.addClass('playing');
                loadSong(next);
            }
            else {
                logDebug('NO next song');
                logDebug('playerStatus : ' + playerStatus);

                $("#play-pause-button").removeClass('icon-pause').addClass('icon-play');
                playerStatus = "paused";
            }
        }
        else {
            logDebug('NO next song');

            $("#play-pause-button").removeClass('icon-pause').addClass('icon-play');
            playerStatus = "paused";

            logDebug('playerStatus : ' + playerStatus);
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
            logDebug("No source");
            return;
        }

        if (playerStatus === "paused") {
            const playPromise = player.play();
            if (playPromise && typeof playPromise.catch === "function") {
                playPromise
                    .then(function() {
                        playerStatus = "playing";
                        $this.removeClass('icon-play').addClass('icon-pause');
                    })
                    .catch(function() {
                        playerStatus = "paused";
                        $this.removeClass('icon-pause').addClass('icon-play');
                        logDebug('player play() failed');
                    });
            } else {
                playerStatus = "playing";
                $this.removeClass('icon-play').addClass('icon-pause');
            }
        } else {
            player.pause();
            playerStatus = "paused";
            $this.removeClass('icon-pause').addClass('icon-play');
        }

        logDebug('in playPause');
        logDebug("- playerStatus = " + playerStatus);
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

        const playPromise = player.play();
        if (playPromise && typeof playPromise.catch === "function") {
            playPromise
                .then(function() {
                    $("#play-pause-button").removeClass("icon-play").addClass("icon-pause");
                    playerStatus = "playing";
                })
                .catch(function() {
                    $("#play-pause-button").removeClass("icon-pause").addClass("icon-play");
                    playerStatus = "paused";
                    logDebug('loadSong play() failed');
                });
        } else {
            $("#play-pause-button").removeClass("icon-play").addClass("icon-pause");
            playerStatus = "playing";
        }

        logDebug('in loadSong');
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

        logDebug('in updatePlaylistInfo');
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
