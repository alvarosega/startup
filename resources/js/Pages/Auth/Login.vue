<script setup>
    import { ref } from 'vue';
    import { Head, useForm } from '@inertiajs/vue3';
    import { VueTelInput } from 'vue-tel-input';
    import 'vue-tel-input/vue-tel-input.css';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    
    // 1. Configuración del formulario de Inertia (Datos a enviar)
    const form = useForm({
        phone: '',     // Se enviará formateado: +59170000000
        password: '',
        remember: false,
    });
    
    // 2. Variable visual (Lo que el usuario escribe)
    const displayPhone = ref('');
    
    // 3. Configuración estricta para Bolivia
    const telOptions = {
        mode: 'international',
        defaultCountry: 'BO',
        autoFormat: true,
        dropdownOptions: {
            showSearchBox: true,
            showFlags: true,
            showDialCodeInSelection: true,
        },
        inputOptions: {
            placeholder: 'Ingresa tu celular',
            required: true,
            autofocus: true
        }
    };
    
    // 4. Captura inteligente del número
    // Este evento se dispara cada vez que el usuario teclea
    const onInput = (phone, phoneObject) => {
        // phoneObject.number contiene el formato internacional (E.164)
        if (phoneObject && phoneObject.number) {
            form.phone = phoneObject.number;
        } else {
            // Fallback: si no es válido aún, guardamos lo que hay
            form.phone = phone; 
        }
    };
    
    const submit = () => {
        // Limpiamos errores previos
        form.clearErrors();
        
        console.log("Enviando credenciales:", form.phone); 
        
        form.post(route('login'), {
            onFinish: () => form.reset('password'),
        });
    };
    </script>
    
    <template>
        <Head title="Iniciar Sesión" />
    
        <div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 p-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tighter italic">Bienvenido</h1>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Ingresa a tu cuenta corporativa</p>
                </div>
    
                <form @submit.prevent="submit">
                    <div class="mb-6">
                        <label class="block text-xs font-black text-gray-500 uppercase mb-2 ml-1">Número de Celular</label>
                        <div class="relative">
                            <vue-tel-input 
                                v-bind="telOptions"
                                v-model="displayPhone"
                                @on-input="onInput"
                                class="custom-tel-input py-1"
                                :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.phone }"
                            />
                        </div>
                        <p v-if="form.errors.phone" class="text-red-500 text-[10px] font-bold uppercase mt-2 ml-1">
                            {{ form.errors.phone }}
                        </p>
                    </div>
    
                    <div class="mb-8">
                        <label class="block text-xs font-black text-gray-500 uppercase mb-2 ml-1">Contraseña</label>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            placeholder="••••••••"
                            class="w-full rounded-xl border-gray-300 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 p-3 text-sm transition-all"
                            required
                        />
                    </div>
    
                    <BaseButton 
                        class="w-full shadow-lg hover:shadow-xl transition-shadow" 
                        :isLoading="form.processing"
                    >
                        Ingresar al Sistema
                    </BaseButton>
                </form>
    
                <div class="mt-6 text-center">
                    <a href="#" class="text-[10px] text-gray-400 hover:text-blue-600 font-bold uppercase underline">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </div>
        </div>
    </template>
    
    <style>
    /* Personalización profunda del input de teléfono para que coincida con tu diseño */
    .vue-tel-input {
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        box-shadow: none;
    }
    .vue-tel-input:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 1px #3b82f6;
    }
    .vti__dropdown {
        border-radius: 0.75rem 0 0 0.75rem;
        background-color: #f9fafb;
    }
    .vti__input {
        border-radius: 0 0.75rem 0.75rem 0;
        background-color: #f9fafb;
    }
    </style>