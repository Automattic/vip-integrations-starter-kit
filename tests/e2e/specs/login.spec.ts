import { expect, test } from '@playwright/test';
import { LoginPage } from '../lib/login.pom';

test.describe('Login page', () => {
    test.beforeEach(async ({ page, context }) => {
        await context.clearCookies({ name: /^wordpress_/u });
        await page.goto('./wp-login.php');
    });

    test('Login page', async ({ page }) => {
        await expect(page).toHaveScreenshot('login.png', {
            fullPage: true,
            // One baseline is shared across the supported WP version matrix
            // (6.9/7.0). Core's login page drifts slightly between versions on
            // the mobile viewport (~3%), so allow headroom while still catching
            // real regressions.
            maxDiffPixelRatio: 0.05,
        });
    });

    test('Login with valid credentials', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.login('vipgo', 'password', true);

        await expect(page).toHaveURL((url) => url.pathname.includes('/wp-admin/'));
    });

    test('Login with invalid credentials', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.login('vipgo', 'wrongpassword', true);

        await expect(loginPage.loginErrorBlock).toBeVisible();
    });

    test('Visit Lost Password page', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.lostPassword();
        await expect(page).toHaveURL((url) => url.pathname.includes('/wp-login.php') && url.searchParams.get('action') === 'lostpassword');
    });
});
