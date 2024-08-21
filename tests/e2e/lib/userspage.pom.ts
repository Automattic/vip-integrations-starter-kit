import type { Locator, Page, Response } from "@playwright/test";
import { AdminPage } from "./adminpage";
import { BasePage } from "./basepage";

export class UsersPage extends AdminPage {
    public readonly toggleAllUsersLocator: Locator;
    public readonly addUserLinkLocator: Locator;
    public readonly searchUsersInputLocator: Locator;
    public readonly searchSubmitButtonLocator: Locator;

    public constructor(page: Page) {
        super(page);

        this.toggleAllUsersLocator = page.locator('#cb-select-all-1');
        this.addUserLinkLocator = page.locator('a.page-title-action');
        this.searchUsersInputLocator = page.locator('input#user-search-input');
        this.searchSubmitButtonLocator = page.locator('input#search-submit');
    }

    public visit(): Promise<Response> {
        return this.page.goto('/wp-admin/users.php') as Promise<Response>;
    }

    public async editUserByID(id: number): Promise<BasePage> {
        await this.userRowLocator(id).locator('td.username > strong > a').click();
        await this.page.waitForLoadState('load');
        return new BasePage(this.page);
    }

    public async rowAction(id: number, action: string): Promise<BasePage> {
        const rowActionsLocator = this.userRowActionLocator(id)
        await rowActionsLocator.hover();
        await rowActionsLocator.locator(`span.${action} > a`).click();
        await this.page.waitForLoadState('load');
        return new BasePage(this.page);
    }

    public viewUserPostsByID(id: number): Promise<BasePage> {
        return this.rowAction(id, 'view');
    }

    public toggleUserSelection(id: number): Promise<void> {
        return this.userRowLocator(id).locator('th.check-column > input[type="checkbox"]').click();
    }

    public toggleAllUsers(): Promise<void> {
        return this.toggleAllUsersLocator.click();
    }

    public async searchForUsers(search: string): Promise<this> {
        await this.searchUsersInputLocator.fill(search);
        await this.searchSubmitButtonLocator.click();
        await this.page.waitForLoadState('load');
        return this;
    }

    public async usersFound(): Promise<boolean> {
        return await this.page.locator('#the-list > .no-items').count() === 0;
    }

    private userRowLocator(id: number): Locator {
        return this.page.locator(`tr#user-${id}`);
    }

    private userRowActionLocator(id: number): Locator {
        return this.userRowLocator(id).locator('div.row-actions');
    }
}
