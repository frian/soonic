const { test, expect } = require('@playwright/test');

test.beforeEach(async ({ page }) => {
    const errors = [];

    page.on('pageerror', function(error) {
        errors.push(error.message);
    });

    await page.goto('/');
    await expect(page).toHaveTitle(/Soonic/);
    await hideSymfonyToolbar(page);

    page.errors = errors;
});

test.afterEach(async ({ page }) => {
    expect(page.errors).toEqual([]);
});

test('add and remove a song shows playlist flash messages', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.evaluate(function() {
        document.querySelector('.songs').style.display = 'block';
        document.querySelector('.playlist').style.display = 'block';
        document.querySelector('#songs tbody').innerHTML = [
            '<tr data-path="/music/test.mp3" data-duration="03:00">',
            '<td class="add"><i class="icon-plus" role="button" tabindex="0"></i></td>',
            '<td>1</td>',
            '<td>Test Artist</td>',
            '<td>Test Song</td>',
            '<td>Test Album</td>',
            '<td>03:00</td>',
            '<td>2026</td>',
            '<td>Test</td>',
            '</tr>'
        ].join('');
    });

    await page.locator('#songs tbody tr .add').first().dispatchEvent('click');
    await expect(page.locator('#playlist tbody tr')).toHaveCount(1);
    await expect(page.locator('#playlist-flash-message')).toBeVisible();

    await page.locator('#playlist tbody tr .add').first().dispatchEvent('click');
    await expect(page.locator('#playlist tbody tr')).toHaveCount(0);
    await expect(page.locator('#playlist-flash-message')).toBeVisible();
});

test('invalid radio shows one flash for repeated media errors', async ({ page }) => {
    await page.locator('#radio-button').click();
    await expect(page.locator('.radios-view')).toBeVisible();

    await ensureRadioExists(page);
    await mockAudioPlayback(page);
    await countFlashCalls(page);

    await page.locator('.radio-play').first().click();

    await expect(page.locator('#radio-flash-message')).toBeVisible();
    await expect.poll(function() {
        return page.evaluate(function() {
            return window.__soonicFlashCalls || 0;
        });
    }).toBe(1);
});

test('topbar player pauses active radio', async ({ page }) => {
    await page.locator('#radio-button').click();
    await expect(page.locator('.radios-view')).toBeVisible();

    await ensureRadioExists(page);
    await mockAudioPlayback(page);

    await page.locator('.radio-play').first().click();
    await expect(page.locator('.radio-play').first()).toHaveClass(/icon-pause/);

    await page.evaluate(function() {
        document.querySelector('#player').setAttribute('src', '/music/test.mp3');
    });

    await page.locator('#play-pause-button').click();
    await expect(page.locator('.radio-play').first()).toHaveClass(/icon-play/);
    await expect(page.locator('#play-pause-button')).toHaveClass(/icon-pause/);
});

test('radio pauses active topbar player', async ({ page }) => {
    await mockAudioPlayback(page);
    await page.evaluate(function() {
        document.querySelector('#player').setAttribute('src', '/music/test.mp3');
    });

    await page.locator('#play-pause-button').click();
    await expect(page.locator('#play-pause-button')).toHaveClass(/icon-pause/);

    await page.locator('#radio-button').click();
    await expect(page.locator('.radios-view')).toBeVisible();

    await ensureRadioExists(page);
    await page.locator('.radio-play').first().click();

    await expect(page.locator('.radio-play').first()).toHaveClass(/icon-pause/);
    await expect(page.locator('#play-pause-button')).toHaveClass(/icon-play/);
});

test('radio new checks stream playback in browser', async ({ page }) => {
    await mockAudioPlayback(page);

    await page.locator('#radio-button').click();
    await expect(page.locator('.radios-view')).toBeVisible();

    await page.locator('#radio-new-button').click();
    await expect(page.locator('.radio-new-view')).toBeVisible();

    await page.locator('.radio-new-view [id$="_streamUrl"]').fill('https://example.invalid/stream.mp3');
    await page.locator('.radio-stream-check-button').click();

    await expect(page.locator('.radio-stream-check-result')).toHaveText(/stream OK/i);
});

