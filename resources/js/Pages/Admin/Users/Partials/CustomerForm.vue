<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

// IMPORTAR EL NUEVO WORKFLOW DEDICADO DE ADMIN
import AdminLocationWorkflow from '@/Components/Admin/Maps/AdminLocationWorkflow.vue';
import { 
    User, MapPin, ClipboardList, Camera, ArrowRight, 
    RefreshCw, CheckCircle
} from 'lucide-vue-next';

const props = defineProps({
    user: { type: Object, default: null },
    branches: Array
});

const isEditing = computed(() => !!props.user);
const currentStep = ref(1);
const validatingStep1 = ref(false);

const steps = [
    { id: 1, title: 'CUENTA', icon: User },
    { id: 2, title: 'MAPA', icon: MapPin },
    { id: 3, title: 'DETALLES', icon: ClipboardList },
    { id: 4, title: 'PERFIL', icon: Camera },
];

const form = useForm({
    id: props.user?.id || null,
    first_name: props.user?.first_name || '',
    last_name: props.user?.last_name || '',
    phone: props.user?.phone || '',
    email: props.user?.email || '',
    password: '',
    country_code: 'BO',
    alias: props.user?.alias || '',
    address: props.user?.address || '',
    details: props.user?.reference || '',
    latitude: props.user?.latitude ? parseFloat(props.user.latitude) : -16.5000,
    longitude: props.user?.longitude ? parseFloat(props.user.longitude) : -68.1500,
    branch_id: props.user?.branch_id || null,
    avatar_source: props.user?.avatar_source || 'avatar_1.png',
    is_active: props.user ? !!props.user.is_active : true,
});

const onPhoneInput = (phone, obj) => {
    // ELIMINAR: if (obj?.number) form.phone = obj.number; <--- ESTO CAUSA EL ERROR
    if (obj?.country?.iso2) {
        form.country_code = obj.country.iso2.toUpperCase();
    }
    // El v-model ya actualiza form.phone automáticamente.
};
const handleStep1Validation = async () => {
    if (isEditing.value) { currentStep.value = 2; return; }
    
    validatingStep1.value = true;
    form.clearErrors();
    try {
        await axios.post(route('admin.users.validate-step-1'), {
            first_name: form.first_name,
            last_name: form.last_name,
            phone: form.phone,
            country_code: form.country_code,
            email: form.email,
            password: form.password,
            password_confirmation: form.password
        });
        currentStep.value = 2;
    } catch (error) {
        if (error.response?.status === 422) {
            const errs = error.response.data.errors;
            for (const f in errs) { form.setError(f, errs[f][0]); }
        }
    } finally { validatingStep1.value = false; }
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.users.update', props.user.id));
    } else {
        form.post(route('admin.users.store'));
    }
};

const progressWidth = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
</script>

