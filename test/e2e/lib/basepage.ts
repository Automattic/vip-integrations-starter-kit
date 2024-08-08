import type { Page } from '@playwright/test';

export class BasePage {
    protected readonly _page: Page

    public constructor(page: Page) {
        this._page = page;
    }

    public get page(): Page {
        return this._page;
    }

    public get url(): string {
        return this._page.url();
    }
}
