$(function() {
    'use strict';

    const debug = false;
    const editableSelector = 'input, textarea, select, [contenteditable="true"]';
    const keyboardSelectedClass = 'keyboard-selected';
    let keyboardScopeSelector = null;

    /**
     * Activate custom role=button controls with keyboard
     */
    $(document).on("keydown", '[role="button"]', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).trigger('click');
        }
    });

    /**
     * Handle global keyboard shortcuts
     */
    $(document).on("keydown", function(e) {
        const key = e.key;

        if (key === 'Escape') {
            closeTransientUi();
            return;
        }

        if (isEditableTarget(e.target)) {
            return;
        }

        if ((e.ctrlKey || e.metaKey) && key.toLowerCase() === 'k') {
            e.preventDefault();
            focusSearch();
            return;
        }

        if (key === '/') {
            e.preventDefault();
            focusSearch();
            return;
        }

        if (handleListNavigation(e)) {
            return;
        }

        const shortcuts = {
            p: '#play-pause-button',
            n: '.icon-to-end',
            b: '.icon-to-start',
            r: '#radio-button',
            a: '#albums-button',
            l: '#library-button',
            f: 'input[name=filter]'
        };
        const selector = shortcuts[key.toLowerCase()];

        if (!selector) {
            return;
        }

        e.preventDefault();

        if (selector === 'input[name=filter]') {
            $(selector).first().trigger('focus');
            return;
        }

        $(selector).first().trigger('click');
        logDebug('keyboard shortcut: ' + key);
    });


    function logDebug(message) {
        window.logSoonicDebug(debug, message);
    }

    function isEditableTarget(target) {
        return $(target).is(editableSelector);
    }

    function closeTransientUi() {
        $(document).trigger("soonic:closeAlbumOverlay");
        $(".songs-context-menu, .playlist-context-menu").css('display', 'none');
        $("#songs tbody tr.selected, #playlist tbody tr.selected").removeClass("selected");

        if ($(".hamburger").hasClass("is-active")) {
            $(".hamburger").trigger('click');
        }
    }

    function focusSearch() {
        if ($(window).width() < 1024 && !$(".top-nav").hasClass("is-active")) {
            $(".hamburger").trigger('click');
        }

        $("#form-keyword").trigger('focus');
    }

    function handleListNavigation(e) {
        if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Delete', 'Backspace'].includes(e.key)) {
            return false;
        }

        const $items = getKeyboardItems();
        const itemSelector = getKeyboardItemSelector($items);

        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            activateMobileBack();
            return true;
        }

        if (!$items.length) {
            return false;
        }

        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
            moveKeyboardSelection($items, e.key === 'ArrowDown' ? 1 : -1);
            keyboardScopeSelector = itemSelector;
            return true;
        }

        if (e.key === 'ArrowRight') {
            e.preventDefault();
            activateKeyboardSelection($items);
            keyboardScopeSelector = itemSelector;
            return true;
        }

        if (e.key === 'Delete' || e.key === 'Backspace') {
            return removeSelectedPlaylistSong(e, $items);
        }

        return false;
    }

    function getKeyboardItems() {
        const currentSelector = getCurrentKeyboardSelector();
        if (currentSelector) {
            const $currentItems = $(currentSelector).filter(':visible');
            if ($currentItems.length) {
                return $currentItems;
            }
        }

        if (keyboardScopeSelector) {
            const $scopedItems = $(keyboardScopeSelector).filter(':visible');
            if ($scopedItems.length) {
                return $scopedItems;
            }
        }

        const candidates = [
            '#playlist:visible tbody tr',
            '#songs:visible tbody tr',
            '.album-songs:visible tbody tr',
            '.artists-navigation:visible a'
        ];

        for (const selector of candidates) {
            const $items = $(selector).filter(':visible');
            if ($items.length) {
                return $items;
            }
        }

        return $();
    }

    function getCurrentKeyboardSelector() {
        const $selected = $("." + keyboardSelectedClass).filter(':visible').first();
        if ($selected.length) {
            return getKeyboardItemSelector($selected);
        }

        const $activeArtist = $(".artists-navigation a.active:visible").first();
        if ($activeArtist.length) {
            return getKeyboardItemSelector($activeArtist);
        }

        return null;
    }

    function getKeyboardItemSelector($items) {
        if (!$items.length) {
            return null;
        }

        const item = $items.first();

        if (item.closest('#playlist').length) {
            return '#playlist:visible tbody tr';
        }
        if (item.closest('#songs').length) {
            return '#songs:visible tbody tr';
        }
        if (item.closest('.album-songs').length) {
            return '.album-songs:visible tbody tr';
        }
        if (item.closest('.artists-navigation').length) {
            return '.artists-navigation:visible a';
        }

        return null;
    }

    function moveKeyboardSelection($items, direction) {
        let currentIndex = getCurrentKeyboardIndex($items);

        if (currentIndex < 0) {
            currentIndex = direction > 0 ? -1 : 0;
        }

        let nextIndex = currentIndex + direction;
        if (nextIndex < 0) {
            nextIndex = $items.length - 1;
        } else if (nextIndex >= $items.length) {
            nextIndex = 0;
        }

        $("." + keyboardSelectedClass).removeClass(keyboardSelectedClass);
        $items.filter('.active').removeClass('active');
        const $next = $items.eq(nextIndex).addClass(keyboardSelectedClass);
        scrollIntoView($next);
    }

    function getCurrentKeyboardIndex($items) {
        let currentIndex = $items.index($items.filter('.' + keyboardSelectedClass).first());

        if (currentIndex < 0) {
            currentIndex = $items.index($items.filter('.active').first());
        }

        return currentIndex;
    }

    function activateKeyboardSelection($items) {
        let $selected = $items.filter('.' + keyboardSelectedClass).first();

        if (!$selected.length) {
            $selected = $items.filter('.active').first();
        }

        if ($selected.length) {
            $selected.trigger('click');
            return;
        }

        if (!activateMobileForward()) {
            moveKeyboardSelection($items, 1);
        }
    }

    function activateMobileForward() {
        const $button = $(".mobile-artists-to-songs-button:visible, .mobile-songs-to-playlist-button:visible").first();
        if (!$button.length) {
            return false;
        }

        $button.trigger('click');
        return true;
    }

    function activateMobileBack() {
        const $button = $(".mobile-playlist-to-songs-button:visible, .mobile-songs-to-artists-button:visible").first();
        if ($button.length) {
            $button.trigger('click');
            return;
        }

        closeTransientUi();
    }

    function removeSelectedPlaylistSong(e, $items) {
        const $selected = $items.filter('.' + keyboardSelectedClass).first();

        if (!$selected.length || !$selected.closest('#playlist').length) {
            return false;
        }

        e.preventDefault();
        $selected.find('.add').first().trigger('click');
        return true;
    }

    function scrollIntoView($item) {
        if ($item.length && typeof $item[0].scrollIntoView === 'function') {
            $item[0].scrollIntoView({ block: 'nearest' });
        }
    }
});
