<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Save, ArrowLeft, ShieldCheck, User, Truck, FileImage, 
    ExternalLink, AlertTriangle, CheckCircle2, Building2,
    Cpu, Terminal, Fingerprint, Phone, MapPin, Wifi, WifiOff,
    Bike, Car, Camera, Eye, EyeOff, Copy, ChevronRight
} from 'lucide-vue-next';

const props = defineProps({ 
    driver: Object,
    branches: Array
});

const showQrCode = ref(false);
const activeTab = ref('info'); // 'info', 'documents'

const form = useForm({
    branch_id: props.driver.branch_id || '',
    first_name: props.driver.details?.first_name || '',
    last_name: props.driver.details?.last_name || '',
    license_number: props.driver.details?.license_number || '',
    license_plate: props.driver.details?.license_plate || '',
    vehicle_type: props.driver.details?.vehicle_type || 'moto',
    
    // MAPEO DESDE LA COLUMNA STATUS
    is_identity_verified: props.driver.status === 'active',
    is_active: props.driver.status !== 'inactive'
});

const submit = () => {
    form.put(route('admin.drivers.update', props.driver.id), {
        preserveScroll: true,
    });
};

// Resuelve la URL pública del storage local
const getImageUrl = (path) => path ? `/storage/${path}` : null;

// Código del conductor
const driverCode = computed(() => {
    return `DRV_${String(props.driver.id).padStart(4, '0')}`;
});

// Estado de verificación
const verificationStatus = computed(() => {
    if (form.is_identity_verified) return { 
        icon: CheckCircle2, 
        class: 'text-cyan-500', 
        bg: 'bg-cyan-500/10', 
        border: 'border-cyan-500/30',
        label: 'IDENTIDAD VERIFICADA',
        badge: 'VERIFIED'
    };
    return { 
        icon: AlertTriangle, 
        class: 'text-warning', 
        bg: 'bg-warning/10', 
        border: 'border-warning/30',
        label: 'VERIFICACIÓN PENDIENTE',
        badge: 'PENDING'
    };
});

// Tipo de vehículo en texto
const vehicleTypeLabel = (type) => {
    const types = {
        moto: 'MOTOCICLETA',
        car: 'AUTOMÓVIL',
        truck: 'CAMIONETA'
    };
    return types[type] || type.toUpperCase();
};

// Icono de vehículo
const getVehicleIcon = (type) => {
    if (type === 'moto') return Bike;
    if (type === 'car') return Car;
    return Truck;
};
</script>

