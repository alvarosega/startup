<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    Truck, Bike, Car, User, ArrowRight, ArrowLeft, 
    CheckCircle, Lock, Mail, FileText, Upload, Camera, AlertTriangle 
} from 'lucide-vue-next';

const currentStep = ref(1);
const validatingStep1 = ref(false);
const ciPreview = ref(null);
const licensePreview = ref(null);

const steps = [
    { id: 1, title: 'Cuenta', icon: Lock },
    { id: 2, title: 'Perfil', icon: User },
    { id: 3, title: 'Documentos', icon: FileText },
    { id: 4, title: 'Identidad', icon: CheckCircle },
];

const form = useForm({
    phone: '', 
    email: '', 
    password: '', 
    password_confirmation: '', 
    first_name: '', 
    last_name: '', 
    license_number: '', 
    license_plate: '', 
    vehicle_type: 'moto',
    ci_front: null,
    license_photo: null,
    avatar_type: 'icon', 
    avatar_source: 'avatar_1.svg'
});

const onPhoneInput = (phone, obj) => {
    if (obj?.number) {
        form.phone = obj.number;
    } else if (phone) {
        form.phone = phone.startsWith('+') ? phone : `+591${phone}`;
    }
};

const handleFileUpload = (event, field) => {
    form.clearErrors(field);
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 4194304) {
        form.setError(field, 'La imagen supera los 4MB permitidos.');
        return;
    }

    form[field] = file;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        if (field === 'ci_front') ciPreview.value = e.target.result;
        if (field === 'license_photo') licensePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const nextStep = () => {
    form.clearErrors();

    if (currentStep.value === 1) {
        let hasErrors = false;

        if (!form.phone.trim()) {
            form.setError('phone', 'El número de celular es obligatorio.');
            hasErrors = true;
        }
        if (!form.email.trim()) {
            form.setError('email', 'El correo electrónico es obligatorio.');
            hasErrors = true;
        }
        if (!form.password) {
            form.setError('password', 'La contraseña es obligatoria.');
            hasErrors = true;
        } else if (form.password.length < 8) {
            form.setError('password', 'La contraseña debe tener mínimo 8 caracteres.');
            hasErrors = true;
        }
        if (form.password !== form.password_confirmation) {
            form.setError('password_confirmation', 'Las contraseñas no coinciden.');
            hasErrors = true;
        }

        if (hasErrors) return;

        validatingStep1.value = true;
        form.post(route('driver.register.validate-step-1'), {
            preserveScroll: true,
            onSuccess: () => {
                currentStep.value = 2;
                validatingStep1.value = false;
            },
            onError: () => {
                validatingStep1.value = false;
            },
        });

    } else if (currentStep.value === 2) {
        let hasErrors = false;

        if (!form.first_name.trim()) {
            form.setError('first_name', 'El nombre es obligatorio.');
            hasErrors = true;
        }
        if (!form.last_name.trim()) {
            form.setError('last_name', 'El apellido es obligatorio.');
            hasErrors = true;
        }
        if (!form.license_number.trim()) {
            form.setError('license_number', 'El número de licencia es obligatorio.');
            hasErrors = true;
        }
        if (!form.license_plate.trim()) {
            form.setError('license_plate', 'La placa es obligatoria.');
            hasErrors = true;
        }

        if (!hasErrors) {
            currentStep.value = 3;
        }

    } else if (currentStep.value === 3) {
        let hasErrors = false;

        if (!form.ci_front) {
            form.setError('ci_front', 'Debes adjuntar la foto de la cédula.');
            hasErrors = true;
        }
        if (!form.license_photo) {
            form.setError('license_photo', 'Debes adjuntar la foto de la licencia.');
            hasErrors = true;
        }

        if (!hasErrors) {
            currentStep.value = 4;
        }
    }
};

const submit = () => {
    form.post(route('driver.register.store'), { 
        preserveScroll: true,
        forceFormData: true,
    });
};

