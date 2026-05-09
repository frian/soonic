window.showSoonicFlash = (function() {
    'use strict';

    const timers = {};

    return function(id, message, duration) {
        if (!id || !message) {
            return;
        }

        let $flash = $("#" + id);
        if (!$flash.length) {
            $flash = $("<div>", { id: id });
            $("body").append($flash);
        }

        if (timers[id]) {
            clearTimeout(timers[id]);
            timers[id] = null;
        }

        $flash.stop(true, true).text(message).fadeIn(80);
        timers[id] = setTimeout(function() {
            $flash.fadeOut(120);
            timers[id] = null;
        }, duration || 1800);
    };
}());

window.logSoonicDebug = function(enabled, message) {
    'use strict';

    if (enabled) {
        console.log(message);
    }
};
