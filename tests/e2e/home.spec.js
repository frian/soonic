const { test, expect } = require('@playwright/test');

test('home page loads without JavaScript errors', async ({ page }) => {
    const errors = [];

    page.on('pageerror', function(error) {
        errors.push(error.message);
    });

    await page.goto('/');
    await expect(page).toHaveTitle(/Soonic/);

    expect(errors).toEqual([]);
});

test('main pages load without JavaScript errors', async ({ page }) => {
    const errors = [];
    const pages = ['/settings', '/album/', '/radio/'];

    page.on('pageerror', function(error) {
        errors.push(error.message);
    });

    for (const url of pages) {
        await page.goto(url);
        await expect(page).toHaveTitle(/Soonic/);
    }

    expect(errors).toEqual([]);
});

test('ajax navigation updates document title from visible view metadata', async ({ page }) => {
    await page.goto('/');

    await assertTitleMatchesView(page, '.library-view [data-page-title]');

    await page.locator('#albums-button').click();
    await expect(page.locator('.albums-view')).toBeVisible();
    await assertTitleMatchesView(page, '.albums-view');

    await page.locator('#library-button').click();
    await expect(page.locator('.library-view')).toBeVisible();
    await assertTitleMatchesView(page, '.library-view [data-page-title]');

    await page.locator('#radio-button').click();
    await expect(page.locator('.radios-view')).toBeVisible();
    await assertTitleMatchesView(page, '.radios-view');

    await page.locator('#settings-button').click();
    await expect(page.locator('.settings-view')).toBeVisible();
    await assertTitleMatchesView(page, '.settings-view');
});

test('ajax history back and forward keep URL and document title in sync', async ({ page }) => {
    await page.goto('/');
    await assertTitleMatchesView(page, '.library-view [data-page-title]');

    await page.locator('#albums-button').click();
    await expect(page).toHaveURL(/\/album\/$/);
    await assertTitleMatchesView(page, '.albums-view');

    await page.locator('#radio-button').click();
    await expect(page).toHaveURL(/\/radio\/$/);
    await assertTitleMatchesView(page, '.radios-view');

    await page.goBack();
    await expect(page).toHaveURL(/\/album\/$/);
    await assertTitleMatchesView(page, '.albums-view');

    await page.goBack();
    await expect(page).toHaveURL(/\/$/);
    await assertTitleMatchesView(page, '.library-view [data-page-title]');

    await page.goForward();
    await expect(page).toHaveURL(/\/album\/$/);
    await assertTitleMatchesView(page, '.albums-view');

    await page.goForward();
    await expect(page).toHaveURL(/\/radio\/$/);
    await assertTitleMatchesView(page, '.radios-view');
});

test('saving settings refreshes topbar through update fragment endpoint', async ({ page }) => {
    const seenUpdateRequests = [];

    page.on('request', function(request) {
        if (request.url().includes('/settings/?action=update')) {
            seenUpdateRequests.push(request.url());
        }
    });

    await page.goto('/');
    await page.locator('#settings-button').click();
    await expect(page.locator('.settings-view')).toBeVisible();

    await page.evaluate(function() {
        const topbar = document.querySelector('.topbar');
        if (topbar) {
            topbar.setAttribute('data-test-refresh-marker', 'before');
        }
    });

    await page.locator('#settings-form-button').click();

    await expect.poll(function() {
        return seenUpdateRequests.length;
    }).toBeGreaterThan(0);

    await expect(page.locator('.topbar[data-test-refresh-marker=\"before\"]')).toHaveCount(0);
    await expect(page.locator('.settings-view')).toBeVisible();

    // On settings view, only library/albums/radios should stay visible in topbar.
    await assertTopbarNavState(page, {
        visible: ['#navigation-library', '#navigation-albums', '#navigation-radios'],
        hidden: ['#navigation-settings', '#navigation-random', '#navigation-search-form', '#navigation-radio-new']
    });

    await expect(page.locator('.icon-to-start')).toHaveAttribute('aria-label', /.+/);
    await expect(page.locator('.icon-to-end')).toHaveAttribute('aria-label', /.+/);
});