test('radio edit checks stream playback in browser', async ({ page }) => {
    await mockAudioPlayback(page);

    await page.locator('#radio-button').click();
    await expect(page.locator('.radios-view')).toBeVisible();

    const editLink = page.locator('.radios-view .radio-edit-link').first();
    test.skip(await editLink.count() === 0, 'No radio edit link in the current fixture.');

    await editLink.click();
    await expect(page.locator('.radio-edit-view')).toBeVisible();

    await page.locator('.radio-edit-view [id$="_streamUrl"]').fill('https://example.invalid/stream.mp3');
    await page.locator('.radio-edit-view .radio-stream-check-button').click();

    await expect(page.locator('.radio-edit-view .radio-stream-check-result')).toHaveText(/stream OK/i);
});

test('topbar player play pause and play failure update the button', async ({ page }) => {
    await mockAudioPlayback(page);
    await page.evaluate(function() {
        document.querySelector('#player').setAttribute('src', '/music/test.mp3');
    });

    await page.locator('#play-pause-button').click();
    await expect(page.locator('#play-pause-button')).toHaveClass(/icon-pause/);

    await page.locator('#play-pause-button').click();
    await expect(page.locator('#play-pause-button')).toHaveClass(/icon-play/);

    await page.evaluate(function() {
        document.querySelector('#player').__soonicRejectPlay = true;
    });

    await page.locator('#play-pause-button').click();
    await expect(page.locator('#play-pause-button')).toHaveClass(/icon-play/);
});

test('topbar next and previous controls move the playing row', async ({ page }) => {
    await mockAudioPlayback(page);
    await seedSongRows(page);

    await page.locator('#songs tbody tr').first().click();
    await expect(page.locator('#songs tbody tr').first()).toHaveClass(/playing/);
    await expect(page.locator('#song-title')).toHaveText('First Song');

    await page.locator('.icon-to-end').first().click();
    await expect(page.locator('#songs tbody tr').nth(1)).toHaveClass(/playing/);
    await expect(page.locator('#song-title')).toHaveText('Second Song');

    await page.locator('.icon-to-start').first().click();
    await expect(page.locator('#songs tbody tr').first()).toHaveClass(/playing/);
    await expect(page.locator('#song-title')).toHaveText('First Song');
});

test('keyboard shortcuts focus search, control player, and navigate lists', async ({ page }) => {
    await mockAudioPlayback(page);
    await seedSongRows(page);

    await page.keyboard.press('/');
    await expect(page.locator('#form-keyword')).toBeFocused();
    await page.locator('#form-keyword').evaluate(function(input) {
        input.blur();
    });

    await page.evaluate(function() {
        document.querySelector('#player').setAttribute('src', '/music/test.mp3');
    });
    await page.keyboard.press('p');
    await expect(page.locator('#play-pause-button')).toHaveClass(/icon-pause/);
    await page.keyboard.press('p');
    await expect(page.locator('#play-pause-button')).toHaveClass(/icon-play/);

    await page.keyboard.press('ArrowDown');
    await expect(page.locator('#songs tbody tr').first()).toHaveClass(/keyboard-selected/);
    await page.keyboard.press('ArrowDown');
    await expect(page.locator('#songs tbody tr').nth(1)).toHaveClass(/keyboard-selected/);
    await page.keyboard.press('ArrowRight');
    await expect(page.locator('#songs tbody tr').nth(1)).toHaveClass(/playing/);

    await page.keyboard.press('b');
    await expect(page.locator('#songs tbody tr').first()).toHaveClass(/playing/);
    await page.keyboard.press('n');
    await expect(page.locator('#songs tbody tr').nth(1)).toHaveClass(/playing/);
});

test('keyboard shortcut removes selected playlist song', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await seedSongRows(page);

    await page.locator('#songs tbody tr .add').first().dispatchEvent('click');
    await page.locator('#songs tbody tr .add').nth(1).dispatchEvent('click');
    await expect(page.locator('#playlist tbody tr')).toHaveCount(2);

    await page.keyboard.press('ArrowDown');
    await expect(page.locator('#playlist tbody tr').first()).toHaveClass(/keyboard-selected/);

    await page.keyboard.press('Backspace');
    await expect(page.locator('#playlist tbody tr')).toHaveCount(1);
});

