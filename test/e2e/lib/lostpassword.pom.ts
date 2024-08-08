import type { Locator, Page, Response } from '@playwright/test';
import { BasePage } from './basepage';
import { ResetPasswordSuccessPage } from './resetpasswordsuccess.pom';

export class LostPasswordPage extends BasePage {
    public readonly loginField: Locator;
    public readonly getPasswordButton: Locator;
    public readonly loginErrorBlock: Locator;
    public readonly loginLink: Locator;
    public readonly registerLink: Locator;
    public readonly backToBlogLink: Locator;

    public constructor(page: Page) {
        super(page);
        this.loginField = page.locator('input#user_login');
        this.getPasswordButton = page.locator('input#wp-submit');
        this.loginErrorBlock = page.locator('div#login_error');
        this.loginLink = page.locator('#nav a[href$="wp-login.php"]');
        this.registerLink = page.locator('#nav a[href*="wp-login.php?action=register"]');
        this.backToBlogLink = page.locator('#backtoblog a');
    }

    /**
     * Returns the main resource response. In case of multiple redirects, the navigation will resolve with the first
     * non-redirect response.
     *
     * The method will throw an error if:
     * - there's an SSL error (e.g. in case of self-signed certificates).
     * - target URL is invalid.
     * - the `timeout` is exceeded during navigation.
     * - the remote server does not respond or is unreachable.
     * - the main resource failed to load.
     *
     * The method will not throw an error when any valid HTTP status code is returned by the remote server, including 404
     * "Not Found" and 500 "Internal Server Error".  The status code for such responses can be retrieved by calling
     * [response.status()](https://playwright.dev/docs/api/class-response#response-status).
     */
    public visit(): Promise<Response> {
        return this.page.goto('./wp-login.php?action=lostpassword') as Promise<Response>;
    }

    public async resetPassword(login: string): Promise<LostPasswordPage | ResetPasswordSuccessPage | Page> {
        await this.loginField.fill(login);
        await this.getPasswordButton.click();
        await this.page.waitForLoadState('load');

        const url = new URL(this.lastNavigationRequest?.url() ?? '');
        if (url.pathname.endsWith('/wp-login.php')) {
            if (url.searchParams.get('action') === 'lostpassword') {
                return new LostPasswordPage(this.page);
            }

            if (url.searchParams.get('checkemail') === 'confirm') {
                return new ResetPasswordSuccessPage(this.page);
            }
        }

        return this.page;
    }
}
