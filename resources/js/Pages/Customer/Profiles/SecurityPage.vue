<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProfileNav from './Partials/ProfileNav.vue';
import { ShieldAlert, Key, LogOut } from 'lucide-vue-next';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('customer.password.update'), {
        onSuccess: () => form.reset(),
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Seguridad de Cuenta" />
    <ShopLayout :is-profile-section="true">
        <div class="max-w-4xl mx-auto pb-24 px-4 py-8">
            
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-zinc-900 tracking-tight uppercase italic">Seguridad</h1>
                <p class="text-zinc-500 text-sm mt-1">Protege tu acceso y credenciales.</p>
            </div>

            <ProfileNav />

            <div class="bg-white border border-zinc-200 rounded-[2rem] p-8 shadow-sm max-w-2xl">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-zinc-100 rounded-xl flex items-center justify-center text-zinc-500">
                        <Key :size="20" />
                    </div>
                    <h2 class="text-lg font-bold text-zinc-900 tracking-tight">Cambiar Contraseña</h2>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold uppercase text-zinc-500 ml-1">Contraseña Actual</label>
                        <input v-model="form.current_password" type="password" 
                               class="w-full bg-zinc-50 border-zinc-200 rounded-2xl p-4 text-sm font-mono focus:ring-zinc-900 focus:border-zinc-900 transition-all">
                        <p v-if="form.errors.current_password" class="text-[10px] text-red-500 font-bold ml-1 uppercase">{{ form.errors.current_password }}</p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold uppercase text-zinc-500 ml-1">Nueva Contraseña</label>
                            <input v-model="form.password" type="password" 
                                   class="w-full bg-zinc-50 border-zinc-200 rounded-2xl p-4 text-sm font-mono focus:ring-zinc-900 focus:border-zinc-900 transition-all">
                            <p v-if="form.errors.password" class="text-[10px] text-red-500 font-bold ml-1 uppercase">{{ form.errors.password }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold uppercase text-zinc-500 ml-1">Confirmar Nueva</label>
                            <input v-model="form.password_confirmation" type="password" 
                                   class="w-full bg-zinc-50 border-zinc-200 rounded-2xl p-4 text-sm font-mono focus:ring-zinc-900 focus:border-zinc-900 transition-all">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" :disabled="form.processing" 
                                class="bg-zinc-900 text-white px-8 h-12 rounded-xl font-bold text-sm tracking-widest uppercase hover:bg-zinc-800 transition-all active:scale-95 disabled:opacity-30">
                            <span v-if="form.processing" class="animate-spin text-xl">...</span>
                            <span v-else>Actualizar Credenciales</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-8 p-6 bg-red-50 border border-red-100 rounded-[2rem] max-w-2xl flex items-start gap-4">
                <ShieldAlert :size="24" class="text-red-500 shrink-0" />
                <div>
                    <h4 class="text-sm font-bold text-red-900">Zona Restringida</h4>
                    <p class="text-xs text-red-700 mt-1">Si detectas actividad inusual, cambia tu contraseña inmediatamente. El sistema cerrará todas tus sesiones activas en otros dispositivos por seguridad.</p>
                </div>
            </div>

        </div>
    </ShopLayout>
</template>