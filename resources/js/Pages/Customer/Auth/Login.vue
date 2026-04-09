<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, Link, Head, router } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import { Lock, Smartphone, ArrowRight, Truck, UserCircle } from 'lucide-vue-next';

const isPhoneValid = ref(false);

const form = useForm({
    phone: '',
    password: '',
    remember: false,
    guest_client_uuid: null 
});

onMounted(() => {
    // Captura de identidad persistente antes del montaje
    form.guest_client_uuid = localStorage.getItem('guest_client_uuid') || localStorage.getItem('guest_id');
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

    form.transform((data) => ({
        ...data,
        guest_client_uuid: form.guest_client_uuid
    })).post(route('customer.login'), {
        preserveScroll: true,
        onSuccess: () => {
            const localFavs = localStorage.getItem('guest_favorites');
            if (localFavs) {
                const skuIds = JSON.parse(localFavs);
                if (skuIds.length > 0) {
                    router.post(route('customer.favorites.sync'), { 
                        sku_ids: skuIds 
                    }, {
                        onFinish: () => localStorage.removeItem('guest_favorites')
                    });
                }
            }
        },
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acceso de Socio" />

    <div class="du-cyber-canvas min-h-[100svh] flex flex-col justify-center items-center p-4 lg:p-8 relative overflow-hidden">
        
        <div class="relative z-10 w-full max-w-[420px] glass-chassis rounded-[2.5rem] shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-700">
            
            <div class="p-8 md:p-10 pb-8">
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center shadow-f1-glow mb-6">
                        <span class="text-primary-foreground font-black text-2xl tracking-tighter">DU</span>
                    </div>
                    <h2 class="text-3xl font-black text-foreground tracking-tighter uppercase italic leading-none">Acceso</h2>
                    <p class="text-[10px] uppercase font-black tracking-[0.2em] text-foreground/40 mt-3">Identidad de Cliente</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 ml-1 flex items-center gap-2">
                            <Smartphone :size="12" stroke-width="3" /> Celular
                        </label>
                        
                        <vue-tel-input 
                            v-model="form.phone" 
                            @on-input="onInput"
                            mode="international"
                            :preferredCountries="['BO']" 
                            :defaultCountry="'BO'"
                            :dropdownOptions="{ showDialCodeInSelection: false, showFlags: true, showSearchBox: true }"
                            class="hardware-tel-input transition-colors duration-300"
                            :class="{ '!border-destructive': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="text-[9px] text-destructive font-bold uppercase ml-1">{{ form.errors.phone }}</p>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-foreground/70 flex items-center gap-2">
                                <Lock :size="12" stroke-width="3" /> Contraseña
                            </label>
                            <Link :href="route('customer.password.request')" class="text-[9px] font-black text-primary hover:underline uppercase italic">
                                ¿Olvidada?
                            </Link>
                        </div>
                        
                        <input 
                            v-model="form.password" 
                            type="password" 
                            placeholder="••••••••" 
                            class="w-full h-[56px] rounded-2xl bg-foreground/5 border border-border/30 px-4 text-foreground font-mono font-bold shadow-inner outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-colors duration-300 placeholder:text-foreground/20 autofill-shield"
                            :class="{ 'border-destructive focus:border-destructive focus:ring-destructive/50': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-[9px] text-destructive font-bold uppercase ml-1">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center ml-1 mt-2">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center justify-center">
                                <input 
                                    type="checkbox" 
                                    v-model="form.remember"
                                    class="peer appearance-none w-5 h-5 rounded-md border-2 border-border/50 bg-foreground/5 checked:bg-primary checked:border-primary transition-all duration-200 cursor-pointer"
                                />
                                <svg class="absolute w-3 h-3 text-background opacity-0 peer-checked:opacity-100 transition-opacity duration-200 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <span class="font-black text-[10px] text-foreground/60 uppercase tracking-widest group-hover:text-foreground transition-colors select-none">
                                Mantener Sesión
                            </span>
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="!canSubmit" 
                        class="w-full h-14 rounded-2xl font-black text-xs uppercase tracking-widest transition-all duration-300 flex items-center justify-center gap-3 mt-8 shadow-xl outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-background"
                        :class="canSubmit ? 'bg-foreground text-background hover:opacity-90 active:scale-95' : 'bg-foreground/5 text-foreground/20 border border-border/40 cursor-not-allowed'"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-background/30 border-t-background rounded-full animate-spin"></span>
                        <template v-else>Entrar <ArrowRight :size="16" stroke-width="3" /></template>
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-[10px] text-foreground/40 font-black uppercase tracking-widest">
                        ¿Sin cuenta? 
                        <Link :href="route('customer.register')" class="text-primary hover:underline ml-1">Crear Registro</Link>
                    </p>
                </div>
            </div>

            <div class="bg-foreground/[0.03] border-t border-border/20 p-5 md:p-6">
                <Link :href="route('driver.login')" class="flex items-center justify-between group p-3 rounded-xl hover:bg-foreground/5 transition-all active:scale-95">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-foreground/5 border border-border/30 text-foreground/60 flex items-center justify-center group-hover:text-primary group-hover:border-primary/30 transition-colors">
                            <Truck :size="18" stroke-width="2.5" />
                        </div>
                        <div class="text-left">
                            <p class="text-[10px] font-black uppercase text-foreground/70 group-hover:text-foreground transition-colors">Silo Logístico</p>
                            <p class="text-[8px] text-foreground/40 font-bold uppercase tracking-widest">Panel Conductor</p>
                        </div>
                    </div>
                    <ArrowRight :size="14" stroke-width="3" class="text-foreground/20 group-hover:text-primary transition-colors" />
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* 1. CHASIS DE IDENTIDAD (40px Blur) */
.glass-chassis {
    background-color: hsl(var(--background) / 0.6);
    backdrop-filter: blur(40px) saturate(200%);
    -webkit-backdrop-filter: blur(40px) saturate(200%);
    border: 1px solid hsl(var(--border) / 0.4);
}

.dark .glass-chassis {
    background-color: hsl(var(--background) / 0.8);
    border-color: hsl(var(--border) / 0.3);
}

/* 2. LIENZO MAESTRO */
.du-cyber-canvas {
    background-color: hsl(var(--background));
    background-image: 
        linear-gradient(to bottom, hsl(var(--background)) 0%, hsl(var(--background)) 40%, transparent 100%),
        linear-gradient(to right, #0ed2da, #5f29c7);
}

.du-cyber-canvas::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 0;
    background-image: linear-gradient(90deg, hsl(var(--border) / 0.4) 1px, transparent 1px);
    background-size: 50px 100%;
    pointer-events: none;
    mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 80%);
    -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 80%);
}

.dark .du-cyber-canvas {
    background-image: 
        linear-gradient(to bottom, hsl(var(--background)) 0%, hsl(var(--background)) 50%, transparent 100%),
        linear-gradient(to right, #0ed2da33, #5f29c733);
}

/* 3. BLINDAJE DE AUTOFILL (Evita el bloque blanco de Chrome) */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active {
    -webkit-text-fill-color: hsl(var(--foreground)) !important;
    transition: background-color 5000s ease-in-out 0s !important;
    background-color: transparent !important;
    box-shadow: inset 0 0 20px 20px hsl(var(--foreground) / 0.05) !important;
    font-family: 'JetBrains Mono', monospace !important;
}

/* 4. INPUT TELEFÓNICO */
:deep(.hardware-tel-input) {
    background: hsl(var(--foreground) / 0.05) !important;
    border: 1px solid hsl(var(--border) / 0.3) !important;
    border-radius: 1rem !important;
    height: 56px !important;
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
}

:deep(.hardware-tel-input:focus-within) {
    border-color: hsl(var(--primary) / 0.5) !important;
    box-shadow: 0 0 0 2px hsl(var(--primary) / 0.1) !important;
}

:deep(.hardware-tel-input .vti__input) {
    background: transparent !important;
    color: hsl(var(--foreground)) !important;
    font-weight: 800 !important;
    font-size: 14px !important;
    font-family: 'JetBrains Mono', monospace;
}

:deep(.hardware-tel-input .vti__selection .vti__country-code) {
    display: none !important;
}

:deep(.hardware-tel-input .vti__dropdown) {
    padding: 0 12px !important;
    background: transparent !important;
    border-right: 1px solid hsl(var(--border) / 0.2) !important;
}

:deep(.hardware-tel-input .vti__dropdown-list) {
    background-color: hsl(var(--background)) !important;
    border: 1px solid hsl(var(--border) / 0.5) !important;
    border-radius: 1rem !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
    color: hsl(var(--foreground)) !important;
    width: 280px !important;
    max-width: 90vw !important;
}

:deep(.hardware-tel-input .vti__dropdown-item.highlighted) {
    background-color: hsl(var(--foreground) / 0.1) !important;
}

/* 5. UTILIDADES */
.shadow-f1-glow {
    box-shadow: 0 0 15px -3px hsl(var(--primary) / 0.5), 0 0 6px -2px hsl(var(--primary) / 0.3);
}
</style>