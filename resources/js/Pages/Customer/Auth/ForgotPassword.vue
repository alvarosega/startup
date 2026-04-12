<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { Mail, ArrowLeft, Send, ShieldQuestion } from 'lucide-vue-next';
//import BaseInput from '@/Components/Base/BaseInput.vue';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Recuperar Acceso" />
    <ShopLayout> 
        <div class="flex-1 flex items-center justify-center p-4 min-h-[calc(100svh-144px)]">
            <div class="w-full max-w-md py-6 animate-in fade-in zoom-in-95 duration-500">
                
                <div class="bg-surface/20 backdrop-blur-2xl border border-white/10 dark:border-white/5 rounded-[40px] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col">
                    <div class="p-8 md:p-10">
                        
                        <div class="text-center mb-10">
                            <div class="w-20 h-20 rounded-3xl bg-transparent border-4 border-foreground/10 flex items-center justify-center mx-auto mb-6 shadow-inner">
                                <ShieldQuestion :size="36" class="text-primary" stroke-width="2.5" />
                            </div>
                            <h2 class="text-3xl font-sans font-black text-foreground tracking-tighter leading-none">
                                Recuperar Acceso
                            </h2>
                            <p class="text-[11px] uppercase font-black tracking-[0.1em] text-foreground/60 mt-4 leading-relaxed">
                                Ingresa tu correo y te enviaremos un código de seguridad.
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-8">
                            <div class="space-y-2">
                                <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                    <Mail :size="14" stroke-width="3" /> Correo de Recuperación
                                </label>
                                <BaseInput 
                                    v-model="form.email" 
                                    type="email" 
                                    placeholder="ejemplo@correo.com" 
                                    :error="form.errors.email"
                                    autofocus
                                    required
                                    class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-bold shadow-inner"
                                />
                            </div>

                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="w-full h-14 bg-primary text-white rounded-[20px] font-black text-sm uppercase tracking-widest shadow-xl shadow-primary/20 active:scale-95 transition-all flex items-center justify-center gap-3"
                            >
                                <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <template v-else>
                                    Enviar Código <Send :size="18" stroke-width="3" class="group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                                </template>
                            </button>

                            <Transition name="fade">
                                <div v-if="$page.props.flash?.status" class="p-4 rounded-2xl bg-success/20 backdrop-blur-md border border-success/30 text-success text-[11px] font-black text-center uppercase tracking-widest">
                                    {{ $page.props.flash.status }}
                                </div>
                            </Transition>
                        </form>
                    </div>

                    <div class="p-8 bg-transparent shrink-0 border-t border-white/5 dark:border-white/5">
                        <Link :href="route('login')" class="h-14 px-6 bg-foreground/5 backdrop-blur-lg border border-foreground/10 rounded-[20px] font-black uppercase text-[11px] text-foreground hover:bg-foreground/10 transition-colors flex items-center justify-center gap-2">
                            <ArrowLeft :size="16" stroke-width="3" /> Volver al Login
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>