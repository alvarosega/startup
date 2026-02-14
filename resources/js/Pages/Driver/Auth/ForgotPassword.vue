<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { Mail, ArrowLeft, Send, Truck } from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';

const form = useForm({ email: '' });
const submit = () => form.post(route('driver.password.email'));
</script>

<template>
    <Head title="Recuperar Panel Driver" />
    <div class="min-h-screen flex items-center justify-center bg-slate-900 p-4">
        <div class="w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-amber-100 flex items-center justify-center mx-auto mb-4 text-amber-600">
                    <Truck :size="32" />
                </div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Recuperar Acceso</h2>
                <p class="text-sm text-slate-500 mt-2">Ingresa tu correo de conductor para recibir el código.</p>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <BaseInput v-model="form.email" type="email" label="Correo Corporativo" :icon="Mail" :error="form.errors.email" required />
                <button type="submit" :disabled="form.processing" class="btn bg-amber-500 hover:bg-amber-600 text-white w-full py-3 font-bold flex items-center justify-center gap-2">
                    Enviar Código <Send :size="18" />
                </button>
            </form>
            <div class="mt-8 text-center border-t pt-6">
                <Link :href="route('driver.login')" class="text-sm font-bold text-slate-400 hover:text-slate-600 flex items-center justify-center gap-2">
                    <ArrowLeft :size="16" /> Volver al Panel
                </Link>
            </div>
        </div>
    </div>
</template>