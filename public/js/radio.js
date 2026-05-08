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

        $(document).trigger("soonic:pausePlayer");

        const playPromise = radioPlayer.play();
        if (playPromise && typeof playPromise.catch === "function") {
            playPromise
                .then(function() {
                    setRadioPlaying($button);
                    logDebug("radio playing");
                })
                .catch(function() {
                    setRadioPaused($button);
                    showRadioFlash();
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


    function logDebug(message) {
        if (debug) {
            console.log(message);
        }
    }

    function showRadioFlash() {
        const message = typeof window.t === 'function'
            ? window.t('radio.flash.error', 'Radio stream unavailable')
            : 'Radio stream unavailable';

        window.showSoonicFlash("radio-flash-message", message, 1800);
    }

    function setRadioPlaying($button) {
        $button
            .removeClass("icon-play")
            .addClass("icon-pause activePlayer")
            .closest("tr, li, .radio-item")
            .addClass("active-radio");
    }

    function setRadioPaused($button) {
        $button
            .removeClass("icon-pause activePlayer")
            .addClass("icon-play")
            .closest("tr, li, .radio-item")
            .removeClass("active-radio");
    }

    function handleRadioStreamError(audio) {
        const $button = $(audio).prev(".radio-play");
        if ($button.length) {
            setRadioPaused($button);
        }
        showRadioFlash();
        logDebug("radio stream error/stalled");
    }
});
