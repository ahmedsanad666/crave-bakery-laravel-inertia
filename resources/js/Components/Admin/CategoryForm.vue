<script setup>
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    IconChevronRight,
    IconCloudUpload,
    IconInfoCircle,
    IconPhoto,
    IconPhotoPlus,
    IconSearch,
    IconSend,
} from '@tabler/icons-vue';
import { computed, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    mode: {
        type: String,
        required: true,
        validator: (value) => ['create', 'edit'].includes(value),
    },
    parentOptions: {
        type: Array,
        default: () => [],
    },
    category: {
        type: Object,
        default: null,
    },
});

const isEdit = computed(() => props.mode === 'edit');
const page = usePage();
const siteName = computed(
    () => page.props.siteSettings?.site_name ?? 'Crave Bakery',
);

const queryParentId = new URLSearchParams(
    page.url.includes('?') ? page.url.split('?')[1] : '',
).get('parent_id');

const existingImageUrl = computed(() => props.category?.image ?? null);
const existingBannerUrl = computed(() => props.category?.banner_image ?? null);

const form = useForm({
    name: props.category?.name ?? '',
    slug: props.category?.slug ?? '',
    description: props.category?.description ?? '',
    parent_id: props.category
        ? (props.category.parent_id ?? null)
        : queryParentId
          ? Number(queryParentId)
          : null,
    status: props.category?.status ?? 'draft',
    sort_order: props.category?.sort_order ?? 0,
    show_in_navigation: props.category?.show_in_navigation ?? true,
    show_in_footer: props.category?.show_in_footer ?? false,
    image: null,
    banner_image: null,
    meta_title: props.category?.meta_title ?? '',
    meta_description: props.category?.meta_description ?? '',
});

const slugTouched = ref(isEdit.value);
const imagePreview = ref(existingImageUrl.value);
const bannerPreview = ref(existingBannerUrl.value);
const imageInput = ref(null);
const bannerInput = ref(null);
const imageIsObjectUrl = ref(false);
const bannerIsObjectUrl = ref(false);

const pageTitle = computed(() =>
    isEdit.value ? 'Edit Category' : 'Create Category',
);
const breadcrumbCurrent = computed(() =>
    isEdit.value
        ? `Edit ${props.category?.name ?? 'Category'}`
        : 'Add New Category',
);
const layoutBreadcrumb = computed(() =>
    isEdit.value
        ? `Categories / Edit`
        : 'Categories / New',
);
const primaryActionLabel = computed(() =>
    isEdit.value ? 'Update & Publish' : 'Publish Category',
);

const revokePreview = (url, isObjectUrl) => {
    if (isObjectUrl && url) {
        URL.revokeObjectURL(url);
    }
};

const onImageChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    form.image = file;
    revokePreview(imagePreview.value, imageIsObjectUrl.value);
    if (file) {
        imagePreview.value = URL.createObjectURL(file);
        imageIsObjectUrl.value = true;
    } else {
        imagePreview.value = existingImageUrl.value;
        imageIsObjectUrl.value = false;
    }
};

const onBannerChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    form.banner_image = file;
    revokePreview(bannerPreview.value, bannerIsObjectUrl.value);
    if (file) {
        bannerPreview.value = URL.createObjectURL(file);
        bannerIsObjectUrl.value = true;
    } else {
        bannerPreview.value = existingBannerUrl.value;
        bannerIsObjectUrl.value = false;
    }
};

