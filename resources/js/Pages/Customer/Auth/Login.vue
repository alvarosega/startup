<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'; 
import BaseInput from '@/Components/Base/BaseInput.vue'; 
import { Lock, Smartphone, ArrowRight, Truck, UserCircle } from 'lucide-vue-next';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const isPhoneValid = ref(false);

const form = useForm({
    phone: '',
    password: '',
    remember: false,
    device_name: 'Web Browser',
    guest_client_uuid: localStorage.getItem('guest_id') || localStorage.getItem('guest_client_uuid')
});

const onInput = (phone, phoneObject) => {
    isPhoneValid.value = phoneObject?.valid || false;
    if (phoneObject?.number) {
        form.phone = phoneObject.number;
    }
};

const canSubmit = computed(() => {
    return isPhoneValid.value && form.password.length > 0 && !form.processing;
});

const submit = () => {
    if (!canSubmit.value) return;
    form.clearErrors();
    form.post(route('login'), {
        preserveScroll: true,
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Inicio de sesión" />

    <ShopLayout>
        <div class="flex-1 flex items-center justify-center p-4 min-h-[calc(100svh-144px)]">
            <div class="w-full max-w-md py-6 animate-in fade-in zoom-in-95 duration-500">
                
                <div class="bg-surface/20 backdrop-blur-2xl border border-white/10 dark:border-white/5 rounded-[40px] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col">
                    
                    <div class="p-8 md:p-10">
                        <div class="text-center mb-10">
                            <div class="w-20 h-20 rounded-3xl bg-transparent border-4 border-foreground/10 flex items-center justify-center mx-auto mb-6 shadow-inner">
                                <UserCircle :size="36" class="text-primary" stroke-width="2.5" />
                            </div>
                            <h2 class="text-3xl font-sans font-black text-foreground tracking-tighter leading-none">
                                ¡Hola!
                            </h2>
                            <p class="text-[11px] uppercase font-black tracking-[0.1em] text-foreground/60 mt-3">
                                Inicia sesión para continuar
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div class="space-y-2">
                                <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                    <Smartphone :size="14" stroke-width="3" /> Número de celular
                                </label>
                                <vue-tel-input 
                                    v-model="form.phone" 
                                    @on-input="onInput"
                                    mode="international"
                                    :preferredCountries="['BO']" 
                                    :defaultCountry="'BO'"
                                    :autoFocus="true"
                                    class="custom-tel-input !bg-transparent !backdrop-blur-xl !border-foreground/10 !rounded-2xl h-[56px] transition-all focus-within:!border-primary/50 shadow-inner"
                                    :class="{ '!border-f1-red': form.errors.phone }"
                                />
                                <p v-if="form.errors.phone" class="text-[10px] text-f1-red font-bold mt-1 ml-1 uppercase">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between items-center mb-1 ml-1">
                                    <label class="text-[12px] font-black uppercase tracking-tight text-foreground flex items-center gap-2 bg-transparent">
                                        <Lock :size="14" stroke-width="3" /> Contraseña
                                    </label>
                                    <Link :href="route('password.request')" class="text-[10px] font-black text-primary hover:underline uppercase tracking-widest">
                                        ¿La olvidaste?
                                    </Link>
                                </div>
                                <BaseInput 
                                    v-model="form.password" 
                                    type="password" 
                                    placeholder="••••••••" 
                                    class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-mono font-bold shadow-inner"
                                    :error="form.errors.password" 
                                />
                            </div>

                            <div class="flex items-center ml-1 py-1">
                                <BaseCheckbox v-model="form.remember" label="Recordarme" class="font-black text-xs text-foreground" />
                            </div>

                            <button 
                                type="submit" 
                                :disabled="!canSubmit" 
                                class="w-full h-14 rounded-[20px] font-black text-sm uppercase tracking-widest transition-all duration-300 flex items-center justify-center gap-3 mt-4"
                                :class="canSubmit 
                                    ? 'bg-primary text-white shadow-xl shadow-primary/20 active:scale-95' 
                                    : 'bg-foreground/5 text-foreground/40 border border-foreground/10 cursor-not-allowed'"
                            >
                                <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <template v-else>
                                    Entrar <ArrowRight :size="18" stroke-width="3" />
                                </template>
                            </button>
                        </form>

                        <div class="mt-10 text-center">
                            <p class="text-[11px] text-foreground/60 font-black uppercase tracking-widest">
                                ¿No tienes cuenta? 
                                <Link :href="route('register')" class="text-primary hover:underline ml-1">
                                    Crea una aquí
                                </Link>
                            </p>
                        </div>
                    </div>

                    <div class="bg-foreground/5 backdrop-blur-md border-t border-white/5 dark:border-white/5 p-6 shrink-0">
                        <Link :href="route('driver.login')" class="flex items-center justify-between group p-2 rounded-2xl hover:bg-foreground/10 transition-colors border border-transparent">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-transparent border-2 border-foreground/10 text-foreground flex items-center justify-center shadow-inner">
                                    <Truck :size="20" stroke-width="2.5" />
                                </div>
                                <div class="text-left">
                                    <p class="text-[11px] font-black uppercase tracking-tight text-foreground">Soy Conductor</p>
                                    <p class="text-[9px] text-foreground/50 font-bold uppercase tracking-widest">Panel logístico</p>
                                </div>
                            </div>
                            <ArrowRight :size="16" stroke-width="3" class="text-foreground/50 group-hover:text-primary transition-colors" />
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style>
.custom-tel-input { box-shadow: inset 0 2px 4px rgba(0,0,0,0.05) !important; }
.custom-tel-input .vti__input { background: transparent !important; color: currentColor !important; font-weight: 900 !important; font-size: 16px !important; }
.custom-tel-input .vti__dropdown { background: transparent !important; border-radius: 16px 0 0 16px !important; border-right: 1px solid rgba(var(--foreground), 0.1) !important; }
.custom-tel-input .vti__dropdown:hover { background: rgba(var(--foreground), 0.05) !important; }
</style>