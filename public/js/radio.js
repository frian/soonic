$(function() {

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
        }

        // -- if we clicked the active one, this means toggle to pause only
        if (radioPlayer === activePlayer) {
            return;
        }

        const playPromise = radioPlayer.play();
        if (playPromise && typeof playPromise.catch === "function") {
            playPromise
                .then(function() {
                    setRadioPlaying($button);
                })
                .catch(function() {
                    setRadioPaused($button);
                });
        } else {
            setRadioPlaying($button);
        }
    });
});
