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
            maxDiffPixelRatio: 0.02,
        });
    });

    test('Login with valid credentials', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.login('vipgo', 'password', true);

        expect(page.url()).toMatch(/\/wp-admin\//ug);
    });

    test('Login with invalid credentials', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.login('vipgo', 'wrongpassword', true);

        await expect(loginPage.loginErrorBlock).toBeVisible();
    });

    test('Visit Lost Password page', async ({ page }) => {
        const loginPage = new LoginPage(page);
        const lostPasswordPage = await loginPage.lostPassword();
        expect(lostPasswordPage.url).toMatch(/\/wp-login\.php\?action=lostpassword/ug);
    });
});
