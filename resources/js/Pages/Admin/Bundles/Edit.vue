<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Save, ArrowLeft, Package, Plus, Trash2, MapPin, 
    Image as ImageIcon, DollarSign, FileText, Layers, Upload,
    Cpu, Terminal, Wifi, WifiOff, AlertTriangle, CheckCircle2,
    Hash, Box, GitBranch, Zap, Eye, EyeOff, Info, Settings, Clock
} from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    bundle: Object,
    skus: Object,
    branches: Array
});

// DESEMPAQUETADO SEGURO (La Ley 2.0)
const bundleData = computed(() => props.bundle.data || props.bundle);
const skuList = computed(() => props.skus?.data || props.skus || []);

const form = useForm({
    _method: 'PUT',
    branch_id: bundleData.value.branch_id || '',
    name: bundleData.value.name,
    description: bundleData.value.description,
    fixed_price: bundleData.value.fixed_price,
    is_active: Boolean(bundleData.value.is_active),
    image: null,
    items: bundleData.value.items.map(i => ({ sku_id: i.sku_id, quantity: i.quantity })),
    // REINCORPORACIÓN DE CAMPOS DE VIGENCIA
    starts_at: bundleData.value.starts_at ? bundleData.value.starts_at.slice(0, 16) : '', 
    ends_at: bundleData.value.ends_at ? bundleData.value.ends_at.slice(0, 16) : ''
});

if (form.items.length === 0) form.items.push({ sku_id: '', quantity: 1 });

// --- LÓGICA IMAGEN ---
const imageInputRef = ref(null);
const imagePreview = ref(null);
const imageHover = ref(false);

onMounted(() => { 
    if (bundleData.value.image_path) {
        imagePreview.value = bundleData.value.image_path.startsWith('http') 
            ? bundleData.value.image_path 
            : `/storage/${bundleData.value.image_path}`; 
    }
});

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
const packCode = computed(() => {
    return `PCK_${String(bundleData.value.id).substring(0, 8).toUpperCase()}`;
});

const totalPrice = computed(() => {
    if (form.fixed_price) return parseFloat(form.fixed_price).toFixed(2);
    return 'DINÁMICO';
});

const itemCount = computed(() => {
    return form.items.filter(i => i.sku_id).length;
});

