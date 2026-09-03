import { defineConfig } from "@playwright/test";

export default defineConfig({
    testDir: "./tests/browser",
    workers: 1,
    use: {
        baseURL: "http://127.0.0.1:8010",
        channel: process.env.PLAYWRIGHT_CHANNEL || "chrome",
        viewport: { width: 1440, height: 1100 },
        screenshot: "only-on-failure",
    },
    webServer: {
        command: "php artisan serve --host=127.0.0.1 --port=8010",
        url: "http://127.0.0.1:8010",
        reuseExistingServer: !process.env.CI,
    },
});
