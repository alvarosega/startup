<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Save, ArrowLeft, Package, Plus, Trash2, MapPin, 
    Image as ImageIcon, DollarSign, FileText, Layers, Upload,
    Cpu, Terminal, Wifi, WifiOff, AlertTriangle, CheckCircle2,
    Hash, Box, GitBranch, Zap, Eye, EyeOff, Info, Settings,
    Clock // <--- AÑADIR ESTE
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    skus: Object,
    branches: Array
});

const form = useForm({
    branch_id: '',
    name: '',
    description: '',
    fixed_price: '',
    is_active: true,
    image: null,
    items: [{ sku_id: '', quantity: 1 }],
    // --- NUEVOS CAMPOS TEMPORALES ---
    starts_at: '', 
    ends_at: ''
});

// --- LÓGICA IMAGEN ---
const imageInputRef = ref(null);
const imagePreview = ref(null);
const imageHover = ref(false);

const triggerImageInput = () => imageInputRef.value?.click();

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) { 
        form.image = file; 
        const reader = new FileReader(); 
        reader.onload = (ev) => imagePreview.value = ev.target.result; 
        reader.readAsDataURL(file); 
    }
};

const clearImage = () => { 
    form.image = null; 
    imagePreview.value = null; 
    if (imageInputRef.value) imageInputRef.value.value = ''; 
};

// --- LÓGICA ITEMS ---
const addItem = () => form.items.push({ sku_id: '', quantity: 1 });

const removeItem = (i) => {
    if (form.items.length > 1) {
        form.items.splice(i, 1);
    } else {
        form.items[0] = { sku_id: '', quantity: 1 };
    }
};

// --- UTILIDADES ---
const tempCode = computed(() => {
    return `PCK_NEW_${String(Math.floor(Math.random() * 1000)).padStart(3, '0')}`;
});

const itemCount = computed(() => {
    return form.items.filter(i => i.sku_id).length;
});

