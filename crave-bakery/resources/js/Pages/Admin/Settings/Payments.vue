<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    IconChevronDown,
    IconChevronUp,
    IconCircleCheck,
    IconCreditCard,
    IconSettings,
    IconTruck,
} from '@tabler/icons-vue';
import { reactive, ref } from 'vue';
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    gateways: {
        type: Array,
        default: () => [],
    },
});

const expandedGateway = ref(null);

const forms = reactive(
    Object.fromEntries(
        props.gateways.map((gateway) => [
            gateway.name,
            useForm({
                is_enabled: gateway.is_enabled,
                is_test_mode: gateway.is_test_mode,
                label: gateway.label,
                description: gateway.description ?? '',
                instructions: gateway.instructions ?? '',
                sort_order: gateway.sort_order,
                config: Object.fromEntries(
                    (gateway.config_fields ?? []).map((field) => [field.key, '']),
                ),
            }),
        ]),
    ),
);

const gatewayIcons = {
    stripe: IconCreditCard,
    cod: IconTruck,
};

function toggleExpand(gatewayName) {
    expandedGateway.value =
        expandedGateway.value === gatewayName ? null : gatewayName;
}

function toggleEnable(gateway) {
    router.patch(
        route('admin.settings.payments.toggle', { gateway: gateway.id }),
        {},
        { preserveScroll: true },
    );
}

function saveGateway(gateway) {
    forms[gateway.name].patch(
        route('admin.settings.payments.update', { gateway: gateway.id }),
        {
            preserveScroll: true,
            onSuccess: () => {
                expandedGateway.value = null;
            },
        },
    );
}
</script>

