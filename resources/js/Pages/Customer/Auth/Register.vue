<script setup>
import { ref, watch, computed } from 'vue'; 
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import BaseInput from '@/Components/Base/BaseInput.vue';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
import { 
    MapPin, UserPlus, Smartphone, Lock, Mail, 
    CheckCircle, Upload, ArrowRight, ArrowLeft, Image, ShieldCheck, UserCircle
} from 'lucide-vue-next';

const props = defineProps({
    activeBranches: { type: Array, default: () => [] }
});

const currentStep = ref(1);
const step1Errors = ref({});
const validatingStep1 = ref(false);
const customPreview = ref(null);
const isPhoneValid = ref(false);

const form = useForm({
    first_name: '',
    last_name: '',  
    phone: '', 
    country_code: '',
    email: '', 
    password: '', 
    password_confirmation: '', 
    terms: true, 
    avatar_type: 'icon', 
    avatar_source: 'avatar_1.svg', 
    avatar_file: null,
    alias: 'Mi casa', 
    address: '', 
    details: '', 
    latitude: -16.5000, 
    longitude: -68.1500, 
    branch_id: null, 
    role: 'client',
    guest_client_uuid: localStorage.getItem('guest_client_uuid')
});

const steps = [
    { id: 1, title: 'Datos', icon: UserPlus },
    { id: 2, title: 'Foto', icon: Image },
    { id: 3, title: 'Ubicación', icon: MapPin },
];

const onInput = (phone, obj) => { 
    isPhoneValid.value = obj?.valid ?? false; 
    if (obj?.country?.iso2) form.country_code = obj.country.iso2.toUpperCase();
    if (obj?.number) form.phone = obj.number; 
    if (step1Errors.value.phone) delete step1Errors.value.phone;
};

const nextStep = () => {
    if (currentStep.value === 1) {
        if (!isPhoneValid.value) {
            step1Errors.value = { phone: ['Ingrese un número válido.'] };
            return;
        }
        if (!form.country_code && isPhoneValid.value) {
            // Intento de recuperación de emergencia si falló el evento
            form.country_code = 'BO'; 
        }
        validatingStep1.value = true;
        form.post(route('register.validate-step-1'), {
            preserveScroll: true,
            onError: (errors) => {
                step1Errors.value = errors;
                validatingStep1.value = false;
            },
            onSuccess: () => {
                currentStep.value = 2;
                validatingStep1.value = false;
            }
        });
    } else if (currentStep.value === 2) {
        currentStep.value = 3;
    }
};

const submit = () => {
    if (form.processing) return;
    form.post(route('register'), {
        preserveScroll: true,
        forceFormData: true
    });
};
</script>

