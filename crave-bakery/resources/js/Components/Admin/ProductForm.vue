<script setup>
import AppButton from "@/Components/Shared/AppButton.vue";
import AppInput from "@/Components/Shared/AppInput.vue";
import AppInputError from "@/Components/Shared/AppInputError.vue";
import AppInputLabel from "@/Components/Shared/AppInputLabel.vue";
import AppMultiSelect from "@/Components/Shared/AppMultiSelect.vue";
import AppSelect from "@/Components/Shared/AppSelect.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import {
    IconAdjustments,
    IconChevronRight,
    IconCloudUpload,
    IconCurrencyDollar,
    IconInfoCircle,
    IconPhoto,
    IconPlus,
    IconSearch,
    IconSend,
    IconTrash,
} from "@tabler/icons-vue";
import { computed, onUnmounted, ref, watch } from "vue";

const props = defineProps({
    mode: {
        type: String,
        required: true,
        validator: (value) => ["create", "edit"].includes(value),
    },
    categoryOptions: {
        type: Array,
        default: () => [],
    },
    attributeOptions: {
        type: Array,
        default: () => [],
    },
    product: {
        type: Object,
        default: null,
    },
});

const isEdit = computed(() => props.mode === "edit");
const page = usePage();
const siteName = computed(
    () => page.props.siteSettings?.site_name ?? "Crave Bakery",
);

const existingThumbnailUrl = computed(() => props.product?.thumbnail ?? null);
const existingGallery = computed(() => props.product?.images ?? []);

const initialAttributeValueIds = props.product?.attribute_value_ids ?? [];

const form = useForm({
    name: props.product?.name ?? "",
    slug: props.product?.slug ?? "",
    short_description: props.product?.short_description ?? "",
    description: props.product?.description ?? "",
    regular_price: props.product?.regular_price ?? "",
    sale_price: props.product?.sale_price ?? "",
    cost_price: props.product?.cost_price ?? "",
    sku: props.product?.sku ?? "",
    barcode: props.product?.barcode ?? "",
    stock_quantity: props.product?.stock_quantity ?? 0,
    low_stock_threshold: props.product?.low_stock_threshold ?? 5,
    allow_backorders: props.product?.allow_backorders ?? false,
    stock_status: props.product?.stock_status ?? null,
    is_featured: props.product?.is_featured ?? false,
    is_active: props.product?.is_active ?? true,
    status: props.product?.status ?? "draft",
    published_at: props.product?.published_at ?? null,
    meta_title: props.product?.meta_title ?? "",
    meta_description: props.product?.meta_description ?? "",
    meta_keywords: props.product?.meta_keywords ?? [],
    thumbnail: null,
    images: [],
    remove_image_ids: [],
    og_image: null,
    canonical_url: props.product?.canonical_url ?? "",
    category_ids: [...(props.product?.category_ids ?? [])],
    attribute_value_ids: [...initialAttributeValueIds],
});

const slugTouched = ref(isEdit.value);
const thumbnailPreview = ref(existingThumbnailUrl.value);
const thumbnailInput = ref(null);
const thumbnailIsObjectUrl = ref(false);
const galleryInput = ref(null);
const galleryPreviews = ref([]);
const trackStock = ref(
    isEdit.value
        ? (props.product?.stock_quantity ?? 0) > 0 ||
              props.product?.allow_backorders === true
        : true,
);
const activeStep = ref(isEdit.value ? 5 : 1);

const addedAttributeIds = ref(
    deriveAddedAttributeIds(initialAttributeValueIds, props.attributeOptions),
);

function deriveAddedAttributeIds(valueIds, attributes) {
    const selected = new Set(valueIds);
    return attributes
        .filter((attr) =>
            (attr.values ?? []).some((value) => selected.has(value.id)),
        )
        .map((attr) => attr.id);
}

const pageTitle = computed(() =>
    isEdit.value ? "Edit Product" : "Add New Product",
);
const breadcrumbCurrent = computed(() =>
    isEdit.value
        ? `Edit ${props.product?.name ?? "Product"}`
        : "Add New Product",
);
const layoutBreadcrumb = computed(() =>
    isEdit.value ? "Products / Edit" : "Products / New",
);
const primaryActionLabel = computed(() =>
    isEdit.value ? "Update & Publish" : "Publish Product",
);

const addedAttributes = computed(() =>
    props.attributeOptions.filter((attr) =>
        addedAttributeIds.value.includes(attr.id),
    ),
);

const availableAttributes = computed(() =>
    props.attributeOptions.filter(
        (attr) => !addedAttributeIds.value.includes(attr.id),
    ),
);

const statusOptions = [
    { id: "draft", name: "Draft" },
    { id: "active", name: "Active" },
    { id: "archived", name: "Archived" },
];

