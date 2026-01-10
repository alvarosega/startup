<script setup>
    import { ref } from 'vue';
    import { Head, useForm } from '@inertiajs/vue3';
    import { VueTelInput } from 'vue-tel-input';
    import 'vue-tel-input/vue-tel-input.css';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import RoleSelector from '@/Components/Base/RoleSelector.vue';
    
    const form = useForm({
        phone: '',      
        password: '',
        password_confirmation: '',
        role: '', 
        terms: false,
        // Campos de Avatar
        avatar_type: 'icon',
        avatar_source: 'avatar_1.svg',
        avatar_file: null,
    });
    
    // Variable visual para el input de teléfono (separada del form)
    const displayPhone = ref('');
    
    // Variable para previsualizar imagen propia (blob local)
    const customPreview = ref(null);
    
    const telOptions = {
        mode: 'international',
        defaultCountry: 'BO',
        dropdownOptions: { showSearchBox: true, showFlags: true, showDialCodeInSelection: true },
        inputOptions: { placeholder: 'Ingrese su celular', required: true }
    };
    
    // Lógica de Teléfono (Idéntica al Login)
    const onInput = (phone, phoneObject) => {
        if (phoneObject?.number) {
            form.phone = phoneObject.number;
        } else {
            form.phone = phone; // Fallback
        }
    };
    
    // Lógica de Selección de Icono Predefinido
    const selectIcon = (iconName) => {
        form.avatar_type = 'icon';
        form.avatar_source = iconName;
        form.avatar_file = null;
        customPreview.value = null; // Limpiar preview custom
    };
    
    // Lógica de Subida de Imagen Propia
    const uploadCustom = (event) => {
        const file = event.target.files[0];
        if (file) {
            form.avatar_type = 'custom';
            form.avatar_file = file;
            // Crear URL temporal para mostrar la imagen inmediatamente
            customPreview.value = URL.createObjectURL(file);
        }
    };
    
    const submit = () => {
        form.post(route('register'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };
    </script>
    
    <template>
        <Head title="Registro" />
        <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 px-4 py-12">
            <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-2xl border border-gray-200">
                <h1 class="text-2xl font-black text-blue-900 text-center mb-6 uppercase tracking-tighter">Nueva Cuenta</h1>
                
                <form @submit.prevent="submit">
                    <RoleSelector v-model="form.role" :error="form.errors.role" />
    
                    <div class="mb-6">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-3 text-center">Elige tu Avatar</p>
                        
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div v-for="i in 6" :key="i" 
                                @click="selectIcon(`avatar_${i}.svg`)"
                                :class="[
                                    'cursor-pointer border-2 p-2 rounded-xl transition-all flex justify-center items-center',
                                    (form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg`) 
                                        ? 'border-blue-600 bg-blue-50 scale-105 shadow-md' 
                                        : 'border-gray-100 hover:border-gray-300'
                                ]">
                                <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-10 h-10" />
                            </div>
                        </div>
    
                        <div class="text-center">
                            <input type="file" @change="uploadCustom" class="hidden" id="avatar_upload" accept="image/*" />
                            <label for="avatar_upload" class="text-[10px] font-black uppercase text-blue-600 cursor-pointer hover:underline flex items-center justify-center gap-2">
                                <span v-if="!customPreview">O sube tu propia foto</span>
                                <span v-else class="text-green-600">¡Foto cargada correctamente!</span>
                            </label>
                            
                            <div v-if="customPreview" class="mt-2 flex justify-center">
                                 <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-green-500 shadow-sm">
                                    <img :src="customPreview" class="w-full h-full object-cover" />
                                 </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Teléfono Móvil</label>
                        <vue-tel-input 
                            v-bind="telOptions"
                            v-model="displayPhone"
                            @on-input="onInput"
                            class="custom-tel-input"
                            :class="{ 'is-invalid': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="text-red-600 text-[10px] mt-1 font-bold uppercase">{{ form.errors.phone }}</p>
                    </div>
    
                    <div class="grid grid-cols-1 gap-4 mb-4">
                        <BaseInput v-model="form.password" label="Contraseña" type="password" :error="form.errors.password" />
                        <BaseInput v-model="form.password_confirmation" label="Confirmar Contraseña" type="password" />
                    </div>
    
                    <div class="mt-6 mb-6">
                        <label class="flex items-start text-xs text-gray-500 cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                v-model="form.terms" 
                                class="rounded border-gray-300 mr-2 mt-0.5 text-blue-600 focus:ring-blue-500"
                            >
                            <span>
                                Acepto los 
                                <a :href="route('terms.show')" target="_blank" class="text-blue-600 font-bold hover:underline">
                                    Términos de Servicio
                                </a> 
                                y la 
                                <a :href="route('privacy.show')" target="_blank" class="text-blue-600 font-bold hover:underline">
                                    Política de Privacidad
                                </a>.
                            </span>
                        </label>
                        <p v-if="form.errors.terms" class="text-red-600 text-[10px] mt-1 font-bold">{{ form.errors.terms }}</p>
                    </div>
    
                    <BaseButton 
                        class="w-full" 
                        :isLoading="form.processing"
                        :disabled="!form.terms" 
                        :class="{ 'opacity-50 cursor-not-allowed': !form.terms }"
                    >
                        Crear Cuenta
                    </BaseButton>
                </form>
            </div>
        </div>
    </template>
    
    <style scoped>
    .custom-tel-input {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 2px 4px;
        background: #ffffff;
    }
    .custom-tel-input:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }
    .is-invalid {
        border-color: #dc2626;
    }
    </style>