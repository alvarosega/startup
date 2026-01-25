<!-- resources/js/Components/Auth/AuthModal.vue -->
<script setup>
import { X } from 'lucide-vue-next';

const props = defineProps({
    title: String,
    show: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close']);
</script>

<template>
    <Transition name="fade">
        <div v-if="show" class="modal-backdrop" @click.self="emit('close')">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="relative bg-background rounded-xl shadow-xl w-full max-w-md animate-scale-in">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-6 border-b border-input">
                        <h2 class="text-xl font-display font-semibold text-foreground">
                            {{ title }}
                        </h2>
                        <button @click="emit('close')" 
                                class="p-1.5 rounded-md text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                            <X :size="20" />
                        </button>
                    </div>

                    <!-- Content -->
                    <div>
                        <slot></slot>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s var(--ease-smooth);
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>