<script setup>
    import { useForm } from '@inertiajs/vue3';
    import { VueTelInput } from 'vue-tel-input';
    import 'vue-tel-input/vue-tel-input.css';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import { useAuthModal } from '@/Composables/useAuthModal'; // <--- 1. IMPORTAR ESTADO GLOBAL
    
    const emit = defineEmits(['close', 'switchToRegister']);
    
    // 2. OBTENER LA FUNCIÓN DE CIERRE
    const { closeModals } = useAuthModal();
    
    const form = useForm({
        phone: '',
        password: '',
        remember: false
    });
    
    const onInput = (phone, phoneObject) => {
        if (phoneObject?.number) form.phone = phoneObject.number;
    };
    
    const submit = () => {
        form.post(route('login'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModals(); // <--- 3. FORZAR CIERRE DIRECTAMENTE
                emit('close'); // Mantenemos el emit por seguridad
            },
            onFinish: () => form.reset('password'),
        });
    };
    </script>
    
    <template>
        <div class="p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-black text-gray-800 uppercase italic">Bienvenido</h2>
                <p class="text-xs text-gray-400 font-bold uppercase">Ingresa para continuar comprando</p>
            </div>
    
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-500 uppercase ml-1">Celular</label>
                    <vue-tel-input 
                        v-model="form.phone" 
                        @on-input="onInput"
                        mode="international"
                        :inputOptions="{ placeholder: '77712345', autofocus: true }"
                        class="custom-tel-input mt-1"
                        :class="{ 'border-red-500': form.errors.phone }"
                    />
                    <p v-if="form.errors.phone" class="text-red-500 text-[10px] font-bold mt-1">{{ form.errors.phone }}</p>
                </div>
    
                <div class="mb-6">
                    <label class="text-xs font-bold text-gray-500 uppercase ml-1">Contraseña</label>
                    <input v-model="form.password" type="password" class="w-full rounded-lg border-gray-300 mt-1 focus:ring-blue-500 focus:border-blue-500" placeholder="••••••••">
                    <p v-if="form.errors.password" class="text-red-500 text-[10px] font-bold mt-1">{{ form.errors.password }}</p>
                </div>
    
                <BaseButton class="w-full shadow-lg" :isLoading="form.processing">Ingresar</BaseButton>
            </form>
    
            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-500 mb-2">¿No tienes cuenta?</p>
                <button @click="$emit('switchToRegister')" class="text-sm font-bold text-blue-600 hover:underline uppercase">
                    Regístrate Gratis
                </button>
            </div>
        </div>
    </template>
    
    <style scoped>
    .custom-tel-input { border: 1px solid #e5e7eb; border-radius: 0.5rem; }
    </style>