<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/Public/ProductCard.vue';
import LatestOvenCard from '@/Components/Public/LatestOvenCard.vue';
import RecommendedProductCard from '@/Components/Public/RecommendedProductCard.vue';
import EmptyState from '@/Components/Shared/EmptyState.vue';
import {
    IconArrowLeft,
    IconArrowRight,
    IconStarFilled,
    IconStarHalfFilled,
} from '@tabler/icons-vue';
import { useCart } from '@/Composables/useCart';

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
    latestProducts: {
        type: Array,
        default: () => [],
    },
    recommendedProducts: {
        type: Array,
        default: () => [],
    },
});

const { add } = useCart();

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

const filterOptions = computed(() => [
    { id: 'all', name: 'All Items' },
    ...categoryFilters.value,
]);

const cycleFilter = (direction) => {
    const options = filterOptions.value;
    if (!options.length) {
        return;
    }

    const currentIndex = options.findIndex(
        (option) => String(option.id) === String(activeCategory.value),
    );
    const nextIndex =
        (currentIndex + direction + options.length) % options.length;

    activeCategory.value = options[nextIndex].id;
};

const testimonials = [
    {
        name: 'Sarah Jenkins',
        role: 'Loyal Customer',
        rating: 5,
        body: 'The sourdough here is life-changing. I drive 20 miles every Saturday just to get a fresh loaf. It\'s the highlight of my week.',
        avatar: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCJP5azAsw_utqBVlVzNYWKI_X0gOBKKU3NHA3Kx5E0sbXVGIbHJyvL9diOGE6KlvH4pwhAYzOKTNWpmTrDKVEAHybN-GnznkyghpctMjlQHAuY4EXDn_YkB53bXXkk6T-ZN3wSXKTBw2mWnuPKWsH2Iu-rOSLeLFB7S0rclq9DVxaybGrDzD9ooG59q80wvpdmmTO8sH1d12-w9W3-AddmILPzvgtX7hUg41huOQE9mK4lNfIWPYPHzF4c72hLbVxS1mtPBJrXUA',
    },
    {
        name: 'Mark Thompson',
        role: 'Food Critic',
        rating: 5,
        body: 'Their croissants are exactly like the ones I had in Paris. Flaky, buttery, and perfectly layered. Absolutely incredible quality.',
        avatar: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAj7ecliR_lGu9qb9kdES-4-sFsTLO6gDHu5s5KHsOpxkV51EBN8xY9z8jQ8r30tNOw9CweXCi1iHgSnUazsWtIwxykrNN5PctJZUdVGh8GRSB5ltM8FvJcD82OG_To61qYnMSBeY3Jy-HofxoCAm5nO05nnj1tXlNj7kqaaP6_T2ni4m4tvErrq60nmcDwFfZf1O9HCGfeRGYllhZpjf0k3uYET5JTeB-HbV9hNm6gNDcAYCrPTWXqLe4Ww7-PTX3GU-IxOfjLxQ',
    },
    {
        name: 'Elena Rodriguez',
        role: 'Local Resident',
        rating: 4.5,
        body: 'The staff is so warm and the atmosphere is lovely. My kids love the dark chocolate muffins. It\'s our favorite neighborhood spot.',
        avatar: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCWuz6Y2hEMoQ-Vrt_FtOIo6nR5A8TK0l6BDaq4x3LUlWQ8zyBVlpJr6xszestnl0rvUIYu2oCwg5wnvYx6oc-oX_nAkUyR_PoEfNQozADqLEI4J9mW3jt7c7cpfYy3zBGKaIax9eaHSTLCJplvLKKZ-GlZ-f1p4xqdnqUah71lbrSWlXCaw_bg5Uf9PYylGGPsfg3SVxNbxKDJKVO86trRBlJosUxoYpNqIxFo6lQfrt5r1259xIMZWHsM9blMIUZM7AhXQE2bMg',
    },
];

const handleAddToCart = (product) => {
    if (!product?.slug) {
        return;
    }

    add(product.slug, { quantity: 1 });
};

