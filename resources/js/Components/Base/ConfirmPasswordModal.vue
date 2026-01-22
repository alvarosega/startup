<script setup>
    import { ref } from 'vue';
    import axios from 'axios';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    
    const props = defineProps({ show: Boolean });
    const emit = defineEmits(['close', 'confirmed']);
    
    const password = ref('');
    const error = ref('');
    const isLoading = ref(false);
    
    const confirm = async () => {
        isLoading.value = true;
        error.value = '';
        try {
            await axios.post(route('user.confirm-password'), { password: password.value });
            emit('confirmed');
            password.value = '';
        } catch (err) {
            error.value = err.response?.data?.message || 'Contraseña incorrecta.';
        } finally {
            isLoading.value = false;
        }
    };
    
    const close = () => {
        password.value = '';
        error.value = '';
        emit('close');
    };
    </script>
    
    <template>
        <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all scale-100">
                    <h3 class="text-lg font-black text-gray-900 mb-2">Seguridad Requerida</h3>
                    <p class="text-sm text-gray-500 mb-6">Estás cambiando información sensible (Email). Confirma tu contraseña actual.</p>
                    <form @submit.prevent="confirm">
                        <BaseInput v-model="password" type="password" placeholder="Tu contraseña actual" autofocus class="mb-2" />
                        <p v-if="error" class="text-red-600 text-xs font-bold mb-4">{{ error }}</p>
                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                            <button type="button" @click="close" class="px-4 py-2 text-sm text-gray-500 font-bold hover:text-gray-800">Cancelar</button>
                            <BaseButton :isLoading="isLoading" class="bg-blue-600 hover:bg-blue-700 text-white">Confirmar</BaseButton>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </template>