<template>
    <AdminLayout title="Payment Methods" breadcrumb="Settings / Payments">
        <Head title="Payment Methods" />

        <div class="mx-auto w-full max-w-3xl space-y-lg">
            <div>
                <h1 class="font-serif text-headline-sm text-primary">
                    Payment Methods
                </h1>
                <p class="mt-1 font-sans text-body-sm text-on-surface-variant">
                    Enable payment methods and configure their credentials. Only
                    enabled methods appear on the checkout page.
                </p>
            </div>

            <div class="space-y-4">
                <div
                    v-for="gateway in gateways"
                    :key="gateway.name"
                    class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
                >
                    <div class="flex flex-wrap items-center gap-4 p-5">
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-surface-container"
                        >
                            <component
                                :is="gatewayIcons[gateway.name] ?? IconCreditCard"
                                :size="20"
                                stroke-width="1.5"
                                class="text-primary"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-sans text-title-lg font-semibold text-primary">
                                    {{ gateway.label }}
                                </p>
                                <span
                                    v-if="gateway.is_test_mode && gateway.name !== 'cod'"
                                    class="rounded-badge bg-warning/10 px-2 py-0.5 font-sans text-xs font-medium text-warning"
                                >
                                    Test Mode
                                </span>
                                <span
                                    v-if="gateway.is_enabled"
                                    class="inline-flex items-center gap-1 rounded-badge bg-success/10 px-2 py-0.5 font-sans text-xs font-medium text-success"
                                >
                                    <IconCircleCheck :size="12" stroke-width="1.5" />
                                    Active
                                </span>
                                <span
                                    v-else
                                    class="rounded-badge bg-surface-container-high px-2 py-0.5 font-sans text-xs font-medium text-on-surface-variant"
                                >
                                    Disabled
                                </span>
                            </div>
                            <p class="truncate font-sans text-body-sm text-on-surface-variant">
                                {{ gateway.description }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/40"
                            :class="
                                gateway.is_enabled
                                    ? 'bg-accent'
                                    : 'bg-outline-variant'
                            "
                            :aria-pressed="gateway.is_enabled"
                            :aria-label="
                                gateway.is_enabled
                                    ? `Disable ${gateway.label}`
                                    : `Enable ${gateway.label}`
                            "
                            @click="toggleEnable(gateway)"
                        >
                            <span
                                class="pointer-events-none inline-block size-5 rounded-full bg-white shadow transition"
                                :class="
                                    gateway.is_enabled
                                        ? 'translate-x-5'
                                        : 'translate-x-0'
                                "
                            />
                        </button>

                        <button
                            type="button"
                            class="inline-flex shrink-0 items-center gap-1 font-sans text-sm font-medium text-accent hover:underline"
                            @click="toggleExpand(gateway.name)"
                        >
                            <IconSettings :size="16" stroke-width="1.5" />
                            Configure
                            <component
                                :is="
                                    expandedGateway === gateway.name
                                        ? IconChevronUp
                                        : IconChevronDown
                                "
                                :size="14"
                                stroke-width="1.5"
                            />
                        </button>
                    </div>

                    <div
                        v-if="expandedGateway === gateway.name"
                        class="space-y-5 border-t border-outline-variant p-5"
                    >
                        <div
                            v-if="gateway.name !== 'cod'"
                            class="flex items-center justify-between rounded-[10px] bg-surface-container p-4"
                        >
                            <div>
                                <p class="font-sans text-sm font-medium text-primary">
                                    Test Mode
                                </p>
                                <p class="font-sans text-xs text-on-surface-variant">
                                    Use test credentials. Disable when going live.
                                </p>
                            </div>
                            <button
                                type="button"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors"
                                :class="
                                    forms[gateway.name].is_test_mode
                                        ? 'bg-accent'
                                        : 'bg-outline-variant'
                                "
                                @click="
                                    forms[gateway.name].is_test_mode =
                                        !forms[gateway.name].is_test_mode
                                "
                            >
                                <span
                                    class="pointer-events-none inline-block size-5 rounded-full bg-white shadow transition"
                                    :class="
                                        forms[gateway.name].is_test_mode
                                            ? 'translate-x-5'
                                            : 'translate-x-0'
                                    "
                                />
                            </button>
                        </div>

                        <div
                            v-if="gateway.config_fields.length > 0"
                            class="space-y-4"
                        >
                            <p
                                class="font-sans text-xs font-medium uppercase tracking-wide text-on-surface-variant"
                            >
                                API Credentials
                            </p>

                            <div
                                v-for="field in gateway.config_fields"
                                :key="field.key"
                            >
                                <AppInputLabel
                                    :for="`${gateway.name}-${field.key}`"
                                    :value="field.label"
                                />
                                <span
                                    v-if="field.filled"
                                    class="ml-2 font-sans text-xs text-success"
                                >
                                    Saved
                                </span>
                                <AppInput
                                    :id="`${gateway.name}-${field.key}`"
                                    v-model="forms[gateway.name].config[field.key]"
                                    :type="field.type"
                                    class="mt-1 block w-full"
                                    autocomplete="off"
                                    :placeholder="
                                        field.filled
                                            ? '•••••••••••• (leave blank to keep existing)'
                                            : `Enter your ${field.label}`
                                    "
                                />
                                <AppInputError
                                    class="mt-1"
                                    :message="
                                        forms[gateway.name].errors[
                                            `config.${field.key}`
                                        ]
                                    "
                                />
                            </div>
                        </div>

                        <div v-if="gateway.name === 'cod'" class="space-y-4">
                            <p
                                class="font-sans text-xs font-medium uppercase tracking-wide text-on-surface-variant"
                            >
                                Customer Instructions
                            </p>
                            <div>
                                <AppInputLabel
                                    :for="`${gateway.name}-instructions`"
                                    value="Instructions shown at checkout"
                                />
                                <textarea
                                    :id="`${gateway.name}-instructions`"
                                    v-model="forms[gateway.name].instructions"
                                    rows="3"
                                    class="input-field mt-1 block w-full"
                                />
                                <AppInputError
                                    class="mt-1"
                                    :message="forms[gateway.name].errors.instructions"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <AppInputLabel
                                    :for="`${gateway.name}-label`"
                                    value="Display Label"
                                />
                                <AppInput
                                    :id="`${gateway.name}-label`"
                                    v-model="forms[gateway.name].label"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <AppInputError
                                    class="mt-1"
                                    :message="forms[gateway.name].errors.label"
                                />
                            </div>
                            <div>
                                <AppInputLabel
                                    :for="`${gateway.name}-sort`"
                                    value="Sort Order"
                                />
                                <AppInput
                                    :id="`${gateway.name}-sort`"
                                    v-model="forms[gateway.name].sort_order"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                />
                                <AppInputError
                                    class="mt-1"
                                    :message="forms[gateway.name].errors.sort_order"
                                />
                            </div>
                        </div>

                        <div>
                            <AppInputLabel
                                :for="`${gateway.name}-description`"
                                value="Description"
                            />
                            <AppInput
                                :id="`${gateway.name}-description`"
                                v-model="forms[gateway.name].description"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <AppInputError
                                class="mt-1"
                                :message="forms[gateway.name].errors.description"
                            />
                        </div>

                        <div class="flex justify-end gap-3">
                            <AppButton
                                variant="secondary"
                                type="button"
                                @click="expandedGateway = null"
                            >
                                Cancel
                            </AppButton>
                            <AppButton
                                variant="primary"
                                type="button"
                                :loading="forms[gateway.name].processing"
                                :disabled="forms[gateway.name].processing"
                                @click="saveGateway(gateway)"
                            >
                                Save Changes
                            </AppButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
