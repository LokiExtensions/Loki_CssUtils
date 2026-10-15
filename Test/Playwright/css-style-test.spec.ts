import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {configure, TEST_HANDLE, THEMES} from './helpers/configure';

for (const theme of THEMES) {
    test.describe(`Loki CSS styles (${theme})`, () => {
        test.beforeEach(async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page, [TEST_HANDLE]);
        });

        test('should render the style attribute from the XML layout', async ({page}) => {
            const element = page.locator('#loki-css-utils-test');
            await expect(element).toHaveAttribute('style', 'color:rgb(255, 0, 0);margin-top:7px;');
            await expect(element).toHaveCSS('color', 'rgb(255, 0, 0)');
            await expect(element).toHaveCSS('margin-top', '7px');
        });
    });
}
