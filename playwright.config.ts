import { defineConfig, devices } from '@playwright/test';

let url: string;
if (process.env.E2E_URL) {
    url = process.env.E2E_URL;
} else if (process.env.CODESPACE_NAME && process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN) {
    url = `https://${process.env.CODESPACE_NAME}-80.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}/`;
} else {
    url = 'http://localhost';
}

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
    testDir: './test/e2e/specs',
    /* Run tests in files in parallel */
    fullyParallel: true,
    /* Fail the build on CI if you accidentally left test.only in the source code. */
    forbidOnly: !!process.env.CI,
    /* Retry on CI only */
    retries: process.env.CI ? 2 : 0,
    /* Opt out of parallel tests on CI. */
    workers: process.env.CI ? 1 : undefined,
    /* Reporter to use. See https://playwright.dev/docs/test-reporters */
    reporter: 'dot',

    outputDir: '.playwright',
    snapshotDir: './test/e2e/snapshots',
    globalSetup: './test/e2e/globalSetup',

    /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
    use: {
        /* Base URL to use in actions like `await page.goto('/')`. */
        baseURL: url,
        /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
        trace: 'on-first-retry',
    },

    /* Configure projects for major browsers */
    projects: [
        {
            name: 'setup',
            testMatch: /codespaces\.ts/
        },
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'], storageState: '.playwright/state.json' },
            dependencies: ['setup'],
        },
        {
            name: 'firefox',
            use: { ...devices['Desktop Firefox'], storageState: '.playwright/state.json' },
            dependencies: ['setup'],
        },
        {
            name: 'webkit',
            use: { ...devices['Desktop Safari'], storageState: '.playwright/state.json' },
            dependencies: ['setup'],
        },
        /* Test against mobile viewports. */
        // {
        //     name: 'Mobile Chrome',
        //     use: { ...devices['Pixel 5'], storageState: '.playwright/state.json' },
        //     dependencies: ['setup'],
        // },
    ],
});
