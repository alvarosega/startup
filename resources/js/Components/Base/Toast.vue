<script setup>
    import { ref, watch } from 'vue';
    import { usePage } from '@inertiajs/vue3';
    import { CheckCircle, AlertCircle, X } from 'lucide-vue-next';
    
    const page = usePage();
    const show = ref(false);
    const message = ref('');
    const type = ref('success');
    
    let timeout = null;
    
    const autoHide = () => {
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => { show.value = false; }, 4000);
    };
    
    // Escuchar mensajes Flash del backend
    watch(() => page.props.flash, (flash) => {
        if (flash?.success || flash?.message) {
            type.value = 'success';
            message.value = flash.success || flash.message;
            show.value = true;
            autoHide();
        } else if (flash?.error) {
            type.value = 'error';
            message.value = flash.error;
            show.value = true;
            autoHide();
        }
    }, { deep: true });
    
    // Escuchar errores de validación
    watch(() => page.props.errors, (errors) => {
        if (Object.keys(errors).length > 0) {
            type.value = 'error';
            message.value = Object.values(errors)[0] || 'Hay errores en el formulario.';
            show.value = true;
            autoHide();
        }
    }, { deep: true });
    </script>
    
    <template>
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed top-20 right-4 z-[100] max-w-sm w-full bg-white shadow-xl rounded-xl pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden border-l-4"
                :class="type === 'success' ? 'border-green-500' : 'border-red-500'">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <CheckCircle v-if="type === 'success'" class="h-6 w-6 text-green-500" />
                            <AlertCircle v-else class="h-6 w-6 text-red-500" />
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-bold text-gray-900">
                                {{ type === 'success' ? 'Operación Exitosa' : 'Atención' }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500 leading-snug">{{ message }}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="show = false" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                                <X class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>