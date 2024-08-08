import { expect, test } from '@playwright/test';

test('codespaces', async ({ page, baseURL }) => {
    const url = new URL(baseURL ?? '');
    if (url.hostname.endsWith('.app.github.dev')) {
        await page.goto('/');
        const title = await page.title();
        if (title.trim() === 'Codespaces Access Port') {
            const button = page.getByRole('button', { name: 'Continue', exact: true });
            await button.click();
            await expect(page).toHaveTitle(/WordPress/u);
        }
    }

    await page.context().storageState({ path: `state.json` });
});