const summaryCounts = computed(() => ({
    categories: form.category_ids.length,
    images:
        (thumbnailPreview.value ? 1 : 0) +
        visibleExistingGallery.value.length +
        galleryPreviews.value.length,
    attributeValues: form.attribute_value_ids.length,
    attributes: addedAttributeIds.value.length,
}));

const visibleExistingGallery = computed(() =>
    existingGallery.value.filter(
        (image) => !form.remove_image_ids.includes(image.id),
    ),
);

const gallerySlotsRemaining = computed(() =>
    Math.max(
        0,
        8 - visibleExistingGallery.value.length - galleryPreviews.value.length,
    ),
);

const shortDescriptionCount = computed(
    () => (form.short_description ?? "").length,
);
const metaTitleCount = computed(() => (form.meta_title ?? "").length);
const metaDescriptionCount = computed(
    () => (form.meta_description ?? "").length,
);

const steps = [
    { id: 1, label: "Basic Info", section: "section-basic" },
    { id: 2, label: "Media", section: "section-media" },
    { id: 3, label: "Pricing", section: "section-pricing" },
    { id: 4, label: "Attributes", section: "section-attributes" },
    { id: 5, label: "SEO", section: "section-seo" },
];

const revokePreview = (url, isObjectUrl) => {
    if (isObjectUrl && url) {
        URL.revokeObjectURL(url);
    }
};

const onThumbnailChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    form.thumbnail = file;
    revokePreview(thumbnailPreview.value, thumbnailIsObjectUrl.value);
    if (file) {
        thumbnailPreview.value = URL.createObjectURL(file);
        thumbnailIsObjectUrl.value = true;
    } else {
        thumbnailPreview.value = existingThumbnailUrl.value;
        thumbnailIsObjectUrl.value = false;
    }
};

const clearThumbnail = () => {
    form.thumbnail = null;
    revokePreview(thumbnailPreview.value, thumbnailIsObjectUrl.value);
    thumbnailPreview.value = existingThumbnailUrl.value;
    thumbnailIsObjectUrl.value = false;
    if (thumbnailInput.value) {
        thumbnailInput.value.value = "";
    }
};

const onGalleryChange = (event) => {
    const files = Array.from(event.target.files ?? []);
    if (!files.length) {
        return;
    }

    const allowed = files.slice(0, gallerySlotsRemaining.value);
    form.images = [...form.images, ...allowed];
    galleryPreviews.value = [
        ...galleryPreviews.value,
        ...allowed.map((file) => ({
            url: URL.createObjectURL(file),
            name: file.name,
        })),
    ];

    if (galleryInput.value) {
        galleryInput.value.value = "";
    }
};

const removeNewGalleryImage = (index) => {
    const preview = galleryPreviews.value[index];
    if (preview?.url) {
        URL.revokeObjectURL(preview.url);
    }
    galleryPreviews.value = galleryPreviews.value.filter((_, i) => i !== index);
    form.images = form.images.filter((_, i) => i !== index);
};

const removeExistingGalleryImage = (imageId) => {
    if (!form.remove_image_ids.includes(imageId)) {
        form.remove_image_ids = [...form.remove_image_ids, imageId];
    }
};

onUnmounted(() => {
    revokePreview(thumbnailPreview.value, thumbnailIsObjectUrl.value);
    galleryPreviews.value.forEach((preview) => {
        if (preview.url) {
            URL.revokeObjectURL(preview.url);
        }
    });
});

const slugify = (value) =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, "")
        .replace(/[\s_]+/g, "-")
        .replace(/-+/g, "-")
        .replace(/^-|-$/g, "");

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
    return `Product | ${siteName.value}`;
});

const previewUrl = computed(() => {
    const slug = form.slug || "product-slug";
    return `https://cravebakery.com/products/${slug}`;
});

const previewSnippet = computed(() => {
    const text =
        form.meta_description?.trim() ||
        form.short_description?.trim() ||
        form.description?.trim() ||
        "Describe this product to help customers discover your artisanal offerings.";
    return text.length > 160 ? `${text.slice(0, 157)}…` : text;
});

const addAttribute = (id) => {
    const attributeId = Number(id);
    if (!attributeId || addedAttributeIds.value.includes(attributeId)) {
        return;
    }
    addedAttributeIds.value = [...addedAttributeIds.value, attributeId];
};

const removeAttribute = (attribute) => {
    const valueIds = new Set((attribute.values ?? []).map((value) => value.id));
    form.attribute_value_ids = form.attribute_value_ids.filter(
        (id) => !valueIds.has(id),
    );
    addedAttributeIds.value = addedAttributeIds.value.filter(
        (id) => id !== attribute.id,
    );
};

const isValueSelected = (valueId) => form.attribute_value_ids.includes(valueId);

const toggleAttributeValue = (valueId) => {
    if (isValueSelected(valueId)) {
        form.attribute_value_ids = form.attribute_value_ids.filter(
            (id) => id !== valueId,
        );
        return;
    }
    form.attribute_value_ids = [...form.attribute_value_ids, valueId];
};

