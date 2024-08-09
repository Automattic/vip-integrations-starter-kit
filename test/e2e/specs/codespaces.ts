import { expect, test } from '@playwright/test';

test('codespaces', async ({ page, baseURL }) => {
    const url = new URL(baseURL ?? '');
    if (url.hostname.endsWith('.app.github.dev')) {
        await page.goto('.');
        const title = await page.title();
        if (title.trim() === 'Codespaces Access Port') {
            await page.getByRole('button', { name: 'Continue', exact: true }).click();
            await expect(page).toHaveTitle(/WordPress/u);
        }
    }

    await page.context().storageState({ path: `.playwright/state.json` });
});
