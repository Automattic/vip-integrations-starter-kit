import { expect, test } from '@playwright/test';
import { LoginPage } from '../lib/login.pom';
import { LostPasswordPage } from '../lib/lostpassword.pom';
import { ResetPasswordSuccessPage } from '../lib/resetpasswordsuccess.pom';

test.describe('Reset Password page', () => {
    test.beforeEach(async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.visit();
        await loginPage.lostPassword();
    });

    test('Reset password', async ({ page }) => {
        const resetPasswordPage = new LostPasswordPage(page);
        const newPage = await resetPasswordPage.resetPassword('vipgo');
        expect(newPage).toBeInstanceOf(ResetPasswordSuccessPage);
    });
});
