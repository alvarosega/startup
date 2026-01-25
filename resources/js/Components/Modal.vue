<!-- resources/js/Components/Modal.vue -->
<script setup>
    import { X } from 'lucide-vue-next';
    import { onClickOutside } from '@vueuse/core';
    import { ref } from 'vue';
    
    const props = defineProps({
        show: {
            type: Boolean,
            default: false
        },
        title: {
            type: String,
            default: 'Confirmar'
        },
        maxWidth: {
            type: String,
            default: 'md'
        }
    });
    
    const emit = defineEmits(['close', 'confirm']);
    const modalRef = ref(null);
    
    const maxWidthClass = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl'
    }[props.maxWidth];
    
    const close = () => {
        emit('close');
    };
    
    const confirm = () => {
        emit('confirm');
    };
    
    onClickOutside(modalRef, close);
    </script>
    
    <template>
        <Transition name="fade">
            <div v-if="show" class="modal-backdrop">
                <div class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
                    <div class="flex items-center justify-center min-h-full">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                            <div class="absolute inset-0 bg-foreground/60" @click="close"></div>
                        </div>
    
                        <div class="relative bg-background rounded-xl shadow-xl transition-all transform animate-scale-in w-full"
                             :class="maxWidthClass"
                             ref="modalRef">
                            
                            <!-- Header -->
                            <div class="flex items-center justify-between p-6 border-b border-border">
                                <h3 class="text-lg font-semibold text-foreground">
                                    {{ title }}
                                </h3>
                                <button @click="close" 
                                        class="p-1 rounded-md text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                                    <X :size="20" />
                                </button>
                            </div>
    
                            <!-- Content -->
                            <div class="p-6">
                                <slot></slot>
                            </div>
    
                            <!-- Footer -->
                            <div class="px-6 py-4 bg-muted/30 border-t border-border flex justify-end gap-3">
                                <slot name="actions" :close="close" :confirm="confirm">
                                    <button @click="close" 
                                            class="btn btn-outline btn-sm">
                                        Cancelar
                                    </button>
                                    <button @click="confirm" 
                                            class="btn btn-primary btn-sm">
                                        Confirmar
                                    </button>
                                </slot>
                            </div>
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