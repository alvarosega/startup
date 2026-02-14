<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('admin.login.store'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Admin Login" />
    <div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-gray-800 rounded-lg shadow-xl p-8 border border-gray-700">
            <h2 class="text-2xl font-bold text-white text-center mb-6">Acceso Admin</h2>
            
            <form @submit.prevent="submit" class="space-y-6">
                
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Email</label>
                    <input 
                        v-model="form.email" 
                        type="email" 
                        required
                        class="w-full bg-gray-700 text-white rounded border border-gray-600 p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                    />
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div>
                    <label class="block text-gray-400 text-sm mb-1">Contraseña</label>
                    <input 
                        v-model="form.password" 
                        type="password" 
                        required
                        class="w-full bg-gray-700 text-white rounded border border-gray-600 p-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                    />
                </div>

                <div class="flex items-center">
                    <input v-model="form.remember" type="checkbox" class="rounded bg-gray-700 border-gray-600 text-blue-600" />
                    <span class="ml-2 text-gray-400 text-sm">Recordarme</span>
                </div>

                <button 
                    :disabled="form.processing"
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded transition disabled:opacity-50"
                >
                    {{ form.processing ? 'Verificando...' : 'Ingresar' }}
                </button>
            </form>
        </div>
    </div>
</template>