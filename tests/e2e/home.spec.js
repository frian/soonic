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