const vehicleTypes = [
    { id: 'moto', label: 'Moto', icon: Bike },
    { id: 'car', label: 'Auto', icon: Car },
    { id: 'truck', label: 'Camión', icon: Truck }
];

const progressPercentage = computed(() => (currentStep.value / steps.length) * 100);
</script>
<template>
    <Head title="Registro de Conductor" />

    <ShopLayout>
        <div class="min-h-screen flex items-center justify-center p-4 bg-muted/30 pt-24 pb-12">
            <div class="w-full max-w-xl bg-card border border-border rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-500">
                
                <div v-if="form.errors.error" class="m-6 p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-2xl flex items-center gap-3">
                    <AlertTriangle :size="18" />
                    <span class="text-xs font-black uppercase tracking-widest">{{ form.errors.error }}</span>
                </div>

                <!-- Cabecera de Progreso -->
                <div class="p-8 bg-muted/20 border-b border-border relative">
                    <div class="flex justify-between items-center mb-6">
                        <div class="space-y-1">
                            <h2 class="text-3xl font-black italic uppercase tracking-tighter text-foreground">{{ steps[currentStep-1].title }}</h2>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-600">Paso {{ currentStep }} de 4</p>
                        </div>
                        <div class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                            <component :is="steps[currentStep-1].icon" :size="24" />
                        </div>
                    </div>
                    <div class="h-1.5 w-full bg-muted rounded-full overflow-hidden">
                        <div class="h-full bg-amber-500 transition-all duration-500" :style="{ width: progressPercentage + '%' }" />
                    </div>
                </div>

                <div class="p-8">
                    <!-- PASO 1: CUENTA -->
                    <div v-show="currentStep === 1" class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Celular (WhatsApp) *</label>
                            <vue-tel-input 
                                v-model="form.phone" 
                                @on-input="onPhoneInput" 
                                mode="international" 
                                :defaultCountry="'BO'" 
                                :onlyCountries="['BO']"
                                class="driver-tel-input" 
                                :class="{ '!border-red-500': form.errors.phone }"
                            />
                            <p v-if="form.errors.phone" class="text-[10px] text-red-500 font-bold uppercase ml-1 mt-1">{{ form.errors.phone }}</p>
                        </div>
                        
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Correo Electrónico *</label>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                placeholder="conductor@empresa.com"
                                class="w-full h-12 bg-transparent border border-border rounded-xl px-4 text-sm font-bold text-foreground focus:border-amber-500 transition-all outline-none"
                                :class="{ 'border-red-500': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="text-[10px] text-red-500 font-bold uppercase ml-1 mt-1">{{ form.errors.email }}</p>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Contraseña *</label>
                                <input 
                                    v-model="form.password" 
                                    type="password" 
                                    placeholder="••••••••"
                                    class="w-full h-12 bg-transparent border border-border rounded-xl px-4 text-sm font-mono font-bold text-foreground focus:border-amber-500 transition-all outline-none"
                                    :class="{ 'border-red-500': form.errors.password }"
                                />
                                <p v-if="form.errors.password" class="text-[10px] text-red-500 font-bold uppercase ml-1 mt-1">{{ form.errors.password }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Confirmar Contraseña *</label>
                                <input 
                                    v-model="form.password_confirmation" 
                                    type="password" 
                                    placeholder="••••••••"
                                    class="w-full h-12 bg-transparent border border-border rounded-xl px-4 text-sm font-mono font-bold text-foreground focus:border-amber-500 transition-all outline-none"
                                    :class="{ 'border-red-500': form.errors.password_confirmation }"
                                />
                                <p v-if="form.errors.password_confirmation" class="text-[10px] text-red-500 font-bold uppercase ml-1 mt-1">{{ form.errors.password_confirmation }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- PASO 2: PERFIL Y VEHÍCULO -->
                    <div v-show="currentStep === 2" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Nombres *</label>
                                <input 
                                    v-model="form.first_name" 
                                    type="text" 
                                    placeholder="Juan"
                                    class="w-full h-12 bg-transparent border border-border rounded-xl px-4 text-sm font-bold text-foreground focus:border-amber-500 transition-all outline-none"
                                    :class="{ 'border-red-500': form.errors.first_name }"
                                />
                                <p v-if="form.errors.first_name" class="text-[10px] text-red-500 font-bold uppercase ml-1 mt-1">{{ form.errors.first_name }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Apellidos *</label>
                                <input 
                                    v-model="form.last_name" 
                                    type="text" 
                                    placeholder="Perez"
                                    class="w-full h-12 bg-transparent border border-border rounded-xl px-4 text-sm font-bold text-foreground focus:border-amber-500 transition-all outline-none"
                                    :class="{ 'border-red-500': form.errors.last_name }"
                                />
                                <p v-if="form.errors.last_name" class="text-[10px] text-red-500 font-bold uppercase ml-1 mt-1">{{ form.errors.last_name }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Unidad de Transporte *</label>
                            <div class="grid grid-cols-3 gap-3">
                                <button v-for="v in vehicleTypes" :key="v.id" type="button" @click="form.vehicle_type = v.id"
                                    class="flex flex-col items-center p-4 rounded-2xl border-2 transition-all"
                                    :class="form.vehicle_type === v.id ? 'border-amber-500 bg-amber-500/10 text-amber-600 shadow-sm' : 'border-border text-muted-foreground opacity-60 hover:opacity-100'">
                                    <component :is="v.icon" :size="24" />
                                    <span class="text-[10px] font-black uppercase mt-2">{{ v.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Nº Licencia de Conducir *</label>
                                <input 
                                    v-model="form.license_number" 
                                    type="text" 
                                    placeholder="LP-4839201"
                                    class="w-full h-12 bg-transparent border border-border rounded-xl px-4 text-sm font-mono font-bold text-foreground focus:border-amber-500 transition-all outline-none"
                                    :class="{ 'border-red-500': form.errors.license_number }"
                                />
                                <p v-if="form.errors.license_number" class="text-[10px] text-red-500 font-bold uppercase ml-1 mt-1">{{ form.errors.license_number }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Número de Placa *</label>
                                <input 
                                    v-model="form.license_plate" 
                                    type="text" 
                                    placeholder="4059-ABC"
                                    class="w-full h-12 bg-transparent border border-border rounded-xl px-4 text-sm font-mono font-bold text-foreground focus:border-amber-500 transition-all outline-none"
                                    :class="{ 'border-red-500': form.errors.license_plate }"
                                />
                                <p v-if="form.errors.license_plate" class="text-[10px] text-red-500 font-bold uppercase ml-1 mt-1">{{ form.errors.license_plate }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- PASO 3: DOCUMENTOS -->
                    <div v-show="currentStep === 3" class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Cédula de Identidad (Anverso) *</label>
                            <div @click="$refs.ciInput.click()" class="relative h-36 rounded-2xl border-2 border-dashed border-border flex flex-col items-center justify-center cursor-pointer hover:bg-muted/40 transition-all overflow-hidden" :class="{ 'border-red-500': form.errors.ci_front }">
                                <img v-if="ciPreview" :src="ciPreview" class="absolute inset-0 w-full h-full object-cover" />
                                <template v-else>
                                    <Camera class="text-muted-foreground mb-1.5" :size="24" />
                                    <span class="text-[10px] font-black uppercase text-muted-foreground">Subir Foto JPG/PNG</span>
                                </template>
                            </div>
                            <input type="file" ref="ciInput" class="hidden" @change="e => handleFileUpload(e, 'ci_front')" accept="image/jpeg, image/png, image/jpg" />
                            <p v-if="form.errors.ci_front" class="text-[10px] text-red-500 font-bold uppercase ml-1 mt-1">{{ form.errors.ci_front }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Licencia de Conducir (Vigente) *</label>
                            <div @click="$refs.licenseInput.click()" class="relative h-36 rounded-2xl border-2 border-dashed border-border flex flex-col items-center justify-center cursor-pointer hover:bg-muted/40 transition-all overflow-hidden" :class="{ 'border-red-500': form.errors.license_photo }">
                                <img v-if="licensePreview" :src="licensePreview" class="absolute inset-0 w-full h-full object-cover" />
                                <template v-else>
                                    <Upload class="text-muted-foreground mb-1.5" :size="24" />
                                    <span class="text-[10px] font-black uppercase text-muted-foreground">Subir Foto JPG/PNG</span>
                                </template>
                            </div>
                            <input type="file" ref="licenseInput" class="hidden" @change="e => handleFileUpload(e, 'license_photo')" accept="image/jpeg, image/png, image/jpg" />
                            <p v-if="form.errors.license_photo" class="text-[10px] text-red-500 font-bold uppercase ml-1 mt-1">{{ form.errors.license_photo }}</p>
                        </div>
                    </div>

                    <!-- PASO 4: AVATAR -->
                    <div v-show="currentStep === 4" class="space-y-6 text-center">
                        <div class="w-28 h-28 rounded-2xl bg-amber-500/10 border-2 border-amber-500/30 mx-auto p-2">
                            <img :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-contain" />
                        </div>
                        <div class="grid grid-cols-4 gap-3 max-w-sm mx-auto">
                            <button v-for="i in 8" :key="i" type="button" 
                                @click="form.avatar_source = `avatar_${i}.svg`"
                                class="aspect-square rounded-xl border-2 transition-all p-2 flex items-center justify-center"
                                :class="form.avatar_source === `avatar_${i}.svg` ? 'border-amber-500 bg-amber-500/10 scale-105' : 'border-border bg-muted/20 opacity-40 hover:opacity-100'">
                                <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-full h-full object-contain" />
                            </button>
                        </div>
                        <p class="text-[10px] font-black uppercase text-muted-foreground tracking-wider">Identidad Visual Asignada</p>
                    </div>
                </div>

                <!-- Botones de Navegación -->
                <div class="p-6 border-t border-border bg-muted/10">
                    <div class="flex gap-3">
                        <button v-if="currentStep > 1" @click="currentStep--" type="button" class="flex-1 h-12 rounded-xl border border-border font-black uppercase text-xs hover:bg-muted transition-colors">Atrás</button>
                        <Link v-else :href="route('driver.login')" class="flex-1 h-12 rounded-xl border border-border font-black uppercase text-xs flex items-center justify-center hover:bg-muted transition-colors">Login</Link>

                        <button v-if="currentStep < 4" @click="nextStep" :disabled="validatingStep1" type="button"
                            class="flex-[2] h-12 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-black uppercase text-xs tracking-wider shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 active:scale-95 transition-all disabled:opacity-50">
                            <span v-if="validatingStep1" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span v-else class="flex items-center gap-2">Siguiente <ArrowRight :size="14" /></span>
                        </button>

                        <button v-else @click="submit" :disabled="form.processing" type="button"
                            class="flex-[2] h-12 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-black uppercase text-xs tracking-wider shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 active:scale-95 transition-all disabled:opacity-50">
                            <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span v-else class="flex items-center gap-2">Enviar Solicitud <CheckCircle :size="14" /></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
:deep(.driver-tel-input) {
    height: 48px !important;
    background: transparent !important;
    border: 1px solid hsl(var(--border)) !important;
    border-radius: 0.75rem !important;
}
:deep(.driver-tel-input:focus-within) {
    border-color: hsl(var(--amber-500)) !important;
}
:deep(.driver-tel-input .vti__input) {
    background: transparent !important;
    color: currentColor !important;
    font-weight: 700 !important;
}
:deep(.driver-tel-input .vti__dropdown-list) {
    background-color: #15151e !important;
    border: 1px solid #32323b !important;
    color: #ffffff !important;
}
</style>
