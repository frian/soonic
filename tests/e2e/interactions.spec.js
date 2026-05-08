const { test, expect } = require('@playwright/test');

test.beforeEach(async ({ page }) => {
    const errors = [];

    page.on('pageerror', function(error) {
        errors.push(error.message);
    });

    await page.goto('/');
    await expect(page).toHaveTitle(/Soonic/);

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
            this.__soonicPaused = false;

            if (this.closest('.radios-view') && this.__soonicRejectPlay) {
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
        audio.__soonicRejectPlay = true;
    });
}
