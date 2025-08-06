import { expect, test } from '@playwright/test';
import { UsersPage } from '../lib/userspage.pom';

test.describe('User List', () => {
    let usersPage: UsersPage;
    test.beforeEach(async ({ page }) => {
        usersPage = new UsersPage(page);
        await usersPage.visit();
    });

    test('Search for vipgo', async ({ page }) => {
        await usersPage.searchForUsers('vipgo');
        expect(await usersPage.usersFound()).toBe(true);
    });

    test('Search for non-existing user', async ({ page }) => {
        await usersPage.searchForUsers('this-user-does-not-exist');
        expect(await usersPage.usersFound()).toBe(false);
    });
});
