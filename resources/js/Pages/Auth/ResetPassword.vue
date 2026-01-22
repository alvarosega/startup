<script setup>
    import { Head, useForm, Link } from '@inertiajs/vue3';
    import { KeyRound, CheckCircle, Loader2 } from 'lucide-vue-next';
    
    const props = defineProps({
        email: String,
        token: String, // Laravel a veces inyecta esto por defecto, lo ignoramos para usar código
    });
    
    const form = useForm({
        email: props.email || '',
        code: '', // El código de 6 dígitos
        password: '',
        password_confirmation: '',
    });
    
    const submit = () => {
        form.post(route('password.update'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };
    </script>
    
    <template>
        <Head title="Restablecer Contraseña" />
    
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
            
            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-2xl border border-gray-100">
                
                <div class="mb-6 text-center">
                    <div class="inline-flex bg-green-50 p-3 rounded-full mb-4 text-green-600">
                        <KeyRound :size="32" />
                    </div>
                    <h2 class="text-2xl font-black text-gray-900">Crear nueva contraseña</h2>
                    <p class="text-sm text-gray-500 mt-2">
                        Revisa tu correo {{ form.email }}, ingresa el código recibido y define tu nueva clave.
                    </p>
                </div>
    
                <form @submit.prevent="submit" class="space-y-4">
                    
                    <div>
                        <label class="block font-bold text-xs uppercase text-gray-500 mb-1">Correo Electrónico</label>
                        <input 
                            v-model="form.email"
                            type="email" 
                            class="w-full bg-gray-100 text-gray-500 border-gray-300 rounded-xl shadow-sm cursor-not-allowed" 
                            readonly
                        />
                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.email }}</div>
                    </div>
    
                    <div>
                        <label class="block font-bold text-sm text-gray-900 mb-1">Código de Verificación</label>
                        <input 
                            v-model="form.code"
                            type="text" 
                            inputmode="numeric"
                            maxlength="6"
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-center text-2xl tracking-widest font-mono placeholder-gray-200" 
                            placeholder="000000"
                            required 
                            autofocus
                        />
                        <div v-if="form.errors.code" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.code }}</div>
                    </div>
    
                    <div class="border-t border-gray-100 my-4"></div>
    
                    <div>
                        <label class="block font-bold text-sm text-gray-700 mb-1">Nueva Contraseña</label>
                        <input 
                            v-model="form.password"
                            type="password" 
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm" 
                            required 
                            autocomplete="new-password"
                            placeholder="Mínimo 8 caracteres"
                        />
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.password }}</div>
                    </div>
    
                    <div>
                        <label class="block font-bold text-sm text-gray-700 mb-1">Confirmar Contraseña</label>
                        <input 
                            v-model="form.password_confirmation"
                            type="password" 
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm" 
                            required 
                            autocomplete="new-password"
                            placeholder="Repite la contraseña"
                        />
                        <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.password_confirmation }}</div>
                    </div>
    
                    <div class="flex items-center justify-end mt-6">
                        <button 
                            class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3 px-4 rounded-xl shadow-lg transition flex justify-center items-center gap-2" 
                            :class="{ 'opacity-75 cursor-wait': form.processing }" 
                            :disabled="form.processing"
                        >
                            <Loader2 v-if="form.processing" class="animate-spin" :size="20" />
                            <span v-else>Restablecer Contraseña</span>
                            <CheckCircle v-if="!form.processing" :size="18" />
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>