const starIcons = (rating) => {
    const full = Math.floor(rating);
    const half = rating % 1 >= 0.5;

    return { full, half };
};
</script>

<template>
    <AppLayout>
        <Head title="Home" />

        <!-- Hero -->
        <section
            class="hero-pattern relative flex min-h-[80vh] items-center overflow-hidden bg-primary pt-xxl"
        >
            <div
                class="container-page grid w-full items-center gap-xl py-xxl md:grid-cols-2"
            >
                <div class="animate-fade-in z-10 space-y-xl">
                    <div class="space-y-md">
                        <h1
                            class="font-serif text-display-lg-mobile leading-tight text-on-primary md:text-display-lg"
                        >
                            Baking Smiles,
                            <br />
                            One Pastry At A Time
                        </h1>
                        <p class="max-w-md font-sans text-body-lg text-on-primary/70">
                            Experience the warmth of our ovens delivered straight
                            to your heart. Artisanal craftsmanship meets
                            neighborhood comfort.
                        </p>
                    </div>

                    <Link
                        :href="route('products.index')"
                        class="inline-flex rounded-full bg-accent px-xxl py-md font-sans text-body-lg font-bold text-on-primary shadow-lg transition-all duration-200 hover:shadow-xl active:scale-95"
                    >
                        See Our Menu
                    </Link>

                    <div class="border-t border-white/10 pt-xl">
                        <p class="font-sans text-body-lg text-on-primary">
                            Bringing Joy With
                            <span class="font-bold text-accent">
                                Homemade Baked Delights
                            </span>
                        </p>
                    </div>
                </div>

                <div class="relative z-10 flex items-center justify-center">
                    <div
                        class="absolute -left-8 -top-8 hidden max-w-[200px] rounded-xl border border-white/20 bg-white/10 p-lg shadow-2xl backdrop-blur-md md:block"
                    >
                        <div class="mb-xs flex gap-xs text-accent">
                            <IconStarFilled
                                v-for="i in 4"
                                :key="i"
                                :size="18"
                            />
                            <IconStarHalfFilled :size="18" />
                        </div>
                        <p class="text-title-lg font-bold text-on-primary">
                            4.8 Stars
                        </p>
                        <p class="text-body-sm italic text-on-primary/60">
                            "The best croissant in the city, hands down!"
                        </p>
                    </div>

                    <div
                        class="animate-float relative aspect-square w-full max-w-md overflow-hidden rounded-full border-8 border-white/5 shadow-2xl"
                    >
                        <img
                            alt="Gourmet Croissant"
                            class="h-full w-full object-cover"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuCEiZOv3vfT-iDVk6YRQoz11JxNndYpTEDoOg98ZkNqTBfBLTS4ubx2TLUm_5KaC3-o1Sc7K9A1pGE7udIj_tO9IGJZS4cg7XiKCznzvrHH_PNFz0zpMf71y2Zc4Mm6Paw41iWA-FEb-oT5ddg4mYDoiBf6lfyeMm-sVUQYFDNaQVCqJj9HzJHf81hu9lRJI712daE-1AkEy3A1EtXG7JQX-pcHR0CuNo3-3bpWIEckwzcDfS8E_DRifQ7RPivZwu6VUXGRRq4Xgg"
                        />
                    </div>

                    <div
                        class="absolute -bottom-4 -right-4 -z-10 h-32 w-32 rounded-full bg-accent opacity-20 blur-3xl"
                    />
                </div>
            </div>
        </section>

        <!-- About -->
        <section id="about" class="bg-surface py-32">
            <div
                class="container-page grid items-center gap-xxl md:grid-cols-2"
            >
                <div class="group relative">
                    <div
                        class="aspect-[4/5] overflow-hidden rounded-2xl shadow-2xl"
                    >
                        <img
                            alt="Bakery Interior"
                            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuCmPeHM84-yiu02IwoBqmVbF2-FiRMsGOuHE1Bvly-ruXAUdfL8uFtzo-dMvYQjLUkcNSGjkiJE7CbKp6WJUyuhaCG2WRJpx1_ANQAd8xT90ZJ2HOncP7qEyopQ_-BVqWYUNKVUIk7G4rCX0S_c2f4MaWrTeii0DN973MghMCj3_pQWDq-uVQ6wxtn67TuD9CFsna6rgbpgYgKkJWp4V9p8csAKusW9YW0pegKPGnhlaeVJL9I5w9KPJOu6roeF4Azbyb3RgRAuiw"
                        />
                    </div>
                    <div
                        class="absolute -bottom-8 -right-8 hidden rounded-2xl bg-primary p-xl shadow-xl lg:block"
                    >
                        <p
                            class="font-serif text-headline-md italic text-on-primary"
                        >
                            Since 1994
                        </p>
                    </div>
                </div>

                <div class="space-y-xl">
                    <div class="space-y-md">
                        <h2
                            class="font-serif text-headline-lg leading-tight text-primary"
                        >
                            The Heart of Our Bakery
                        </h2>
                        <div class="h-1 w-24 rounded-full bg-accent" />
                    </div>
                    <div
                        class="space-y-lg font-sans text-body-lg leading-relaxed text-on-surface-variant"
                    >
                        <p>
                            For over three decades, Crave Bakery has been the
                            aromatic heartbeat of our neighborhood. What started
                            as a small family dream has blossomed into a
                            destination for those who appreciate the patient art
                            of slow-fermented dough and the golden crunch of a
                            perfect crust.
                        </p>
                        <p>
                            We believe that good bread takes time. Our bakers
                            arrive when the city still sleeps, hand-shaping every
                            loaf and tempering every batch of chocolate to ensure
                            that the warmth you feel in every bite is as authentic
                            as the ingredients we source from local artisans.
                        </p>
                    </div>
                    <Link
                        href="/#about"
                        class="group flex items-center gap-md font-bold text-primary transition-colors hover:text-accent"
                    >
                        <span>Learn Our Full Story</span>
                        <IconArrowRight
                            :size="20"
                            stroke-width="1.5"
                            class="transition-transform group-hover:translate-x-2"
                        />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Premium Selection -->
        <section id="products" class="bg-white py-32">
            <div class="container-page">
                <div
                    class="mb-xl flex flex-col items-end justify-between gap-lg md:flex-row"
                >
                    <div class="space-y-sm">
                        <h2 class="font-serif text-headline-lg text-primary">
                            Our Premium Selection
                        </h2>
                        <div class="h-1 w-24 rounded-full bg-accent" />
                    </div>
                    <div class="flex items-center gap-md">
                        <button
                            type="button"
                            class="flex h-12 w-12 items-center justify-center rounded-full border border-outline text-primary transition-all hover:bg-primary hover:text-white"
                            aria-label="Previous category"
                            @click="cycleFilter(-1)"
                        >
                            <IconArrowLeft :size="20" stroke-width="1.5" />
                        </button>
                        <button
                            type="button"
                            class="flex h-12 w-12 items-center justify-center rounded-full border border-outline text-primary transition-all hover:bg-primary hover:text-white"
                            aria-label="Next category"
                            @click="cycleFilter(1)"
                        >
                            <IconArrowRight :size="20" stroke-width="1.5" />
                        </button>
                    </div>
                </div>

                <div
                    v-if="filterOptions.length > 1"
                    class="no-scrollbar flex gap-md overflow-x-auto pb-md"
                >
                    <button
                        v-for="option in filterOptions"
                        :key="option.id"
                        type="button"
                        class="whitespace-nowrap rounded-full px-lg py-sm font-sans text-body-sm font-bold transition-all"
                        :class="
                            String(activeCategory) === String(option.id)
                                ? 'bg-primary text-on-primary shadow-md'
                                : 'border border-outline text-on-surface-variant hover:border-primary hover:text-primary'
                        "
                        @click="activeCategory = option.id"
                    >
                        {{ option.name }}
                    </button>
                </div>

                <div
                    v-if="filteredProducts.length"
                    class="mt-xl grid grid-cols-1 gap-xl md:grid-cols-2 lg:grid-cols-3"
                >
                    <ProductCard
                        v-for="(product, index) in filteredProducts"
                        :key="product.id"
                        :product="product"
                        :featured-cta="index === 0"
                        @add-to-cart="handleAddToCart"
                    />
                </div>

                <EmptyState
                    v-else
                    class="mt-xl"
                    title="Products coming soon"
                    description="Featured products will appear here once they are added in the admin panel."
                >
                    <Link :href="route('products.index')" class="btn-primary">
                        Browse Products
                    </Link>
                </EmptyState>
            </div>
        </section>

        <!-- Latest From Our Ovens -->
        <section class="hero-pattern bg-primary py-32">
            <div class="container-page">
                <div class="mb-xl space-y-sm text-center">
                    <h2 class="font-serif text-headline-lg text-on-primary">
                        Latest From Our Ovens
                    </h2>
                    <p class="font-sans text-body-lg text-on-primary/60">
                        Fresh innovations and seasonal specials
                    </p>
                    <div class="mx-auto h-1 w-24 rounded-full bg-accent" />
                </div>

                <div
                    v-if="latestProducts.length"
                    class="grid grid-cols-1 gap-xl md:grid-cols-3"
                >
                    <LatestOvenCard
                        v-for="product in latestProducts"
                        :key="product.id"
                        :product="product"
                        @add-to-cart="handleAddToCart"
                    />
                </div>

                <EmptyState
                    v-else
                    class="mt-xl rounded-2xl bg-white/5"
                    title="Fresh batches on the way"
                    description="New products will show up here as soon as they are published."
                />
            </div>
        </section>

        <!-- Recommended For You -->
        <section class="bg-surface-bright py-32">
            <div class="container-page">
                <div class="mb-xl space-y-sm">
                    <h2 class="font-serif text-headline-lg text-primary">
                        Recommended For You
                    </h2>
                    <div class="h-1 w-24 rounded-full bg-accent" />
                </div>

                <div
                    v-if="recommendedProducts.length"
                    class="grid grid-cols-1 gap-lg sm:grid-cols-2 lg:grid-cols-4"
                >
                    <RecommendedProductCard
                        v-for="product in recommendedProducts"
                        :key="product.id"
                        :product="product"
                        @add-to-cart="handleAddToCart"
                    />
                </div>

                <EmptyState
                    v-else
                    class="mt-xl"
                    title="Recommendations coming soon"
                    description="Personalized picks will appear here once more products are available."
                />
            </div>
        </section>

        <!-- Testimonials -->
        <section class="bg-surface py-32">
            <div class="container-page">
                <div class="mb-xxl space-y-md text-center">
                    <h2 class="font-serif text-headline-lg text-primary">
                        What Our Customers Say
                    </h2>
                    <div class="mx-auto h-1 w-24 rounded-full bg-accent" />
                </div>

                <div class="grid grid-cols-1 gap-xl md:grid-cols-3">
                    <article
                        v-for="item in testimonials"
                        :key="item.name"
                        class="relative space-y-lg rounded-2xl bg-white p-xl shadow-md"
                    >
                        <span
                            class="absolute -top-4 left-8 font-serif text-6xl text-accent/20"
                        >
                            “
                        </span>
                        <div class="flex text-accent">
                            <IconStarFilled
                                v-for="i in starIcons(item.rating).full"
                                :key="`full-${i}`"
                                :size="20"
                            />
                            <IconStarHalfFilled
                                v-if="starIcons(item.rating).half"
                                :size="20"
                            />
                        </div>
                        <p
                            class="font-sans text-body-lg italic leading-relaxed text-on-surface-variant"
                        >
                            "{{ item.body }}"
                        </p>
                        <div
                            class="flex items-center gap-md border-t border-outline-variant pt-md"
                        >
                            <div
                                class="h-12 w-12 overflow-hidden rounded-full bg-surface-container"
                            >
                                <img
                                    :alt="item.name"
                                    class="h-full w-full object-cover"
                                    :src="item.avatar"
                                />
                            </div>
                            <div>
                                <p class="font-bold text-primary">
                                    {{ item.name }}
                                </p>
                                <p
                                    class="text-xs uppercase tracking-widest text-on-surface-variant"
                                >
                                    {{ item.role }}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
