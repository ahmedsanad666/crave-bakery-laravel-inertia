<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { IconAlertTriangle } from '@tabler/icons-vue';
import SettingsImageField from '@/Components/Admin/Settings/SettingsImageField.vue';
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({}),
    },
    palettes: {
        type: Array,
        default: () => [],
    },
    fonts: {
        type: Object,
        default: () => ({ heading: [], body: [] }),
    },
    resolvedPalette: {
        type: Object,
        default: null,
    },
});

const mediaUrl = (path) => {
    if (!path) {
        return null;
    }
    if (/^https?:\/\//i.test(path) || path.startsWith('/')) {
        return path;
    }
    return `/storage/${path}`;
};

const keywordsToString = (value) => {
    if (Array.isArray(value)) {
        return value.join(', ');
    }
    return value ? String(value) : '';
};

const formDefaults = () => ({
    name: props.settings.name ?? '',
    overview: props.settings.overview ?? '',
    hero_title: props.settings.hero_title ?? '',
    hero_description: props.settings.hero_description ?? '',
    hero_rating: props.settings.hero_rating ?? 3.5,
    hero_rating_description: props.settings.hero_rating_description ?? '',
    story_title: props.settings.story_title ?? '',
    story_content: props.settings.story_content ?? '',
    since_year: props.settings.since_year ?? 1999,
    email: props.settings.email ?? '',
    phone: props.settings.phone ?? '',
    address: props.settings.address ?? '',
    social_links: {
        facebook: props.settings.social_links?.facebook ?? '',
        instagram: props.settings.social_links?.instagram ?? '',
        twitter: props.settings.social_links?.twitter ?? '',
        youtube: props.settings.social_links?.youtube ?? '',
    },
    theme_palette: props.settings.theme_palette ?? props.resolvedPalette?.id ?? 'artisanal_warmth',
    font_heading: props.settings.font_heading ?? 'Playfair Display',
    font_body: props.settings.font_body ?? 'Inter',
    seo_title_template: props.settings.seo_title_template ?? '%site_name% | %tagline%',
    seo_meta_description: props.settings.seo_meta_description ?? '',
    seo_meta_keywords: keywordsToString(props.settings.seo_meta_keywords),
    logo: null,
    favicon: null,
    hero_image: null,
    story_image: null,
});

const form = useForm(formDefaults());

const logoPreviewUrl = ref(mediaUrl(props.settings.logo));
const faviconPreviewUrl = ref(mediaUrl(props.settings.favicon));
const heroImagePreviewUrl = ref(mediaUrl(props.settings.hero_image));
const storyImagePreviewUrl = ref(mediaUrl(props.settings.story_image));

const headingFontOptions = computed(() =>
    (props.fonts.heading ?? []).map((name) => ({ id: name, name })),
);

const bodyFontOptions = computed(() =>
    (props.fonts.body ?? []).map((name) => ({ id: name, name })),
);

const selectedPalette = computed(() => {
    return (
        props.palettes.find((palette) => palette.id === form.theme_palette)
        ?? props.resolvedPalette
        ?? props.palettes[0]
        ?? null
    );
});

const seoPreviewTitle = computed(() => {
    const template = form.seo_title_template || '%site_name%';
    return template
        .replaceAll('%site_name%', form.name || 'Crave Bakery')
        .replaceAll('%tagline%', form.overview || props.settings.tagline || '')
        .replaceAll('%category%', 'Category')
        .replaceAll('%product%', 'Product');
});

const seoDescriptionLength = computed(
    () => (form.seo_meta_description ?? '').length,
);

const googleFontsHref = computed(() => {
    const families = [form.font_heading, form.font_body]
        .filter(Boolean)
        .map((name) => `family=${encodeURIComponent(name).replace(/%20/g, '+')}:wght@400;600;700`);
    return `https://fonts.googleapis.com/css2?${families.join('&')}&display=swap`;
});

watch(
    () => props.settings,
    () => {
        form.defaults(formDefaults());
        form.reset();
        form.clearErrors();
        logoPreviewUrl.value = mediaUrl(props.settings.logo);
        faviconPreviewUrl.value = mediaUrl(props.settings.favicon);
        heroImagePreviewUrl.value = mediaUrl(props.settings.hero_image);
        storyImagePreviewUrl.value = mediaUrl(props.settings.story_image);
    },
    { deep: true },
);