<template>
    <AdminLayout>
        <div class="px-4 md:px-6 lg:px-8 py-6 max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 border-b border-primary/30 pb-6 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="flex items-center gap-4 relative z-10">
                    <Link :href="route('admin.drivers.index')" 
                          class="p-2 border border-border hover:border-primary hover:shadow-neon-primary transition-all relative group/back">
                        <ArrowLeft :size="20" class="group-hover/back:text-primary transition-colors" />
                        <!-- Esquinas decorativas -->
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                    </Link>
                    
                    <div class="relative group/title">
                        <h1 class="text-2xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                            data-text="EDITAR CONDUCTOR">
                            EDITAR CONDUCTOR
                        </h1>
                        <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                            <Cpu :size="12" class="text-primary animate-pulse" />
                            <span>{{ driverCode }} // {{ driver.phone }}</span>
                            <Terminal :size="12" class="text-primary animate-pulse" />
                        </p>
                        <!-- Línea de escaneo -->
                        <div class="absolute -bottom-2 left-0 w-0 h-[1px] bg-primary group-hover/title:w-full transition-all duration-700"></div>
                    </div>
                </div>
                
                <button @click="submit" :disabled="form.processing" 
                        class="px-6 py-3 bg-primary text-primary-foreground font-mono text-xs border border-primary/50 relative overflow-hidden group/save">
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <Cpu :size="16" class="animate-spin" /> GUARDANDO...
                    </span>
                    <span v-else class="flex items-center gap-2 relative z-10">
                        <Save :size="16" /> GUARDAR CAMBIOS
                    </span>
                    <!-- Efecto scan -->
                    <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/save:translate-y-0 transition-transform duration-500"></span>
                    <!-- Esquinas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                </button>
            </div>

            <!-- Tabs de navegación -->
            <div class="flex gap-1 mb-6 border-b border-primary/30">
                <button @click="activeTab = 'info'"
                        class="px-6 py-3 text-[10px] font-mono font-bold transition-all relative group/tab"
                        :class="activeTab === 'info' ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-primary'">
                    DATOS DEL CONDUCTOR
                    <span class="absolute bottom-0 left-0 w-0 h-[1px] bg-primary group-hover/tab:w-full transition-all"></span>
                </button>
                <button @click="activeTab = 'documents'"
                        class="px-6 py-3 text-[10px] font-mono font-bold transition-all relative group/tab"
                        :class="activeTab === 'documents' ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-primary'">
                    DOCUMENTACIÓN
                    <span class="absolute bottom-0 left-0 w-0 h-[1px] bg-primary group-hover/tab:w-full transition-all"></span>
                </button>
            </div>

            <form @submit.prevent="submit">
                <!-- Tab: Información del Conductor -->
                <div v-if="activeTab === 'info'" class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-left-4 duration-500">
                    
                    <!-- Columna izquierda: Control de Acceso -->
                    <div class="space-y-6">
                        
                        <!-- Tarjeta de Control de Acceso -->
                        <div class="border transition-all duration-500 relative overflow-hidden group/access"
                             :class="[verificationStatus.border, verificationStatus.bg]">
                            
                            <!-- Scanline -->
                            <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/access:translate-x-[100%] transition-transform duration-1000"></div>
                            
                            <div class="p-6">
                                <h3 class="font-mono font-bold flex items-center gap-2 mb-4">
                                    <ShieldCheck :size="18" :class="verificationStatus.class" /> 
                                    <span :class="verificationStatus.class">CONTROL DE ACCESO</span>
                                </h3>
                                
                                <!-- Badge de estado -->
                                <div class="mb-6 inline-block px-3 py-1 border" :class="[verificationStatus.border, verificationStatus.class]">
                                    <span class="text-[10px] font-mono flex items-center gap-1">
                                        <component :is="verificationStatus.icon" :size="12" />
                                        {{ verificationStatus.badge }}
                                    </span>
                                </div>
                                
                                <p class="text-[10px] font-mono text-muted-foreground mb-6 leading-relaxed">
                                    AUDITA LOS DOCUMENTOS ADJUNTOS. SI LA INFORMACIÓN ES VERÍDICA, APRUEBA LA IDENTIDAD PARA HABILITAR AL CONDUCTOR EN EL SISTEMA DE DESPACHO.
                                </p>

                                <!-- Toggles -->
                                <div class="space-y-4">
                                    <!-- Verificación de Identidad -->
                                    <div class="border border-border/50 p-4 relative group/verified">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="block text-sm font-mono font-bold text-foreground">IDENTIDAD VERIFICADA</span>
                                                <span class="text-[8px] font-mono text-muted-foreground uppercase mt-1 flex items-center gap-1">
                                                    <Fingerprint :size="10" />
                                                    {{ form.is_identity_verified ? 'APROBADO' : 'PENDIENTE' }}
                                                </span>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer group/toggle">
                                                <input type="checkbox" v-model="form.is_identity_verified" class="sr-only peer" />
                                                <div class="w-12 h-6 border border-border/50 peer-checked:border-primary transition-all relative overflow-hidden">
                                                    <div class="absolute inset-0 bg-primary/20 translate-x-[-100%] peer-checked:translate-x-0 transition-transform duration-300"></div>
                                                </div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-muted-foreground peer-checked:bg-primary peer-checked:translate-x-6 transition-all duration-300"></div>
                                                <!-- Esquinas del toggle -->
                                                <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 peer-checked:opacity-100"></span>
                                                <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 peer-checked:opacity-100"></span>
                                                <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 peer-checked:opacity-100"></span>
                                                <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 peer-checked:opacity-100"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Cuenta Activa -->
                                    <div class="border border-border/50 p-4 relative group/active">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="block text-sm font-mono font-bold text-foreground">CUENTA ACTIVA</span>
                                                <span class="text-[8px] font-mono text-muted-foreground uppercase mt-1 flex items-center gap-1">
                                                    <component :is="form.is_active ? Wifi : WifiOff" :size="10" :class="form.is_active ? 'text-cyan-500' : 'text-destructive'" />
                                                    {{ form.is_active ? 'PERMITIDO' : 'BLOQUEADO' }}
                                                </span>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer group/toggle">
                                                <input type="checkbox" v-model="form.is_active" class="sr-only peer" />
                                                <div class="w-12 h-6 border border-border/50 peer-checked:border-primary transition-all relative overflow-hidden">
                                                    <div class="absolute inset-0 bg-primary/20 translate-x-[-100%] peer-checked:translate-x-0 transition-transform duration-300"></div>
                                                </div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-muted-foreground peer-checked:bg-primary peer-checked:translate-x-6 transition-all duration-300"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Esquinas decorativas -->
                            <span class="absolute top-0 left-0 w-2 h-2 border-t border-l border-primary/30"></span>
                            <span class="absolute top-0 right-0 w-2 h-2 border-t border-r border-primary/30"></span>
                            <span class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-primary/30"></span>
                            <span class="absolute bottom-0 right-0 w-2 h-2 border-b border-r border-primary/30"></span>
                        </div>
                    </div>

                    <!-- Columna derecha: Datos del Perfil (ocupa 2 columnas) -->
                    <div class="lg:col-span-2">
                        <div class="border border-border/50 p-6 relative group/profile h-full">
                            
                            <!-- Scanline -->
                            <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/profile:translate-x-[100%] transition-transform duration-1000"></div>
                            
                            <h3 class="font-mono font-bold flex items-center gap-2 mb-6 pb-3 border-b border-primary/30">
                                <User :size="18" class="text-primary" /> DATOS DEL PERFIL
                            </h3>

                            <div class="space-y-6">
                                <!-- Nombres y Apellidos -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                            <Terminal :size="12" /> // NOMBRES
                                        </label>
                                        <input v-model="form.first_name" 
                                               type="text" 
                                               class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                               placeholder="INGRESAR NOMBRES" />
                                        <p v-if="form.errors.first_name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                            <AlertTriangle :size="10" /> {{ form.errors.first_name }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                            <Terminal :size="12" /> // APELLIDOS
                                        </label>
                                        <input v-model="form.last_name" 
                                               type="text" 
                                               class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                               placeholder="INGRESAR APELLIDOS" />
                                        <p v-if="form.errors.last_name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                            <AlertTriangle :size="10" /> {{ form.errors.last_name }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Tipo de Vehículo y Sucursal -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                            <Truck :size="12" /> // TIPO DE VEHÍCULO
                                        </label>
                                        <select v-model="form.vehicle_type" 
                                                class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                                            <option value="moto">MOTOCICLETA</option>
                                            <option value="car">AUTOMÓVIL</option>
                                            <option value="truck">CAMIONETA</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                            <Building2 :size="12" /> // BASE / SUCURSAL
                                        </label>
                                        <select v-model="form.branch_id" 
                                                class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                                            <option value="">SIN BASE ASIGNADA</option>
                                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                                {{ branch.name }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.branch_id" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                            <AlertTriangle :size="10" /> {{ form.errors.branch_id }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Licencia y Placa -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                            <Fingerprint :size="12" /> // LICENCIA
                                        </label>
                                        <input v-model="form.license_number" 
                                               type="text" 
                                               class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                               placeholder="NÚMERO DE LICENCIA" />
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                            <MapPin :size="12" /> // PLACA
                                        </label>
                                        <input v-model="form.license_plate" 
                                               type="text" 
                                               class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                               placeholder="PLACA DEL VEHÍCULO" />
                                    </div>
                                </div>

                                <!-- Resumen de información -->
                                <div class="border border-dashed border-primary/30 p-4 bg-primary/5 mt-4">
                                    <div class="flex items-center gap-4">
                                        <div class="p-2 border border-primary/30 bg-background">
                                            <component :is="getVehicleIcon(form.vehicle_type)" :size="24" class="text-primary" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-mono text-primary">{{ vehicleTypeLabel(form.vehicle_type) }}</p>
                                            <p class="text-[8px] font-mono text-muted-foreground mt-1">
                                                {{ form.license_plate || 'SIN PLACA' }} // {{ form.license_number || 'SIN LICENCIA' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Esquinas decorativas -->
                            <span class="absolute top-0 left-0 w-2 h-2 border-t border-l border-primary/30"></span>
                            <span class="absolute top-0 right-0 w-2 h-2 border-t border-r border-primary/30"></span>
                            <span class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-primary/30"></span>
                            <span class="absolute bottom-0 right-0 w-2 h-2 border-b border-r border-primary/30"></span>
                        </div>
                    </div>
                </div>

                <!-- Tab: Documentación -->
                <div v-else-if="activeTab === 'documents'" class="animate-in fade-in slide-in-from-right-4 duration-500">
                    <div class="border border-border/50 p-6 relative group/documents">
                        
                        <!-- Scanline -->
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/documents:translate-x-[100%] transition-transform duration-1000"></div>
                        
                        <h3 class="font-mono font-bold flex items-center gap-2 mb-6 pb-3 border-b border-primary/30">
                            <FileImage :size="18" class="text-primary" /> EVIDENCIA DOCUMENTAL // SÓLO LECTURA
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Carnet de Identidad -->
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-mono font-bold text-primary uppercase flex items-center gap-1">
                                        <Camera :size="12" /> CARNET DE IDENTIDAD
                                    </span>
                                </div>
                                <div class="relative border-2 border-dashed border-primary/30 bg-background/50 aspect-video flex items-center justify-center overflow-hidden group/image">
                                    <img v-if="driver.details?.ci_front_path" 
                                         :src="getImageUrl(driver.details.ci_front_path)" 
                                         class="w-full h-full object-contain p-2" />
                                    
                                    <div v-else class="text-muted-foreground flex flex-col items-center p-4">
                                        <AlertTriangle :size="32" class="mb-2 text-warning/50" />
                                        <span class="text-[10px] font-mono">DOCUMENTO AUSENTE</span>
                                    </div>
                                    
                                    <a v-if="driver.details?.ci_front_path" 
                                       :href="getImageUrl(driver.details.ci_front_path)" 
                                       target="_blank" 
                                       class="absolute inset-0 bg-background/80 opacity-0 group-hover/image:opacity-100 flex items-center justify-center transition-opacity duration-300">
                                        <span class="px-4 py-2 border border-primary text-primary text-[10px] font-mono flex items-center gap-2 bg-background">
                                            <ExternalLink :size="14" /> VER ORIGINAL
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <!-- Licencia de Conducir -->
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-mono font-bold text-primary uppercase flex items-center gap-1">
                                        <Fingerprint :size="12" /> LICENCIA DE CONDUCIR
                                    </span>
                                    <span v-if="driver.details?.license_number" 
                                          class="text-[8px] font-mono border border-primary/30 px-2 py-0.5 text-primary">
                                        {{ driver.details.license_number }}
                                    </span>
                                </div>
                                <div class="relative border-2 border-dashed border-primary/30 bg-background/50 aspect-video flex items-center justify-center overflow-hidden group/image">
                                    <img v-if="driver.details?.license_photo_path" 
                                         :src="getImageUrl(driver.details.license_photo_path)" 
                                         class="w-full h-full object-contain p-2" />
                                    
                                    <div v-else class="text-muted-foreground flex flex-col items-center p-4">
                                        <AlertTriangle :size="32" class="mb-2 text-warning/50" />
                                        <span class="text-[10px] font-mono">DOCUMENTO AUSENTE</span>
                                    </div>
                                    
                                    <a v-if="driver.details?.license_photo_path" 
                                       :href="getImageUrl(driver.details.license_photo_path)" 
                                       target="_blank" 
                                       class="absolute inset-0 bg-background/80 opacity-0 group-hover/image:opacity-100 flex items-center justify-center transition-opacity duration-300">
                                        <span class="px-4 py-2 border border-primary text-primary text-[10px] font-mono flex items-center gap-2 bg-background">
                                            <ExternalLink :size="14" /> VER ORIGINAL
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <!-- Fotografía del Vehículo -->
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-mono font-bold text-primary uppercase flex items-center gap-1">
                                        <Truck :size="12" /> FOTOGRAFÍA DEL VEHÍCULO
                                    </span>
                                    <span v-if="driver.details?.license_plate" 
                                          class="text-[8px] font-mono border border-primary/30 px-2 py-0.5 text-primary">
                                        {{ driver.details.license_plate }}
                                    </span>
                                </div>
                                <div class="relative border-2 border-dashed border-primary/30 bg-background/50 aspect-video flex items-center justify-center overflow-hidden group/image">
                                    <img v-if="driver.details?.vehicle_photo_path" 
                                         :src="getImageUrl(driver.details.vehicle_photo_path)" 
                                         class="w-full h-full object-contain p-2" />
                                    
                                    <div v-else class="text-muted-foreground flex flex-col items-center p-4">
                                        <Camera :size="32" class="mb-2 text-muted-foreground/50" />
                                        <span class="text-[10px] font-mono">SIN IMAGEN</span>
                                    </div>
                                    
                                    <a v-if="driver.details?.vehicle_photo_path" 
                                       :href="getImageUrl(driver.details.vehicle_photo_path)" 
                                       target="_blank" 
                                       class="absolute inset-0 bg-background/80 opacity-0 group-hover/image:opacity-100 flex items-center justify-center transition-opacity duration-300">
                                        <span class="px-4 py-2 border border-primary text-primary text-[10px] font-mono flex items-center gap-2 bg-background">
                                            <ExternalLink :size="14" /> VER ORIGINAL
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Nota informativa -->
                        <div class="mt-6 p-4 border border-dashed border-primary/30 bg-primary/5">
                            <p class="text-[8px] font-mono text-muted-foreground flex items-center gap-2">
                                <Info :size="12" class="text-primary" />
                                LOS DOCUMENTOS SON DE SÓLO LECTURA. PARA ACTUALIZARLOS, EL CONDUCTOR DEBE SUBIR NUEVAS IMÁGENES DESDE LA APLICACIÓN MÓVIL.
                            </p>
                        </div>

                        <!-- Esquinas decorativas -->
                        <span class="absolute top-0 left-0 w-2 h-2 border-t border-l border-primary/30"></span>
                        <span class="absolute top-0 right-0 w-2 h-2 border-t border-r border-primary/30"></span>
                        <span class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-primary/30"></span>
                        <span class="absolute bottom-0 right-0 w-2 h-2 border-b border-r border-primary/30"></span>
                    </div>
                </div>
            </form>

            <!-- Session ID -->
            <div class="mt-8 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // {{ driverCode }}_EDIT // {{ new Date().toISOString().slice(0,10) }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Animaciones */
@keyframes scanline {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(1000%); }
}

