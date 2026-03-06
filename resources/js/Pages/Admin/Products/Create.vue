<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Package, Info, ArrowRight, Save, UploadCloud, Wine, Tag, Layers,
    Cpu, Terminal, Wifi, WifiOff, AlertTriangle, CheckCircle2,
    Hash, ImageIcon, Box, FileText, Settings, Zap, Shield, 
    GitBranch // <--- AÑADIR ESTE
} from 'lucide-vue-next';
import axios from 'axios';
import debounce from 'lodash/debounce';

const props = defineProps({ brands: Array, categories: Array });

// 1. INICIALIZAR FORMULARIO
const form = useForm({
    name: '', 
    brand_id: '', 
    category_id: '', 
    description: '',
    image: null, 
    is_active: true, 
    is_alcoholic: false
});

// 2. REFS DE ESTADO
const isNameAvailable = ref(true);
const isCheckingName = ref(false);
const masterImagePreview = ref(null);
const selectedParentId = ref('');
const imageHover = ref(false);

// 3. LÓGICA DE VALIDACIÓN
const checkNameAvailability = debounce(async (name) => {
    if (name.length < 3) return;
    
    isCheckingName.value = true;
    try {
        const { data } = await axios.get(route('admin.products.check-name'), { params: { name } });
        isNameAvailable.value = data.available;
        
        if (!data.available) {
            form.setError('name', '// NOMBRE NO DISPONIBLE EN CATÁLOGO');
        } else {
            form.clearErrors('name');
        }
    } catch (e) {
        console.error("Error validando nombre");
    } finally {
        isCheckingName.value = false;
    }
}, 500);

// 4. WATCHERS
watch(() => form.name, (newVal) => {
    isNameAvailable.value = true;
    checkNameAvailability(newVal);
});

watch(selectedParentId, () => form.category_id = '');

const isShaking = ref(false);

// --- MODIFICAR el computed de subcategorías (Añadir null-safety) ---
const availableSubcategories = computed(() => {
    if (!selectedParentId.value) return [];
    const parent = props.categories.find(c => c.id === selectedParentId.value);
    // Blindaje técnico: Evita errores si la categoría padre no se encuentra
    return parent?.children || []; 
});
const isValidToContinue = computed(() => {
    return form.name && isNameAvailable.value && form.brand_id && form.category_id;
});

// 6. MÉTODOS
const handleImage = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.image = file;
    const reader = new FileReader();
    reader.onload = (ev) => masterImagePreview.value = ev.target.result;
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(route('admin.products.store'), {
        forceFormData: true,
        onError: (errors) => {
            console.error("VALIDATION_ERRORS:", errors);
            // Ejecutamos el shake mediante estado neón reactivo
            isShaking.value = true;
            setTimeout(() => isShaking.value = false, 500);
        }
    });
};
// Código temporal
const tempCode = computed(() => {
    return `PRD_NEW_${String(Math.floor(Math.random() * 1000)).padStart(3, '0')}`;
});
</script>