const submit = () => {
    form.post(route('admin.bundles.update', bundleData.value.id), { 
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
        <Head title="Editar Pack" />

        <div class="max-w-7xl mx-auto pb-40 md:pb-12 px-4 md:px-0">
            
            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur-md border-b border-primary/30 px-4 py-4 mb-6 transition-all duration-300 group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.bundles.index')" class="p-2 border border-border hover:border-primary transition-all relative group/back">
                            <ArrowLeft :size="20" />
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                        </Link>
                        
                        <div>
                            <h1 class="text-xl font-display font-black tracking-widest text-primary uppercase glitch-text" :data-text="'EDITAR PACK: ' + bundleData.name">
                                EDITAR PACK
                            </h1>
                            <p class="text-[8px] font-mono text-muted-foreground mt-1 flex items-center gap-2 uppercase">
                                <Cpu :size="10" class="text-primary animate-pulse" />
                                <span>{{ packCode }} // {{ bundleData.name }}</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 border border-primary/30 p-1 bg-background/50">
                        <span class="text-[8px] font-mono font-bold uppercase px-2" :class="form.is_active ? 'text-cyan-500' : 'text-destructive'">
                            {{ form.is_active ? 'ONLINE' : 'OFFLINE' }}
                        </span>
                        <input type="checkbox" v-model="form.is_active" class="sr-only peer" id="active-toggle" />
                        <label for="active-toggle" class="w-10 h-5 border border-border/50 peer-checked:border-primary transition-all relative cursor-pointer">
                            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-muted-foreground peer-checked:bg-primary peer-checked:translate-x-5 transition-all"></div>
                        </label>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" id="pack-card" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <div class="lg:col-span-5 space-y-6">
                    <div class="border border-border/50 bg-background shadow-2xl relative p-5 space-y-5">
                        <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                        <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block flex items-center gap-1">
                                <MapPin :size="12" /> // SUCURSAL ASIGNADA
                            </label>
                            <select v-model="form.branch_id" class="w-full pl-4 pr-8 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary outline-none transition-all appearance-none uppercase">
                                <option value="" disabled>SELECCIONAR UBICACIÓN...</option>
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block flex items-center gap-1">
                                <Terminal :size="12" /> // NOMBRE DEL PACK
                            </label>
                            <input v-model="form.name" type="text" class="w-full px-4 py-3 bg-background border border-border/50 font-mono text-lg font-bold focus:border-primary outline-none transition-all uppercase" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block flex items-center gap-1">
                                <ImageIcon :size="12" /> // IMAGEN PROMOCIONAL
                            </label>
                            <div @click="triggerImageInput" class="w-full aspect-video border-2 border-dashed border-primary/30 bg-primary/5 flex items-center justify-center cursor-pointer hover:bg-primary/10 transition-all overflow-hidden relative group">
                                <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                                <div v-else class="text-center">
                                    <Upload :size="20" class="mx-auto text-primary/50" />
                                    <span class="text-[8px] font-mono text-primary/50">SUBIR NUEVA</span>
                                </div>
                                <input ref="imageInputRef" type="file" accept="image/*" class="hidden" @change="handleImageChange" />
                            </div>
                        </div>

                        <div class="space-y-4 border border-primary/20 p-4 bg-primary/5 relative">
                            <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block flex items-center gap-1">
                                <Clock :size="12" /> // VENTANA DE VIGENCIA
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[7px] font-mono text-muted-foreground uppercase">INICIO</label>
                                    <input v-model="form.starts_at" type="datetime-local" class="w-full bg-background border border-border/50 px-2 py-1.5 font-mono text-[10px] focus:border-primary outline-none" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[7px] font-mono text-muted-foreground uppercase">CIERRE</label>
                                    <input v-model="form.ends_at" type="datetime-local" class="w-full bg-background border border-border/50 px-2 py-1.5 font-mono text-[10px] focus:border-primary outline-none" />
                                </div>
                            </div>
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block flex items-center gap-1">
                                <DollarSign :size="12" /> // PRECIO FIJO (BS)
                            </label>
                            <input v-model="form.fixed_price" type="number" step="0.01" class="w-full px-4 py-3 bg-background border border-border/50 font-mono text-lg font-bold focus:border-primary outline-none text-cyan-500" placeholder="DINÁMICO" />
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-6">
                    <div class="border border-border/50 bg-background shadow-2xl h-full flex flex-col relative group/items">
                        <div class="p-4 border-b border-primary/30 bg-muted/10 flex justify-between items-center">
                            <h3 class="text-[10px] font-mono font-bold text-primary uppercase tracking-wider flex items-center gap-2">
                                <Layers :size="16" /> CONTENIDO DEL PACK
                            </h3>
                        </div>

                        <div class="p-5 flex-1 space-y-3 overflow-y-auto">
                            <div v-for="(item, index) in form.items" :key="index" class="border border-border/50 p-3 relative flex flex-col sm:flex-row gap-3 items-center bg-primary/5">
                                <div class="flex-1 w-full">
                                    <label class="text-[7px] font-mono text-primary uppercase ml-1">// PRODUCTO</label>
                                    <select v-model="item.sku_id" class="w-full pl-3 pr-8 py-2 bg-background border border-border/50 font-mono text-xs focus:border-primary outline-none uppercase">
                                        <option value="" disabled>SELECCIONAR ITEM...</option>
                                        <option v-for="sku in skuList" :key="sku.id" :value="sku.id">
                                            {{ sku.name }} ({{ sku.code }})
                                        </option>
                                    </select>
                                </div>

                                <div class="w-full sm:w-24">
                                    <label class="text-[7px] font-mono text-primary uppercase text-center block">// CANT.</label>
                                    <input v-model="item.quantity" type="number" min="1" class="w-full py-2 bg-background border border-border/50 text-center font-mono text-xs focus:border-primary outline-none" />
                                </div>

                                <button type="button" @click="removeItem(index)" class="p-2 text-destructive hover:bg-destructive/10 transition-all mt-3 sm:mt-4" :disabled="form.items.length === 1">
                                    <Trash2 :size="14" />
                                </button>
                                <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                            </div>

                            <button type="button" @click="addItem" class="w-full py-4 border-2 border-dashed border-primary/30 hover:border-primary/50 hover:bg-primary/5 transition-all text-[10px] font-mono flex items-center justify-center gap-2">
                                <Plus :size="16" /> AÑADIR OTRO PRODUCTO
                            </button>
                        </div>

                        <div class="p-4 bg-primary/10 border-t border-primary/30 flex justify-between items-center">
                            <span class="text-[8px] font-mono text-primary uppercase font-bold">TOTAL ESTIMADO</span>
                            <span class="text-sm font-mono font-black text-cyan-500 uppercase">{{ totalPrice }} {{ form.fixed_price ? 'BS' : '' }}</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-12">
                    <button type="submit" :disabled="form.processing" class="w-full h-14 bg-primary text-primary-foreground font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative overflow-hidden group/submit">
                        <span v-if="form.processing" class="flex items-center justify-center gap-2"><Cpu :size="16" class="animate-spin" /> PROCESANDO...</span>
                        <span v-else class="flex items-center justify-center gap-2 relative z-10"><Save :size="16" /> ACTUALIZAR PACK</span>
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/submit:translate-y-0 transition-transform duration-500"></span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes shake { 10%, 90% { transform: translate3d(-1px, 0, 0); } 20%, 80% { transform: translate3d(2px, 0, 0); } 30%, 50%, 70% { transform: translate3d(-4px, 0, 0); } 40%, 60% { transform: translate3d(4px, 0, 0); } }
.shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
.glitch-text { position: relative; animation: glitch-skew 4s infinite linear alternate-reverse; }
.glitch-text::before, .glitch-text::after { content: attr(data-text); position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8; }
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }
@keyframes glitch-skew { 0%, 20%, 22%, 80%, 82%, 100% { transform: skew(0deg); } 21% { transform: skew(2deg); } 81% { transform: skew(-2deg); } }
.shadow-neon-primary { box-shadow: 0 0 20px hsl(var(--primary) / 0.3); }
</style>