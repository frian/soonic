$(function() {
    'use strict';

    const debug = false;

    /**
     * Pause radio from external controls
     */
    $(document).on("soonic:pauseRadio", function() {
        const $activePlayerButton = $(".radios-view i.activePlayer").first();
        const activePlayer = $activePlayerButton.length ? $activePlayerButton.next()[0] : null;

        if (!activePlayer || activePlayer.paused) {
            return;
        }

        activePlayer.pause();
        setRadioPaused($activePlayerButton);
        logDebug("radio paused");
    });

    /**
     * Play / Pause currently loaded radio
     */
    $(document).on("click", ".radio-play", function(e) {
        e.preventDefault();

        const $button = $(this);
        const radioPlayer = $button.next()[0];
        if (!radioPlayer) {
            return;
        }

        // -- find currently active player and pause it
        const $activePlayerButton = $(".radios-view i.activePlayer").first();
        const activePlayer = $activePlayerButton.length ? $activePlayerButton.next()[0] : null;

        if (activePlayer) {
            activePlayer.pause();
            setRadioPaused($activePlayerButton);
            logDebug("radio paused");
        }

        // -- if we clicked the active one, this means toggle to pause only
        if (radioPlayer === activePlayer) {
            return;
        }

        $(radioPlayer).removeData("stream-error-handled");
        $(document).trigger("soonic:pausePlayer");
        setRadioLoading($button);

        const playPromise = radioPlayer.play();
        if (playPromise && typeof playPromise.catch === "function") {
            playPromise
                .then(function() {
                    setRadioPlaying($button);
                    logDebug("radio playing");
                })
                .catch(function() {
                    setRadioPaused($button);
                    showRadioStreamError(radioPlayer);
                    logDebug("radio play() failed");
                });
        } else {
            setRadioPlaying($button);
            logDebug("radio playing");
        }
    });

    /**
     * Handle radio stream errors
     */
    ["error", "stalled", "abort"].forEach(function(eventName) {
        document.addEventListener(eventName, function(e) {
            const audio = $(e.target).is(".radios-view audio")
                ? e.target
                : $(e.target).closest(".radios-view audio").get(0);

            if (audio) {
                handleRadioStreamError(audio);
            }
        }, true);
    });

    /**
     * Check stream playback in browser
     */
    $(document).on("click", ".radio-stream-check-button", function(e) {
        e.preventDefault();
        checkRadioStream($(this));
    });


    function logDebug(message) {
        window.logSoonicDebug(debug, message);
    }

    function showRadioFlash() {
        const message = typeof window.t === 'function'
            ? window.t('radio.flash.error', 'Radio stream unavailable')
            : 'Radio stream unavailable';

        window.showSoonicFlash("radio-flash-message", message, 1800);
    }

    function setRadioPlaying($button) {
        $button
            .removeClass("icon-play is-loading")
            .addClass("icon-pause activePlayer")
            .closest("tr, li, .radio-item")
            .addClass("active-radio");
    }

    function setRadioPaused($button) {
        $button
            .removeClass("icon-pause activePlayer is-loading")
            .addClass("icon-play")
            .closest("tr, li, .radio-item")
            .removeClass("active-radio");
    }

    function setRadioLoading($button) {
        $button
            .removeClass("icon-pause activePlayer")
            .addClass("icon-play is-loading")
            .closest("tr, li, .radio-item")
            .removeClass("active-radio");
    }

    function showRadioStreamError(audio) {
        const $audio = $(audio);

        if ($audio.data("stream-error-handled")) {
            return false;
        }

        $audio.data("stream-error-handled", true);
        showRadioFlash();

        return true;
    }

    function handleRadioStreamError(audio) {
        const $button = $(audio).prev(".radio-play");
        if ($button.length) {
            setRadioPaused($button);
        }

        if (!showRadioStreamError(audio)) {
            return;
        }

        logDebug("radio stream error/stalled");
    }

    function checkRadioStream($button) {
        const $form = $button.closest("form");
        const $input = $form.find("[id$='_streamUrl']").first();
        const url = String($input.val() || "").trim();
        const $result = $button.closest("td").find(".radio-stream-check-result").first();

        if (!url) {
            setStreamCheckResult($button, $result, "empty");
            return;
        }

        $button.prop("disabled", true);
        setStreamCheckResult($button, $result, "checking");

        const audio = new Audio();
        const timeout = window.setTimeout(function() {
            fail();
        }, 8000);
        let isDone = false;

        audio.preload = "none";
        audio.muted = true;
        audio.volume = 0;

        $(audio).one("canplay playing loadedmetadata", function() {
            done("ok");
        });
        $(audio).one("error stalled abort", function() {
            fail();
        });

        audio.src = url;
        audio.load();

        const playPromise = audio.play();
        if (playPromise && typeof playPromise.catch === "function") {
            playPromise.catch(function() {
                fail();
            });
        }

        function fail() {
            done("error");
        }

        function done(state) {
            if (isDone) {
                return;
            }

            isDone = true;
            window.clearTimeout(timeout);
            $(audio).off();
            audio.pause();
            audio.removeAttribute("src");
            audio.load();
            $button.prop("disabled", false);
            setStreamCheckResult($button, $result, state);
            logDebug("radio stream check: " + state);
        }
    }

    function setStreamCheckResult($button, $result, state) {
        const labels = {
            checking: $button.data("checking-label") || "checking...",
            ok: $button.data("ok-label") || "stream OK",
            error: $button.data("error-label") || "stream unavailable",
            empty: $button.data("empty-label") || "enter a stream URL"
        };

        $result
            .removeClass("is-ok is-error is-checking")
            .addClass("is-" + state)
            .text(labels[state] || "");
    }
});