test('keyboard navigation starts from active artist and clears active state', async ({ page }) => {
    await page.evaluate(function() {
        document.querySelector('.songs').style.display = 'none';
        document.querySelector('.playlist').style.display = 'none';
        const artists = document.querySelectorAll('.artists-navigation a');

        artists.forEach(function(artist) {
            artist.classList.remove('active', 'keyboard-selected');
        });
        artists[2].classList.add('active');
    });

    await page.keyboard.press('ArrowDown');

    await expect(page.locator('.artists-navigation a').nth(2)).not.toHaveClass(/active/);
    await expect(page.locator('.artists-navigation a').nth(3)).toHaveClass(/keyboard-selected/);
    await expect(page.locator('.artists-navigation a.active')).toHaveCount(0);
});

test('keyboard navigation stays in artists list after opening artist albums', async ({ page }) => {
    await seedSongRows(page);
    await page.evaluate(function() {
        const artists = document.querySelectorAll('.artists-navigation a.artist');

        artists.forEach(function(artist) {
            artist.classList.remove('active', 'keyboard-selected');
        });
        artists[2].classList.add('active');
    });

    await page.keyboard.press('ArrowRight');
    await page.keyboard.press('ArrowDown');

    await expect(page.locator('.artists-navigation a.artist').nth(3)).toHaveClass(/keyboard-selected/);
    await expect(page.locator('#songs tbody tr.keyboard-selected')).toHaveCount(0);
});

test('empty playlist resets playlist info', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await seedSongRows(page);

    await page.locator('#songs tbody tr .add').first().dispatchEvent('click');
    await page.locator('#songs tbody tr .add').nth(1).dispatchEvent('click');

    await expect(page.locator('#playlist tbody tr')).toHaveCount(2);
    await expect(page.locator('#playlist-num-files')).toHaveText('2');

    await page.locator('.icon-trash').first().dispatchEvent('click');

    await expect(page.locator('#playlist tbody tr')).toHaveCount(0);
    await expect(page.locator('#playlist-num-files')).toHaveText('0');
    await expect(page.locator('#playlist-duration')).toHaveText('00:00');
});

test('switching radios pauses the previous radio', async ({ page }) => {
    await page.locator('#radio-button').click();
    await expect(page.locator('.radios-view')).toBeVisible();

    await ensureTwoRadiosExist(page);
    await mockAudioPlayback(page);

    const radios = page.locator('.radio-play');

    await radios.first().click();
    await expect(radios.first()).toHaveClass(/icon-pause/);

    await radios.nth(1).click();
    await expect(radios.first()).toHaveClass(/icon-play/);
    await expect(radios.nth(1)).toHaveClass(/icon-pause/);
});

test('album overlay opens, closes, and closes on browser back', async ({ page }) => {
    await page.locator('#albums-button').click();
    await expect(page.locator('.albums-view')).toBeVisible();

    const firstAlbum = page.locator('.albums-view .album-container .img-wrapper').first();
    await expect(firstAlbum).toBeVisible();

    await firstAlbum.click();
    await expect(page.locator('.single-album-view')).toBeVisible();

    await page.keyboard.press('Escape');
    await expect(page.locator('.single-album-view')).toHaveCount(0);

    await firstAlbum.click();
    await expect(page.locator('.single-album-view')).toBeVisible();

    await page.goBack();
    await expect(page.locator('.single-album-view')).toHaveCount(0);
});

test('album artist link is navigable from overlay', async ({ page }) => {
    await page.locator('#albums-button').click();
    await expect(page.locator('.albums-view')).toBeVisible();

    const firstAlbum = page.locator('.albums-view .album-container .img-wrapper').first();
    await expect(firstAlbum).toBeVisible();

    await firstAlbum.click();
    await expect(page.locator('.single-album-view')).toBeVisible();

    const artistLink = page.locator('.single-album-view a[href^="/artist/"]').first();
    test.skip(await artistLink.count() === 0, 'No artist link in the current album fixture.');

    const href = await artistLink.getAttribute('href');
    await artistLink.click();
    await expect(page).toHaveURL(new RegExp(href.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$'));
});

test('album pagination link keeps albums view visible', async ({ page }) => {
    await page.locator('#albums-button').click();
    await expect(page.locator('.albums-view')).toBeVisible();

    const paginationLink = page.locator('.albums-view .pagination a, .albums-pagination a').first();
    test.skip(await paginationLink.count() === 0, 'No album pagination in the current fixture.');

    await paginationLink.click();
    await expect(page.locator('.albums-view')).toBeVisible();
});

test('mobile menu closes after link click and search submit', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });

    await page.locator('.hamburger').click();
    await expect(page.locator('.top-nav')).toHaveClass(/is-active/);

    await page.locator('#form-keyword').evaluate(function(input) {
        input.value = 'test';
        input.form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    });
    await expect(page.locator('.top-nav')).not.toHaveClass(/is-active/);

    await page.locator('.hamburger').click();
    await expect(page.locator('.top-nav')).toHaveClass(/is-active/);

    await page.locator('#albums-button').click();
    await expect(page.locator('.top-nav')).not.toHaveClass(/is-active/);
});