const displayTypeLabel = (displayType) => {
    const labels = {
        pills: "Pills",
        dropdown: "Dropdown",
        swatches: "Swatches",
        checkboxes: "Checkboxes",
    };
    return labels[displayType] ?? displayType;
};

const isStepReached = (stepId) => stepId <= activeStep.value;

const isConnectorFilled = (fromStepId) => fromStepId < activeStep.value;

const scrollToStep = (step) => {
    activeStep.value = Math.max(activeStep.value, step.id);
    const el = document.getElementById(step.section);
    if (el) {
        el.scrollIntoView({ behavior: "smooth", block: "start" });
    }
};

const submit = () => {
    if (form.sale_price === "" || form.sale_price === null) {
        form.sale_price = null;
    }
    if (form.cost_price === "" || form.cost_price === null) {
        form.cost_price = null;
    }
    if (form.barcode === "") {
        form.barcode = null;
    }
    if (form.canonical_url === "") {
        form.canonical_url = null;
    }
    if (form.published_at === "") {
        form.published_at = null;
    }

    if (!trackStock.value) {
        form.stock_quantity = 0;
        form.allow_backorders = false;
    }

    const options = { forceFormData: true };

    if (isEdit.value) {
        form.put(route("admin.products.update", props.product.id), options);
        return;
    }

    form.post(route("admin.products.store"), options);
};

const publish = () => {
    form.status = "active";
    form.is_active = true;
    submit();
};

const saveDraft = () => {
    form.status = "draft";
    submit();
};
</script>

