<script setup>
import PermissionsMatrix from '@/Components/Admin/PermissionsMatrix.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import { useForm } from '@inertiajs/vue3';
import {
    IconAdjustments,
    IconShieldCheck,
    IconUserPlus,
    IconUserShield,
    IconX,
} from '@tabler/icons-vue';
import { computed, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    schema: {
        type: Object,
        default: () => ({}),
    },
    templates: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const emptyMatrix = () => {
    const matrix = {};
    for (const [scope, definition] of Object.entries(props.schema ?? {})) {
        matrix[scope] = {};
        for (const action of Object.keys(definition.actions ?? {})) {
            matrix[scope][action] = false;
        }
    }
    return matrix;
};

const form = useForm({
    email: '',
    role: 'admin',
    roleType: 'admin',
    template: 'full_admin',
    permissions: emptyMatrix(),
});

const roleCards = [
    {
        id: 'super_admin',
        label: 'Super Admin',
        description: 'Full access to all settings and financial data.',
        icon: IconShieldCheck,
    },
    {
        id: 'admin',
        label: 'Admin',
        description: 'Manage products, orders, and customer lists.',
        icon: IconUserShield,
    },
    {
        id: 'custom',
        label: 'Custom Role',
        description: 'Define granular permissions for specific modules.',
        icon: IconAdjustments,
    },
];

const templateOptions = computed(() =>
    (props.templates ?? []).map((name) => ({
        id: name,
        name: name
            .split('_')
            .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
            .join(' '),
    })),
);

watch(
    () => props.show,
    (open) => {
        if (open) {
            form.reset();
            form.clearErrors();
            form.email = '';
            form.role = 'admin';
            form.roleType = 'admin';
            form.template = props.templates?.[0] ?? 'full_admin';
            form.permissions = emptyMatrix();
        }
    },
);

const selectRoleType = (type) => {
    form.roleType = type;
    form.role = type === 'super_admin' ? 'super_admin' : 'admin';
};

const close = () => {
    if (form.processing) {
        return;
    }
    emit('close');
};

const submit = () => {
    const payload = {
        email: form.email,
        role: form.roleType === 'super_admin' ? 'super_admin' : 'admin',
    };

    if (form.roleType === 'admin') {
        payload.template = form.template || null;
    }

    if (form.roleType === 'custom') {
        payload.permissions = form.permissions;
    }

    form
        .transform(() => payload)
        .post(route('admin.users.invite'), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
};
</script>

<template>
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" class="relative z-50" @close="close">
            <TransitionChild
                as="template"
                enter="ease-out duration-200"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-150"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-primary/40 backdrop-blur-[4px]" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto p-md">
                <div class="flex min-h-full items-center justify-center">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-200"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-150"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-surface-container-lowest shadow-xl"
                        >
                            <div
                                class="flex items-center justify-between border-b border-outline-variant/30 bg-surface-bright px-lg py-lg"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex size-12 items-center justify-center rounded-full bg-secondary-fixed"
                                    >
                                        <IconUserPlus
                                            class="size-6 text-on-secondary-fixed"
                                            stroke="1.5"
                                        />
                                    </div>
                                    <div>
                                        <DialogTitle
                                            class="font-serif text-headline-sm text-primary"
                                        >
                                            Invite a New Admin
                                        </DialogTitle>
                                        <p class="text-body-sm text-on-surface-variant">
                                            Add a team member to manage your bakery.
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="rounded-full p-2 transition-colors hover:bg-surface-container"
                                    aria-label="Close"
                                    @click="close"
                                >
                                    <IconX class="size-5 text-on-surface-variant" stroke="1.5" />
                                </button>
                            </div>

                            <form
                                class="flex min-h-0 flex-1 flex-col"
                                @submit.prevent="submit"
                            >
                                <div
                                    class="admin-scrollbar flex-1 space-y-xl overflow-y-auto p-lg"
                                >
                                    <div class="space-y-sm">
                                        <AppInputLabel for="invite-email" value="Email Address" />
                                        <AppInput
                                            id="invite-email"
                                            v-model="form.email"
                                            type="email"
                                            placeholder="julian@cravebakery.com"
                                            required
                                            autocomplete="email"
                                        />
                                        <AppInputError :message="form.errors.email" />
                                    </div>

                                    <div class="space-y-md">
                                        <p
                                            class="font-sans text-label-caps uppercase text-on-surface-variant"
                                        >
                                            Select Admin Role
                                        </p>
                                        <div class="grid grid-cols-1 gap-md md:grid-cols-3">
                                            <button
                                                v-for="card in roleCards"
                                                :key="card.id"
                                                type="button"
                                                class="rounded-xl border p-md text-left transition-all"
                                                :class="
                                                    form.roleType === card.id
                                                        ? 'border-secondary bg-surface shadow-[0_0_0_1px_theme(colors.secondary)]'
                                                        : 'border-outline-variant hover:border-secondary-container'
                                                "
                                                @click="selectRoleType(card.id)"
                                            >
                                                <component
                                                    :is="card.icon"
                                                    class="mb-2 size-6 text-secondary"
                                                    stroke="1.5"
                                                />
                                                <p class="font-sans text-title-lg text-primary">
                                                    {{ card.label }}
                                                </p>
                                                <p
                                                    class="mt-1 text-[12px] leading-snug text-on-surface-variant"
                                                >
                                                    {{ card.description }}
                                                </p>
                                            </button>
                                        </div>
                                        <AppInputError :message="form.errors.role" />
                                    </div>

                                    <div
                                        v-if="form.roleType === 'admin'"
                                        class="space-y-sm"
                                    >
                                        <AppInputLabel value="Permission Template" />
                                        <AppSelect
                                            v-model="form.template"
                                            :options="templateOptions"
                                            placeholder="Choose template"
                                        />
                                        <AppInputError :message="form.errors.template" />
                                    </div>

                                    <div
                                        v-if="form.roleType === 'custom'"
                                        class="space-y-md border-t border-outline-variant/30 pt-lg"
                                    >
                                        <div class="flex items-center justify-between gap-4">
                                            <h3 class="font-sans text-title-lg text-primary">
                                                Granular Permissions
                                            </h3>
                                        </div>
                                        <PermissionsMatrix
                                            v-model="form.permissions"
                                            :schema="schema"
                                        />
                                        <AppInputError :message="form.errors.permissions" />
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between border-t border-outline-variant/30 bg-surface-bright p-lg"
                                >
                                    <button
                                        type="button"
                                        class="font-semibold text-on-surface-variant transition-all hover:underline"
                                        :disabled="form.processing"
                                        @click="close"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="flex h-12 items-center gap-2 rounded-full bg-secondary px-8 font-bold text-white shadow-md transition-all hover:shadow-lg active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="form.processing"
                                    >
                                        {{ form.processing ? 'Sending…' : 'Send Invite' }}
                                    </button>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
