import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {configure, TEST_HANDLE, THEMES} from './helpers/configure';

for (const theme of THEMES) {
    test.describe(`Loki CSS properties (${theme})`, () => {
        test('should render the CSS properties block on every page', async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page);

            const styles = await page.locator('style').allTextContents();
            const emptyRootStyles = styles.filter(style => /^:root\s*\{\s*}$/.test(style.trim()));
            expect(emptyRootStyles).toHaveLength(1);
        });

        test('should define CSS custom properties from the XML layout', async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page, [TEST_HANDLE]);

            const value = await page.evaluate(() =>
                getComputedStyle(document.documentElement).getPropertyValue('--loki-css-utils-test').trim()
            );
            expect(value).toBe('42px');
        });
    });
}
