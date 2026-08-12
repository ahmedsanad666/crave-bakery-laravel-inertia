<script setup>
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { IconChevronRight } from '@tabler/icons-vue';
import { computed } from 'vue';

const props = defineProps({
    mode: {
        type: String,
        required: true,
        validator: (value) => ['create', 'edit'].includes(value),
    },
    promoCode: {
        type: Object,
        default: null,
    },
});

const isEdit = computed(() => props.mode === 'edit');

const toDatetimeLocal = (iso) => {
    if (!iso) {
        return '';
    }

    const date = new Date(iso);
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const pad = (n) => String(n).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const form = useForm({
    code: props.promoCode?.code ?? '',
    title: props.promoCode?.title ?? '',
    type: props.promoCode?.type ?? 'percentage',
    value: props.promoCode?.value ?? '',
    min_order_amount: props.promoCode?.min_order_amount ?? '',
    max_uses: props.promoCode?.max_uses ?? '',
    starts_at: toDatetimeLocal(props.promoCode?.starts_at),
    expires_at: toDatetimeLocal(props.promoCode?.expires_at),
    is_active: props.promoCode?.is_active ?? true,
});

const typeOptions = [
    { id: 'percentage', name: 'Percentage (%)' },
    { id: 'fixed', name: 'Fixed amount ($)' },
];

const pageTitle = computed(() =>
    isEdit.value ? 'Edit Promo Code' : 'Create Promo Code',
);

const breadcrumbCurrent = computed(() =>
    isEdit.value
        ? `Edit ${props.promoCode?.code ?? 'Promo Code'}`
        : 'Add Promo Code',
);

const valueHint = computed(() =>
    form.type === 'percentage'
        ? 'Enter a percentage between 0.01 and 100'
        : 'Enter a fixed dollar amount off the subtotal',
);

const emptyToNull = (value) => {
    if (value === '' || value === null || value === undefined) {
        return null;
    }

    return value;
};

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            code: String(data.code ?? '').trim().toUpperCase(),
            title: String(data.title ?? '').trim(),
            min_order_amount: emptyToNull(data.min_order_amount),
            max_uses: emptyToNull(data.max_uses),
            starts_at: emptyToNull(data.starts_at),
            expires_at: emptyToNull(data.expires_at),
            is_active: Boolean(data.is_active),
        }))
        [isEdit.value ? 'put' : 'post'](
            isEdit.value
                ? route('admin.promo-codes.update', props.promoCode.id)
                : route('admin.promo-codes.store'),
        );
};
</script>

