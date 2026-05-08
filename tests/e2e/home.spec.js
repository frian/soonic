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