test('desktop search updates the songs panel and keeps menu state stable', async ({ page }) => {
    await mockSearchResults(page);

    await page.locator('#form-keyword').fill('test');
    await page.locator('#search-form').evaluate(function(form) {
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    });

    await expect(page.locator('#songs tbody tr')).toHaveCount(1);
    await expect(page.locator('#songs tbody tr').first()).toContainText('Search Song');
    await expect(page.locator('.top-nav')).not.toHaveClass(/is-active/);
});

test('empty search clears keyword without updating songs panel', async ({ page }) => {
    await seedSongRows(page);
    await page.locator('#form-keyword').fill('ab');

    await page.locator('#search-form').evaluate(function(form) {
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    });

    await expect(page.locator('#form-keyword')).toHaveValue('');
    await expect(page.locator('#songs tbody tr')).toHaveCount(2);
});

test('mobile panel controls switch artists songs and playlist views', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await seedSongRows(page);

    await page.locator('.mobile-artists-to-songs-button').dispatchEvent('click');
    await expect(page.locator('.songs')).toBeVisible();
    await expect(page.locator('.playlist')).not.toBeVisible();

    await page.locator('.mobile-songs-to-playlist-button').dispatchEvent('click');
    await expect(page.locator('.playlist')).toBeVisible();
    await expect(page.locator('.songs')).not.toBeVisible();

    await page.locator('.mobile-playlist-to-songs-button').dispatchEvent('click');
    await expect(page.locator('.songs')).toBeVisible();
    await expect(page.locator('.playlist')).not.toBeVisible();

    await page.locator('.mobile-songs-to-artists-button').dispatchEvent('click');
    await expect(page.locator('.artists-navigation')).toBeVisible();
});

test('responsive breakpoints keep topbar controls usable', async ({ page }) => {
    for (const width of [390, 700, 1023, 1024, 1280]) {
        await page.setViewportSize({ width: width, height: 844 });
        await page.reload();
        await hideSymfonyToolbar(page);

        await expect(page.locator('#play-pause-button')).toBeVisible();
        if (width < 1024) {
            await page.locator('.hamburger').click();
        }
        await expect(page.locator('#form-keyword')).toBeVisible();
    }
});

async function hideSymfonyToolbar(page) {
    await page.addStyleTag({
        content: '.sf-toolbar { display: none !important; pointer-events: none !important; }'
    });
}

test('radio pagination link keeps radio list visible', async ({ page }) => {
    await page.locator('#radio-button').click();
    await expect(page.locator('.radios-view')).toBeVisible();

    const paginationLink = page.locator('.radios-pagination a').first();
    test.skip(await paginationLink.count() === 0, 'No radio pagination in the current fixture.');

    await paginationLink.click();
    await expect(page.locator('.radios-view')).toBeVisible();
});

async function ensureRadioExists(page) {
    await page.evaluate(function() {
        if (document.querySelector('.radios-view .radio-play')) {
            return;
        }

        document.querySelector('.radios-view').insertAdjacentHTML('afterbegin', [
            '<section class="radio clearfix">',
            '<div class="button-wrapper">',
            '<i class="icon-play radio-play" role="button" tabindex="0" aria-label="Play radio" title="Play radio"></i>',
            '<audio preload="none"><source src="https://invalid.local/stream.mp3" type="audio/mpeg"></audio>',
            '</div>',
            '<div class="radio-name">Invalid test radio</div>',
            '<div class="radio-url">-</div>',
            '</section>'
        ].join(''));
    });
}

