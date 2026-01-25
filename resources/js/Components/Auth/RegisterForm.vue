<script setup>
    import { ref, watch, nextTick, computed } from 'vue'; 
    import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
    import { useForm } from '@inertiajs/vue3';
    import axios from 'axios';
    import { VueTelInput } from 'vue-tel-input';
    import 'vue-tel-input/vue-tel-input.css';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import { Mail } from 'lucide-vue-next';
    import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
    import { 
        Crosshair, MapPin, UserPlus, Smartphone, 
        Lock, CheckCircle, Upload, Truck 
    } from 'lucide-vue-next';
    
    // Definir Emits para comunicarse con el ShopLayout
        const emit = defineEmits(['close', 'switchToLogin', 'switchToDriver']);
    // Props recibidas del Layout
    const props = defineProps({
        activeBranches: { type: Array, default: () => [] }
    });
    
    // --- ESTADO ---
    const currentStep = ref(1);
    const step1Errors = ref({});
    const mapComponentRef = ref(null); 
    const validatingStep1 = ref(false);
    const customPreview = ref(null);
    const locating = ref(false);
    
    const steps = [
        { id: 1, title: 'Cuenta', icon: UserPlus },
        { id: 2, title: 'Avatar', icon: UserPlus },
        { id: 3, title: 'Ubicación', icon: MapPin },
    ];
    
    const form = useForm({
        // Datos Cuenta
        phone: '', 
        email: '', 
        password: '', 
        password_confirmation: '', 
        terms: false,
        // Avatar
        avatar_type: 'icon',
        avatar_source: 'avatar_1.svg',
        avatar_file: null,
        // Ubicación
        alias: 'Mi Ubicación', 
        address: '', 
        details: '', 
        latitude: -16.5000, 
        longitude: -68.1500, 
        branch_id: null, 
        role: 'client'
    });
    
    // Configuración Teléfono
    const telOptions = { 
        mode: 'international', 
        defaultCountry: 'BO', 
        dropdownOptions: { showSearchBox: true, showFlags: true, showDialCodeInSelection: true }, 
        inputOptions: { placeholder: '77712345', required: true } 
    };
    
    const onInput = (phone, obj) => { 
        if(obj?.number) form.phone = obj.number; 
        if (step1Errors.value.phone) delete step1Errors.value.phone;
    };
    
    // --- NAVEGACIÓN ---
    
    // Validar Paso 1 (Backend)
    const nextStep = async () => {
        if (currentStep.value === 1) {
            if (!form.terms) {
                alert('Debes aceptar los términos y condiciones');
                return;
            }
            
            step1Errors.value = {};
            validatingStep1.value = true;
            
            try {
                await axios.post(route('register.validate-step-1'), {
                    phone: form.phone, 
                    email: form.email, 
                    password: form.password, 
                    password_confirmation: form.password_confirmation
                });
                currentStep.value = 2; // Ir a Avatar
            } catch (error) {
                if (error.response?.status === 422) {
                    step1Errors.value = error.response.data.errors;
                }
            } finally {
                validatingStep1.value = false;
            }
        } else if (currentStep.value === 2) {
            currentStep.value = 3; // Ir a Mapa
        }
    };
    
    const prevStep = () => {
        if (currentStep.value > 1) currentStep.value--;
    };
    
    // --- LOGICA AVATAR ---
    const selectIcon = (iconName) => { 
        form.avatar_type = 'icon'; 
        form.avatar_source = iconName; 
        form.avatar_file = null; 
        customPreview.value = null; 
    };
    
    const uploadCustom = (e) => { 
        const file = e.target.files[0]; 
        if (file) { 
            form.avatar_type = 'custom'; 
            form.avatar_file = file; 
            customPreview.value = URL.createObjectURL(file); 
        } 
    };
    
    // --- LOGICA MAPA ---
    // Refrescar mapa al entrar al paso 3
    watch(currentStep, (val) => {
        if (val === 3) {
            setTimeout(() => {
                if (mapComponentRef.value && typeof mapComponentRef.value.refreshMap === 'function') {
                    mapComponentRef.value.refreshMap();
                }
            }, 350); 
        }
    });
    
    const getMyLocation = () => {
        if (!navigator.geolocation) {
            alert('Tu navegador no soporta geolocalización.');
            return;
        }
        
        locating.value = true;
        form.address = "Obteniendo ubicación...";
        
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                form.latitude = pos.coords.latitude;
                form.longitude = pos.coords.longitude;
                locating.value = false;
            },
            (err) => { 
                console.error(err); 
                locating.value = false; 
                form.address = "";
                alert('No se pudo obtener tu ubicación. Por favor, selecciona manualmente en el mapa.');
            },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    };
    
    // --- ENVÍO CORREGIDO ---
    const submit = () => {
        // Fallback de dirección
        if (!form.address || form.address.trim() === '') {
            form.address = `Ubicación GPS (${form.latitude.toFixed(5)}, ${form.longitude.toFixed(5)})`;
        }
    
        form.post(route('register'), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
            onError: (errors) => {
                if (errors.branch_id) {
                    alert('Por favor selecciona una ubicación válida (dentro de cobertura).');
                }
            }
        });
    };
    
    // ✅ DEFINIDO CORRECTAMENTE
    const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
    </script>

    <template>
        <div class="h-full flex flex-col p-6 bg-card w-full relative">
            
            <!-- PROGRESS BAR -->
            <div class="absolute top-0 left-0 w-full h-1.5 bg-muted">
                <div class="h-full bg-gradient-to-r from-primary to-secondary transition-all duration-base ease-smooth" 
                     :style="{ width: progressPercentage + '%' }">
                </div>
            </div>
    
            <!-- HEADER -->
            <div class="text-center mb-8 mt-2">
                <div class="avatar avatar-lg bg-gradient-to-br from-primary to-secondary text-primary-foreground mx-auto mb-4 shadow-lg">
                    <component :is="steps[currentStep - 1].icon" :size="24" />
                </div>
                <h2 class="text-xl font-display font-black text-foreground uppercase italic leading-none">
                    <span v-if="currentStep === 1">Crea tu Cuenta</span>
                    <span v-else-if="currentStep === 2">Elige tu Avatar</span>
                    <span v-else>Tu Ubicación</span>
                </h2>
                <p class="text-xs text-muted-foreground font-bold uppercase tracking-wider mt-1">
                    Paso {{ currentStep }} de {{ steps.length }}
                </p>
            </div>
    
            <!-- CONTENT -->
            <div class="flex-1 overflow-y-auto scrollbar-thin px-1">
                <form @submit.prevent="submit" class="h-full flex flex-col">
                    
                    <!-- STEP 1: CUENTA -->
                    <div v-show="currentStep === 1" class="space-y-6 animate-in">
                        <!-- PHONE INPUT -->
                        <div class="form-group">
                            <label class="form-label flex items-center gap-2">
                                <Smartphone :size="14" />
                                Celular *
                            </label>
                            <vue-tel-input 
                                v-bind="telOptions"
                                v-model="form.phone" 
                                @on-input="onInput" 
                                class="custom-tel-input" 
                                :class="{'form-input-error': step1Errors.phone}"
                            />
                            <p v-if="step1Errors.phone" class="form-error">
                                {{ step1Errors.phone[0] }}
                            </p>
                        </div>
                        <div class="form-group">
                            <BaseInput v-model="form.email" 
                                    type="email" 
                                    label="Correo Electrónico *" 
                                    placeholder="ejemplo@email.com"
                                    :error="step1Errors.email ? step1Errors.email[0] : ''">
                                <template #icon>
                                    <Mail :size="14" />
                                </template>
                            </BaseInput>
                            <p class="text-[10px] text-muted-foreground mt-1 ml-1">
                                Lo usarás para recuperar tu cuenta si olvidas la contraseña.
                            </p>
                        </div>
    
                        <!-- PASSWORDS -->
                        <div class="space-y-4">
                            <div class="form-group">
                                <BaseInput v-model="form.password" 
                                           type="password" 
                                           label="Contraseña *" 
                                           placeholder="Mínimo 8 caracteres"
                                           :error="step1Errors.password ? step1Errors.password[0] : ''">
                                    <template #icon>
                                        <Lock :size="14" />
                                    </template>
                                </BaseInput>
                            </div>
                            <div class="form-group">
                                <BaseInput v-model="form.password_confirmation" 
                                           type="password" 
                                           label="Confirmar Contraseña *">
                                    <template #icon>
                                        <Lock :size="14" />
                                    </template>
                                </BaseInput>
                            </div>
                        </div>
    
                        <!-- TERMS -->
                        <div class="alert alert-info">
                            <BaseCheckbox v-model="form.terms" class="w-full">
                                <template #default>
                                    Acepto 
                                    <a href="#" class="font-bold text-primary hover:underline">Términos</a> 
                                    y 
                                    <a href="#" class="font-bold text-primary hover:underline">Privacidad</a>
                                </template>
                            </BaseCheckbox>
                        </div>
    
                        <!-- DRIVER LINK -->
                        <div class="text-center pt-2">
                            <button type="button" 
                                    @click="$emit('switchToDriver')"
                                    class="text-xs text-muted-foreground font-bold uppercase hover:text-primary transition flex items-center justify-center gap-2 w-full">
                                <Truck :size="14" />
                                ¿Eres conductor? Regístrate aquí
                            </button>
                        </div>
                    </div>
    
                    <!-- STEP 2: AVATAR -->
                    <div v-show="currentStep === 2" class="space-y-6 animate-in">
                        <div class="text-center">
                            <p class="text-sm text-muted-foreground mb-4">
                                Selecciona un icono o sube tu foto
                            </p>
                            
                            <!-- ICON GRID -->
                            <div class="grid grid-cols-3 gap-4 mb-6 justify-items-center px-4">
                                <div v-for="i in 6" :key="i" 
                                     @click="selectIcon(`avatar_${i}.svg`)" 
                                     :class="[
                                         'cursor-pointer w-16 h-16 rounded-full border-2 transition-all flex justify-center items-center bg-muted/30',
                                         (form.avatar_type === 'icon' && form.avatar_source === `avatar_${i}.svg`) 
                                             ? 'border-primary ring-4 ring-primary/20 scale-105 hover-lift' 
                                             : 'border-transparent hover:border-border hover-lift'
                                     ]">
                                    <img :src="`/assets/avatars/avatar_${i}.svg`" 
                                         class="w-12 h-12" 
                                         :alt="`Avatar ${i}`" />
                                </div>
                            </div>
    
                            <!-- CUSTOM UPLOAD -->
                            <div class="relative group cursor-pointer inline-block">
                                <input type="file" 
                                       @change="uploadCustom" 
                                       class="hidden" 
                                       id="avatar_upload" 
                                       accept="image/*" />
                                <label for="avatar_upload" 
                                       class="flex flex-col items-center gap-3 cursor-pointer">
                                    <div class="w-20 h-20 rounded-full bg-muted/30 border-2 border-dashed border-border flex items-center justify-center overflow-hidden group-hover:border-primary transition hover-lift">
                                        <img v-if="customPreview" 
                                             :src="customPreview" 
                                             class="w-full h-full object-cover" />
                                        <Upload v-else :size="24" class="text-muted-foreground" />
                                    </div>
                                    <span class="text-xs font-bold uppercase text-primary group-hover:underline">
                                        Subir Foto Personal
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
    
                    <!-- STEP 3: UBICACIÓN -->
                    <div v-show="currentStep === 3" class="flex-1 flex flex-col h-full animate-in">
                        <!-- HEADER -->
                        <div class="flex justify-between items-end mb-3 shrink-0">
                            <label class="form-label">
                                Ubicación de Entrega
                            </label>
                            <button type="button" 
                                    @click="getMyLocation" 
                                    :disabled="locating"
                                    class="btn btn-outline btn-sm flex items-center gap-2">
                                <Crosshair v-if="!locating" :size="12" />
                                <span v-if="locating" class="spinner spinner-sm mr-1"></span>
                                <span>{{ locating ? 'Buscando...' : 'Usar mi GPS' }}</span>
                            </button>
                        </div>
    
                        <!-- MAP -->
                        <div class="w-full h-64 rounded-xl overflow-hidden border border-border relative bg-muted/30 shrink-0">
                            <ClientLocationPicker
                                ref="mapComponentRef" 
                                v-model:modelValueLat="form.latitude"
                                v-model:modelValueLng="form.longitude"
                                v-model:modelValueAddress="form.address"
                                v-model:modelValueBranchId="form.branch_id"
                                :activeBranches="props.activeBranches"
                            />
                        </div>
    
                        <!-- FORM FIELDS -->
                        <div class="mt-6 space-y-4 pb-2 flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <BaseInput v-model="form.alias" 
                                           label="Alias" 
                                           placeholder="Ej: Casa, Oficina" />
                                <BaseInput v-model="form.details" 
                                           label="Referencia" 
                                           placeholder="Ej: Portón rojo, Piso 3" />
                            </div>
                            
                            <!-- ERROR MESSAGE -->
                            <p v-if="form.errors.branch_id" 
                               class="alert alert-error text-center animate-in">
                                ⚠️ Mueve el pin a una zona con cobertura disponible
                            </p>
    
                            <!-- NAVIGATION BUTTONS -->
                            <div class="flex gap-3 pt-4 mt-auto">
                                <button type="button" 
                                        @click="prevStep" 
                                        class="btn btn-outline btn-md flex-1">
                                    Atrás
                                </button>
                                <button type="submit" 
                                        :disabled="form.processing"
                                        class="btn btn-primary btn-md flex-1 hover-lift">
                                    <span v-if="form.processing" 
                                          class="spinner spinner-sm mr-2">
                                    </span>
                                    <span v-else class="flex items-center justify-center gap-2">
                                        <CheckCircle :size="16" />
                                        Finalizar Registro
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
    
            <!-- FOOTER NAVIGATION -->
            <div class="mt-6 pt-6 border-t border-border">
                <!-- STEP NAVIGATION -->
                <div v-if="currentStep < 3" class="flex gap-3 mb-4">
                    <button v-if="currentStep > 1" 
                            type="button" 
                            @click="prevStep" 
                            class="btn btn-outline btn-md flex-1">
                        Atrás
                    </button>
    
                    <button type="button" 
                            @click="nextStep" 
                            :disabled="currentStep === 1 && !form.terms"
                            class="btn btn-primary btn-md flex-1 hover-lift">
                        <span v-if="validatingStep1" 
                              class="spinner spinner-sm mr-2">
                        </span>
                        Siguiente Paso
                    </button>
                </div>
    
                <!-- LOGIN LINK -->
                <div class="text-center">
                    <button @click="$emit('switchToLogin')" 
                            class="btn btn-ghost btn-sm">
                        ¿Ya tienes cuenta? Inicia Sesión
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <style scoped>
    .custom-tel-input { 
        border: 1px solid hsl(var(--input));
        border-radius: var(--radius-lg);
        padding: 2px 0;
        background: hsl(var(--background));
        transition: all var(--duration-fast) var(--ease-smooth);
    }
    
    .custom-tel-input:focus-within {
        border-color: hsl(var(--ring));
        box-shadow: 0 0 0 2px hsl(var(--ring) / 0.2);
    }
    
    .custom-tel-input.form-input-error {
        border-color: hsl(var(--error));
    }
    
    .custom-tel-input.form-input-error:focus-within {
        border-color: hsl(var(--error));
        box-shadow: 0 0 0 2px hsl(var(--error) / 0.2);
    }
    </style>