<template>
    <div class="max-w-3xl mx-auto pb-20">
        
        <div class="relative mb-12 px-6">
            <div class="absolute top-5 left-12 right-12 h-[1px] bg-zinc-800"></div>
            <div class="absolute top-5 left-12 h-[1px] bg-primary transition-all duration-700 shadow-[0_0_10px_rgba(var(--primary-rgb),0.5)]" :style="{ width: progressWidth + '%' }"></div>

            <div class="relative flex justify-between">
                <div v-for="step in steps" :key="step.id" class="flex flex-col items-center">
                    <div class="w-10 h-10 border flex items-center justify-center transition-all duration-500 bg-zinc-950 z-10"
                         :class="currentStep >= step.id ? 'border-primary text-primary shadow-[0_0_15px_rgba(var(--primary-rgb),0.1)]' : 'border-zinc-800 text-zinc-600'">
                        <component :is="step.icon" :size="16" />
                    </div>
                    <span class="text-[8px] font-mono font-black mt-2 tracking-[0.2em]" :class="currentStep >= step.id ? 'text-primary' : 'text-zinc-700'">{{ step.title }}</span>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="bg-zinc-900/40 border border-zinc-800 rounded-none overflow-hidden relative">
            <div class="absolute top-0 left-0 w-full h-[1px] bg-primary/10 animate-scanline pointer-events-none"></div>

            <div class="p-8">
                <div v-if="currentStep === 1" class="space-y-6 animate-in fade-in duration-500">
                    <div class="flex items-center gap-3 mb-8 border-l-2 border-primary pl-4">
                        <h2 class="text-xl font-black italic uppercase text-white tracking-tighter">Acceso_Sistema</h2>
                        <span class="text-[9px] font-mono text-zinc-500">[ SEC_LEVEL_01 ]</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="admin-label">// NOMBRES</label>
                            <input v-model="form.first_name" type="text" class="admin-input" :disabled="isEditing" />
                            <p v-if="form.errors.first_name" class="admin-error">{{ form.errors.first_name }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <label class="admin-label">// APELLIDOS</label>
                            <input v-model="form.last_name" type="text" class="admin-input" :disabled="isEditing" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="admin-label">// EMAIL_ID</label>
                        <input v-model="form.email" type="email" class="admin-input" :disabled="isEditing" />
                        <p v-if="form.errors.email" class="admin-error">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="admin-label">// TERMINAL_MOVIL</label>
                        <vue-tel-input v-model="form.phone" @on-input="onPhoneInput" mode="international" class="admin-tel-input" :disabled="isEditing" />
                        <p v-if="form.errors.phone" class="admin-error">{{ form.errors.phone }}</p>
                    </div>

                    <div v-if="!isEditing" class="space-y-1.5">
                        <label class="admin-label">// CLAVE_ACCESO</label>
                        <input v-model="form.password" type="password" class="admin-input" placeholder="********" />
                        <p v-if="form.errors.password" class="admin-error">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="button" @click="handleStep1Validation" :disabled="validatingStep1" class="admin-btn-primary">
                            <span v-if="validatingStep1">VALIDANDO...</span>
                            <span v-else class="flex items-center gap-2">CONTINUAR <ArrowRight :size="14"/></span>
                        </button>
                    </div>
                </div>

                <div v-if="currentStep === 2 || currentStep === 3" class="-m-8 relative transition-all duration-500" :class="currentStep === 2 ? 'h-[520px]' : 'min-h-[520px] py-10'">
                    <AdminLocationWorkflow 
                        :form="form"
                        :activeBranches="branches"
                        :step="currentStep === 2 ? 1 : 2" 
                        @next="currentStep++"
                        @back="currentStep--"
                    />
                </div>

                <div v-if="currentStep === 4" class="space-y-8 animate-in fade-in duration-500 text-center">
                    <div class="border-l-2 border-primary pl-4 text-left mb-8">
                        <h2 class="text-xl font-black italic uppercase text-white tracking-tighter">Identidad_Visual</h2>
                        <span class="text-[9px] font-mono text-zinc-500">[ PROFILE_SYNC ]</span>
                    </div>

                    <div class="flex justify-center mb-10">
                        <div class="w-32 h-32 border-2 border-primary/30 p-1 bg-zinc-950">
                            <img :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover" />
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-2 max-w-sm mx-auto">
                        <button v-for="i in 8" :key="i" type="button" 
                                @click="form.avatar_source = `avatar_${i}.png`"
                                class="aspect-square border transition-all"
                                :class="form.avatar_source === `avatar_${i}.png` ? 'border-primary bg-primary/5' : 'border-zinc-800 hover:border-zinc-600'">
                            <img :src="`/assets/avatars/avatar_${i}.png`" class="w-full h-full object-contain p-1" />
                        </button>
                    </div>

                    <div class="flex justify-between items-center pt-10 mt-10 border-t border-zinc-800">
                        <button type="button" @click="currentStep = 3" class="admin-btn-secondary">ATRÁS</button>
                        
                        <button type="submit" :disabled="form.processing" class="admin-btn-primary group">
                            <span v-if="form.processing" class="flex gap-2 items-center"><RefreshCw class="animate-spin" :size="14"/> PROCESANDO</span>
                            <span v-else class="flex items-center gap-2">FINALIZAR REGISTRO <CheckCircle :size="14"/></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
.admin-label { @apply text-[9px] font-mono font-black text-zinc-500 uppercase tracking-widest ml-1; }
.admin-input { @apply w-full bg-zinc-950 border border-zinc-800 focus:border-primary px-5 py-3.5 text-sm font-mono text-white outline-none transition-all; }
.admin-error { @apply text-[9px] font-mono font-bold text-red-500 mt-1 uppercase; }
.admin-btn-primary { @apply px-10 py-4 bg-primary text-white font-mono text-[10px] font-black uppercase tracking-[0.2em] hover:bg-primary/90 transition-all active:scale-95 disabled:opacity-50; }
.admin-btn-secondary { @apply px-10 py-4 border border-zinc-800 text-zinc-500 font-mono text-[10px] font-bold hover:bg-zinc-800 transition-all uppercase tracking-widest; }

:deep(.admin-tel-input) { @apply bg-zinc-950 border border-zinc-800 rounded-none font-mono !important; height: 52px; }
:deep(.vti__input) { @apply bg-transparent text-white text-sm !important; }
:deep(.vti__dropdown) { @apply border-r border-zinc-800 !important; }

@keyframes scanline { 0% { transform: translateY(-100%); } 100% { transform: translateY(1000%); } }
.animate-scanline { animation: scanline 10s linear infinite; }
</style>