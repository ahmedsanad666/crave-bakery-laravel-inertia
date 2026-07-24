import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { bootTheme } from './Composables/useTheme';

const fallbackSiteName = import.meta.env.VITE_APP_NAME || 'Crave Bakery';

const resolveSiteName = (page) =>
    page?.props?.siteSettings?.site_name ||
    page?.props?.siteSettings?.name ||
    fallbackSiteName;

let siteName = fallbackSiteName;

createInertiaApp({
    title: (title) => {
        if (!title) {
            return siteName;
        }
        if (title === siteName || title.includes(siteName)) {
            return title;
        }
        return `${title} - ${siteName}`;
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        siteName = resolveSiteName(props.initialPage);
        bootTheme(props.initialPage);

        router.on('navigate', (event) => {
            siteName = resolveSiteName(event.detail.page);
        });

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
