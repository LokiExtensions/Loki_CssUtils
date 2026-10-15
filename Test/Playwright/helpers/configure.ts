import {expect, Page} from '@playwright/test';

export const THEMES = ['Magento/luma', 'Hyva/default-csp'];

export const TEST_HANDLE = 'loki_css_utils_test';

export async function configure(page: Page, config: object) {
    const token = process.env.TEST_TOKEN;
    if (!token) {
        throw new Error('TEST_TOKEN is not set');
    }

    const response = await page.request.post(
        '/loki_functional_tests/index/configure?token=' + encodeURIComponent(token),
        {form: {config: JSON.stringify(config)}}
    );

    const body = await response.text();
    expect(response.status(), 'Configure response: ' + body).toBe(200);

    const data = JSON.parse(body);
    expect(data.error, 'Configure error').toBeUndefined();
    expect(data.result?.theme?.errors, 'Theme errors').toBeUndefined();
}

export async function getClassList(page: Page, selector: string): Promise<string[]> {
    const className = await page.locator(selector).getAttribute('class');
    return (className ?? '').split(/\s+/).filter(Boolean);
}