test('topbar nav state stays coherent across settings save and next navigations', async ({ page }) => {
    const seenUpdateRequests = [];
    page.on('request', function(request) {
        if (request.url().includes('/settings/?action=update')) {
            seenUpdateRequests.push(request.url());
        }
    });

    await page.goto('/');

    await page.locator('#settings-button').click();
    await expect(page.locator('.settings-view')).toBeVisible();
    await assertTopbarNavState(page, {
        visible: ['#navigation-library', '#navigation-albums', '#navigation-radios'],
        hidden: ['#navigation-settings', '#navigation-random', '#navigation-search-form', '#navigation-radio-new']
    });

    await page.locator('#settings-form-button').click();
    await expect.poll(function() {
        return seenUpdateRequests.length;
    }).toBeGreaterThan(0);
    await expect(page.locator('.settings-view')).toBeVisible();
    await assertTopbarNavState(page, {
        visible: ['#navigation-library', '#navigation-albums', '#navigation-radios'],
        hidden: ['#navigation-settings', '#navigation-random', '#navigation-search-form', '#navigation-radio-new']
    });

    await page.locator('#library-button').click();
    await expect(page.locator('.library-view')).toBeVisible();
    await assertTopbarNavState(page, {
        visible: ['#navigation-random', '#navigation-albums', '#navigation-radios', '#navigation-settings', '#navigation-search-form'],
        hidden: ['#navigation-library', '#navigation-radio-new']
    });
});

test('ajax navigation redirects to error page on fatal load error', async ({ page }) => {
    await page.route('**/album/', async function(route) {
        await route.fulfill({
            status: 503,
            contentType: 'text/html',
            body: 'Service Unavailable'
        });
    });

    await page.goto('/');
    await page.locator('#albums-button').click();

    await expect(page).toHaveURL(/\/error\/503$/);
});

test('ajax random load error shows flash message without redirect', async ({ page }) => {
    await page.route('**/songs/random', async function(route) {
        await route.fulfill({
            status: 500,
            contentType: 'application/json',
            body: '{}'
        });
    });

    await page.goto('/');
    await page.locator('#random-button').click();

    await expect(page.locator('#ajax-flash-message')).toBeVisible();
    await expect(page.locator('#ajax-flash-message')).toHaveText('Unable to load random songs.');
    await expect(page).toHaveURL(/\/$/);
});

test('search ajax error shows flash message without redirect', async ({ page }) => {
    await page.route('**/search', async function(route) {
        await route.fulfill({
            status: 500,
            contentType: 'application/json',
            body: '{}'
        });
    });

    await page.goto('/');
    await page.locator('#form-keyword').fill('radio');
    await page.locator('#search-form').dispatchEvent('submit');

    await expect(page.locator('#ajax-flash-message')).toHaveText('Unable to load search results.');
    await expect(page).toHaveURL(/\/$/);
});

test('album actions work when landing directly on album page', async ({ page }) => {
    const errors = [];
    page.on('pageerror', function(error) {
        errors.push(error.message);
    });

    await page.goto('/album/');

    const firstAlbumId = await page.locator('.album-container').first().getAttribute('data-album-id');
    test.skip(!firstAlbumId, 'No album available in the current fixture.');

    await page.goto('/album/' + firstAlbumId);
    await mockAudioPlayback(page);

    const albumSongsCount = await page.locator('.album-songs tbody tr').count();
    test.skip(albumSongsCount === 0, 'Selected album has no songs in the current fixture.');

    await page.locator('.play-album').click();
    await expect(page.locator('#songs tbody tr')).toHaveCount(albumSongsCount);
    await expect(page.locator('#songs tbody tr.playing')).toHaveCount(1);

    await page.locator('.add-album-to-playlist').click();
    await expect(page.locator('#playlist tbody tr')).toHaveCount(albumSongsCount);

    expect(errors).toEqual([]);
});

async function assertTitleMatchesView(page, selector) {
    const expectedTitle = await page.locator(selector).first().getAttribute('data-page-title');
    expect(expectedTitle).toBeTruthy();
    await expect(page).toHaveTitle(expectedTitle);
}

async function assertTopbarNavState(page, state) {
    for (const selector of state.visible) {
        await expect(page.locator(selector)).toBeVisible();
    }

    for (const selector of state.hidden) {
        await expect(page.locator(selector)).toBeHidden();
    }
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
            this.dispatchEvent(new Event('playing'));
            return Promise.resolve();
        };
        HTMLMediaElement.prototype.pause = function() {
            this.__soonicPaused = true;
        };
        HTMLMediaElement.prototype.load = function() {};
    });
}
