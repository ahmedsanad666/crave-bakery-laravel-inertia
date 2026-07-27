<script setup>
import { IconBell, IconTruck, IconX } from '@tabler/icons-vue';

defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const notifications = [
    {
        id: 1,
        title: 'Low stock warning!',
        body: 'French Baguette is below critical level (3 left).',
        time: 'Just now',
        highlight: true,
        icon: IconBell,
        iconClass: 'text-secondary-container',
    },
    {
        id: 2,
        title: 'Delivery Partner Assigned',
        body: 'Order #9403 is now being picked up by Courier Joe.',
        time: '12 min ago',
        highlight: false,
        icon: IconTruck,
        iconClass: 'text-info',
    },
    {
        id: 3,
        title: 'Payment Refunded',
        body: 'Customer Sarah W. was refunded for Order #9388.',
        time: '45 min ago',
        highlight: false,
        icon: IconBell,
        iconClass: 'text-warning',
    },
];
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
            class="fixed right-0 top-0 z-[60] flex h-full w-[360px] max-w-full flex-col bg-white shadow-modal transition-transform duration-300"
            :class="open ? 'translate-x-0' : 'translate-x-full'"
        >
            <div class="flex items-center justify-between border-b border-outline-variant p-lg">
                <h5 class="font-sans text-title-lg text-primary">Notifications</h5>
                <button
                    type="button"
                    class="rounded-full p-1 text-outline transition-colors hover:bg-surface-container-low hover:text-primary"
                    @click="emit('close')"
                >
                    <IconX class="size-5" stroke="1.5" />
                </button>
            </div>

            <div class="flex-1 space-y-4 overflow-y-auto p-md admin-scrollbar">
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="rounded-lg p-4 transition-colors"
                    :class="
                        notification.highlight
                            ? 'border-l-4 border-secondary-container bg-secondary-container/5'
                            : 'border-l-4 border-transparent hover:bg-surface-container-low'
                    "
                >
                    <div class="flex gap-3">
                        <component
                            :is="notification.icon"
                            class="size-5 shrink-0"
                            :class="notification.iconClass"
                            stroke="1.5"
                        />
                        <div>
                            <p class="text-sm font-bold text-on-surface">
                                {{ notification.title }}
                            </p>
                            <p class="text-xs text-on-surface-variant">
                                {{ notification.body }}
                            </p>
                            <p class="mt-2 text-[10px] font-bold uppercase text-outline">
                                {{ notification.time }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-outline-variant bg-white p-lg">
                <button
                    type="button"
                    class="w-full rounded-lg bg-primary-container py-3 font-sans text-sm font-bold text-white transition-colors hover:bg-primary"
                >
                    Mark all as read
                </button>
            </div>
        </aside>
    </Teleport>
</template>