<template>
    <AdminLayout :title="pageTitle" :breadcrumb="`Promo Codes / ${isEdit ? 'Edit' : 'New'}`">
        <Head :title="pageTitle" />

        <section class="mb-lg pt-4">
            <nav
                class="mb-2 flex items-center gap-2 text-label-caps uppercase text-on-surface-variant"
            >
                <Link
                    :href="route('admin.promo-codes.index')"
                    class="hover:text-on-surface"
                >
                    Sales
                </Link>
                <IconChevronRight class="size-3.5" stroke="2" />
                <Link
                    :href="route('admin.promo-codes.index')"
                    class="hover:text-on-surface"
                >
                    Promo Codes
                </Link>
                <IconChevronRight class="size-3.5" stroke="2" />
                <span class="text-on-surface">{{ breadcrumbCurrent }}</span>
            </nav>
            <h2 class="font-serif text-headline-lg text-primary">
                {{ pageTitle }}
            </h2>
            <p class="mt-1 text-body-sm text-on-surface-variant">
                Codes are matched case-insensitively at checkout and stored in uppercase.
            </p>
        </section>

        <form class="mx-auto max-w-3xl space-y-lg" @submit.prevent="submit">
            <section class="card-elevated space-y-md p-lg">
                <h3 class="font-serif text-headline-sm text-primary">
                    Card display
                </h3>
                <p class="text-body-sm text-on-surface-variant">
                    This copy appears on the public promo card. Brand name comes from site settings.
                </p>

                <div>
                    <AppInputLabel for="title" value="Title" />
                    <AppInput
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1"
                        :has-error="Boolean(form.errors.title)"
                        placeholder="Buy 1 Get 1 Free on Muffins"
                        autofocus
                    />
                    <p class="mt-1 text-body-sm text-on-surface-variant">
                        Main headline shown on the promo card
                    </p>
                    <AppInputError class="mt-1" :message="form.errors.title" />
                </div>
            </section>

            <section class="card-elevated space-y-md p-lg">
                <h3 class="font-serif text-headline-sm text-primary">
                    Discount details
                </h3>

                <div>
                    <AppInputLabel for="code" value="Code" />
                    <AppInput
                        id="code"
                        v-model="form.code"
                        type="text"
                        class="mt-1 uppercase tracking-wide"
                        :has-error="Boolean(form.errors.code)"
                        placeholder="WELCOME15"
                    />
                    <AppInputError class="mt-1" :message="form.errors.code" />
                </div>

                <div class="grid gap-md sm:grid-cols-2">
                    <div>
                        <AppInputLabel value="Type" />
                        <div class="mt-1">
                            <AppSelect
                                v-model="form.type"
                                :options="typeOptions"
                                :has-error="Boolean(form.errors.type)"
                                placeholder="Select type"
                            />
                        </div>
                        <AppInputError class="mt-1" :message="form.errors.type" />
                    </div>

                    <div>
                        <AppInputLabel for="value" value="Value" />
                        <AppInput
                            id="value"
                            v-model="form.value"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="mt-1"
                            :has-error="Boolean(form.errors.value)"
                            :placeholder="form.type === 'percentage' ? '15' : '5.00'"
                        />
                        <p class="mt-1 text-body-sm text-on-surface-variant">
                            {{ valueHint }}
                        </p>
                        <AppInputError class="mt-1" :message="form.errors.value" />
                    </div>
                </div>

                <div class="grid gap-md sm:grid-cols-2">
                    <div>
                        <AppInputLabel
                            for="min_order_amount"
                            value="Minimum order amount"
                        />
                        <AppInput
                            id="min_order_amount"
                            v-model="form.min_order_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1"
                            :has-error="Boolean(form.errors.min_order_amount)"
                            placeholder="Optional"
                        />
                        <AppInputError
                            class="mt-1"
                            :message="form.errors.min_order_amount"
                        />
                    </div>

                    <div>
                        <AppInputLabel for="max_uses" value="Max uses" />
                        <AppInput
                            id="max_uses"
                            v-model="form.max_uses"
                            type="number"
                            step="1"
                            min="1"
                            class="mt-1"
                            :has-error="Boolean(form.errors.max_uses)"
                            placeholder="Unlimited"
                        />
                        <AppInputError
                            class="mt-1"
                            :message="form.errors.max_uses"
                        />
                    </div>
                </div>

                <div
                    v-if="isEdit"
                    class="rounded-lg border border-outline-variant/40 bg-surface-container-low px-4 py-3"
                >
                    <p class="text-label-caps uppercase text-outline">
                        Usage (read-only)
                    </p>
                    <p class="mt-1 font-sans text-sm font-semibold text-primary">
                        {{ promoCode?.usage_label ?? '0 / ∞' }}
                    </p>
                    <p class="mt-1 text-body-sm text-on-surface-variant">
                        Incremented automatically when customers complete orders with this code.
                    </p>
                </div>
            </section>

            <section class="card-elevated space-y-md p-lg">
                <h3 class="font-serif text-headline-sm text-primary">
                    Schedule & status
                </h3>

                <div class="grid gap-md sm:grid-cols-2">
                    <div>
                        <AppInputLabel for="starts_at" value="Starts at" />
                        <AppInput
                            id="starts_at"
                            v-model="form.starts_at"
                            type="datetime-local"
                            class="mt-1"
                            :has-error="Boolean(form.errors.starts_at)"
                        />
                        <AppInputError
                            class="mt-1"
                            :message="form.errors.starts_at"
                        />
                    </div>

                    <div>
                        <AppInputLabel for="expires_at" value="Expires at" />
                        <AppInput
                            id="expires_at"
                            v-model="form.expires_at"
                            type="datetime-local"
                            class="mt-1"
                            :has-error="Boolean(form.errors.expires_at)"
                        />
                        <AppInputError
                            class="mt-1"
                            :message="form.errors.expires_at"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 rounded-lg border border-outline-variant/40 px-4 py-3">
                    <div>
                        <p class="font-sans text-sm font-semibold text-on-surface">
                            Active
                        </p>
                        <p class="text-body-sm text-on-surface-variant">
                            Inactive codes cannot be applied at checkout
                        </p>
                    </div>
                    <button
                        type="button"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/40"
                        :class="form.is_active ? 'bg-accent' : 'bg-outline-variant'"
                        :aria-pressed="form.is_active"
                        aria-label="Toggle active"
                        @click="form.is_active = !form.is_active"
                    >
                        <span
                            class="pointer-events-none inline-block size-5 rounded-full bg-white shadow transition"
                            :class="
                                form.is_active
                                    ? 'translate-x-5'
                                    : 'translate-x-0'
                            "
                        />
                    </button>
                </div>
                <AppInputError :message="form.errors.is_active" />
            </section>

            <div class="flex flex-col-reverse gap-sm sm:flex-row sm:justify-end">
                <Link
                    :href="route('admin.promo-codes.index')"
                    class="inline-flex h-12 items-center justify-center rounded-full border border-primary px-lg font-sans text-sm font-bold text-primary transition hover:bg-primary/5"
                >
                    Cancel
                </Link>
                <AppButton
                    type="submit"
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                    class="inline-flex h-12 items-center justify-center px-lg"
                >
                    {{ isEdit ? 'Update Promo Code' : 'Create Promo Code' }}
                </AppButton>
            </div>
        </form>
    </AdminLayout>
</template>