const submit = () => {
    form.post(route('admin.bundles.store'), { 
        forceFormData: true, 
        preserveScroll: true,
        onError: (errors) => {
            const card = document.getElementById('pack-card');
            card?.classList.add('shake');
            setTimeout(() => card?.classList.remove('shake'), 400);
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Crear Pack" />

        <div class="max-w-7xl mx-auto pb-40 md:pb-12 px-4 md:px-0">
            
            <!-- Header sticky -->
            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur-md border-b border-primary/30 px-4 py-4 mb-6 transition-all duration-300 group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <Link :href="route('admin.bundles.index')" 
                                  class="p-2 border border-border hover:border-primary hover:shadow-neon-primary transition-all relative group/back">
                                <ArrowLeft :size="20" class="group-hover/back:text-primary transition-colors" />
                                <!-- Esquinas decorativas -->
                                <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                                <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                                <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                                <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                            </Link>
                            
                            <div class="relative group/title">
                                <h1 class="text-xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_8px_hsl(var(--primary)/0.6)] leading-none"
                                    data-text="NUEVO PACK">
                                    NUEVO PACK
                                </h1>
                                <p class="text-[8px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                                    <Cpu :size="10" class="text-primary animate-pulse" />
                                    <span>{{ tempCode }} // CONFIGURACIÓN DE OFERTA</span>
                                    <Terminal :size="10" class="text-primary animate-pulse" />
                                </p>
                            </div>
                        </div>
                        
                        <!-- Toggle de estado -->
                        <div class="flex items-center gap-2 border border-primary/30 p-1 bg-background/50">
                            <span class="text-[8px] font-mono font-bold uppercase px-2 flex items-center gap-1"
                                  :class="form.is_active ? 'text-cyan-500' : 'text-destructive'">
                                <component :is="form.is_active ? Wifi : WifiOff" :size="10" />
                                {{ form.is_active ? 'VISIBLE' : 'OCULTO' }}
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.is_active" class="sr-only peer" />
                                <div class="w-10 h-5 border border-border/50 peer-checked:border-primary transition-all relative overflow-hidden">
                                    <div class="absolute inset-0 bg-primary/20 translate-x-[-100%] peer-checked:translate-x-0 transition-transform duration-300"></div>
                                </div>
                                <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-muted-foreground peer-checked:bg-primary peer-checked:translate-x-5 transition-all duration-300"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" id="pack-card" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Columna izquierda: Información Base -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="border border-border/50 bg-background shadow-2xl overflow-hidden relative group/card">
                        
                        <!-- Scanline superior -->
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                        
                        <!-- Efecto de glow -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 blur-3xl pointer-events-none"></div>
                        
                        <!-- Esquinas decorativas -->
                        <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                        <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                        <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                        <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                        <div class="p-4 border-b border-primary/30 bg-muted/10 flex items-center gap-2">
                            <Package :size="16" class="text-primary" />
                            <h3 class="text-[10px] font-mono font-bold text-primary uppercase tracking-wider">INFORMACIÓN BASE</h3>
                        </div>

                        <div class="p-5 space-y-5 relative z-10">
                            
                            <!-- Sucursal -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <MapPin :size="12" /> // SUCURSAL OBJETIVO <span class="text-destructive">*</span>
                                </label>
                                <div class="relative group/select">
                                    <MapPin :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/select:text-primary transition-colors" />
                                    <select v-model="form.branch_id" 
                                            class="w-full pl-10 pr-8 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all appearance-none"
                                            :class="{'border-destructive/50': form.errors.branch_id}">
                                        <option value="" disabled>SELECCIONAR UBICACIÓN...</option>
                                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1L5 5L9 1"/></svg>
                                    </div>
                                </div>
                                <p v-if="form.errors.branch_id" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.branch_id }}
                                </p>
                            </div>

                            <!-- Nombre del Pack -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Terminal :size="12" /> // NOMBRE DEL PACK
                                </label>
                                <div class="relative group/input">
                                    <Package :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/input:text-primary transition-colors" />
                                    <input v-model="form.name" 
                                           type="text" 
                                           class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-lg font-bold focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                           :class="{'border-destructive/50': form.errors.name}"
                                           placeholder="PACK FIN DE SEMANA" />
                                    <!-- Esquinas decorativas en focus -->
                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                </div>
                                <p v-if="form.errors.name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Imagen Promocional -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <ImageIcon :size="12" /> // IMAGEN PROMOCIONAL
                                </label>
                                <div @mouseenter="imageHover = true"
                                     @mouseleave="imageHover = false"
                                     @click="triggerImageInput" 
                                     class="w-full aspect-video border-2 border-dashed border-primary/30 bg-primary/5 flex flex-col items-center justify-center cursor-pointer hover:border-primary hover:bg-primary/10 transition-all group/image overflow-hidden relative">
                                    
                                    <img v-if="imagePreview" 
                                         :src="imagePreview" 
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover/image:scale-105" />
                                    
                                    <div v-else class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 border border-primary/30 flex items-center justify-center group-hover/image:scale-110 transition-transform">
                                            <Upload :size="20" class="text-primary/50 group-hover/image:text-primary" />
                                        </div>
                                        <span class="text-[8px] font-mono text-primary/50">CLICK PARA SUBIR</span>
                                    </div>
                                    
                                    <!-- Overlay de escaneo en hover -->
                                    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/10 to-transparent translate-y-[-100%] group-hover/image:translate-y-[100%] transition-transform duration-700"></div>
                                    
                                    <input ref="imageInputRef" type="file" accept="image/*" class="hidden" @change="handleImageChange" />
                                </div>
                                
                                <div v-if="imagePreview" class="flex justify-end">
                                    <button type="button" @click.stop="clearImage" 
                                            class="text-[8px] font-mono text-destructive hover:bg-destructive/10 px-2 py-1 border border-transparent hover:border-destructive/30 transition-all flex items-center gap-1">
                                        <Trash2 :size="10" /> ELIMINAR IMAGEN
                                    </button>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <FileText :size="12" /> // DESCRIPCIÓN
                                </label>
                                <div class="relative group/textarea">
                                    <FileText :size="14" class="absolute left-3 top-3 text-muted-foreground group-focus-within/textarea:text-primary transition-colors" />
                                    <textarea v-model="form.description" 
                                              rows="3" 
                                              class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all resize-none"
                                              placeholder="DETALLES DEL PACK..."></textarea>
                                </div>
                            </div>

                            <!-- Precio Fijo -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <DollarSign :size="12" /> // PRECIO FIJO
                                </label>
                                <div class="relative group/price">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-mono text-primary">BS</span>
                                    <input v-model="form.fixed_price" 
                                           type="number" step="0.01" 
                                           class="w-full pl-12 pr-4 py-3 bg-background border border-border/50 font-mono text-lg font-bold focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                           placeholder="0.00" />
                                </div>
                                <p class="text-[7px] font-mono text-muted-foreground mt-1">
                                    DEJAR VACÍO PARA SUMAR AUTOMÁTICAMENTE LOS ITEMS
                                </p>
                            </div>
                            <div class="space-y-4 border border-primary/20 p-4 bg-primary/5 relative group/timer">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Clock :size="12" /> // VIGENCIA DEL PACK (OPCIONAL)
                                </label>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[7px] font-mono text-muted-foreground uppercase ml-1">FECHA_INICIO</label>
                                        <input v-model="form.starts_at" 
                                            type="datetime-local" 
                                            class="w-full bg-background border border-border/50 px-3 py-2 font-mono text-[10px] focus:border-primary outline-none transition-all"
                                            :class="{'border-destructive/50': form.errors.starts_at}" />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[7px] font-mono text-muted-foreground uppercase ml-1">FECHA_EXPIRACIÓN</label>
                                        <input v-model="form.ends_at" 
                                            type="datetime-local" 
                                            class="w-full bg-background border border-border/50 px-3 py-2 font-mono text-[10px] focus:border-primary outline-none transition-all"
                                            :class="{'border-destructive/50': form.errors.ends_at}" />
                                    </div>
                                </div>
                                <p v-if="form.errors.ends_at" class="text-[8px] font-mono text-destructive mt-1">{{ form.errors.ends_at }}</p>
                                
                                <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                                <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                            </div>

                            <!-- Resumen del Pack (placeholder) -->
                            <div class="border border-primary/30 bg-primary/5 p-3 mt-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-[8px] font-mono text-primary uppercase">ITEMS</span>
                                    <span class="text-xs font-mono font-bold text-foreground">{{ itemCount }} / {{ form.items.length }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Contenido del Pack -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="border border-border/50 bg-background shadow-2xl h-full flex flex-col relative group/items">
                        
                        <!-- Scanline superior -->
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/items:translate-x-[100%] transition-transform duration-1000"></div>
                        
                        <!-- Esquinas decorativas -->
                        <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                        <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                        <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                        <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                        <div class="p-4 border-b border-primary/30 bg-muted/10 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <Layers :size="16" class="text-primary" />
                                <h3 class="text-[10px] font-mono font-bold text-primary uppercase tracking-wider">CONTENIDO DEL PACK</h3>
                            </div>
                            <span class="px-2 py-0.5 border border-primary/30 text-[8px] font-mono text-primary">
                                {{ form.items.length }} ITEMS
                            </span>
                        </div>

                        <div class="p-5 flex-1 space-y-4">
                            
                            <TransitionGroup name="list" tag="div" class="space-y-3">
                                <div v-for="(item, index) in form.items" :key="index" 
                                     class="border border-border/50 p-3 hover:border-primary/30 hover:shadow-neon-primary transition-all relative group/item">
                                    
                                    <!-- Scanline interna -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/item:translate-x-[100%] transition-transform duration-700"></div>
                                    
                                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center relative z-10">
                                        
                                        <!-- Selector de SKU -->
                                        <div class="flex-1 w-full sm:w-auto space-y-1">
                                            <label class="text-[7px] font-mono text-primary uppercase tracking-wider ml-1">// PRODUCTO</label>
                                            <div class="relative">
                                                <Box :size="12" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                                <select v-model="item.sku_id" 
                                                        class="w-full pl-10 pr-4 py-2 bg-background border border-border/50 font-mono text-xs focus:border-primary focus:shadow-neon-primary outline-none transition-all appearance-none"
                                                        :class="{'border-destructive/50': form.errors[`items.${index}.sku_id`]}">
                                                    <option value="" disabled>SELECCIONAR ITEM...</option>
                                                    <option v-for="sku in skus.data" :key="sku.id" :value="sku.id">
                                                        {{ sku.name }} ({{ sku.code }})
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Cantidad con controles -->
                                        <div class="w-full sm:w-32 flex flex-col space-y-1">
                                            <label class="text-[7px] font-mono text-primary uppercase tracking-wider ml-1 text-center">// CANT.</label>
                                            <div class="flex items-center">
                                                <button type="button" 
                                                        @click="item.quantity > 1 ? item.quantity-- : null" 
                                                        class="w-8 h-8 border border-r-0 border-border/50 bg-background hover:border-primary/50 hover:text-primary transition-all flex items-center justify-center">
                                                    -
                                                </button>
                                                <input v-model="item.quantity" 
                                                       type="number" min="1" 
                                                       class="w-full h-8 border-y border-border/50 text-center font-mono text-xs focus:outline-none focus:border-primary bg-background" />
                                                <button type="button" 
                                                        @click="item.quantity++" 
                                                        class="w-8 h-8 border border-l-0 border-border/50 bg-background hover:border-primary/50 hover:text-primary transition-all flex items-center justify-center">
                                                    +
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Botón eliminar -->
                                        <button type="button" @click="removeItem(index)" 
                                                class="w-8 h-8 border border-border/50 hover:border-destructive/30 hover:bg-destructive/5 hover:text-destructive transition-all flex items-center justify-center mt-auto sm:mt-5"
                                                :disabled="form.items.length === 1"
                                                :class="{'opacity-30 cursor-not-allowed': form.items.length === 1}">
                                            <Trash2 :size="14" />
                                        </button>
                                    </div>

                                    <!-- Esquinas -->
                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                                </div>
                            </TransitionGroup>

                            <!-- Botón Agregar -->
                            <button type="button" @click="addItem" 
                                    class="w-full py-4 border-2 border-dashed border-primary/30 hover:border-primary/50 hover:bg-primary/5 transition-all flex items-center justify-center gap-2 text-[10px] font-mono relative group/add">
                                <Plus :size="16" class="group-hover/add:rotate-90 transition-transform duration-300" />
                                AGREGAR OTRO PRODUCTO
                                <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/add:opacity-100"></span>
                                <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/add:opacity-100"></span>
                                <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/add:opacity-100"></span>
                                <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/add:opacity-100"></span>
                            </button>

                        </div>
                    </div>
                </div>

                <!-- Botón de submit -->
                <div class="lg:col-span-12 mt-4">
                    <button type="submit" :disabled="form.processing" 
                            class="w-full h-14 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase tracking-widest shadow-neon-primary hover:bg-primary/90 transition-all relative group/submit overflow-hidden">
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                            <Cpu :size="16" class="animate-spin" /> PROCESANDO...
                        </span>
                        <span v-else class="flex items-center justify-center gap-2 relative z-10">
                            <Save :size="16" /> GUARDAR PACK PROMOCIONAL
                        </span>
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/submit:translate-y-0 transition-transform duration-500"></span>
                        <!-- Esquinas -->
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </button>
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

/* Transiciones de lista */
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}
.list-leave-active {
    position: absolute;
    width: 100%;
}
</style>