const clearImage = () => {
    form.image = null;
    revokePreview(imagePreview.value, imageIsObjectUrl.value);
    imagePreview.value = existingImageUrl.value;
    imageIsObjectUrl.value = false;
    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const clearBanner = () => {
    form.banner_image = null;
    revokePreview(bannerPreview.value, bannerIsObjectUrl.value);
    bannerPreview.value = existingBannerUrl.value;
    bannerIsObjectUrl.value = false;
    if (bannerInput.value) {
        bannerInput.value.value = '';
    }
};

onUnmounted(() => {
    revokePreview(imagePreview.value, imageIsObjectUrl.value);
    revokePreview(bannerPreview.value, bannerIsObjectUrl.value);
});

const slugify = (value) =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');

watch(
    () => form.name,
    (name) => {
        if (slugTouched.value) {
            return;
        }
        form.slug = slugify(name);
    },
);

const previewTitle = computed(() => {
    if (form.meta_title?.trim()) {
        return form.meta_title;
    }
    if (form.name?.trim()) {
        return `${form.name} | ${siteName.value}`;
    }
    return `Category | ${siteName.value}`;
});

const previewUrl = computed(() => {
    const slug = form.slug || 'category-slug';
    return `cravebakery.com › shop › ${slug}`;
});

const previewSnippet = computed(() => {
    const text =
        form.meta_description?.trim() ||
        form.description?.trim() ||
        'Describe this category to help customers discover your products.';
    return text.length > 160 ? `${text.slice(0, 157)}…` : text;
});

const previewImage = computed(
    () =>
        imagePreview.value ||
        'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&q=80',
);

const submit = () => {
    if (form.parent_id === '' || form.parent_id === null) {
        form.parent_id = null;
    } else {
        form.parent_id = Number(form.parent_id);
    }

    if (isEdit.value) {
        form.put(route('admin.categories.update', props.category.id), {
            forceFormData: true,
        });
        return;
    }

    form.post(route('admin.categories.store'), {
        forceFormData: true,
    });
};

const publish = () => {
    form.status = 'active';
    submit();
};

const saveDraft = () => {
    form.status = 'draft';
    submit();
};
</script>

<template>
    <AdminLayout :title="pageTitle" :breadcrumb="layoutBreadcrumb">
        <Head :title="pageTitle" />

        <section class="mb-xl pt-4">
            <nav
                class="mb-xs flex items-center gap-xs text-body-sm text-on-surface-variant"
            >
                <Link
                    :href="route('admin.dashboard')"
                    class="transition-colors hover:text-secondary"
                >
                    Dashboard
                </Link>
                <IconChevronRight class="size-3.5" stroke="2" />
                <Link
                    :href="route('admin.categories.index')"
                    class="transition-colors hover:text-secondary"
                >
                    Categories
                </Link>
                <IconChevronRight class="size-3.5" stroke="2" />
                <span class="font-semibold text-on-surface">{{
                    breadcrumbCurrent
                }}</span>
            </nav>
            <h1 class="font-serif text-headline-lg text-primary">
                Category Management
            </h1>
        </section>

        <form
            class="grid grid-cols-1 items-start gap-xl lg:grid-cols-[65%_35%]"
            @submit.prevent="publish"
        >
            <div class="space-y-lg">
                <section
                    class="rounded-xl border border-outline-variant bg-surface-container-lowest p-lg shadow-card"
                >
                    <div class="mb-lg flex items-center gap-sm">
                        <IconInfoCircle
                            class="text-secondary"
                            :size="22"
                            stroke-width="1.5"
                        />
                        <h2 class="font-serif text-headline-sm">
                            Category Information
                        </h2>
                    </div>

                    <div class="mb-lg grid grid-cols-2 gap-lg">
                        <div class="col-span-2 md:col-span-1">
                            <AppInputLabel
                                for="name"
                                value="Category Name"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppInput
                                id="name"
                                v-model="form.name"
                                class="mt-0 block w-full"
                                placeholder="e.g. Artisanal Breads"
                                :has-error="!!form.errors.name"
                                required
                                autofocus
                            />
                            <AppInputError :message="form.errors.name" />
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <AppInputLabel
                                for="slug"
                                value="URL Slug Preview"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <div
                                class="flex h-12 items-center rounded-lg border border-dashed border-outline-variant bg-surface-container-low px-md text-sm italic text-on-surface-variant"
                                :class="{
                                    'border-error': form.errors.slug,
                                }"
                            >
                                <span class="truncate">
                                    shop/
                                    <input
                                        id="slug"
                                        v-model="form.slug"
                                        type="text"
                                        class="inline-block min-w-[8rem] max-w-full border-0 bg-transparent p-0 font-semibold not-italic text-primary focus:ring-0"
                                        placeholder="category-slug"
                                        @input="slugTouched = true"
                                    />
                                </span>
                            </div>
                            <AppInputError :message="form.errors.slug" />
                        </div>
                    </div>

                    <div class="mb-lg grid grid-cols-2 gap-lg">
                        <div class="col-span-2 md:col-span-1">
                            <AppInputLabel
                                for="parent_id"
                                value="Parent Category"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <select
                                id="parent_id"
                                v-model="form.parent_id"
                                class="input-field"
                                :class="{
                                    'input-field-error': form.errors.parent_id,
                                }"
                            >
                                <option :value="null">None (Top Level)</option>
                                <option
                                    v-for="option in parentOptions"
                                    :key="option.id"
                                    :value="option.id"
                                >
                                    {{ option.name }}
                                </option>
                            </select>
                            <AppInputError :message="form.errors.parent_id" />
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <AppInputLabel
                                for="sort_order"
                                value="Display Order"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppInput
                                id="sort_order"
                                v-model="form.sort_order"
                                type="number"
                                min="0"
                                class="block w-full"
                                :has-error="!!form.errors.sort_order"
                            />
                            <AppInputError :message="form.errors.sort_order" />
                        </div>
                    </div>

                    <div>
                        <AppInputLabel
                            for="description"
                            value="Description"
                            class="!mb-xs !font-sans !text-label-caps !uppercase"
                        />
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="input-field h-auto py-md"
                            placeholder="Describe this category's unique offerings..."
                            :class="{
                                'input-field-error': form.errors.description,
                            }"
                        />
                        <AppInputError :message="form.errors.description" />
                    </div>
                </section>

                <section
                    class="rounded-xl border border-outline-variant bg-surface-container-lowest p-lg shadow-card"
                >
                    <div class="mb-lg flex items-center gap-sm">
                        <IconPhoto
                            class="text-secondary"
                            :size="22"
                            stroke-width="1.5"
                        />
                        <h2 class="font-serif text-headline-sm">
                            Media &amp; Assets
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-lg md:grid-cols-2">
                        <div>
                            <AppInputLabel
                                for="image"
                                value="Primary Thumbnail"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <input
                                id="image"
                                ref="imageInput"
                                type="file"
                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                class="sr-only"
                                @change="onImageChange"
                            />
                            <label
                                for="image"
                                class="group relative flex h-[160px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-outline-variant bg-surface-container-low transition-colors hover:bg-surface-container"
                                :class="{
                                    'border-error': form.errors.image,
                                }"
                            >
                                <img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    alt="Thumbnail preview"
                                    class="absolute inset-0 h-full w-full object-cover"
                                />
                                <div
                                    class="relative z-10 flex flex-col items-center"
                                    :class="{
                                        'rounded-lg bg-white/80 px-md py-sm':
                                            imagePreview,
                                    }"
                                >
                                    <IconCloudUpload
                                        class="mb-sm text-secondary transition-transform group-hover:scale-110"
                                        :size="36"
                                        stroke-width="1.5"
                                    />
                                    <span class="text-body-sm font-medium">
                                        {{
                                            imagePreview
                                                ? 'Replace thumbnail (1:1)'
                                                : 'Upload thumbnail (1:1 recommended)'
                                        }}
                                    </span>
                                    <span class="text-xs text-on-surface-variant">
                                        PNG, JPG, WEBP up to 5MB
                                    </span>
                                </div>
                            </label>
                            <button
                                v-if="imageIsObjectUrl"
                                type="button"
                                class="mt-sm text-body-sm font-medium text-error hover:underline"
                                @click="clearImage"
                            >
                                Discard new image
                            </button>
                            <AppInputError :message="form.errors.image" />
                        </div>

                        <div>
                            <AppInputLabel
                                for="banner_image"
                                value="Category Banner"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <input
                                id="banner_image"
                                ref="bannerInput"
                                type="file"
                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                class="sr-only"
                                @change="onBannerChange"
                            />
                            <label
                                for="banner_image"
                                class="group relative flex aspect-video cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-outline-variant bg-surface-container-low transition-colors hover:bg-surface-container"
                                :class="{
                                    'border-error': form.errors.banner_image,
                                }"
                            >
                                <img
                                    v-if="bannerPreview"
                                    :src="bannerPreview"
                                    alt="Banner preview"
                                    class="absolute inset-0 h-full w-full object-cover"
                                />
                                <div
                                    class="relative z-10 flex flex-col items-center"
                                    :class="{
                                        'rounded-lg bg-white/80 px-md py-sm':
                                            bannerPreview,
                                    }"
                                >
                                    <IconPhotoPlus
                                        class="mb-sm text-secondary"
                                        :size="36"
                                        stroke-width="1.5"
                                    />
                                    <span class="text-body-sm font-medium">
                                        {{
                                            bannerPreview
                                                ? 'Replace Banner Image (16:9)'
                                                : 'Upload Banner Image (16:9)'
                                        }}
                                    </span>
                                    <span class="text-xs text-on-surface-variant">
                                        PNG, JPG, WEBP up to 5MB
                                    </span>
                                </div>
                            </label>
                            <button
                                v-if="bannerIsObjectUrl"
                                type="button"
                                class="mt-sm text-body-sm font-medium text-error hover:underline"
                                @click="clearBanner"
                            >
                                Discard new image
                            </button>
                            <AppInputError :message="form.errors.banner_image" />
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-xl border border-outline-variant bg-surface-container-lowest p-lg shadow-card"
                >
                    <div class="mb-lg flex items-center gap-sm">
                        <IconSearch
                            class="text-secondary"
                            :size="22"
                            stroke-width="1.5"
                        />
                        <h2 class="font-serif text-headline-sm">
                            SEO Optimization
                        </h2>
                    </div>

                    <div class="mb-xl grid grid-cols-1 gap-lg md:grid-cols-2">
                        <div class="space-y-md">
                            <div>
                                <AppInputLabel
                                    for="meta_title"
                                    value="Meta Title"
                                    class="!mb-xs !font-sans !text-label-caps !uppercase"
                                />
                                <AppInput
                                    id="meta_title"
                                    v-model="form.meta_title"
                                    class="block w-full"
                                    :placeholder="`${form.name || 'Category'} | ${siteName}`"
                                    :has-error="!!form.errors.meta_title"
                                />
                                <AppInputError :message="form.errors.meta_title" />
                            </div>
                            <div>
                                <AppInputLabel
                                    for="meta_description"
                                    value="Meta Description"
                                    class="!mb-xs !font-sans !text-label-caps !uppercase"
                                />
                                <textarea
                                    id="meta_description"
                                    v-model="form.meta_description"
                                    rows="3"
                                    class="input-field h-auto py-md"
                                    placeholder="Shop our daily selection of crusty sourdough, baguettes, and specialty grains."
                                    :class="{
                                        'input-field-error':
                                            form.errors.meta_description,
                                    }"
                                />
                                <AppInputError
                                    :message="form.errors.meta_description"
                                />
                            </div>
                        </div>

                        <div>
                            <AppInputLabel
                                value="Google Search Preview"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <div
                                class="h-full rounded-lg border border-outline-variant bg-white p-md shadow-sm"
                            >
                                <div
                                    class="mb-1 truncate text-lg font-medium leading-tight text-[#1a0dab]"
                                >
                                    {{ previewTitle }}
                                </div>
                                <div class="mb-1 truncate text-sm text-[#006621]">
                                    {{ previewUrl }}
                                </div>
                                <div class="line-clamp-2 text-sm text-[#4d5156]">
                                    {{ previewSnippet }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="sticky top-24 space-y-lg">
                <section
                    class="rounded-xl border border-outline-variant bg-surface-container-lowest p-lg shadow-card"
                >
                    <h3 class="mb-lg font-serif text-headline-sm">
                        Publish Settings
                    </h3>

                    <div class="mb-lg space-y-md">
                        <div>
                            <AppInputLabel
                                for="status"
                                value="Status"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <select
                                id="status"
                                v-model="form.status"
                                class="input-field h-11 text-sm"
                                :class="{
                                    'input-field-error': form.errors.status,
                                }"
                            >
                                <option value="draft">Draft</option>
                                <option value="active">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                            <AppInputError :message="form.errors.status" />
                        </div>
                    </div>

                    <div
                        class="mb-md flex items-center justify-between border-t border-outline-variant py-md"
                    >
                        <span class="text-body-sm font-medium">
                            Show in Main Nav
                        </span>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input
                                v-model="form.show_in_navigation"
                                type="checkbox"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-outline-variant after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-secondary peer-checked:after:translate-x-full peer-checked:after:border-white"
                            />
                        </label>
                    </div>

                    <div
                        class="mb-lg flex items-center justify-between border-t border-outline-variant py-md"
                    >
                        <span class="text-body-sm font-medium">
                            Show in Footer
                        </span>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input
                                v-model="form.show_in_footer"
                                type="checkbox"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-outline-variant after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-secondary peer-checked:after:translate-x-full peer-checked:after:border-white"
                            />
                        </label>
                    </div>

                    <AppButton
                        type="button"
                        variant="primary"
                        class="!h-auto w-full !rounded-full !bg-secondary py-md !font-sans !text-label-caps !uppercase hover:!bg-secondary/90"
                        :loading="form.processing"
                        :disabled="form.processing"
                        @click="publish"
                    >
                        <span class="inline-flex items-center justify-center gap-2">
                            <IconSend :size="18" stroke-width="1.5" />
                            {{ primaryActionLabel }}
                        </span>
                    </AppButton>

                    <AppButton
                        type="button"
                        variant="secondary"
                        class="mt-sm !h-auto w-full !rounded-full border-outline py-md !font-sans !text-label-caps !uppercase"
                        :disabled="form.processing"
                        @click="saveDraft"
                    >
                        Save as Draft
                    </AppButton>
                </section>

                <section
                    class="rounded-xl border border-outline-variant bg-surface-container-lowest p-lg shadow-card"
                >
                    <h3 class="mb-md font-serif text-headline-sm">Live Preview</h3>
                    <p
                        class="mb-lg text-xs font-bold uppercase tracking-widest text-on-surface-variant"
                    >
                        Storefront Card View
                    </p>

                    <div
                        class="group overflow-hidden rounded-xl border border-outline-variant bg-white shadow-card"
                    >
                        <div class="relative aspect-[4/3] overflow-hidden">
                            <img
                                :src="previewImage"
                                alt="Category thumbnail preview"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"
                            />
                            <div class="absolute bottom-4 left-4 right-4">
                                <span
                                    class="mb-1 inline-block rounded-full bg-secondary px-2 py-0.5 text-[10px] font-bold uppercase text-white"
                                >
                                    New Arrival
                                </span>
                                <h4 class="font-serif text-headline-sm text-white">
                                    {{ form.name || 'Category Name' }}
                                </h4>
                            </div>
                        </div>
                        <div class="p-md">
                            <p
                                class="mb-md line-clamp-2 text-body-sm text-on-surface-variant"
                            >
                                {{
                                    form.description ||
                                    'Category description will appear here.'
                                }}
                            </p>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-bold uppercase tracking-widest text-secondary"
                                >
                                    Shop Now
                                </span>
                                <IconChevronRight
                                    class="text-secondary"
                                    :size="18"
                                    stroke-width="1.5"
                                />
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </form>
    </AdminLayout>
</template>