.animate-scanline {
    animation: scanline 8s linear infinite;
}

/* Efecto glitch */
.glitch-text {
    position: relative;
    animation: glitch-skew 4s infinite linear alternate-reverse;
}

.glitch-text::before,
.glitch-text::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.8;
}

.glitch-text::before {
    color: #0ff;
    z-index: -1;
    animation: glitch-anim-1 0.4s infinite linear alternate-reverse;
}

.glitch-text::after {
    color: #f0f;
    z-index: -2;
    animation: glitch-anim-2 0.4s infinite linear alternate-reverse;
}

@keyframes glitch-skew {
    0% { transform: skew(0deg); }
    20% { transform: skew(0deg); }
    21% { transform: skew(2deg); }
    22% { transform: skew(0deg); }
    80% { transform: skew(0deg); }
    81% { transform: skew(-2deg); }
    82% { transform: skew(0deg); }
    100% { transform: skew(0deg); }
}

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); }
    20% { clip-path: inset(50% 0 10% 0); }
    40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(30% 0 40% 0); }
    100% { clip-path: inset(40% 0 20% 0); }
}

@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); }
    20% { clip-path: inset(20% 0 50% 0); }
    40% { clip-path: inset(70% 0 5% 0); }
    60% { clip-path: inset(10% 0 70% 0); }
    80% { clip-path: inset(40% 0 30% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}

/* Sombras neón */
.shadow-neon-primary {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.3);
}

/* Colores personalizados */
.text-warning { color: #ffaa00; }
.bg-warning { background-color: #ffaa00; }
.border-warning { border-color: #ffaa00; }

.text-cyan-500 { color: #00ffff; }
.bg-cyan-500 { background-color: #00ffff; }
.border-cyan-500 { border-color: #00ffff; }
</style>