<template>
    <AdminLayout :title="pageTitle" :breadcrumb="layoutBreadcrumb">
        <Head :title="pageTitle" />

        <section
            class="mb-xl flex flex-col gap-md pt-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div>
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
                        :href="route('admin.products.index')"
                        class="transition-colors hover:text-secondary"
                    >
                        Products
                    </Link>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span class="font-semibold text-on-surface">{{
                        breadcrumbCurrent
                    }}</span>
                </nav>
                <h1 class="font-serif text-headline-lg text-primary">
                    {{ pageTitle }}
                </h1>
                <p class="mt-1 text-on-surface-variant">
                    Fill in the details below to create your artisanal
                    masterpiece.
                </p>
            </div>
            <div class="flex flex-wrap gap-md">
                <!-- <AppButton
                    type="button"
                    variant="secondary"
                    class="!h-auto !rounded-full border-primary-container px-6 py-3 !font-sans !text-label-caps !uppercase text-primary-container"
                    :disabled="form.processing"
                    @click="saveDraft"
                >
                    Save as Draft
                </AppButton>
                <AppButton
                    type="button"
                    variant="primary"
                    class="!h-auto !rounded-full !bg-[#E8572A] px-8 py-3 !font-sans !text-label-caps !uppercase shadow-lg hover:!brightness-110"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="publish"
                >
                    {{ primaryActionLabel }}
                </AppButton> -->
            </div>
        </section>

        <!-- Stepper -->
        <div class="mx-auto mb-xl w-1/2 max-sm:w-full">
            <!-- <div class="mx-auto mb-xl w-full max-w-[44rem] px-4 sm:max-w-lg"> -->
            <div class="flex items-start">
                <template v-for="(step, index) in steps" :key="step.id">
                    <button
                        type="button"
                        class="relative z-10 flex w-14 shrink-0 flex-col items-center gap-2 sm:w-16"
                        @click="scrollToStep(step)"
                    >
                        <div
                            class="flex size-9 items-center justify-center rounded-full text-sm font-bold transition-colors sm:size-10"
                            :class="
                                isStepReached(step.id)
                                    ? 'bg-secondary text-white shadow-md'
                                    : 'bg-surface-container-highest text-on-surface-variant'
                            "
                        >
                            {{ step.id }}
                        </div>
                        <span
                            class="text-center font-sans text-[10px] font-bold uppercase tracking-wide sm:text-label-caps"
                            :class="
                                isStepReached(step.id)
                                    ? 'text-secondary'
                                    : 'text-on-surface-variant'
                            "
                        >
                            {{ step.label }}
                        </span>
                    </button>
                    <div
                        v-if="index < steps.length - 1"
                        class="mt-[1.125rem] min-w-0 flex-1 self-start border-t-2 border-dashed sm:mt-5"
                        :class="
                            isConnectorFilled(step.id)
                                ? 'border-secondary'
                                : 'border-outline-variant'
                        "
                        aria-hidden="true"
                    />
                </template>
            </div>
        </div>

        <form
            class="grid grid-cols-1 items-start  lg:grid-cols-8 gap-md  "
            @submit.prevent="publish"
        >
           
        <div class="space-y-lg lg:col-span-5">
                <!-- Basic Info -->
                <section
                    id="section-basic"
                    class="space-y-md rounded-xl bg-surface-container-lowest p-xl shadow-card"
                    @focusin="activeStep = Math.max(activeStep, 1)"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <IconInfoCircle
                            class="text-secondary"
                            :size="22"
                            stroke-width="1.5"
                        />
                        <h2 class="font-serif text-headline-sm text-primary">
                            Basic Information
                        </h2>
                    </div>

                    <div class="space-y-md">
                        <div class="grid grid-cols-1 gap-md md:grid-cols-2">
                            <div>
                                <AppInputLabel
                                    for="name"
                                    value="Product Name"
                                    class="!mb-xs !font-sans !text-label-caps !uppercase"
                                />
                                <AppInput
                                    id="name"
                                    v-model="form.name"
                                    class="mt-0 block w-full"
                                    placeholder="e.g. Artisanal Sourdough Batard"
                                    :has-error="!!form.errors.name"
                                    required
                                    autofocus
                                />
                                <AppInputError :message="form.errors.name" />
                            </div>

                            <div>
                                <AppInputLabel
                                    for="slug"
                                    value="URL Slug"
                                    class="!mb-xs !font-sans !text-label-caps !uppercase"
                                />
                                <div
                                    class="flex h-12 items-center rounded-xl border border-dashed border-outline-variant bg-surface-container-low px-md text-sm italic text-on-surface-variant"
                                    :class="{
                                        'border-error': form.errors.slug,
                                    }"
                                >
                                    <span class="truncate">
                                    products/
                                        <input
                                            id="slug"
                                            v-model="form.slug"
                                            type="text"
                                            class="inline-block min-w-[8rem] max-w-full border-0 bg-transparent p-0 font-semibold not-italic text-primary focus:ring-0"
                                            placeholder="product-slug"
                                            @input="slugTouched = true"
                                        />
                                    </span>
                                </div>
                                <AppInputError :message="form.errors.slug" />
                            </div>
                        </div>

                        <div>
                            <AppInputLabel
                                for="category_picker"
                                value="Categories *"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppMultiSelect
                                id="category_picker"
                                v-model="form.category_ids"
                                :options="categoryOptions"
                                placeholder="Add…"
                                :has-error="!!form.errors.category_ids"
                            />
                            <AppInputError
                                :message="form.errors.category_ids"
                            />
                            <AppInputError
                                :message="form.errors['category_ids.0']"
                            />
                        </div>

                        <div>
                            <div
                                class="mb-xs flex items-center justify-between"
                            >
                                <AppInputLabel
                                    for="short_description"
                                    value="Short Description *"
                                    class="!mb-0 !font-sans !text-label-caps !uppercase"
                                />
                                <span class="text-[10px] text-outline">
                                    {{ shortDescriptionCount }}/150 characters
                                </span>
                            </div>
                            <textarea
                                id="short_description"
                                v-model="form.short_description"
                                rows="3"
                                maxlength="500"
                                required
                                class="input-field h-auto resize-none py-md"
                                placeholder="Briefly describe the product's flavor profile..."
                                :class="{
                                    'input-field-error':
                                        form.errors.short_description,
                                }"
                            />
                            <AppInputError
                                :message="form.errors.short_description"
                            />
                        </div>

                        <div>
                            <AppInputLabel
                                for="description"
                                value="Full Description *"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="6"
                                required
                                class="input-field h-auto min-h-[200px] py-md"
                                placeholder="Tell the story of this product..."
                                :class="{
                                    'input-field-error':
                                        form.errors.description,
                                }"
                            />
                            <AppInputError :message="form.errors.description" />
                        </div>
                    </div>
                </section>

                <!-- Media -->
                <section
                    id="section-media"
                    class="space-y-md rounded-xl bg-surface-container-lowest p-xl shadow-card"
                    @focusin="activeStep = Math.max(activeStep, 2)"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <IconPhoto
                            class="text-secondary"
                            :size="22"
                            stroke-width="1.5"
                        />
                        <h2 class="font-serif text-headline-sm text-primary">
                            Product Media
                        </h2>
                    </div>

                    <div>
                        <AppInputLabel
                            for="thumbnail"
                            value="Main Thumbnail"
                            class="!mb-xs !font-sans !text-label-caps !uppercase"
                        />
                        <input
                            id="thumbnail"
                            ref="thumbnailInput"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            class="sr-only"
                            @change="onThumbnailChange"
                        />
                        <label
                            for="thumbnail"
                            class="group relative flex aspect-video w-full cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-outline-variant bg-surface-container-low transition-colors hover:bg-surface-container"
                            :class="{ 'border-error': form.errors.thumbnail }"
                        >
                            <img
                                v-if="thumbnailPreview"
                                :src="thumbnailPreview"
                                alt="Product thumbnail preview"
                                class="absolute inset-0 h-full w-full object-cover"
                            />
                            <div
                                class="relative z-10 flex flex-col items-center"
                                :class="{
                                    'rounded-lg bg-white/80 px-md py-sm':
                                        thumbnailPreview,
                                }"
                            >
                                <div
                                    class="mb-4 flex size-16 items-center justify-center rounded-full bg-white shadow-sm transition-transform group-hover:scale-110"
                                >
                                    <IconCloudUpload
                                        class="text-secondary"
                                        :size="32"
                                        stroke-width="1.5"
                                    />
                                </div>
                                <p class="font-sans text-title-lg text-primary">
                                    {{
                                        thumbnailPreview
                                            ? "Replace Thumbnail"
                                            : "Upload Thumbnail"
                                    }}
                                </p>
                                <p class="text-sm text-on-surface-variant">
                                    Primary product image. Max 5MB,
                                    JPG/PNG/WEBP.
                                </p>
                            </div>
                        </label>
                        <button
                            v-if="thumbnailIsObjectUrl"
                            type="button"
                            class="mt-sm text-body-sm font-medium text-error hover:underline"
                            @click="clearThumbnail"
                        >
                            Discard new thumbnail
                        </button>
                        <AppInputError :message="form.errors.thumbnail" />
                    </div>

                    <div>
                        <AppInputLabel
                            for="gallery_images"
                            value="Gallery Images *"
                            class="!mb-xs !font-sans !text-label-caps !uppercase"
                        />
                        <input
                            id="gallery_images"
                            ref="galleryInput"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            multiple
                            class="sr-only"
                            @change="onGalleryChange"
                        />
                        <div class="grid grid-cols-2 gap-md sm:grid-cols-4">
                            <div
                                v-for="image in visibleExistingGallery"
                                :key="`existing-${image.id}`"
                                class="group relative aspect-square overflow-hidden rounded-xl"
                            >
                                <img
                                    :src="image.url"
                                    alt="Product gallery image"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        type="button"
                                        class="rounded-full bg-error p-2 text-white"
                                        aria-label="Remove gallery image"
                                        @click="
                                            removeExistingGalleryImage(image.id)
                                        "
                                    >
                                        <IconTrash
                                            :size="16"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                </div>
                            </div>

                            <div
                                v-for="(preview, index) in galleryPreviews"
                                :key="`new-${preview.name}-${index}`"
                                class="group relative aspect-square overflow-hidden rounded-xl"
                            >
                                <img
                                    :src="preview.url"
                                    alt="New gallery image"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        type="button"
                                        class="rounded-full bg-error p-2 text-white"
                                        aria-label="Remove new gallery image"
                                        @click="removeNewGalleryImage(index)"
                                    >
                                        <IconTrash
                                            :size="16"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                </div>
                            </div>

                            <label
                                v-if="gallerySlotsRemaining > 0"
                                for="gallery_images"
                                class="flex aspect-square cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-outline-variant bg-surface-container-low transition-colors hover:bg-surface-container"
                                :class="{ 'border-error': form.errors.images }"
                            >
                                <IconPlus
                                    class="text-outline"
                                    :size="24"
                                    stroke-width="1.5"
                                />
                                <span
                                    class="mt-1 text-[10px] font-bold uppercase tracking-wide text-outline"
                                >
                                    Add
                                </span>
                            </label>
                        </div>
                        <p class="mt-sm text-xs text-on-surface-variant">
                            At least 1 gallery image required. Up to 8. Max 5MB
                            each.
                        </p>
                        <AppInputError :message="form.errors.images" />
                        <AppInputError :message="form.errors['images.0']" />
                    </div>
                </section>

                <!-- Pricing & Stock -->
                <section
                    id="section-pricing"
                    class="space-y-md rounded-xl bg-surface-container-lowest p-xl shadow-card"
                    @focusin="activeStep = Math.max(activeStep, 3)"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <IconCurrencyDollar
                            class="text-secondary"
                            :size="22"
                            stroke-width="1.5"
                        />
                        <h2 class="font-serif text-headline-sm text-primary">
                            Pricing &amp; Stock
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-md md:grid-cols-3">
                        <div>
                            <AppInputLabel
                                for="regular_price"
                                value="Regular Price ($)"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppInput
                                id="regular_price"
                                v-model="form.regular_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="block w-full"
                                :has-error="!!form.errors.regular_price"
                                required
                            />
                            <AppInputError
                                :message="form.errors.regular_price"
                            />
                        </div>
                        <div>
                            <AppInputLabel
                                for="sale_price"
                                value="Sale Price ($)"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppInput
                                id="sale_price"
                                v-model="form.sale_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="block w-full"
                                placeholder="Optional"
                                :has-error="!!form.errors.sale_price"
                            />
                            <AppInputError :message="form.errors.sale_price" />
                        </div>
                        <div>
                            <AppInputLabel
                                for="cost_price"
                                value="Cost per item ($)"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppInput
                                id="cost_price"
                                v-model="form.cost_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="block w-full"
                                placeholder="Optional"
                                :has-error="!!form.errors.cost_price"
                            />
                            <AppInputError :message="form.errors.cost_price" />
                        </div>
                    </div>

                    <hr class="my-4 border-outline-variant" />

                    <div class="grid grid-cols-1 gap-md md:grid-cols-2">
                        <div>
                            <AppInputLabel
                                for="sku"
                                value="SKU"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppInput
                                id="sku"
                                v-model="form.sku"
                                class="block w-full"
                                placeholder="e.g. BRD-SOUR-001"
                                :has-error="!!form.errors.sku"
                                required
                            />
                            <AppInputError :message="form.errors.sku" />
                        </div>
                        <div>
                            <AppInputLabel
                                for="barcode"
                                value="Barcode (EAN/UPC)"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppInput
                                id="barcode"
                                v-model="form.barcode"
                                class="block w-full"
                                placeholder="Optional"
                                :has-error="!!form.errors.barcode"
                            />
                            <AppInputError :message="form.errors.barcode" />
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-xl bg-surface-container-low p-4"
                    >
                        <div>
                            <p class="font-sans text-title-lg text-primary">
                                Track Stock Quantity
                            </p>
                            <p class="text-sm text-on-surface-variant">
                                Manage inventory and low-stock alerts.
                            </p>
                        </div>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                v-model="trackStock"
                                type="checkbox"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-outline-variant after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-secondary peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"
                            />
                        </label>
                    </div>

                    <div
                        v-if="trackStock"
                        class="grid grid-cols-1 gap-md md:grid-cols-2"
                    >
                        <div>
                            <AppInputLabel
                                for="stock_quantity"
                                value="Stock Quantity *"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppInput
                                id="stock_quantity"
                                v-model="form.stock_quantity"
                                type="number"
                                min="0"
                                class="block w-full"
                                :has-error="!!form.errors.stock_quantity"
                                required
                            />
                            <AppInputError
                                :message="form.errors.stock_quantity"
                            />
                        </div>
                        <div>
                            <AppInputLabel
                                for="low_stock_threshold"
                                value="Low Stock Threshold"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppInput
                                id="low_stock_threshold"
                                v-model="form.low_stock_threshold"
                                type="number"
                                min="0"
                                class="block w-full"
                                :has-error="!!form.errors.low_stock_threshold"
                            />
                            <AppInputError
                                :message="form.errors.low_stock_threshold"
                            />
                        </div>
                        <div
                            class="flex items-center justify-between rounded-xl border border-outline-variant bg-white p-4 md:col-span-2"
                        >
                            <div>
                                <p
                                    class="text-body-sm font-medium text-primary"
                                >
                                    Allow Backorders
                                </p>
                                <p class="text-xs text-on-surface-variant">
                                    Keep selling when stock reaches zero.
                                </p>
                            </div>
                            <label
                                class="relative inline-flex cursor-pointer items-center"
                            >
                                <input
                                    v-model="form.allow_backorders"
                                    type="checkbox"
                                    class="peer sr-only"
                                />
                                <div
                                    class="peer h-6 w-11 rounded-full bg-outline-variant after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-secondary peer-checked:after:translate-x-full peer-checked:after:border-white"
                                />
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Attributes -->
                <section
                    id="section-attributes"
                    class="space-y-md rounded-xl bg-surface-container-lowest p-xl shadow-card"
                    @focusin="activeStep = Math.max(activeStep, 4)"
                >
                    <div class="mb-4 flex items-center justify-between gap-md">
                        <div class="flex items-center gap-2">
                            <IconAdjustments
                                class="text-secondary"
                                :size="22"
                                stroke-width="1.5"
                            />
                            <h2
                                class="font-serif text-headline-sm text-primary"
                            >
                                Attributes *
                            </h2>
                        </div>
                        <Link
                            :href="route('admin.attributes.index')"
                            class="inline-flex items-center gap-1 font-sans text-label-caps uppercase text-secondary hover:underline"
                        >
                            <IconPlus :size="16" stroke-width="1.5" />
                            Manage Attributes
                        </Link>
                    </div>

                    <div
                        v-if="availableAttributes.length"
                        class="mb-md flex flex-wrap items-end gap-md"
                    >
                        <div class="min-w-[12rem] flex-1">
                            <AppInputLabel
                                for="attribute_picker"
                                value="Add Attribute"
                                class="!mb-xs !font-sans !text-label-caps !uppercase"
                            />
                            <AppSelect
                                id="attribute_picker"
                                :model-value="null"
                                :options="availableAttributes"
                                placeholder="Select an attribute…"
                                :has-error="!!form.errors.attribute_value_ids"
                                @update:model-value="addAttribute"
                            />
                        </div>
                    </div>

                    <div
                        v-if="addedAttributes.length === 0"
                        class="rounded-xl border border-dashed bg-surface-container-low p-lg text-center text-body-sm text-on-surface-variant"
                        :class="
                            form.errors.attribute_value_ids
                                ? 'border-error'
                                : 'border-outline-variant'
                        "
                    >
                        At least one attribute value is required. Select from
                        your catalog above.
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="attribute in addedAttributes"
                            :key="attribute.id"
                            class="flex gap-lg rounded-xl border border-outline-variant bg-white p-4"
                        >
                            <div class="min-w-0 flex-1">
                                <div
                                    class="mb-3 flex items-start justify-between gap-md"
                                >
                                    <div>
                                        <h4
                                            class="font-sans text-title-lg text-primary"
                                        >
                                            {{ attribute.name }}
                                        </h4>
                                        <p
                                            class="text-xs text-on-surface-variant"
                                        >
                                            Display:
                                            {{
                                                displayTypeLabel(
                                                    attribute.display_type,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="value in attribute.values ?? []"
                                        :key="value.id"
                                        type="button"
                                        class="rounded-lg border px-4 py-2 text-sm transition-colors"
                                        :class="
                                            isValueSelected(value.id)
                                                ? 'border-secondary bg-secondary/10 font-semibold text-secondary'
                                                : 'border-outline-variant bg-surface-bright text-on-surface hover:border-secondary/40'
                                        "
                                        @click="toggleAttributeValue(value.id)"
                                    >
                                        {{ value.value }}
                                    </button>
                                    <p
                                        v-if="!(attribute.values ?? []).length"
                                        class="text-xs text-on-surface-variant"
                                    >
                                        No values defined for this attribute.
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col justify-center">
                                <button
                                    type="button"
                                    class="p-2 text-outline transition-colors hover:text-error"
                                    aria-label="Remove attribute"
                                    @click="removeAttribute(attribute)"
                                >
                                    <IconTrash :size="20" stroke-width="1.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <AppInputError :message="form.errors.attribute_value_ids" />
                </section>

                <!-- SEO -->
                <section
                    id="section-seo"
                    class="space-y-md rounded-xl bg-surface-container-lowest p-xl shadow-card"
                    @focusin="activeStep = Math.max(activeStep, 5)"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <IconSearch
                            class="text-secondary"
                            :size="22"
                            stroke-width="1.5"
                        />
                        <h2 class="font-serif text-headline-sm text-primary">
                            SEO &amp; Visibility
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-md">
                        <div>
                            <div
                                class="mb-xs flex items-center justify-between"
                            >
                                <AppInputLabel
                                    for="meta_title"
                                    value="Meta Title"
                                    class="!mb-0 !font-sans !text-label-caps !uppercase"
                                />
                                <span class="text-[10px] text-outline">
                                    {{ metaTitleCount }}/60 characters
                                </span>
                            </div>
                            <AppInput
                                id="meta_title"
                                v-model="form.meta_title"
                                class="block w-full"
                                :placeholder="`${form.name || 'Product'} | ${siteName}`"
                                :has-error="!!form.errors.meta_title"
                            />
                            <AppInputError :message="form.errors.meta_title" />
                        </div>

                        <div>
                            <div
                                class="mb-xs flex items-center justify-between"
                            >
                                <AppInputLabel
                                    for="meta_description"
                                    value="Meta Description"
                                    class="!mb-0 !font-sans !text-label-caps !uppercase"
                                />
                                <span class="text-[10px] text-outline">
                                    {{ metaDescriptionCount }}/160 characters
                                </span>
                            </div>
                            <textarea
                                id="meta_description"
                                v-model="form.meta_description"
                                rows="3"
                                class="input-field h-auto resize-none py-md"
                                placeholder="Write a compelling meta description for search results..."
                                :class="{
                                    'input-field-error':
                                        form.errors.meta_description,
                                }"
                            />
                            <AppInputError
                                :message="form.errors.meta_description"
                            />
                        </div>

                        <div class="mt-4">
                            <AppInputLabel
                                value="Google Search Preview"
                                class="!mb-3 !font-sans !text-label-caps !uppercase"
                            />
                            <div
                                class="rounded-xl border border-outline-variant bg-white p-6 shadow-sm"
                            >
                                <div
                                    class="mb-1 text-xl font-medium leading-tight text-[#1a0dab]"
                                >
                                    {{ previewTitle }}
                                </div>
                                <div class="mb-1 text-sm text-[#006621]">
                                    {{ previewUrl }}
                                </div>
                                <div
                                    class="text-sm leading-relaxed text-[#4d5156]"
                                >
                                    {{ previewSnippet }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Sidebar -->
            <aside class="sticky top-24 space-y-lg lg:col-span-3">
                <section
                    class="space-y-md rounded-xl border border-primary-fixed bg-surface-container-lowest p-lg shadow-card"
                >
                    <h3
                        class="border-b border-outline-variant pb-2 font-serif text-headline-sm text-primary"
                    >
                        Publishing
                    </h3>

                    <div class="space-y-4 pt-2">
                        <div class="flex items-center justify-between">
                            <span
                                class="flex items-center gap-2 text-sm font-medium text-on-surface-variant"
                            >
                                Status
                            </span>
                            <div class="w-36 shrink-0">
                                <AppSelect
                                    id="status"
                                    v-model="form.status"
                                    :options="statusOptions"
                                    size="sm"
                                    :has-error="!!form.errors.status"
                                />
                            </div>
                        </div>
                        <AppInputError :message="form.errors.status" />

                        <div
                            class="flex items-center justify-between border-t border-outline-variant py-md"
                        >
                            <span class="text-sm font-medium text-on-surface">
                                Featured Product
                            </span>
                            <label
                                class="relative inline-flex cursor-pointer items-center"
                            >
                                <input
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="peer sr-only"
                                />
                                <div
                                    class="peer h-6 w-11 rounded-full bg-outline-variant after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-secondary peer-checked:after:translate-x-full peer-checked:after:border-white"
                                />
                            </label>
                        </div>

                        <div class="space-y-3 pt-2">
                            <AppButton
                                type="button"
                                variant="primary"
                                class="!h-auto w-full !rounded-full !bg-[#E8572A] py-3 !font-sans !text-label-caps !uppercase shadow-lg hover:!brightness-110"
                                :loading="form.processing"
                                :disabled="form.processing"
                                @click="publish"
                            >
                                <span
                                    class="inline-flex items-center justify-center gap-2"
                                >
                                    <IconSend :size="18" stroke-width="1.5" />
                                    {{
                                        isEdit
                                            ? "Update & Publish"
                                            : "Publish Now"
                                    }}
                                </span>
                            </AppButton>
                            <AppButton
                                type="button"
                                variant="ghost"
                                class="w-full !rounded-full py-2 !font-sans !text-label-caps !uppercase text-secondary hover:!bg-surface-container"
                                :disabled="form.processing"
                                @click="saveDraft"
                            >
                                Save as Draft
                            </AppButton>
                        </div>
                    </div>
                </section>

                <section
                    class="space-y-md rounded-xl bg-surface-container-lowest p-lg shadow-card"
                >
                    <h4 class="font-sans text-title-lg text-primary">
                        Product Summary
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="rounded-xl bg-surface-container-low p-4 text-center"
                        >
                            <p class="text-2xl font-bold text-secondary">
                                {{ summaryCounts.categories }}
                            </p>
                            <p
                                class="font-sans text-[10px] uppercase tracking-wider text-outline"
                            >
                                Categories
                            </p>
                        </div>
                        <div
                            class="rounded-xl bg-surface-container-low p-4 text-center"
                        >
                            <p class="text-2xl font-bold text-secondary">
                                {{ summaryCounts.images }}
                            </p>
                            <p
                                class="font-sans text-[10px] uppercase tracking-wider text-outline"
                            >
                                Images
                            </p>
                        </div>
                        <div
                            class="rounded-xl bg-surface-container-low p-4 text-center"
                        >
                            <p class="text-2xl font-bold text-secondary">
                                {{ summaryCounts.attributes }}
                            </p>
                            <p
                                class="font-sans text-[10px] uppercase tracking-wider text-outline"
                            >
                                Attributes
                            </p>
                        </div>
                        <div
                            class="rounded-xl bg-surface-container-low p-4 text-center"
                        >
                            <p class="text-2xl font-bold text-secondary">
                                {{ summaryCounts.attributeValues }}
                            </p>
                            <p
                                class="font-sans text-[10px] uppercase tracking-wider text-outline"
                            >
                                Values
                            </p>
                        </div>
                    </div>
                </section>

                <section
                    class="relative overflow-hidden rounded-xl bg-primary-container p-lg group"
                >
                    <div class="relative z-10 text-on-primary-container">
                        <h5 class="mb-2 font-sans text-title-lg text-white">Need Help?</h5>
                        <p class="mb-4 text-xs ">
                            Create attributes first if you need size, flavour,
                            or other options on this product.
                        </p>
                        <Link
                            :href="route('admin.attributes.index')"
                            class="inline-flex rounded-full bg-secondary px-4 py-2 text-xs font-bold text-white transition-transform active:scale-95"
                        >
                            Open Attributes
                        </Link>
                    </div>
                    <div
                        class="absolute -bottom-4 -right-4 rotate-12 text-white opacity-10 transition-transform duration-500 group-hover:scale-125"
                    >
                        <IconPhoto :size="120" stroke-width="1" />
                    </div>
                </section>
            </aside>
        </form>
    </AdminLayout>
</template>
