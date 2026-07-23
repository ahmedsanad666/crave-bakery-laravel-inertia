<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    IconHome,
    IconBuilding,
    IconMapPin,
    IconPencil,
    IconTrash,
    IconStar,
} from '@tabler/icons-vue';
import AppButton from '@/Components/Shared/AppButton.vue';

const props = defineProps({
    address: {
        type: Object,
        required: true,
    },
    deleting: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['edit', 'delete']);

const labelIcon = computed(() => {
    switch (props.address.label) {
        case 'Office':
            return IconBuilding;
        case 'Other':
            return IconMapPin;
        case 'Home':
        default:
            return IconHome;
    }
});

const fullName = computed(() =>
    [props.address.first_name, props.address.last_name].filter(Boolean).join(' '),
);

const addressLines = computed(() => {
    const lines = [
        props.address.address_line1,
        props.address.address_line2,
        [props.address.city, props.address.state, props.address.postal_code]
            .filter(Boolean)
            .join(', '),
        props.address.country,
    ];
    return lines.filter(Boolean);
});

const setAsDefault = () => {
    if (props.address.is_default) {
        return;
    }

    router.patch(route('addresses.default', props.address.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <article
        class="flex h-full flex-col rounded-xl border border-outline-variant/40 bg-white p-lg shadow-[0_2px_12px_rgba(0,0,0,0.07)] transition-shadow hover:shadow-[0_4px_16px_rgba(0,0,0,0.1)]"
    >
        <div class="mb-md flex flex-wrap items-center gap-sm">
            <span
                class="inline-flex items-center gap-1.5 rounded-[6px] bg-surface-container-high px-2 py-1 font-sans text-[12px] font-bold text-primary"
            >
                <component :is="labelIcon" :size="14" stroke-width="1.5" />
                {{ address.label }}
            </span>
            <span
                v-if="address.is_default"
                class="inline-flex items-center gap-1 rounded-[6px] bg-secondary/10 px-2 py-1 font-sans text-[12px] font-bold text-secondary"
            >
                <IconStar :size="14" stroke-width="1.5" />
                Default
            </span>
        </div>

        <div class="flex-1 space-y-1 font-sans text-body-sm text-on-surface-variant">
            <p class="font-sans text-title-lg font-semibold text-primary">
                {{ fullName }}
            </p>
            <p v-if="address.phone">{{ address.phone }}</p>
            <p v-for="(line, index) in addressLines" :key="index">
                {{ line }}
            </p>
        </div>

        <div
            class="mt-lg flex flex-wrap items-center gap-sm border-t border-outline-variant/30 pt-md"
        >
            <button
                v-if="!address.is_default"
                type="button"
                class="inline-flex h-10 items-center gap-1 rounded-full px-3 font-sans text-body-sm font-semibold text-secondary transition-colors hover:bg-secondary/5"
                @click="setAsDefault"
            >
                <IconStar :size="16" stroke-width="1.5" />
                Set as default
            </button>

            <div class="ml-auto flex items-center gap-sm">
                <AppButton
                    type="button"
                    variant="secondary"
                    class="!h-10 !px-4 text-body-sm"
                    @click="emit('edit', address)"
                >
                    <span class="inline-flex items-center gap-1.5">
                        <IconPencil :size="16" stroke-width="1.5" />
                        Edit
                    </span>
                </AppButton>
                <AppButton
                    type="button"
                    variant="ghost"
                    class="!h-10 !px-3 text-error hover:bg-error/5"
                    :disabled="deleting"
                    @click="emit('delete', address)"
                >
                    <span class="inline-flex items-center gap-1.5">
                        <IconTrash :size="16" stroke-width="1.5" />
                        Delete
                    </span>
                </AppButton>
            </div>
        </div>
    </article>
</template>
