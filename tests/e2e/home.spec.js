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
    const pages = ['/', '/album/', '/radio/'];

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

    await expect(page.locator('.icon-to-start')).toHaveAttribute('aria-label', /.+/);
    await expect(page.locator('.icon-to-end')).toHaveAttribute('aria-label', /.+/);
});

async function assertTitleMatchesView(page, selector) {
    const expectedTitle = await page.locator(selector).first().getAttribute('data-page-title');
    expect(expectedTitle).toBeTruthy();
    await expect(page).toHaveTitle(expectedTitle);
}
