const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/e2e',
    fullyParallel: true,
    reporter: 'html',
    use: {
        baseURL: 'http://127.0.0.1:9810',
        trace: 'on-first-retry'
    },
    webServer: {
        command: 'symfony server:start --no-tls --allow-http --port=9810',
        url: 'http://127.0.0.1:9810',
        reuseExistingServer: true,
        timeout: 120 * 1000
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] }
        },
        {
            name: 'firefox',
            use: { ...devices['Desktop Firefox'] }
        }
    ]
});
