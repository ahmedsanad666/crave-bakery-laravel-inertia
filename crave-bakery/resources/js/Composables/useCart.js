import { router } from '@inertiajs/vue3';

const defaultVisitOptions = {
    preserveScroll: true,
};

/**
 * Cart mutations via Inertia (add / update / remove).
 * Shared cart.count updates automatically from HandleInertiaRequests.
 */
export function useCart() {
    const add = (productSlug, { quantity = 1, attributes = {} } = {}, options = {}) => {
        if (!productSlug) {
            return;
        }

        router.post(
            route('cart.add', productSlug),
            {
                quantity,
                attributes,
            },
            {
                ...defaultVisitOptions,
                ...options,
            },
        );
    };

    const update = (cartItemId, quantity, options = {}) => {
        if (!cartItemId) {
            return;
        }

        router.patch(
            route('cart.update', cartItemId),
            { quantity },
            {
                ...defaultVisitOptions,
                ...options,
            },
        );
    };

    const remove = (cartItemId, options = {}) => {
        if (!cartItemId) {
            return;
        }

        router.delete(route('cart.remove', cartItemId), {
            ...defaultVisitOptions,
            ...options,
        });
    };

    return {
        add,
        update,
        remove,
    };
}
