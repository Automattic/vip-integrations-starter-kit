import { test as base, expect } from '@playwright/test';
import { UsersPage } from '../lib/userspage.pom';

const test = base.extend<{ usersPage: UsersPage }>({
    usersPage: async ({ page }, use) => {
        const usersPage = new UsersPage(page);
        await usersPage.visit();
        await use(usersPage);
    }
});

test.describe('User List', () => {
    test('Search for vipgo', async ({ usersPage }) => {
        await usersPage.searchForUsers('vipgo');
        await expect(usersPage.noItemsLocator).toHaveCount(0);
    });

    test('Search for non-existing user', async ({ usersPage }) => {
        await usersPage.searchForUsers('this-user-does-not-exist');
        await expect(usersPage.noItemsLocator).toHaveCount(1);
    });
});
