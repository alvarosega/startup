<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { KeyRound, CheckCircle, ShieldCheck, Hash, XCircle } from 'lucide-vue-next';
//import BaseInput from '@/Components/Base/BaseInput.vue';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const props = defineProps({ email: String });

const form = useForm({
    email: props.email || '',
    code: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Nueva Contraseña" />
    
    <ShopLayout>
        <div class="flex-1 flex items-center justify-center p-4 min-h-[calc(100svh-144px)]">
            <div class="w-full max-w-md py-6 animate-in fade-in zoom-in-95 duration-500">
                
                <div class="bg-surface/20 backdrop-blur-2xl border border-white/10 dark:border-white/5 rounded-[40px] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col">
                    <div class="p-8 md:p-10">
                        
                        <div class="text-center mb-10">
                            <div class="w-20 h-20 rounded-3xl bg-transparent border-4 border-primary/20 flex items-center justify-center mx-auto mb-6 shadow-[inset_0_0_20px_rgba(var(--primary),0.2)]">
                                <ShieldCheck :size="36" class="text-primary animate-pulse" stroke-width="2.5" />
                            </div>
                            <h2 class="text-3xl font-sans font-black text-foreground tracking-tighter leading-none">Nueva Contraseña</h2>
                            <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-foreground/5 backdrop-blur-md border border-foreground/10 text-[10px] uppercase font-black tracking-widest text-foreground shadow-inner">
                                <span class="opacity-60">Para:</span>
                                <span>{{ props.email }}</span>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div class="space-y-3">
                                <label class="flex justify-center items-center gap-2 w-full text-[12px] font-black uppercase tracking-tight text-primary bg-transparent">
                                    <Hash :size="14" stroke-width="3" /> Validación de 6 Dígitos
                                </label>
                                <input 
                                    v-model="form.code" 
                                    type="text" 
                                    maxlength="6" 
                                    class="w-full text-center text-4xl font-mono font-black tracking-[0.4em] h-[80px] bg-transparent backdrop-blur-xl border border-foreground/20 rounded-[20px] focus:outline-none focus:border-primary focus:ring-0 shadow-inner text-foreground placeholder:text-foreground/10 transition-all"
                                    placeholder="000000"
                                    :class="{'!border-f1-red': form.errors.code}"
                                    autofocus
                                />
                                <p v-if="form.errors.code" class="text-center text-[10px] text-f1-red font-black uppercase tracking-widest mt-2">{{ form.errors.code }}</p>
                            </div>

                            <div class="space-y-6 pt-6 border-t border-white/10 dark:border-white/5">
                                <div class="space-y-2">
                                    <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                        <KeyRound :size="14" stroke-width="3" /> Nueva Contraseña
                                    </label>
                                    <BaseInput 
                                        v-model="form.password" 
                                        type="password" 
                                        placeholder="••••••••" 
                                        :error="form.errors.password" 
                                        class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-mono font-bold shadow-inner"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                        <KeyRound :size="14" stroke-width="3" /> Confirmar Clave
                                    </label>
                                    <BaseInput 
                                        v-model="form.password_confirmation" 
                                        type="password" 
                                        placeholder="••••••••" 
                                        class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-mono font-bold shadow-inner"
                                    />
                                </div>
                            </div>

                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="w-full h-14 bg-primary text-white rounded-[20px] font-black text-sm uppercase tracking-widest shadow-xl shadow-primary/20 active:scale-95 transition-all flex items-center justify-center gap-3 mt-6"
                            >
                                <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <template v-else>
                                    Actualizar <CheckCircle :size="18" stroke-width="3" />
                                </template>
                            </button>
                        </form>
                    </div>

                    <div class="p-8 bg-transparent shrink-0 border-t border-white/5 dark:border-white/5 text-center">
                        <Link :href="route('login')" class="inline-flex items-center justify-center gap-2 text-[11px] font-black text-foreground/50 hover:text-f1-red transition-all uppercase tracking-widest group">
                            <XCircle :size="16" stroke-width="3" class="group-hover:rotate-90 transition-transform" /> Cancelar Operación
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>