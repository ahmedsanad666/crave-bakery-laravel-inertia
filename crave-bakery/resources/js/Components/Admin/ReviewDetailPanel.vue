<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    IconBan,
    IconCheck,
    IconChevronDown,
    IconChevronRight,
    IconDotsVertical,
    IconFlag,
    IconPhoto,
    IconSend,
    IconStar,
    IconStarFilled,
    IconThumbDown,
    IconThumbUp,
    IconTrash,
    IconX,
} from '@tabler/icons-vue';
import { computed, watch } from 'vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    review: {
        type: Object,
        default: null,
    },
    canApprove: {
        type: Boolean,
        default: false,
    },
    canDelete: {
        type: Boolean,
        default: false,
    },
    canRespond: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'approve', 'reject', 'delete']);

const responseForm = useForm({
    admin_response: '',
});

watch(
    () => props.review?.id,
    () => {
        responseForm.admin_response = props.review?.admin_response ?? '';
        responseForm.clearErrors();
    },
    { immediate: true },
);

const customer = computed(() => props.review?.customer ?? null);
const product = computed(() => props.review?.product ?? null);
const photos = computed(() => props.review?.photos ?? []);

const initials = computed(() => {
    const name = customer.value?.name ?? '?';
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
});

const statusBadgeClass = computed(() => {
    const map = {
        pending: 'bg-amber-100 text-amber-800',
        approved: 'bg-green-100 text-green-800',
        flagged: 'bg-error-container text-on-error-container',
        rejected: 'bg-surface-container-high text-on-surface-variant',
    };
    return map[props.review?.status] ?? map.pending;
});

const statusLabel = computed(() => {
    const map = {
        pending: 'Pending',
        approved: 'Approved',
        flagged: 'Flagged',
        rejected: 'Rejected',
    };
    return map[props.review?.status] ?? props.review?.status ?? '';
});

const formatDate = (iso) => {
    if (!iso) {
        return '—';
    }
    return new Date(iso).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatMoney = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value ?? 0));

const starFilled = (index) => index <= Math.round(Number(props.review?.rating ?? 0));

