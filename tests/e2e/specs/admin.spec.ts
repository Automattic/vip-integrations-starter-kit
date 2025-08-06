import { expect, test } from '@playwright/test';
import { AdminPage } from '../lib/adminpage';
import { LoginPage } from '../lib/login.pom';

test.describe('Admin Dashboard', () => {
    test.use({ storageState: '.playwright/state.json' });

    test('Admin Dashboard', async ({ page }) => {
        const adminPage = new AdminPage(page);
        await adminPage.visit();
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL((url) => url.pathname.includes('/wp-admin/'));

        await expect(adminPage.adminBar).toBeVisible();
        await expect(adminPage.versionText).toHaveText(/Version/u);

        await adminPage.showMenu();
        await expect(adminPage.adminMenu).toBeVisible();

        if (await adminPage.menuToggle.isVisible()) {
            await adminPage.hideMenu();
            await expect(adminPage.adminMenu).not.toBeVisible();
        }
    });

    test('Log Out', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.visit();
        await loginPage.login('vipgo', 'password', true);

        const adminPage = new AdminPage(page);
        await adminPage.visit();
        const thePage = await adminPage.logOut();
        await expect(thePage.page).not.toHaveURL((url) => url.pathname.includes('/wp-admin/'));
    });
});
