<script setup>
import { computed } from 'vue';
const props = defineProps({
    type: { type: String, default: 'submit' },
    variant: { type: String, default: 'primary' },
    isLoading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const variantClasses = computed(() => {
    const classes = {
        primary: 'bg-blue-600 text-white hover:bg-blue-700',
        secondary: 'bg-gray-200 text-gray-800 hover:bg-gray-300',
        danger: 'bg-red-600 text-white hover:bg-red-700'
    };
    return classes[props.variant] || classes.primary;
});
</script>

<template>
    <button
        :type="type"
        :disabled="isLoading || disabled"
        class="w-full font-bold py-2 px-4 rounded-lg transition-all flex justify-center items-center"
        :class="[variantClasses, (isLoading || disabled) ? 'opacity-70 cursor-not-allowed' : '']"
    >
        <span v-if="isLoading" class="mr-2">⌛</span>
        <slot />
    </button>
</template>
