import type { Locator, Page } from '@playwright/test';
import { BasePage } from './basepage';

export class ResetPasswordSuccessPage extends BasePage {
    public readonly messageBlock: Locator;
    public readonly backToBlogLink: Locator;

    public constructor(page: Page) {
        super(page);
        this.messageBlock = page.locator('#login .message');
        this.backToBlogLink = page.locator('#backtoblog a');
    }
}
