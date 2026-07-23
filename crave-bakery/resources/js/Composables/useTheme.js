import { router, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

const FONT_LINK_ID = 'app-theme-fonts';

/**
 * @param {string} name
 * @param {string} weights
 */
function fontFamilyParam(name, weights) {
    return `family=${encodeURIComponent(name).replace(/%20/g, '+')}:wght@${weights}`;
}

/**
 * @param {string} heading
 * @param {string} body
 */
export function googleFontsHref(heading, body) {
    return (
        'https://fonts.googleapis.com/css2?' +
        fontFamilyParam(heading, '400;600;700') +
        '&' +
        fontFamilyParam(body, '400;500;600;700') +
        '&display=swap'
    );
}

/**
 * @param {string} heading
 * @param {string} body
 */
function upsertGoogleFonts(heading, body) {
    if (typeof document === 'undefined') {
        return;
    }

    let link = document.getElementById(FONT_LINK_ID);

    if (!link) {
        link = document.createElement('link');
        link.id = FONT_LINK_ID;
        link.rel = 'stylesheet';
        document.head.appendChild(link);
    }

    link.href = googleFontsHref(heading, body);
}

/**
 * Apply theme tokens + fonts from siteSettings.theme onto :root.
 *
 * @param {Record<string, any>|null|undefined} theme
 */
export function applyTheme(theme) {
    if (typeof document === 'undefined' || !theme) {
        return;
    }

    const root = document.documentElement;
    const tokens = theme.palette?.tokens ?? {};

    Object.entries(tokens).forEach(([key, value]) => {
        if (typeof value === 'string' && value !== '') {
            root.style.setProperty(`--color-${key}`, value);
        }
    });

    const heading = theme.font_heading || 'Playfair Display';
    const body = theme.font_body || 'Inter';

    root.style.setProperty('--font-heading', `'${heading}'`);
    root.style.setProperty('--font-body', `'${body}'`);

    upsertGoogleFonts(heading, body);
}

/**
 * Keep document theme in sync with shared Inertia siteSettings.
 */
export function useTheme() {
    const page = usePage();

    watch(
        () => page.props?.siteSettings?.theme,
        (theme) => applyTheme(theme),
        { immediate: true, deep: true },
    );
}

/**
 * Bootstrap theme from the initial Inertia page (before Vue setup).
 *
 * @param {Record<string, any>|null|undefined} initialPage
 */
export function bootTheme(initialPage) {
    applyTheme(initialPage?.props?.siteSettings?.theme);

    router.on('success', (event) => {
        applyTheme(event.detail?.page?.props?.siteSettings?.theme);
    });
}