async function ensureTwoRadiosExist(page) {
    await page.evaluate(function() {
        const radiosView = document.querySelector('.radios-view');
        const currentCount = document.querySelectorAll('.radios-view .radio-play').length;

        if (!radiosView || currentCount >= 2) {
            return;
        }

        for (let i = currentCount; i < 2; i++) {
            radiosView.insertAdjacentHTML('afterbegin', [
                '<section class="radio clearfix">',
                '<div class="button-wrapper">',
                '<i class="icon-play radio-play" role="button" tabindex="0" aria-label="Play radio" title="Play radio"></i>',
                '<audio preload="none"><source src="https://example.invalid/stream-' + i + '.mp3" type="audio/mpeg"></audio>',
                '</div>',
                '<div class="radio-name">Test radio ' + i + '</div>',
                '<div class="radio-url">-</div>',
                '</section>'
            ].join(''));
        }
    });
}

async function mockAudioPlayback(page) {
    await page.evaluate(function() {
        if (window.__soonicAudioMocked) {
            return;
        }

        window.__soonicAudioMocked = true;
        Object.defineProperty(HTMLMediaElement.prototype, 'paused', {
            configurable: true,
            get: function() {
                return this.__soonicPaused !== false;
            }
        });
        HTMLMediaElement.prototype.play = function() {
            if (this.__soonicRejectPlay) {
                this.__soonicPaused = true;
                return Promise.reject(new Error('Invalid test media'));
            }

            this.__soonicPaused = false;
            this.dispatchEvent(new Event('playing'));

            if (this.closest('.radios-view') && this.__soonicRejectRadioPlay) {
                ['error', 'stalled', 'abort'].forEach(function(eventName) {
                    this.dispatchEvent(new Event(eventName));
                }, this);

                return Promise.reject(new Error('Invalid test stream'));
            }

            return Promise.resolve();
        };
        HTMLMediaElement.prototype.pause = function() {
            this.__soonicPaused = true;
        };
        HTMLMediaElement.prototype.load = function() {};
    });
}

async function countFlashCalls(page) {
    await page.evaluate(function() {
        window.__soonicFlashCalls = 0;
        const originalShowSoonicFlash = window.showSoonicFlash;

        window.showSoonicFlash = function() {
            window.__soonicFlashCalls++;
            return originalShowSoonicFlash.apply(this, arguments);
        };

        const audio = document.querySelector('.radios-view audio');
        audio.__soonicRejectRadioPlay = true;
    });
}

async function seedSongRows(page) {
    await page.evaluate(function() {
        document.querySelector('.songs').style.display = 'block';
        document.querySelector('.playlist').style.display = 'block';
        document.querySelector('#songs tbody').innerHTML = [
            '<tr data-path="/music/first.mp3" data-duration="03:00">',
            '<td class="add"><i class="icon-plus" role="button" tabindex="0"></i></td>',
            '<td>1</td>',
            '<td>Test Artist</td>',
            '<td>First Song</td>',
            '<td>Test Album</td>',
            '<td>03:00</td>',
            '<td>2026</td>',
            '<td>Test</td>',
            '</tr>',
            '<tr data-path="/music/second.mp3" data-duration="02:30">',
            '<td class="add"><i class="icon-plus" role="button" tabindex="0"></i></td>',
            '<td>2</td>',
            '<td>Test Artist</td>',
            '<td>Second Song</td>',
            '<td>Test Album</td>',
            '<td>02:30</td>',
            '<td>2026</td>',
            '<td>Test</td>',
            '</tr>'
        ].join('');
    });
}

async function mockSearchResults(page) {
    await page.route('**/search**', function(route) {
        route.fulfill({
            status: 200,
            contentType: 'text/html',
            body: [
                '<tbody>',
                '<tr data-path="/music/search.mp3" data-duration="01:00">',
                '<td class="add"><i class="icon-plus" role="button" tabindex="0"></i></td>',
                '<td>1</td>',
                '<td>Search Artist</td>',
                '<td>Search Song</td>',
                '<td>Search Album</td>',
                '<td>01:00</td>',
                '<td>2026</td>',
                '<td>Search</td>',
                '</tr>',
                '</tbody>'
            ].join('')
        });
    });
}