watch(googleFontsHref, (href) => {
    let link = document.getElementById('settings-theme-fonts');
    if (!link) {
        link = document.createElement('link');
        link.id = 'settings-theme-fonts';
        link.rel = 'stylesheet';
        document.head.appendChild(link);
    }
    link.href = href;
}, { immediate: true });

const discard = () => {
    form.defaults(formDefaults());
    form.reset();
    form.clearErrors();
    form.logo = null;
    form.favicon = null;
    form.hero_image = null;
    form.story_image = null;
    logoPreviewUrl.value = mediaUrl(props.settings.logo);
    faviconPreviewUrl.value = mediaUrl(props.settings.favicon);
    heroImagePreviewUrl.value = mediaUrl(props.settings.hero_image);
    storyImagePreviewUrl.value = mediaUrl(props.settings.story_image);
};

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            social_links: {
                facebook: data.social_links.facebook || null,
                instagram: data.social_links.instagram || null,
                twitter: data.social_links.twitter || null,
                youtube: data.social_links.youtube || null,
            },
        }))
        .patch(route('admin.settings.update'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.logo = null;
                form.favicon = null;
                form.hero_image = null;
                form.story_image = null;
            },
        });
};

const fieldClass =
    'w-full rounded-lg border border-outline-variant bg-white px-md py-3 font-sans text-body-sm text-on-surface outline-none transition focus:border-secondary focus:ring-1 focus:ring-secondary/20';
</script>

