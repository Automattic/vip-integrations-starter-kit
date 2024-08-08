import { expect, test } from '@playwright/test';
import { LoginPage } from '../lib/login.pom';

test.describe('Login page', () => {
    test.beforeEach(async ({ page }) => {
        const loginPage = new LoginPage(page);
        return loginPage.visit();
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
