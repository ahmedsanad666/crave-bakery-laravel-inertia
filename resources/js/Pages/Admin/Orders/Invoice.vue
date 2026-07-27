<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconBread,
    IconDownload,
    IconPrinter,
} from '@tabler/icons-vue';
import { computed, ref } from 'vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const downloadingPdf = ref(false);

const site = computed(() => page.props.siteSettings ?? {});
const siteName = computed(() => site.value.site_name ?? 'Crave Bakery');
const siteLogo = computed(() => site.value.logo ?? null);
const siteEmail = computed(() => site.value.email ?? '');
const sitePhone = computed(() => site.value.phone ?? '');
const siteAddress = computed(() => {
    const value = site.value.address;
    return typeof value === 'string' ? value.trim() : '';
});

const formatMoney = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value ?? 0));

const formatDateLong = (iso) => {
    if (!iso) {
        return '—';
    }
    return new Date(iso).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

const dueDate = computed(() => {
    if (!props.order.created_at) {
        return null;
    }
    const d = new Date(props.order.created_at);
    d.setDate(d.getDate() + 14);
    return d.toISOString();
});

const customerName = computed(() => {
    if (props.order.customer?.name) {
        return props.order.customer.name;
    }
    return (
        `${props.order.first_name ?? ''} ${props.order.last_name ?? ''}`.trim() ||
        'Guest'
    );
});

const customerEmail = computed(
    () => props.order.customer?.email || props.order.email || '',
);

const paymentStatusMeta = computed(() => {
    const map = {
        pending: {
            label: 'Unpaid',
            class: 'bg-secondary-fixed text-on-secondary-fixed',
        },
        paid: {
            label: 'Paid',
            class: 'bg-green-100 text-green-800',
        },
        failed: {
            label: 'Failed',
            class: 'bg-error-container text-on-error-container',
        },
        refunded: {
            label: 'Refunded',
            class: 'bg-error-container/40 text-error',
        },
    };
    return (
        map[props.order.payment_status] ?? {
            label: props.order.payment_status || '—',
            class: 'bg-surface-container text-on-surface-variant',
        }
    );
});

const paymentMethodLabel = computed(() => {
    const map = {
        cod: 'Cash on Delivery',
        stripe: 'Stripe',
        card: 'Card',
        paypal: 'PayPal',
        apple_pay: 'Apple Pay',
        google_pay: 'Google Pay',
        cash: 'Cash',
    };
    return map[props.order.payment_method] ?? (props.order.payment_method || '—');
});

const discountLabel = computed(() => {
    if (props.order.promo_code) {
        return `Promo (${props.order.promo_code})`;
    }
    return 'Discount';
});

const formatOptions = (attrs) => {
    if (!attrs || typeof attrs !== 'object') {
        return '';
    }
    if (Array.isArray(attrs)) {
        return attrs
            .map((a) =>
                typeof a === 'string'
                    ? a
                    : `${a.name ?? a.label ?? ''}: ${a.value ?? ''}`.trim(),
            )
            .filter(Boolean)
            .join(' · ');
    }
    return Object.entries(attrs)
        .map(([key, value]) => {
            if (value && typeof value === 'object') {
                return `${value.name ?? key}: ${value.value ?? value.label ?? ''}`;
            }
            return `${key}: ${value}`;
        })
        .join(' · ');
};

const itemSubtitle = (item) => {
    const options = formatOptions(item.selected_attributes);
    if (options) {
        return options;
    }
    if (item.product_sku) {
        return `SKU: ${item.product_sku}`;
    }
    return '';
};

const printInvoice = () => {
    window.print();
};

const downloadPdf = () => {
    if (downloadingPdf.value || !props.order?.id) {
        return;
    }

    downloadingPdf.value = true;

    // Server-generated PDF download (DomPDF).
    window.location.href = route('admin.orders.invoice.pdf', props.order.id);

    window.setTimeout(() => {
        downloadingPdf.value = false;
    }, 1500);
};
</script>

<template>
    <AdminLayout title="Invoice" breadcrumb="Orders">
        <Head :title="`Invoice ${order.order_number}`" />

        <div class="flex flex-col items-center pb-12">
            <!-- Controls -->
            <div
                class="no-print w-full max-w-[800px] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 py-6"
            >
                <Link
                    :href="route('admin.orders.index')"
                    class="flex items-center gap-2 text-on-surface-variant hover:text-secondary transition-colors group"
                >
                    <IconArrowLeft
                        class="h-5 w-5 group-hover:-translate-x-1 transition-transform"
                    />
                    <span class="font-semibold text-title-lg">Back to Orders</span>
                </Link>

                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="h-12 px-6 rounded-full border border-primary-container text-primary-container font-semibold flex items-center gap-2 hover:bg-primary-container/5 transition-colors"
                        @click="printInvoice"
                    >
                        <IconPrinter class="h-5 w-5" />
                        Print Invoice
                    </button>
                    <button
                        type="button"
                        class="h-12 px-8 rounded-full bg-secondary text-on-secondary font-semibold flex items-center gap-2 hover:opacity-90 transition-opacity disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="downloadingPdf"
                        @click="downloadPdf"
                    >
                        <IconDownload class="h-5 w-5" />
                        {{ downloadingPdf ? 'Preparing PDF…' : 'Download PDF' }}
                    </button>
                </div>
            </div>

            <!-- Invoice card -->
            <article
                id="invoice-document"
                class="invoice-card relative w-full max-w-[800px] overflow-hidden rounded-xl border border-outline-variant/30 bg-white p-8 sm:p-12 shadow-[0_2px_12px_rgba(0,0,0,0.07)]"
            >
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-secondary" />

                <!-- Header -->
                <section
                    class="mb-12 flex flex-col gap-8 sm:flex-row sm:justify-between sm:items-start"
                >
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-3">
                            <img
                                v-if="siteLogo"
                                :src="siteLogo"
                                :alt="siteName"
                                class="h-12 w-auto max-w-[160px] object-contain"
                            />
                            <div
                                v-else
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary-container text-on-primary-fixed"
                            >
                                <IconBread class="h-7 w-7" />
                            </div>
                            <h2 class="font-serif text-headline-md text-primary">
                                {{ siteName }}
                            </h2>
                        </div>
                        <address
                            class="not-italic text-on-surface-variant text-body-sm leading-relaxed"
                        >
                            <template v-if="siteAddress">
                                {{ siteAddress }}<br />
                            </template>
                            <template v-if="siteEmail">
                                {{ siteEmail }}<br v-if="sitePhone" />
                            </template>
                            <template v-if="sitePhone">
                                {{ sitePhone }}
                            </template>
                            <template v-if="!siteAddress && !siteEmail && !sitePhone">
                                —
                            </template>
                        </address>
                    </div>
                    <div class="text-left sm:text-right">
                        <h1
                            class="mb-2 font-serif text-headline-lg text-primary tracking-tight"
                        >
                            INVOICE
                        </h1>
                        <div class="space-y-1">
                            <p
                                class="text-label-caps text-[12px] font-bold uppercase tracking-wider text-outline"
                            >
                                Invoice Number
                            </p>
                            <p class="font-semibold text-title-lg text-on-surface">
                                #{{ order.order_number }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Bill to / meta -->
                <section
                    class="mb-12 grid grid-cols-1 gap-10 border-y border-outline-variant/20 py-8 sm:grid-cols-2"
                >
                    <div>
                        <p
                            class="mb-3 text-[12px] font-bold uppercase tracking-wider text-outline"
                        >
                            Bill To
                        </p>
                        <h3 class="mb-1 font-semibold text-title-lg text-primary">
                            {{ customerName }}
                        </h3>
                        <p
                            class="text-on-surface-variant text-body-sm leading-relaxed"
                        >
                            {{ order.address_line1 }}
                            <template v-if="order.address_line2">
                                <br />{{ order.address_line2 }}
                            </template>
                            <br />
                            {{ order.city
                            }}<template v-if="order.state"
                                >, {{ order.state }}</template
                            >
                            {{ order.postal_code }}
                            <template v-if="order.country">
                                <br />{{ order.country }}
                            </template>
                            <template v-if="customerEmail">
                                <br />{{ customerEmail }}
                            </template>
                        </p>
                    </div>
                    <div class="flex flex-col gap-4 sm:items-end">
                        <div class="sm:text-right">
                            <p
                                class="mb-1 text-[12px] font-bold uppercase tracking-wider text-outline"
                            >
                                Date Issued
                            </p>
                            <p class="text-body-lg">
                                {{ formatDateLong(order.created_at) }}
                            </p>
                        </div>
                        <div class="sm:text-right">
                            <p
                                class="mb-1 text-[12px] font-bold uppercase tracking-wider text-outline"
                            >
                                Due Date
                            </p>
                            <p class="text-body-lg">
                                {{ formatDateLong(dueDate) }}
                            </p>
                        </div>
                        <div class="sm:text-right">
                            <p
                                class="mb-1 text-[12px] font-bold uppercase tracking-wider text-outline"
                            >
                                Payment Method
                            </p>
                            <p class="text-body-lg">
                                {{ paymentMethodLabel }}
                            </p>
                        </div>
                        <div class="sm:text-right">
                            <p
                                class="mb-1 text-[12px] font-bold uppercase tracking-wider text-outline"
                            >
                                Payment Status
                            </p>
                            <span
                                class="inline-block rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wider"
                                :class="paymentStatusMeta.class"
                            >
                                {{ paymentStatusMeta.label }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Line items -->
                <section class="mb-12 overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left">
                        <thead>
                            <tr class="border-b border-outline-variant">
                                <th
                                    class="py-4 text-[12px] font-bold uppercase tracking-wider text-outline"
                                >
                                    Description
                                </th>
                                <th
                                    class="py-4 text-center text-[12px] font-bold uppercase tracking-wider text-outline"
                                >
                                    Qty
                                </th>
                                <th
                                    class="py-4 text-right text-[12px] font-bold uppercase tracking-wider text-outline"
                                >
                                    Unit Price
                                </th>
                                <th
                                    class="py-4 text-right text-[12px] font-bold uppercase tracking-wider text-outline"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            <tr
                                v-for="item in order.items ?? []"
                                :key="item.id"
                            >
                                <td class="py-6">
                                    <p class="font-semibold text-title-lg text-primary">
                                        {{ item.product_name }}
                                    </p>
                                    <p
                                        v-if="itemSubtitle(item)"
                                        class="text-body-sm text-outline"
                                    >
                                        {{ itemSubtitle(item) }}
                                    </p>
                                </td>
                                <td class="py-6 text-center text-body-lg">
                                    {{ item.quantity }}
                                </td>
                                <td class="py-6 text-right text-body-lg">
                                    {{ formatMoney(item.unit_price) }}
                                </td>
                                <td class="py-6 text-right text-body-lg">
                                    {{ formatMoney(item.line_total) }}
                                </td>
                            </tr>
                            <tr v-if="!(order.items?.length)">
                                <td
                                    colspan="4"
                                    class="py-8 text-center text-on-surface-variant text-body-sm"
                                >
                                    No line items on this order.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Totals -->
                <section class="mb-12 flex justify-end">
                    <div class="w-full max-w-xs space-y-3">
                        <div
                            class="flex items-center justify-between text-on-surface-variant"
                        >
                            <span class="text-body-lg">Subtotal</span>
                            <span class="text-body-lg">
                                {{ formatMoney(order.subtotal) }}
                            </span>
                        </div>
                        <div
                            v-if="Number(order.discount_amount) > 0"
                            class="flex items-center justify-between text-secondary"
                        >
                            <span class="text-body-lg">{{ discountLabel }}</span>
                            <span class="text-body-lg">
                                -{{ formatMoney(order.discount_amount) }}
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between text-on-surface-variant"
                        >
                            <span class="text-body-lg">Estimated Tax</span>
                            <span class="text-body-lg">
                                {{ formatMoney(order.tax_amount) }}
                            </span>
                        </div>
                        <div
                            v-if="Number(order.delivery_fee) > 0"
                            class="flex items-center justify-between text-on-surface-variant"
                        >
                            <span class="text-body-lg">Delivery Fee</span>
                            <span class="text-body-lg">
                                {{ formatMoney(order.delivery_fee) }}
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between border-t border-outline-variant pt-4"
                        >
                            <span class="font-serif text-headline-sm text-primary">
                                Total Amount
                            </span>
                            <span class="font-serif text-headline-sm text-secondary">
                                {{ formatMoney(order.total) }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Footer -->
                <footer class="border-t border-outline-variant/30 pt-12">
                    <div
                        class="grid grid-cols-1 gap-10 sm:grid-cols-2 sm:items-end"
                    >
                        <div
                            class="rounded-lg border border-outline-variant/20 bg-surface-container p-4"
                        >
                            <p
                                class="mb-2 text-[12px] font-bold uppercase tracking-wider text-outline"
                            >
                                Notes &amp; Instructions
                            </p>
                            <p
                                class="text-body-sm italic text-on-surface-variant"
                            >
                                Please settle the invoice within 14 days. For
                                wholesale inquiries or recurring orders, please
                                contact our kitchen directly.
                            </p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="mb-2 font-serif text-headline-sm text-primary">
                                Thank You!
                            </p>
                            <p class="text-on-surface-variant text-body-sm">
                                We appreciate your support for artisanal baking.
                            </p>
                            <p
                                class="mt-6 text-[12px] font-bold uppercase tracking-wider text-outline"
                            >
                                {{ siteName }}
                            </p>
                        </div>
                    </div>
                </footer>
            </article>

            <!-- Decorative blurs (screen only) -->
            <div
                class="no-print pointer-events-none fixed top-[20%] -right-20 -z-10 h-96 w-96 rounded-full bg-primary-fixed opacity-30 blur-[120px]"
            />
            <div
                class="no-print pointer-events-none fixed bottom-[10%] -left-20 -z-10 h-80 w-80 rounded-full bg-secondary-fixed opacity-30 blur-[100px]"
            />
        </div>
    </AdminLayout>
</template>