const submitResponse = () => {
    if (!props.review || !props.canRespond) {
        return;
    }
    responseForm.post(route('admin.reviews.respond', props.review.id), {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="overlay-backdrop fixed inset-0 z-[55]"
                @click="emit('close')"
            />
        </Transition>

        <aside
            class="fixed right-0 top-0 z-[60] flex h-full w-full max-w-[480px] flex-col border-l border-outline-variant bg-surface shadow-modal transition-transform duration-300"
            :class="open ? 'translate-x-0' : 'translate-x-full'"
            :aria-hidden="!open"
        >
            <template v-if="review">
                <!-- Header -->
                <header
                    class="flex h-16 shrink-0 items-center justify-between border-b border-outline-variant px-lg"
                >
                    <div class="flex items-center gap-md">
                        <button
                            type="button"
                            class="rounded-full p-1 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary"
                            @click="emit('close')"
                        >
                            <IconX class="size-5" stroke="1.5" />
                        </button>
                        <h2 class="font-serif text-[20px] text-primary">
                            Review Detail
                        </h2>
                    </div>

                    <Menu v-if="canApprove" as="div" class="relative">
                        <MenuButton
                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[12px] font-bold"
                            :class="statusBadgeClass"
                        >
                            {{ statusLabel }}
                            <IconChevronDown class="size-3.5" stroke="2" />
                        </MenuButton>
                        <MenuItems
                            class="absolute right-0 z-10 mt-1 w-32 overflow-hidden rounded-lg border border-outline-variant bg-white shadow-lg focus:outline-none"
                        >
                            <MenuItem v-slot="{ active }">
                                <button
                                    type="button"
                                    class="block w-full px-3 py-2 text-left text-[12px]"
                                    :class="active ? 'bg-surface-container-low' : ''"
                                    @click="emit('approve', review)"
                                >
                                    Approve
                                </button>
                            </MenuItem>
                            <MenuItem v-slot="{ active }">
                                <button
                                    type="button"
                                    class="block w-full px-3 py-2 text-left text-[12px]"
                                    :class="active ? 'bg-surface-container-low' : ''"
                                    @click="emit('reject', review)"
                                >
                                    Reject
                                </button>
                            </MenuItem>
                        </MenuItems>
                    </Menu>
                    <span
                        v-else
                        class="rounded-full px-3 py-1 text-[12px] font-bold"
                        :class="statusBadgeClass"
                    >
                        {{ statusLabel }}
                    </span>
                </header>

                <!-- Body -->
                <div class="admin-scrollbar flex-1 space-y-xl overflow-y-auto p-lg pb-28">
                    <!-- Flag banner -->
                    <div
                        v-if="review.status === 'flagged'"
                        class="flex items-start gap-md rounded-xl border border-error/20 bg-error-container/20 p-md"
                    >
                        <IconFlag class="size-5 shrink-0 text-error" stroke="1.5" />
                        <div>
                            <h4 class="font-sans text-[14px] font-semibold text-error">
                                Flagged for Review
                            </h4>
                            <p class="text-body-sm text-on-surface-variant">
                                Reason:
                                <span class="font-medium text-on-surface">
                                    {{ review.flag_reason || 'No reason provided' }}
                                </span>
                            </p>
                            <p
                                v-if="review.flagged_at"
                                class="mt-1 text-[12px] text-on-surface-variant"
                            >
                                Flagged {{ formatDate(review.flagged_at) }}
                            </p>
                        </div>
                    </div>

                    <!-- Reviewer -->
                    <section class="flex flex-col items-center text-center">
                        <div class="relative mb-md">
                            <img
                                v-if="customer?.avatar"
                                :src="customer.avatar"
                                :alt="customer.name"
                                class="size-24 rounded-full border-2 border-outline-variant object-cover shadow-sm"
                            >
                            <div
                                v-else
                                class="flex size-24 items-center justify-center rounded-full border-2 border-outline-variant bg-primary-container text-xl font-bold text-on-primary-container"
                            >
                                {{ initials }}
                            </div>
                            <span
                                v-if="review.is_verified_purchase"
                                class="absolute bottom-1 right-1 flex size-7 items-center justify-center rounded-full border-2 border-white bg-primary text-white"
                                title="Verified purchase"
                            >
                                <IconCheck class="size-3.5" stroke="2" />
                            </span>
                        </div>
                        <h3 class="font-serif text-[24px] text-primary">
                            {{ customer?.name ?? 'Customer' }}
                        </h3>
                        <div class="mt-sm flex gap-xl">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-outline">
                                    Total Reviews
                                </p>
                                <p class="font-sans text-title-lg text-primary">
                                    {{ customer?.reviews_count ?? 0 }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-outline">
                                    Avg Rating
                                </p>
                                <p class="font-sans text-title-lg text-primary">
                                    {{ (customer?.avg_rating ?? 0).toFixed(1) }} / 5.0
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Product -->
                    <section
                        v-if="product"
                        class="rounded-xl border border-outline-variant bg-surface-container-lowest p-md"
                    >
                        <p class="mb-md text-[10px] font-bold uppercase tracking-wider text-outline">
                            Reviewed Product
                        </p>
                        <div class="flex items-center gap-md">
                            <img
                                v-if="product.thumbnail"
                                :src="product.thumbnail"
                                :alt="product.name"
                                class="size-16 rounded-lg border border-outline-variant object-cover"
                            >
                            <div
                                v-else
                                class="flex size-16 items-center justify-center rounded-lg border border-outline-variant bg-surface-container-low"
                            >
                                <IconPhoto class="size-6 text-outline" stroke="1.5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="truncate font-sans text-[15px] font-semibold text-primary">
                                    {{ product.name }}
                                </h4>
                                <p class="text-body-sm text-on-surface-variant">
                                    {{ formatMoney(product.price) }}
                                </p>
                            </div>
                            <Link
                                v-if="product.id"
                                :href="route('admin.products.edit', product.id)"
                                class="rounded-full p-1 text-outline transition-colors hover:bg-surface-container-low hover:text-primary"
                            >
                                <IconChevronRight class="size-5" stroke="1.5" />
                            </Link>
                        </div>
                    </section>

                    <!-- Review content -->
                    <section class="space-y-md">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-1">
                                <span
                                    v-for="n in 5"
                                    :key="n"
                                    class="inline-flex"
                                >
                                    <IconStarFilled
                                        v-if="starFilled(n)"
                                        class="size-5 text-secondary"
                                    />
                                    <IconStar
                                        v-else
                                        class="size-5 text-outline-variant"
                                        stroke="1.5"
                                    />
                                </span>
                                <span class="ml-sm font-sans text-[16px] font-semibold text-primary">
                                    {{ Number(review.rating).toFixed(1) }} / 5.0
                                </span>
                            </div>
                            <div class="flex items-center gap-1 text-[12px] text-outline">
                                <IconCheck
                                    v-if="review.is_verified_purchase"
                                    class="size-3.5"
                                    stroke="2"
                                />
                                <span>
                                    {{ review.is_verified_purchase ? 'Verified Buyer' : 'Customer' }}
                                    • {{ formatDate(review.created_at) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <h4 class="mb-sm font-serif text-[20px] text-primary">
                                {{ review.title }}
                            </h4>
                            <p class="whitespace-pre-wrap text-body-lg leading-relaxed text-on-surface">
                                {{ review.body }}
                            </p>
                        </div>

                        <div class="flex items-center gap-lg pt-sm">
                            <div class="flex items-center gap-sm text-[12px] font-medium text-on-surface-variant">
                                <IconThumbUp class="size-[18px]" stroke="1.5" />
                                <span>Helpful ({{ review.helpful_yes }})</span>
                            </div>
                            <div class="flex items-center gap-sm text-[12px] font-medium text-on-surface-variant">
                                <IconThumbDown class="size-[18px]" stroke="1.5" />
                                <span>Not Helpful ({{ review.helpful_no }})</span>
                            </div>
                        </div>
                    </section>

                    <!-- Photos -->
                    <section v-if="photos.length">
                        <p class="mb-md text-[10px] font-bold uppercase tracking-wider text-outline">
                            Customer Photos ({{ photos.length }})
                        </p>
                        <div class="grid grid-cols-3 gap-md">
                            <img
                                v-for="photo in photos"
                                :key="photo.id"
                                :src="photo.path"
                                alt="Review photo"
                                class="aspect-square w-full rounded-lg border border-outline-variant object-cover"
                            >
                        </div>
                    </section>

                    <!-- Admin response -->
                    <section v-if="canRespond || review.admin_response" class="space-y-md">
                        <p class="mb-md text-[10px] font-bold uppercase tracking-wider text-outline">
                            Public Response
                        </p>
                        <div
                            class="rounded-xl border border-outline-variant bg-surface-container-low p-md transition-colors focus-within:border-primary"
                        >
                            <textarea
                                v-if="canRespond"
                                v-model="responseForm.admin_response"
                                rows="4"
                                class="w-full resize-none border-none bg-transparent p-0 text-body-sm text-on-surface placeholder:text-outline focus:ring-0"
                                :placeholder="`Draft your response to ${customer?.name ?? 'the customer'}...`"
                            />
                            <p
                                v-else
                                class="whitespace-pre-wrap text-body-sm text-on-surface"
                            >
                                {{ review.admin_response }}
                            </p>
                            <p
                                v-if="responseForm.errors.admin_response"
                                class="mt-2 text-body-sm text-error"
                            >
                                {{ responseForm.errors.admin_response }}
                            </p>
                            <div
                                v-if="canRespond"
                                class="mt-sm flex justify-end border-t border-outline-variant pt-sm"
                            >
                                <button
                                    type="button"
                                    class="inline-flex h-10 items-center gap-sm rounded-full bg-primary px-6 text-body-sm font-bold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                                    :disabled="responseForm.processing || !responseForm.admin_response.trim()"
                                    @click="submitResponse"
                                >
                                    <span>{{ review.admin_response ? 'Update Response' : 'Post Response' }}</span>
                                    <IconSend class="size-[18px]" stroke="1.5" />
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Footer -->
                <footer
                    class="absolute bottom-0 left-0 right-0 flex items-center gap-md border-t border-outline-variant bg-surface p-lg"
                >
                    <button
                        v-if="canApprove && review.status !== 'approved'"
                        type="button"
                        class="flex h-12 flex-1 items-center justify-center gap-sm rounded-full bg-success text-[14px] font-bold text-white shadow-sm transition hover:brightness-110"
                        @click="emit('approve', review)"
                    >
                        <IconCheck class="size-5" stroke="2" />
                        Approve
                    </button>
                    <button
                        v-if="canApprove && review.status !== 'rejected'"
                        type="button"
                        class="flex h-12 flex-1 items-center justify-center gap-sm rounded-full border border-error bg-white text-[14px] font-bold text-error transition hover:bg-red-50"
                        @click="emit('reject', review)"
                    >
                        <IconBan class="size-5" stroke="1.5" />
                        Reject
                    </button>
                    <Menu v-if="canDelete" as="div" class="relative">
                        <MenuButton
                            class="flex size-12 items-center justify-center rounded-full border border-outline-variant text-on-surface-variant transition hover:bg-surface-container-high"
                        >
                            <IconDotsVertical class="size-5" stroke="1.5" />
                        </MenuButton>
                        <MenuItems
                            class="absolute bottom-full right-0 z-10 mb-2 w-48 overflow-hidden rounded-xl border border-outline-variant bg-white shadow-xl focus:outline-none"
                        >
                            <MenuItem v-slot="{ active }">
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-sm px-md py-sm text-left text-body-sm text-error"
                                    :class="active ? 'bg-red-50' : ''"
                                    @click="emit('delete', review)"
                                >
                                    <IconTrash class="size-[18px]" stroke="1.5" />
                                    Delete Permanently
                                </button>
                            </MenuItem>
                        </MenuItems>
                    </Menu>
                </footer>
            </template>
        </aside>
    </Teleport>
</template>
