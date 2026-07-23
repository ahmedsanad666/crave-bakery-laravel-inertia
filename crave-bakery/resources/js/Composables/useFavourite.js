import { router } from '@inertiajs/vue3';

const defaultVisitOptions = {
    preserveScroll: true,
};

/**
 * Favourite mutations via Inertia (toggle / clear).
 */
export function useFavourite() {
    const toggle = (productSlug, options = {}) => {
        if (!productSlug) {
            return;
        }

        router.post(route('favourites.toggle', productSlug), {}, {
            ...defaultVisitOptions,
            ...options,
        });
    };

    const clear = (options = {}) => {
        router.delete(route('favourites.clear'), {
            ...defaultVisitOptions,
            ...options,
        });
    };

    return {
        toggle,
        clear,
    };
}