<template>
    <AdminLayout title="Website Settings" breadcrumb="Settings">
        <Head title="Website Settings" />

        <div
            v-if="form.isDirty"
            class="mb-lg flex items-center gap-md rounded-xl border border-secondary/20 bg-secondary-fixed/40 px-lg py-md"
        >
            <IconAlertTriangle class="shrink-0 text-secondary" :size="20" stroke-width="1.5" />
            <span class="font-sans text-body-sm text-on-secondary-fixed-variant">
                You have unsaved changes.
            </span>
        </div>

        <div class="relative pb-32">
            <form class="mx-auto w-full max-w-4xl space-y-xl" @submit.prevent="submit">
                <!-- General -->
                <section
                    id="general"
                    class="scroll-mt-28 overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
                >
                    <div class="border-b border-outline-variant p-lg">
                        <h2 class="font-serif text-headline-sm text-primary">General Settings</h2>
                        <p class="font-sans text-body-sm text-on-surface-variant">
                            Manage your site identity and core business information.
                        </p>
                    </div>
                    <div class="space-y-lg p-lg">
                        <div class="grid gap-lg md:grid-cols-2">
                            <div>
                                <AppInputLabel for="settings-name" value="Site Name" />
                                <AppInput
                                    id="settings-name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :has-error="!!form.errors.name"
                                />
                                <AppInputError class="mt-1" :message="form.errors.name" />
                            </div>
                            <div>
                                <AppInputLabel for="settings-overview" value="Overview" />
                                <AppInput
                                    id="settings-overview"
                                    v-model="form.overview"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :has-error="!!form.errors.overview"
                                />
                                <AppInputError class="mt-1" :message="form.errors.overview" />
                            </div>
                        </div>

                        <div class="grid gap-lg md:grid-cols-2">
                            <SettingsImageField
                                v-model="form.logo"
                                label="Logo"
                                :current-url="logoPreviewUrl"
                                :error="form.errors.logo"
                            />
                            <SettingsImageField
                                v-model="form.favicon"
                                label="Favicon"
                                :current-url="faviconPreviewUrl"
                                :error="form.errors.favicon"
                            />
                        </div>

                        <hr class="border-outline-variant/50" />

                        <div class="grid gap-lg md:grid-cols-2">
                            <div>
                                <AppInputLabel for="settings-email" value="Email" />
                                <AppInput
                                    id="settings-email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    :has-error="!!form.errors.email"
                                />
                                <AppInputError class="mt-1" :message="form.errors.email" />
                            </div>
                            <div>
                                <AppInputLabel for="settings-phone" value="Phone" />
                                <AppInput
                                    id="settings-phone"
                                    v-model="form.phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :has-error="!!form.errors.phone"
                                />
                                <AppInputError class="mt-1" :message="form.errors.phone" />
                            </div>
                        </div>

                        <div>
                            <AppInputLabel for="settings-address" value="Address" />
                            <textarea
                                id="settings-address"
                                v-model="form.address"
                                rows="3"
                                class="mt-1"
                                :class="fieldClass"
                            />
                            <AppInputError class="mt-1" :message="form.errors.address" />
                        </div>
                    </div>
                </section>

                <!-- Appearance -->
                <section
                    id="appearance"
                    class="scroll-mt-28 overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
                >
                    <div
                        class="flex items-center justify-between bg-primary-container px-lg py-3 text-on-primary-container"
                    >
                        <span class="font-sans text-[11px] font-bold uppercase tracking-widest">
                            Palette preview
                        </span>
                    </div>
                    <div class="border-b border-outline-variant p-lg">
                        <h2 class="font-serif text-headline-sm text-primary">Appearance</h2>
                        <p class="font-sans text-body-sm text-on-surface-variant">
                            Choose a brand palette and typography from curated options.
                        </p>
                    </div>
                    <div class="space-y-lg p-lg">
                        <div class="space-y-md">
                            <AppInputLabel value="Brand color palettes" />
                            <div class="grid gap-md sm:grid-cols-2">
                                <button
                                    v-for="palette in palettes"
                                    :key="palette.id"
                                    type="button"
                                    class="rounded-xl border p-md text-left transition-all"
                                    :class="
                                        form.theme_palette === palette.id
                                            ? 'border-secondary ring-2 ring-secondary/30'
                                            : 'border-outline-variant hover:border-secondary/40'
                                    "
                                    @click="form.theme_palette = palette.id"
                                >
                                    <p class="mb-sm font-sans text-body-sm font-semibold text-on-surface">
                                        {{ palette.label }}
                                    </p>
                                    <div class="grid grid-cols-5 gap-1">
                                        <div
                                            v-for="(hex, role) in palette.colors"
                                            :key="role"
                                            class="h-8 rounded-md border border-outline-variant/40"
                                            :style="{ backgroundColor: hex }"
                                            :title="role"
                                        />
                                    </div>
                                </button>
                            </div>
                            <AppInputError :message="form.errors.theme_palette" />
                        </div>

                        <div
                            v-if="selectedPalette"
                            class="grid grid-cols-5 gap-sm rounded-xl border border-outline-variant bg-surface-container-low p-md"
                        >
                            <div
                                v-for="(hex, role) in selectedPalette.colors"
                                :key="role"
                                class="text-center"
                            >
                                <div
                                    class="mb-1 h-10 w-full rounded-lg border border-outline-variant shadow-sm"
                                    :style="{ backgroundColor: hex }"
                                />
                                <span class="text-[10px] font-bold uppercase text-on-surface-variant">
                                    {{ role.replace('_', ' ') }}
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-lg pt-md md:grid-cols-2">
                            <div class="space-y-sm">
                                <AppInputLabel value="Typography" />
                                <select
                                    v-model="form.font_heading"
                                    class="h-12 w-full"
                                    :class="fieldClass"
                                >
                                    <option
                                        v-for="option in headingFontOptions"
                                        :key="option.id"
                                        :value="option.id"
                                    >
                                        {{ option.name }}
                                    </option>
                                </select>
                                <select
                                    v-model="form.font_body"
                                    class="mt-sm h-12 w-full"
                                    :class="fieldClass"
                                >
                                    <option
                                        v-for="option in bodyFontOptions"
                                        :key="option.id"
                                        :value="option.id"
                                    >
                                        {{ option.name }}
                                    </option>
                                </select>
                                <AppInputError :message="form.errors.font_heading || form.errors.font_body" />
                            </div>
                            <div
                                class="flex flex-col justify-center rounded-xl border border-outline-variant bg-surface-container-low p-md"
                            >
                                <h4
                                    class="mb-1 text-primary"
                                    :style="{ fontFamily: `'${form.font_heading}', serif`, fontSize: '1.25rem' }"
                                >
                                    Headline Preview
                                </h4>
                                <p
                                    class="text-on-surface-variant"
                                    :style="{ fontFamily: `'${form.font_body}', sans-serif`, fontSize: '0.875rem' }"
                                >
                                    This is how your paragraph and interface text will appear.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Homepage -->
                <section
                    id="homepage"
                    class="scroll-mt-28 overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
                >
                    <div class="border-b border-outline-variant p-lg">
                        <h2 class="font-serif text-headline-sm text-primary">Homepage</h2>
                        <p class="font-sans text-body-sm text-on-surface-variant">
                            Curate hero and our story content for the storefront.
                        </p>
                    </div>
                    <div class="space-y-xl p-lg">
                        <div class="space-y-md border-l-4 border-secondary pl-lg">
                            <h3 class="font-sans text-title-lg text-primary">Hero Banner</h3>
                            <div>
                                <AppInputLabel for="hero-title" value="Hero Title" />
                                <AppInput
                                    id="hero-title"
                                    v-model="form.hero_title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :has-error="!!form.errors.hero_title"
                                />
                                <AppInputError class="mt-1" :message="form.errors.hero_title" />
                            </div>
                            <div>
                                <AppInputLabel for="hero-description" value="Hero Description" />
                                <textarea
                                    id="hero-description"
                                    v-model="form.hero_description"
                                    rows="3"
                                    class="mt-1"
                                    :class="fieldClass"
                                />
                                <AppInputError class="mt-1" :message="form.errors.hero_description" />
                            </div>
                            <SettingsImageField
                                v-model="form.hero_image"
                                label="Hero Image"
                                :current-url="heroImagePreviewUrl"
                                :error="form.errors.hero_image"
                            />
                            <div class="grid gap-lg md:grid-cols-2">
                                <div>
                                    <AppInputLabel for="hero-rating" value="Rating" />
                                    <AppInput
                                        id="hero-rating"
                                        v-model="form.hero_rating"
                                        type="number"
                                        min="0"
                                        max="5"
                                        step="0.1"
                                        class="mt-1 block w-full"
                                        :has-error="!!form.errors.hero_rating"
                                    />
                                    <p class="mt-1 font-sans text-body-sm text-on-surface-variant">
                                        From 0 to 5. Default 3.5.
                                    </p>
                                    <AppInputError class="mt-1" :message="form.errors.hero_rating" />
                                </div>
                                <div>
                                    <AppInputLabel for="hero-rating-description" value="Rating Description" />
                                    <AppInput
                                        id="hero-rating-description"
                                        v-model="form.hero_rating_description"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="The best croissant in the city, hands down!"
                                        :has-error="!!form.errors.hero_rating_description"
                                    />
                                    <AppInputError class="mt-1" :message="form.errors.hero_rating_description" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-md">
                            <h3 class="font-sans text-title-lg text-primary">Our Story</h3>
                            <div class="grid gap-lg md:grid-cols-2">
                                <div>
                                    <AppInputLabel for="story-title" value="Story Title" />
                                    <AppInput
                                        id="story-title"
                                        v-model="form.story_title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        :has-error="!!form.errors.story_title"
                                    />
                                    <AppInputError class="mt-1" :message="form.errors.story_title" />
                                </div>
                                <div>
                                    <AppInputLabel for="since-year" value="Since Year" />
                                    <AppInput
                                        id="since-year"
                                        v-model="form.since_year"
                                        type="number"
                                        min="1800"
                                        :max="new Date().getFullYear()"
                                        class="mt-1 block w-full"
                                        :has-error="!!form.errors.since_year"
                                    />
                                    <AppInputError class="mt-1" :message="form.errors.since_year" />
                                </div>
                            </div>
                            <SettingsImageField
                                v-model="form.story_image"
                                label="Story Image"
                                :current-url="storyImagePreviewUrl"
                                :error="form.errors.story_image"
                            />
                            <div>
                                <AppInputLabel for="story-content" value="Story Content" />
                                <textarea
                                    id="story-content"
                                    v-model="form.story_content"
                                    rows="8"
                                    class="mt-1"
                                    :class="fieldClass"
                                />
                                <AppInputError class="mt-1" :message="form.errors.story_content" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Social -->
                <section
                    id="social"
                    class="scroll-mt-28 overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
                >
                    <div class="border-b border-outline-variant p-lg">
                        <h2 class="font-serif text-headline-sm text-primary">Social Links</h2>
                        <p class="font-sans text-body-sm text-on-surface-variant">
                            Profile URLs shown in the storefront footer and contact areas.
                        </p>
                    </div>
                    <div class="grid gap-lg p-lg md:grid-cols-2">
                        <div>
                            <AppInputLabel for="social-facebook" value="Facebook" />
                            <AppInput
                                id="social-facebook"
                                v-model="form.social_links.facebook"
                                type="url"
                                class="mt-1 block w-full"
                                placeholder="https://"
                                :has-error="!!form.errors['social_links.facebook']"
                            />
                            <AppInputError
                                class="mt-1"
                                :message="form.errors['social_links.facebook']"
                            />
                        </div>
                        <div>
                            <AppInputLabel for="social-instagram" value="Instagram" />
                            <AppInput
                                id="social-instagram"
                                v-model="form.social_links.instagram"
                                type="url"
                                class="mt-1 block w-full"
                                placeholder="https://"
                                :has-error="!!form.errors['social_links.instagram']"
                            />
                            <AppInputError
                                class="mt-1"
                                :message="form.errors['social_links.instagram']"
                            />
                        </div>
                        <div>
                            <AppInputLabel for="social-twitter" value="Twitter / X" />
                            <AppInput
                                id="social-twitter"
                                v-model="form.social_links.twitter"
                                type="url"
                                class="mt-1 block w-full"
                                placeholder="https://"
                                :has-error="!!form.errors['social_links.twitter']"
                            />
                            <AppInputError
                                class="mt-1"
                                :message="form.errors['social_links.twitter']"
                            />
                        </div>
                        <div>
                            <AppInputLabel for="social-youtube" value="YouTube" />
                            <AppInput
                                id="social-youtube"
                                v-model="form.social_links.youtube"
                                type="url"
                                class="mt-1 block w-full"
                                placeholder="https://"
                                :has-error="!!form.errors['social_links.youtube']"
                            />
                            <AppInputError
                                class="mt-1"
                                :message="form.errors['social_links.youtube']"
                            />
                        </div>
                    </div>
                </section>

                <!-- SEO -->
                <section
                    id="seo"
                    class="scroll-mt-28 overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
                >
                    <div class="border-b border-outline-variant p-lg">
                        <h2 class="font-serif text-headline-sm text-primary">Global SEO</h2>
                        <p class="font-sans text-body-sm text-on-surface-variant">
                            Optimise how search engines index and display your store.
                        </p>
                    </div>
                    <div class="space-y-lg p-lg">
                        <div>
                            <AppInputLabel for="seo-title" value="Site Title Template" />
                            <AppInput
                                id="seo-title"
                                v-model="form.seo_title_template"
                                type="text"
                                class="mt-1 block w-full"
                                :has-error="!!form.errors.seo_title_template"
                            />
                            <p class="mt-1 font-sans text-body-sm text-on-surface-variant">
                                Available tags: %site_name%, %tagline%, %category%, %product%
                            </p>
                            <AppInputError class="mt-1" :message="form.errors.seo_title_template" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-md">
                                <AppInputLabel for="seo-description" value="Meta Description" />
                                <span
                                    class="font-sans text-body-sm"
                                    :class="
                                        seoDescriptionLength > 160
                                            ? 'text-error'
                                            : 'text-on-surface-variant'
                                    "
                                >
                                    {{ seoDescriptionLength }} / 160
                                </span>
                            </div>
                            <textarea
                                id="seo-description"
                                v-model="form.seo_meta_description"
                                rows="4"
                                class="mt-1"
                                :class="fieldClass"
                            />
                            <AppInputError class="mt-1" :message="form.errors.seo_meta_description" />
                        </div>

                        <div>
                            <AppInputLabel for="seo-keywords" value="Search Keywords" />
                            <AppInput
                                id="seo-keywords"
                                v-model="form.seo_meta_keywords"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="bakery, artisan bread, pastries"
                                :has-error="!!form.errors.seo_meta_keywords"
                            />
                            <p class="mt-1 font-sans text-body-sm text-on-surface-variant">
                                Separate keywords with commas.
                            </p>
                            <AppInputError class="mt-1" :message="form.errors.seo_meta_keywords" />
                        </div>

                        <div>
                            <AppInputLabel value="Search Result Preview" />
                            <div
                                class="mt-2 rounded-xl border border-outline-variant bg-white p-md"
                            >
                                <p class="font-sans text-body-sm text-on-surface-variant">
                                    https://cravebakery.com
                                </p>
                                <p class="mt-1 font-sans text-lg text-[#1a0dab]">
                                    {{ seoPreviewTitle }}
                                </p>
                                <p class="mt-1 line-clamp-2 font-sans text-body-sm text-on-surface-variant">
                                    {{ form.seo_meta_description || 'Meta description preview…' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </div>

        <div
            class="fixed inset-x-0 bottom-0 z-40 border-t border-outline-variant bg-white/95 px-lg py-md backdrop-blur"
        >
            <div class="mx-auto flex max-w-[1400px] items-center justify-end gap-md">
                <AppButton
                    type="button"
                    variant="secondary"
                    :disabled="form.processing || !form.isDirty"
                    @click="discard"
                >
                    Discard
                </AppButton>
                <AppButton
                    type="button"
                    :loading="form.processing"
                    :disabled="form.processing || !form.isDirty"
                    @click="submit"
                >
                    Save Changes
                </AppButton>
            </div>
        </div>
    </AdminLayout>
</template>
