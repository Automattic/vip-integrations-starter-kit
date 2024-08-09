import { expect, test } from '@playwright/test';
import { AdminPage } from '../lib/adminpage';

test.describe('Admin Dashboard', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('./wp-admin/');
    });

    test('Admin Dashboard', async ({ page }) => {
        const adminPage = new AdminPage(page);
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
        const adminPage = new AdminPage(page);
        const thePage = await adminPage.logOut();
        expect(thePage.url).not.toContain('/wp-admin/');
    });
});
