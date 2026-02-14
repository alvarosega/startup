<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { KeyRound, CheckCircle, ShieldCheck, Hash, ArrowLeft } from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';

const props = defineProps({ 
    email: String 
});

const form = useForm({
    email: props.email || '',
    code: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('driver.password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Nueva Contraseña Driver - BoliviaLogistics" />

    <div class="min-h-screen flex items-center justify-center bg-slate-950 p-4">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
            
            <div class="bg-amber-500 px-6 py-2 flex items-center justify-center gap-2">
                <ShieldCheck :size="16" class="text-white" />
                <span class="text-[10px] font-black text-white uppercase tracking-widest">Seguridad de Cuenta Driver</span>
            </div>

            <div class="p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-display font-black text-slate-900 tracking-tight uppercase italic">
                        Nueva <span class="text-amber-500">Contraseña</span>
                    </h2>
                    <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-[10px] text-slate-500">
                        <span class="font-bold uppercase">Email:</span>
                        <span class="font-mono text-slate-900">{{ props.email }}</span>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest flex justify-center items-center gap-2">
                            <Hash :size="12"/> Código de Verificación
                        </label>
                        <input 
                            v-model="form.code" 
                            type="text" 
                            maxlength="6" 
                            class="w-full text-center text-3xl font-mono font-bold tracking-[0.4em] h-16 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all text-slate-900"
                            placeholder="000000"
                            :class="{'border-red-500 ring-red-500/10': form.errors.code}"
                            autofocus
                        />
                        <p v-if="form.errors.code" class="text-center text-xs text-red-600 font-bold animate-pulse">
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <div class="h-px bg-slate-100 w-full my-6"></div>

                    <div class="space-y-4">
                        <BaseInput 
                            v-model="form.password" 
                            type="password" 
                            label="Nueva Contraseña" 
                            placeholder="Mínimo 8 caracteres"
                            :icon="KeyRound"
                            :error="form.errors.password"
                        />
                        <BaseInput 
                            v-model="form.password_confirmation" 
                            type="password" 
                            label="Confirmar Contraseña"
                            placeholder="Repite la clave"
                            :icon="KeyRound"
                        />
                    </div>

                    <button type="submit" 
                            :disabled="form.processing"
                            class="btn bg-slate-900 hover:bg-slate-800 text-white w-full py-4 shadow-xl shadow-slate-900/20 active:scale-[0.98] transition-all">
                        <span v-if="form.processing" class="spinner border-white spinner-sm"></span>
                        <span v-else class="flex items-center justify-center gap-2 font-black uppercase tracking-widest text-sm">
                            Actualizar y Entrar <CheckCircle :size="18" />
                        </span>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <Link :href="route('driver.login')" class="text-xs font-bold text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center gap-2 group">
                        <ArrowLeft :size="14" class="group-hover:-translate-x-1 transition-transform" /> Cancelar recuperación
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>