<template>
    <Head title="Crear cuenta" />

    <ShopLayout>
        <div class="flex-1 flex items-center justify-center p-4 py-8 min-h-[calc(100svh-144px)]">
            
            <div class="w-full max-w-xl animate-in fade-in zoom-in-95 duration-500">
                
                <div class="bg-surface/20 backdrop-blur-2xl border border-white/10 dark:border-white/5 rounded-[40px] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col">
                    
                    <div class="px-8 pt-10 pb-6 shrink-0 bg-transparent border-b border-white/10 dark:border-white/5">
                        <div class="flex justify-between items-end mb-6">
                            <div>
                                <h2 class="text-3xl font-sans font-black text-foreground tracking-tighter leading-none">
                                    Crea tu cuenta
                                </h2>
                                <p class="text-[11px] uppercase font-black tracking-[0.1em] text-foreground/60 mt-2">
                                    Paso a paso hacia tu pedido
                                </p>
                            </div>
                            <div class="text-[11px] font-black text-primary bg-primary/10 px-4 py-1.5 rounded-full uppercase border border-primary/30">
                                {{ currentStep }} / 3
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 h-2">
                            <div v-for="step in steps" :key="step.id" 
                                class="h-full rounded-full transition-all duration-700"
                                :class="currentStep >= step.id ? 'bg-primary flex-[3] shadow-[0_0_15px_rgba(var(--primary),0.4)]' : 'bg-foreground/10 flex-1'">
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 custom-scrollbar max-h-[60vh] md:max-h-none">
                        <form id="registerForm" @submit.prevent="submit">
                            
                            <div v-show="currentStep === 1" class="space-y-6 animate-in slide-in-from-right-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                            <UserCircle :size="14" stroke-width="3" /> Nombre
                                        </label>
                                        <BaseInput v-model="form.first_name" placeholder="Tu nombre" :error="step1Errors.first_name?.[0]" 
                                            class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-bold shadow-inner" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                            <UserCircle :size="14" stroke-width="3" /> Apellido
                                        </label>
                                        <BaseInput v-model="form.last_name" placeholder="Tu apellido" :error="step1Errors.last_name?.[0]" 
                                            class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-bold shadow-inner" />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                        <Smartphone :size="14" stroke-width="3" /> Número de celular
                                    </label>
                                    <vue-tel-input 
                                        v-model="form.phone" 
                                        @on-input="onInput"
                                        @country-changed="(obj) => { 
                                            if (obj?.iso2) form.country_code = obj.iso2.toUpperCase();
                                        }"
                                        mode="international" 
                                        :preferredCountries="['BO', 'PE']" 
                                        :defaultCountry="'BO'"
                                        class="custom-tel-input !bg-transparent !backdrop-blur-xl !border-foreground/10 !rounded-2xl h-[56px] transition-all focus-within:!border-primary/50 shadow-inner"
                                        :class="{ '!border-f1-red': step1Errors.phone || step1Errors.country_code }" 
                                    />
                                    <p v-if="step1Errors.phone" class="text-[10px] text-f1-red font-bold mt-1 ml-1 uppercase">{{ step1Errors.phone[0] }}</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                        <Mail :size="14" stroke-width="3" /> Correo electrónico
                                    </label>
                                    <BaseInput v-model="form.email" type="email" placeholder="ejemplo@correo.com" :error="step1Errors.email?.[0]" 
                                        class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-bold shadow-inner" />
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                            <Lock :size="14" stroke-width="3" /> Contraseña
                                        </label>
                                        <BaseInput v-model="form.password" type="password" placeholder="••••••••" :error="step1Errors.password?.[0]" 
                                            class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-mono font-bold shadow-inner" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                            <Lock :size="14" stroke-width="3" /> Confirmar clave
                                        </label>
                                        <BaseInput v-model="form.password_confirmation" type="password" placeholder="••••••••" 
                                            class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-mono font-bold shadow-inner"/>
                                    </div>
                                </div>
                            </div>

                            <div v-show="currentStep === 2" class="space-y-8 animate-in slide-in-from-right-4 text-center py-4">
                                <div class="relative inline-block">
                                    <div class="w-32 h-32 rounded-[2.5rem] border-4 border-foreground/10 p-1 mx-auto bg-transparent backdrop-blur-xl overflow-hidden shadow-2xl">
                                        <img v-if="form.avatar_type === 'custom' && customPreview" :src="customPreview" class="w-full h-full object-cover rounded-[2rem]" />
                                        <img v-else :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover rounded-[2rem]" />
                                    </div>
                                    <label for="reg_avatar_upload" class="absolute -bottom-2 -right-2 p-3 bg-primary text-white rounded-2xl cursor-pointer shadow-xl border-4 border-transparent backdrop-blur-md hover:scale-110 transition-transform">
                                        <Upload :size="18" stroke-width="3" />
                                    </label>
                                    <input type="file" id="reg_avatar_upload" class="hidden" @change="(e) => { const file = e.target.files[0]; if(file) { form.avatar_type='custom'; form.avatar_file=file; customPreview = URL.createObjectURL(file); } }">
                                </div>

                                <div class="grid grid-cols-4 gap-3 max-w-sm mx-auto">
                                    <button type="button" v-for="i in 8" :key="i" 
                                            @click="() => { form.avatar_type='icon'; form.avatar_source=`avatar_${i}.svg`; customPreview=null; }"
                                            class="aspect-square rounded-2xl border-2 flex items-center justify-center p-2 transition-all"
                                            :class="form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg` ? 'border-primary bg-primary/20 backdrop-blur-md scale-105' : 'border-transparent bg-foreground/5 backdrop-blur-sm opacity-60 hover:opacity-100'">
                                        <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-full h-full" />
                                    </button>
                                </div>
                                <p class="text-xs font-black text-foreground uppercase tracking-widest">Elige una foto de perfil</p>
                            </div>

                            <div v-show="currentStep === 3" class="space-y-6 animate-in slide-in-from-right-4">
                                <div class="h-64 rounded-[2rem] overflow-hidden border border-foreground/10 shadow-inner relative">
                                    <ClientLocationPicker ref="mapComponentRef" v-model:modelValueLat="form.latitude" v-model:modelValueLng="form.longitude" v-model:modelValueAddress="form.address" v-model:modelValueBranchId="form.branch_id" :activeBranches="props.activeBranches" />
                                    <div class="absolute top-4 right-4">
                                        <span class="bg-background/40 backdrop-blur-xl px-3 py-1.5 rounded-xl text-[8px] font-black text-foreground uppercase tracking-widest border border-white/10 shadow-lg">Tu ubicación actual</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                            <MapPin :size="14" stroke-width="3" /> Alias
                                        </label>
                                        <BaseInput v-model="form.alias" placeholder="Ej: Mi casa" class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-bold shadow-inner" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[12px] font-black uppercase tracking-tight text-foreground ml-1 flex items-center gap-2 bg-transparent">
                                            <ShieldCheck :size="14" stroke-width="3" /> Notas
                                        </label>
                                        <BaseInput v-model="form.details" placeholder="Ej: Piso 4, timbre A" class="!bg-transparent !backdrop-blur-xl !border-foreground/10 focus:!border-primary/50 !rounded-2xl h-[56px] !text-foreground font-bold shadow-inner" />
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="p-8 bg-transparent shrink-0">
                        <div class="flex gap-4">
                            <button v-if="currentStep > 1" @click="currentStep--" 
                                    class="h-14 px-6 bg-foreground/5 backdrop-blur-lg border border-foreground/10 rounded-[20px] font-black uppercase text-[11px] text-foreground hover:bg-foreground/10 transition-colors flex items-center gap-2">
                                <ArrowLeft :size="16" stroke-width="3" /> Atrás
                            </button>
                            <Link v-else :href="route('login')" 
                                  class="h-14 px-6 bg-foreground/5 backdrop-blur-lg border border-foreground/10 rounded-[20px] font-black uppercase text-[11px] text-foreground hover:bg-foreground/10 transition-colors flex items-center text-center leading-tight">
                                ¿Ya tienes<br>cuenta?
                            </Link>

                            <button v-if="currentStep < 3" @click="nextStep" :disabled="validatingStep1" 
                                    class="flex-1 h-14 bg-primary text-white rounded-[20px] font-black text-sm uppercase tracking-wider shadow-lg shadow-primary/20 flex items-center justify-center gap-3 active:scale-95 transition-all">
                                <span v-if="validatingStep1" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <template v-else>
                                    Siguiente <ArrowRight :size="18" stroke-width="3" />
                                </template>
                            </button>

                            <button v-else type="submit" form="registerForm" :disabled="form.processing" 
                                    class="flex-1 h-14 bg-primary text-white rounded-[20px] font-black text-sm uppercase tracking-wider shadow-lg shadow-primary/20 flex items-center justify-center gap-3 active:scale-95 transition-all">
                                <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <template v-else>
                                    Finalizar <CheckCircle :size="18" stroke-width="3" />
                                </template>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style>
/* Forzar transparencia absoluta en componentes VueTelInput */
.custom-tel-input {
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05) !important; /* Ligera sombra interna para profundidad */
}

.custom-tel-input .vti__input {
    background: transparent !important;
    color: currentColor !important;
    font-weight: 700 !important;
    font-size: 16px !important;
}

.custom-tel-input .vti__dropdown {
    background: transparent !important;
    border-radius: 16px 0 0 16px !important;
    border-right: 1px solid rgba(var(--foreground), 0.1) !important;
}

.custom-tel-input .vti__dropdown:hover {
    background: rgba(var(--foreground), 0.05) !important;
}

/* Ocultar barra de desplazamiento para mantener el diseño limpio */
.custom-scrollbar::-webkit-scrollbar { width: 0px; }
</style>