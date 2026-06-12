<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, Link, Head, router, usePage } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import { Lock, Smartphone, ArrowRight, Truck } from 'lucide-vue-next';

const isPhoneValid = ref(false);
const formattedPhone = ref(''); 

const form = useForm({
    phone: '',
    password: '',
    remember: false,
    guest_client_uuid: null 
});

const page = computed(() => usePage().props);

onMounted(() => {
    form.guest_client_uuid = localStorage.getItem('guest_client_uuid') || localStorage.getItem('guest_id');
});

const onInput = (phone, phoneObject) => {
    isPhoneValid.value = phoneObject?.valid || false;
    formattedPhone.value = phoneObject?.number || phone;
};

const canSubmit = computed(() => {
    return isPhoneValid.value && form.password.length > 0 && !form.processing;
});

const submit = () => {
    if (!canSubmit.value) return;
    form.clearErrors();

    form.transform((data) => ({
        ...data,
        phone: formattedPhone.value, 
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

    <div class="du-cyber-canvas bg-f1-lines-random min-h-[100svh] flex flex-col justify-center items-center p-4 lg:p-8 relative overflow-hidden">
        
        <div class="relative z-10 w-full max-w-[400px] bg-background dark:bg-[#15151e] border border-[#32323b] rounded-xl flex flex-col overflow-hidden transition-colors duration-150">
            
            <div class="p-6 md:p-8 pb-6">
                <div class="flex flex-col items-center text-center mb-8">
                    <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center shadow-f1-glow mb-4">
                        <span class="text-white font-black text-xl tracking-tighter italic">DU</span>
                    </div>
                    <h2 class="text-2xl font-black text-foreground tracking-tighter uppercase italic leading-none">Acceso</h2>
                    <p class="text-[9px] uppercase font-black tracking-[0.2em] text-neutral-500 mt-2">Identidad de Cliente</p>
                </div>

                <div v-if="page.flash?.error" class="mb-5 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-center animate-in fade-in duration-150">
                    <p class="text-[9px] text-red-500 font-black uppercase tracking-wider">{{ page.flash.error }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black uppercase tracking-widest text-neutral-500 ml-0.5 flex items-center gap-1.5">
                            <Smartphone :size="12" :stroke-width="2" /> Celular
                        </label>
                        
                        <vue-tel-input 
                            v-model="form.phone" 
                            @on-input="onInput"
                            mode="international"
                            :preferredCountries="['BO']" 
                            :defaultCountry="'BO'"
                            :dropdownOptions="{ showDialCodeInSelection: false, showFlags: true, showSearchBox: true }"
                            class="hardware-tel-input transition-colors duration-150"
                            :class="{ '!border-red-500': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="text-[9px] text-red-500 font-bold uppercase ml-0.5">{{ form.errors.phone }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center ml-0.5">
                            <label class="text-[9px] font-black uppercase tracking-widest text-neutral-500 flex items-center gap-1.5">
                                <Lock :size="12" :stroke-width="2" /> Contraseña
                            </label>
                            <Link :href="route('customer.password.request')" class="text-[9px] font-black text-primary hover:underline uppercase italic">
                                ¿Olvidada?
                            </Link>
                        </div>
                        
                        <input 
                            v-model="form.password" 
                            type="password" 
                            placeholder="••••••••" 
                            class="w-full h-11 rounded-xl bg-transparent border border-[#32323b] px-3 text-foreground font-mono font-bold outline-none focus:border-primary/50 transition-colors duration-150 placeholder:text-neutral-500 text-sm"
                            :class="{ 'border-red-500 focus:border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-[9px] text-red-500 font-bold uppercase ml-0.5">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center ml-0.5 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <div class="relative flex items-center justify-center">
                                <input 
                                    type="checkbox" 
                                    v-model="form.remember"
                                    class="peer appearance-none w-4 h-4 rounded border border-[#32323b] bg-transparent checked:bg-primary checked:border-primary transition-colors duration-75 cursor-pointer outline-none focus:outline-none"
                                />
                                <svg class="absolute w-2.5 h-2.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-75 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <span class="font-black text-[9px] text-neutral-500 uppercase tracking-widest group-hover:text-foreground transition-colors select-none">
                                Mantener Sesión
                            </span>
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="!canSubmit" 
                        class="w-full h-11 btn-primary flex items-center justify-center gap-2 mt-6 disabled:opacity-30 disabled:hover:bg-primary disabled:cursor-not-allowed outline-none focus:outline-none"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <template v-else>Entrar <ArrowRight :size="14" :stroke-width="3" /></template>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-[9px] text-neutral-500 font-black uppercase tracking-widest">
                        ¿Sin cuenta? 
                        <Link :href="route('customer.register')" class="text-primary hover:underline ml-1">Crear Registro</Link>
                    </p>
                </div>
            </div>

            <div class="bg-neutral-100 dark:bg-[#0c0c12] border-t border-[#32323b] p-4">
                <Link :href="route('driver.login')" class="flex items-center justify-between group p-2 rounded-xl hover:bg-foreground/[0.03] dark:hover:bg-white/[0.02] transition-colors active:scale-95 outline-none focus:outline-none">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-transparent border border-[#32323b] text-neutral-500 flex items-center justify-center group-hover:text-primary group-hover:border-primary transition-colors">
                            <Truck :size="16" :stroke-width="1.5" />
                        </div>
                        <div class="text-left">
                            <p class="text-[9px] font-black uppercase text-neutral-500 group-hover:text-foreground transition-colors">Silo Logístico</p>
                            <p class="text-[8px] text-neutral-400 font-bold uppercase tracking-widest">Panel Conductor</p>
                        </div>
                    </div>
                    <ArrowRight :size="14" :stroke-width="2" class="text-neutral-500 opacity-0 group-hover:opacity-100 group-hover:text-primary transition-all" />
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }

/* INYECCIÓN DE ESTILOS MECÁNICOS PARA EL PLUGIN DE TELÉFONO */
:deep(.hardware-tel-input) {
    background: transparent !important;
    border: 1px solid #32323b !important;
    border-radius: 0.75rem !important; /* Force rounded-xl */
    height: 44px !important;
    outline: none !important;
    box-shadow: none !important;
}

:deep(.hardware-tel-input:focus-within) {
    border-color: rgba(var(--primary), 0.5) !important;
    border-color: hsl(var(--primary) / 0.5) !important;
}

:deep(.hardware-tel-input .vti__input) {
    background: transparent !important;
    color: currentColor !important;
    font-weight: 900 !important;
    font-size: 13px !important;
    font-family: 'JetBrains Mono', monospace;
    outline: none !important;
}

:deep(.hardware-tel-input .vti__selection .vti__country-code) {
    display: none !important;
}

:deep(.hardware-tel-input .vti__dropdown) {
    padding: 0 10px !important;
    background: transparent !important;
    border-right: 1px solid #32323b !important;
}

:deep(.hardware-tel-input .vti__dropdown-list) {
    background-color: #15151e !important;
    border: 1px solid #32323b !important;
    border-radius: 0.75rem !important;
    color: #ffffff !important;
    width: 260px !important;
    z-index: 50 !important;
}

.dark :deep(.hardware-tel-input .vti__dropdown-list) {
    background-color: #15151e !important;
}

/* Modo claro para el desplegable del teléfono */
.du-cyber-canvas:not(.dark) :deep(.hardware-tel-input .vti__dropdown-list) {
    background-color: #ffffff !important;
    color: #15151e !important;
}

:deep(.hardware-tel-input .vti__dropdown-item.highlighted) {
    background-color: hsl(var(--primary) / 0.2) !important;
}

/* Forzar reset del autocompletado del navegador */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active {
    -webkit-text-fill-color: currentColor !important;
    transition: background-color 5000s ease-in-out 0s !important;
    background-color: transparent !important;
    box-shadow: none !important;
}
</style>