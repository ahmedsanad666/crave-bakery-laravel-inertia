<script setup>
import { computed, ref, watch } from 'vue';
import { IconPhoto, IconUpload } from '@tabler/icons-vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    currentUrl: {
        type: String,
        default: null,
    },
    error: {
        type: String,
        default: null,
    },
    accept: {
        type: String,
        default: 'image/*',
    },
});

const model = defineModel({
    type: [File, null],
    default: null,
});

const fileInput = ref(null);
const localPreview = ref(null);

watch(model, (file) => {
    if (file instanceof File) {
        localPreview.value = URL.createObjectURL(file);
        return;
    }
    localPreview.value = null;
});

const preview = computed(() => localPreview.value || props.currentUrl || null);

const hasSelectedFile = computed(
    () => typeof File !== 'undefined' && model.value instanceof File,
);

const selectedFileName = computed(() =>
    hasSelectedFile.value ? model.value.name : '',
);

const pick = () => fileInput.value?.click();

const onChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    model.value = file;
};
</script>

<template>
    <div class="space-y-sm">
        <AppInputLabel :value="label" />
        <div
            class="flex items-center gap-md rounded-xl border-2 border-dashed border-outline-variant p-md"
        >
            <div
                class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-surface-container"
            >
                <img
                    v-if="preview"
                    :src="preview"
                    :alt="label"
                    class="size-full object-cover"
                />
                <IconPhoto
                    v-else
                    class="text-outline"
                    :size="24"
                    stroke-width="1.5"
                />
            </div>
            <div class="min-w-0 flex-1">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-surface-container-high px-md py-2 font-sans text-[10px] font-bold uppercase tracking-wider text-on-surface-variant transition-colors hover:bg-surface-container"
                    @click="pick"
                >
                    <IconUpload :size="14" stroke-width="1.5" />
                    {{ preview ? 'Change' : 'Upload' }}
                </button>
                <p
                    v-if="hasSelectedFile"
                    class="mt-1 truncate font-sans text-body-sm text-on-surface-variant"
                >
                    {{ selectedFileName }}
                </p>
            </div>
            <input
                ref="fileInput"
                type="file"
                class="hidden"
                :accept="accept"
                @change="onChange"
            />
        </div>
        <AppInputError :message="error" />
    </div>
</template>