<template>
    <AdminLayout>
        <Head title="Nuevo Producto Maestro" />
        
        <div class="max-w-4xl mx-auto pb-12 px-4 md:px-0">
            
            <!-- Header -->
            <div class="mb-8 border-b border-primary/30 pb-6 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-[8px] font-mono border border-primary/30 bg-primary/5 text-primary px-2 py-1">
                            {{ tempCode }}
                        </span>
                        <span class="text-[8px] font-mono text-cyan-500 border border-cyan-500/30 bg-cyan-500/10 px-2 py-1 flex items-center gap-1">
                            <Wifi :size="10" /> ACTIVO POR DEFECTO
                        </span>
                    </div>
                    <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                        data-text="NUEVO PRODUCTO">
                        NUEVO PRODUCTO
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                        <Cpu :size="12" class="text-primary animate-pulse" />
                        PASO 1: CONFIGURACIÓN MAESTRA
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
            </div>

            <!-- Main Card -->
            <form @submit.prevent="submit">
                <div id="product-card" 
                    :class="{ 'shake': isShaking }" 
                    class="border border-border/50 bg-background shadow-2xl overflow-hidden relative group/card">
                    
                    <!-- Scanline superior -->
                    <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                    
                    <!-- Efecto de glow en esquinas -->
                    <div class="absolute top-0 left-0 w-20 h-20 bg-primary/5 blur-3xl"></div>
                    <div class="absolute bottom-0 right-0 w-20 h-20 bg-primary/5 blur-3xl"></div>
                    
                    <!-- Esquinas decorativas grandes -->
                    <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                    <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                    <div class="p-6 md:p-8 space-y-6 relative z-10">
                        <!-- Sección de Información Básica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre del Producto -->
                            <div class="md:col-span-2 space-y-1.5">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Terminal :size="12" /> // NOMBRE DEL PRODUCTO <span class="text-destructive">*</span>
                                </label>
                                <div class="relative group/input">
                                    <Package :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/input:text-primary transition-colors" />
                                    <input v-model="form.name" 
                                           type="text" 
                                           class="w-full pl-10 pr-12 py-3 bg-background border border-border/50 font-mono text-lg font-bold focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                           :class="{
                                               'border-destructive/50': form.errors.name || !isNameAvailable,
                                               'border-cyan-500/50': isNameAvailable && form.name
                                           }"
                                           placeholder="RON ABUELO 12 AÑOS" />
                                    
                                    <!-- Indicador de validación -->
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <span v-if="isCheckingName" class="text-primary animate-spin">⚡</span>
                                        <CheckCircle2 v-else-if="isNameAvailable && form.name" class="text-cyan-500" :size="16" />
                                        <AlertTriangle v-else-if="form.name && !isNameAvailable" class="text-warning" :size="16" />
                                    </div>
                                    
                                    <!-- Esquinas decorativas -->
                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                </div>
                                <p v-if="form.errors.name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Marca -->
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Tag :size="12" /> // MARCA <span class="text-destructive">*</span>
                                </label>
                                <select v-model="form.brand_id" 
                                        class="w-full px-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                        :class="{'border-destructive/50': form.errors.brand_id}">
                                    <option value="" disabled>SELECCIONAR...</option>
                                    <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                                <p v-if="form.errors.brand_id" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.brand_id }}
                                </p>
                            </div>

                            <!-- Categorías (grid de 2) -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                        <Layers :size="12" /> // CATEGORÍA
                                    </label>
                                    <select v-model="selectedParentId" 
                                            class="w-full px-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                                        <option value="" disabled>SELECCIONAR...</option>
                                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                        <GitBranch :size="12" /> // SUBCATEGORÍA <span class="text-destructive">*</span>
                                    </label>
                                    <select v-model="form.category_id" 
                                            :disabled="!selectedParentId"
                                            class="w-full px-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all disabled:opacity-50"
                                            :class="{'border-destructive/50': form.errors.category_id}">
                                        <option value="" disabled>SELECCIONAR...</option>
                                        <option v-for="sc in availableSubcategories" :key="sc.id" :value="sc.id">{{ sc.name }}</option>
                                    </select>
                                    <p v-if="form.errors.category_id" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                        <AlertTriangle :size="10" /> {{ form.errors.category_id }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Separador -->
                        <div class="border-t border-primary/30 my-6"></div>

                        <!-- Sección de Imagen y Descripción -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Imagen Principal -->
                            <div class="md:col-span-1">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-3 block">
                                    // IMAGEN PRINCIPAL
                                </label>
                                <div @mouseenter="imageHover = true"
                                     @mouseleave="imageHover = false"
                                     @click="$refs.imgInput.click()"
                                     class="aspect-square border-2 border-dashed border-primary/30 bg-primary/5 flex items-center justify-center cursor-pointer overflow-hidden relative group/image">
                                    
                                    <img v-if="masterImagePreview" 
                                         :src="masterImagePreview" 
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover/image:scale-110" />
                                    
                                    <div v-else class="flex flex-col items-center gap-2">
                                        <UploadCloud :size="32" class="text-primary/50 group-hover/image:text-primary transition-colors" />
                                        <span class="text-[8px] font-mono text-primary/50">CLICK PARA SUBIR</span>
                                    </div>
                                    
                                    <!-- Overlay de escaneo en hover -->
                                    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/10 to-transparent translate-y-[-100%] group-hover/image:translate-y-[100%] transition-transform duration-700"></div>
                                    
                                    <input ref="imgInput" type="file" class="hidden" accept="image/*" @change="handleImage">
                                </div>
                            </div>

                            <!-- Descripción y Toggles -->
                            <div class="md:col-span-2 space-y-4">
                                <!-- Descripción -->
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                        <FileText :size="12" /> // DESCRIPCIÓN GENERAL
                                    </label>
                                    <textarea v-model="form.description" 
                                              rows="4" 
                                              class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all resize-none"
                                              placeholder="DESCRIPCIÓN DEL PRODUCTO..."></textarea>
                                </div>

                                <!-- Toggles -->
                                <div class="flex gap-4">
                                    <!-- Alcohol Toggle -->
                                    <div @click="form.is_alcoholic = !form.is_alcoholic"
                                         class="flex-1 border p-3 cursor-pointer transition-all relative group/toggle"
                                         :class="form.is_alcoholic 
                                             ? 'border-warning/30 bg-warning/5' 
                                             : 'border-border/50 hover:border-primary/30'">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-mono font-bold uppercase flex items-center gap-2"
                                                  :class="form.is_alcoholic ? 'text-warning' : 'text-muted-foreground'">
                                                <Wine :size="14" />
                                                ALCOHOL
                                            </span>
                                            <div class="relative w-10 h-5 border border-border/50">
                                                <div class="absolute top-0.5 left-0.5 w-4 h-4 transition-all duration-300"
                                                     :class="form.is_alcoholic 
                                                         ? 'translate-x-5 bg-warning shadow-[0_0_8px_hsl(var(--warning))]' 
                                                         : 'translate-x-0 bg-muted-foreground'"></div>
                                            </div>
                                        </div>
                                        <!-- Esquinas -->
                                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-warning/50 opacity-0 group-hover/toggle:opacity-100"></span>
                                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-warning/50 opacity-0 group-hover/toggle:opacity-100"></span>
                                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-warning/50 opacity-0 group-hover/toggle:opacity-100"></span>
                                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-warning/50 opacity-0 group-hover/toggle:opacity-100"></span>
                                    </div>

                                    <!-- Activo Toggle -->
                                    <div @click="form.is_active = !form.is_active"
                                         class="flex-1 border p-3 cursor-pointer transition-all relative group/toggle"
                                         :class="form.is_active 
                                             ? 'border-cyan-500/30 bg-cyan-500/5' 
                                             : 'border-border/50 hover:border-primary/30'">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-mono font-bold uppercase flex items-center gap-2"
                                                  :class="form.is_active ? 'text-cyan-500' : 'text-muted-foreground'">
                                                <component :is="form.is_active ? Wifi : WifiOff" :size="14" />
                                                {{ form.is_active ? 'ACTIVO' : 'INACTIVO' }}
                                            </span>
                                            <div class="relative w-10 h-5 border border-border/50">
                                                <div class="absolute top-0.5 left-0.5 w-4 h-4 transition-all duration-300"
                                                     :class="form.is_active 
                                                         ? 'translate-x-5 bg-cyan-500 shadow-[0_0_8px_hsl(var(--primary))]' 
                                                         : 'translate-x-0 bg-muted-foreground'"></div>
                                            </div>
                                        </div>
                                        <!-- Esquinas -->
                                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-cyan-500/50 opacity-0 group-hover/toggle:opacity-100"></span>
                                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-cyan-500/50 opacity-0 group-hover/toggle:opacity-100"></span>
                                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-cyan-500/50 opacity-0 group-hover/toggle:opacity-100"></span>
                                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-cyan-500/50 opacity-0 group-hover/toggle:opacity-100"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="p-6 bg-background/80 backdrop-blur-sm border-t border-primary/30 flex justify-between items-center">
                        <Link :href="route('admin.products.index')" 
                              class="px-6 py-2 border border-destructive/50 text-destructive font-mono text-xs hover:bg-destructive hover:text-destructive-foreground transition-all relative group/cancel">
                            CANCELAR
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                        </Link>

                        <button type="submit" 
                                :disabled="form.processing || !isValidToContinue"
                                class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/submit overflow-hidden disabled:opacity-30 disabled:pointer-events-none">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <Cpu :size="14" class="animate-spin" /> PROCESANDO...
                            </span>
                            <span v-else class="flex items-center gap-2 relative z-10">
                                CONTINUAR A VARIANTES <ArrowRight :size="14" class="ml-2 group-hover/submit:translate-x-1 transition-transform" />
                            </span>
                            <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/submit:translate-y-0 transition-transform duration-500"></span>
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Session ID -->
            <div class="mt-4 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // {{ tempCode }}_CREATE // {{ new Date().toISOString().slice(0,10) }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Animaciones */
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}

.shake {
    animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
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
</style>