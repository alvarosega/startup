<script setup>
    import { Head, useForm, Link, router } from '@inertiajs/vue3';
    import { Mail, ArrowLeft, Loader2 } from 'lucide-vue-next';
    
    defineProps({
        status: String,
    });
    
    const form = useForm({
        email: '',
    });
    
    const submit = () => {
        form.post(route('password.email'), {
            onSuccess: () => {
                // ALERTA DE UX:
                // Si el correo se envía bien, redirigimos al paso 2 automáticamente
                // pasando el email para que el usuario no tenga que escribirlo de nuevo.
                router.visit(route('password.reset', { email: form.email }));
            }
        });
    };
    </script>
    
    <template>
        <Head title="Recuperar Contraseña" />
    
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
            
            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-2xl border border-gray-100">
                
                <div class="mb-6 text-center">
                    <div class="inline-flex bg-blue-50 p-3 rounded-full mb-4 text-blue-600">
                        <Mail :size="32" />
                    </div>
                    <h2 class="text-2xl font-black text-gray-900">¿Olvidaste tu contraseña?</h2>
                    <p class="text-sm text-gray-500 mt-2">
                        Ingresa tu correo electrónico y te enviaremos un código de seguridad de 6 dígitos.
                    </p>
                </div>
    
                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 text-center bg-green-50 p-3 rounded-lg">
                    {{ status }}
                </div>
    
                <form @submit.prevent="submit">
                    <div>
                        <label class="block font-bold text-sm text-gray-700 mb-1">Correo Electrónico</label>
                        <input 
                            v-model="form.email"
                            type="email" 
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm" 
                            placeholder="ejemplo@correo.com"
                            required 
                            autofocus
                        />
                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.email }}</div>
                    </div>
    
                    <div class="flex items-center justify-end mt-6">
                        <button 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-blue-500/30 transition flex justify-center items-center gap-2" 
                            :class="{ 'opacity-75 cursor-wait': form.processing }" 
                            :disabled="form.processing"
                        >
                            <Loader2 v-if="form.processing" class="animate-spin" :size="20" />
                            Enviar Código
                        </button>
                    </div>
    
                    <div class="mt-6 text-center">
                        <Link :href="route('login')" class="text-sm text-gray-500 hover:text-gray-900 font-bold flex items-center justify-center gap-2">
                            <ArrowLeft :size="16" /> Volver al Login
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </template>