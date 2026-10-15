import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {configure, getClassList, TEST_HANDLE, THEMES} from './helpers/configure';

for (const theme of THEMES) {
    test.describe(`Loki CSS classes (${theme})`, () => {
        test.beforeEach(async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page, [TEST_HANDLE]);
        });

        test('should render block CSS classes from all sources', async ({page}) => {
            expect(await getClassList(page, '#loki-css-utils-test')).toEqual([
                'loki-css-utils-test',
                'scope-block',
                'loki-test-default',
                'loki-test-local',
                'loki-test-global',
                'loki-test-template-group',
            ]);
        });

        test('should render scoped CSS classes with overrides and groups', async ({page}) => {
            expect(await getClassList(page, '#loki-css-utils-test-heading')).toEqual([
                'loki-css-utils-test__heading',
                'scope-heading',
                'loki-test-heading-override',
                'loki-test-group',
            ]);
        });

        test('should only render the forced CSS class', async ({page}) => {
            expect(await getClassList(page, '#loki-css-utils-test-forced')).toEqual([
                'loki-css-utils-test__forced',
                'scope-forced',
                'loki-test-forced',
            ]);
        });
    });
}
