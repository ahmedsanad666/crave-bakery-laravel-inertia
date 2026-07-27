import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Resolve global SEO from shared siteSettings (mirrors SiteSettingService::documentSeo).
 *
 * @param {{ pageTitle?: string, category?: string, product?: string }} [options]
 */
export function useSiteSeo(options = {}) {
    const page = usePage();

    const site = computed(() => page.props.siteSettings ?? {});
    const seoSettings = computed(() => site.value.seo ?? {});

    const siteName = computed(
        () => site.value.site_name || site.value.name || 'Crave Bakery',
    );

    const tagline = computed(
        () =>
            site.value.tagline?.trim() ||
            site.value.overview?.trim() ||
            '',
    );

    const description = computed(
        () => seoSettings.value.meta_description?.trim() || '',
    );

    const keywords = computed(() => {
        const raw = seoSettings.value.meta_keywords;

        if (Array.isArray(raw)) {
            return raw.filter(Boolean).join(', ');
        }

        return typeof raw === 'string' ? raw : '';
    });

    const title = computed(() => {
        const pageTitle = options.pageTitle?.trim() || '';
        const template =
            seoSettings.value.title_template?.trim() ||
            '%site_name% | %tagline%';

        if (pageTitle && !template.includes('%page_title%')) {
            return `${pageTitle} - ${siteName.value}`;
        }

        const resolved = template
            .replaceAll('%site_name%', siteName.value)
            .replaceAll('%tagline%', tagline.value)
            .replaceAll('%category%', options.category ?? '')
            .replaceAll('%product%', options.product ?? '')
            .replaceAll('%page_title%', pageTitle)
            .replace(/\s*[|\-–—]\s*$/u, '')
            .replace(/^\s*[|\-–—]\s*/u, '')
            .replace(/\s{2,}/g, ' ')
            .trim();

        return resolved || siteName.value;
    });

    /** Short title for Inertia title callback (Catalogue → Catalogue - SiteName). */
    const headTitle = computed(() => {
        const pageTitle = options.pageTitle?.trim() || '';
        const template =
            seoSettings.value.title_template?.trim() ||
            '%site_name% | %tagline%';

        if (pageTitle && !template.includes('%page_title%')) {
            return pageTitle;
        }

        return title.value;
    });

    return {
        title,
        headTitle,
        description,
        keywords,
        siteName,
    };
}
