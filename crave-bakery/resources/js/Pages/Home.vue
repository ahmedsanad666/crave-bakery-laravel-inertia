<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CategoryCard from '@/Components/Public/CategoryCard.vue';
import ProductCard from '@/Components/Public/ProductCard.vue';
import EmptyState from '@/Components/Shared/EmptyState.vue';
import { IconStarFilled } from '@tabler/icons-vue';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    featuredProducts: {
        type: Array,
        default: () => [],
    },
    featuredCategories: {
        type: Array,
        default: () => [],
    },
});

const activeCategory = ref('all');

const categoryFilters = computed(() => {
    const categories = new Map();

    props.featuredProducts.forEach((product) => {
        product.categories?.forEach((category) => {
            categories.set(category.id, category.name);
        });
    });

    return Array.from(categories.entries()).map(([id, name]) => ({
        id,
        name,
    }));
});

const filteredProducts = computed(() => {
    if (activeCategory.value === 'all') {
        return props.featuredProducts;
    }

    return props.featuredProducts.filter((product) =>
        product.categories?.some(
            (category) => String(category.id) === String(activeCategory.value),
        ),
    );
});

const handleAddToCart = () => {
    // Cart wiring comes in Phase 4
};

const handleToggleFavourite = () => {
    // Favourites wiring comes in Phase 5
};
</script>

<template>
    <AppLayout>
        <Head title="Home" />

        <!-- Hero -->
        <section class="relative overflow-hidden bg-primary">
            <div
                class="pointer-events-none absolute inset-0 opacity-10"
                aria-hidden="true"
            >
                <div
                    class="absolute -left-16 top-10 h-40 w-40 rounded-full border border-white/30"
                />
                <div
                    class="absolute bottom-0 right-10 h-56 w-56 rounded-full border border-white/20"
                />
            </div>

            <div
                class="container-page relative grid gap-10 py-xxl lg:grid-cols-2 lg:items-center lg:py-24"
            >
                <div class="space-y-6">
                    <p class="text-label-caps uppercase text-accent">
                        Artisanal Warmth
                    </p>
                    <h1
                        class="font-serif text-display-lg-mobile text-white md:text-display-lg"
                    >
                        Baking Smiles, One Pastry At A Time
                    </h1>
                    <p class="max-w-lg text-body-lg text-white/80">
                        Freshly baked croissants, cakes, and artisan treats —
                        made with premium ingredients and delivered to your
                        door.
                    </p>

                    <div class="flex flex-wrap items-center gap-4">
                        <Link href="/products" class="btn-primary">
                            Shop Now
                        </Link>
                        <Link
                            href="/#about"
                            class="btn-secondary border-white/40 text-white hover:bg-white/10"
                        >
                            Our Story
                        </Link>
                    </div>

                    <div class="flex items-center gap-2 text-white/90">
                        <div class="flex text-accent">
                            <IconStarFilled
                                v-for="i in 5"
                                :key="i"
                                :size="18"
                            />
                        </div>
                        <span class="text-body-sm">
                            4.9 from 500+ happy customers
                        </span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-lg lg:max-w-none">
                    <div
                        class="aspect-square overflow-hidden rounded-card bg-primary-container shadow-modal"
                    >
                        <div
                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-container to-primary p-8"
                        >
                            <div class="text-center">
                                <div
                                    class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-accent/20 text-5xl"
                                >
                                    🥐
                                </div>
                                <p
                                    class="font-serif text-headline-sm text-white"
                                >
                                    Today's Fresh Batch
                                </p>
                                <p class="mt-2 text-body-sm text-white/70">
                                    Product photography coming soon
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured products -->
        <section id="products" class="section-spacing bg-surface">
            <div class="container-page">
                <div
                    class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <h2 class="font-serif text-headline-md text-primary">
                            Our Premium Products
                        </h2>
                        <p class="mt-2 max-w-xl text-body-lg text-text-muted">
                            Handcrafted pastries and seasonal specials, baked
                            fresh every morning.
                        </p>
                    </div>

                    <Link href="/products" class="btn-secondary shrink-0">
                        View All Products
                    </Link>
                </div>

                <div
                    v-if="categoryFilters.length"
                    class="mt-8 flex flex-wrap gap-2"
                >
                    <button
                        type="button"
                        class="rounded-full px-4 py-2 text-sm font-medium transition-colors"
                        :class="
                            activeCategory === 'all'
                                ? 'bg-primary text-white'
                                : 'bg-card text-primary shadow-card hover:bg-surface-container'
                        "
                        @click="activeCategory = 'all'"
                    >
                        All
                    </button>
                    <button
                        v-for="category in categoryFilters"
                        :key="category.id"
                        type="button"
                        class="rounded-full px-4 py-2 text-sm font-medium transition-colors"
                        :class="
                            activeCategory === category.id
                                ? 'bg-primary text-white'
                                : 'bg-card text-primary shadow-card hover:bg-surface-container'
                        "
                        @click="activeCategory = category.id"
                    >
                        {{ category.name }}
                    </button>
                </div>

                <div
                    v-if="filteredProducts.length"
                    class="mt-10 grid gap-gutter sm:grid-cols-2 lg:grid-cols-3"
                >
                    <ProductCard
                        v-for="product in filteredProducts"
                        :key="product.id"
                        :product="product"
                        @add-to-cart="handleAddToCart"
                        @toggle-favourite="handleToggleFavourite"
                    />
                </div>

                <EmptyState
                    v-else
                    class="mt-10"
                    title="Products coming soon"
                    description="Featured products will appear here once they are added in the admin panel."
                >
                    <Link href="/products" class="btn-primary">
                        Browse Products
                    </Link>
                </EmptyState>
            </div>
        </section>

        <!-- Featured categories -->
        <section class="section-spacing bg-surface-container-low">
            <div class="container-page">
                <div class="text-center">
                    <h2 class="font-serif text-headline-md text-primary">
                        Shop by Category
                    </h2>
                    <p class="mx-auto mt-3 max-w-2xl text-body-lg text-text-muted">
                        From flaky croissants to celebration cakes — find your
                        perfect treat.
                    </p>
                </div>

                <div
                    v-if="featuredCategories.length"
                    class="mt-10 grid gap-gutter sm:grid-cols-2 lg:grid-cols-4"
                >
                    <CategoryCard
                        v-for="category in featuredCategories"
                        :key="category.id"
                        :category="category"
                    />
                </div>

                <EmptyState
                    v-else
                    class="mt-10"
                    title="Categories coming soon"
                    description="Featured categories will appear here once they are configured in the admin panel."
                />
            </div>
        </section>

        <!-- About teaser -->
        <section id="about" class="section-spacing bg-surface">
            <div class="container-page grid items-center gap-10 lg:grid-cols-2">
                <div
                    class="aspect-[4/3] overflow-hidden rounded-card bg-gradient-to-br from-primary-container to-primary shadow-card"
                >
                    <div
                        class="flex h-full items-center justify-center text-7xl"
                    >
                        🍞
                    </div>
                </div>

                <div>
                    <p class="text-label-caps uppercase text-accent">
                        Our Story
                    </p>
                    <h2 class="mt-2 font-serif text-headline-md text-primary">
                        Baked with love, served with warmth
                    </h2>
                    <p class="mt-4 text-body-lg text-text-muted">
                        Crave Bakery started with a simple belief: everyone
                        deserves a moment of joy with something delicious. We
                        use traditional techniques and the finest ingredients
                        to create pastries that feel like a hug in every bite.
                    </p>
                    <Link href="/#about" class="btn-secondary mt-8">
                        Learn More
                    </Link>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
