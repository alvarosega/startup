<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Barcode, DollarSign, Scale, Box, Cuboid, 
    ArrowLeft, Save, UploadCloud, ImageIcon, Info,
    CheckCircle2, Package, Wifi, WifiOff, Terminal, Cpu
} from 'lucide-vue-next';

const props = defineProps({
    sku: Object,      // Variant data (from SkuResource)
    product: Object   // Parent context (id, name)
});

// --- ESTADO LOCAL ---
const skuImageInputRef = ref(null);
const skuImagePreview = ref(null);
const isShaking = ref(false);

// --- FORMULARIO QUIRÚRGICO (DoD v2.0) ---
// Eliminado .number en el template para evitar fallos de precisión en JS
const form = useForm({
    _method: 'PUT',
    name: '',
    code: '',
    base_price: 0,
    conversion_factor: 1,
    weight: 0,
    is_active: true,
    image: null,
});

onMounted(() => {
    if (props.sku) {
        form.name = props.sku.name || '';
        form.code = props.sku.code || '';
        form.base_price = props.sku.base_price || 0; // CORRECCIÓN: Alineado con el Resource (base_price)
        form.conversion_factor = props.sku.conversion_factor || 1;
        form.weight = props.sku.weight || 0;
        form.is_active = !!props.sku.is_active;
        skuImagePreview.value = props.sku.image_url || null;
    }
});

const handleImage = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.image = file;
    const reader = new FileReader();
    reader.onload = (ev) => skuImagePreview.value = ev.target.result;
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(route('admin.skus.update', props.sku.id), {
        forceFormData: true,
        preserveScroll: true,
        onError: () => {
            isShaking.value = true;
            setTimeout(() => isShaking.value = false, 500);
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Editar SKU - ${sku.name}`" />

        <div class="max-w-5xl mx-auto pb-12 px-4 md:px-0">
            
            <div class="mb-8 border-b border-primary/30 pb-6 relative group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <span class="text-[8px] font-mono text-cyan-500 border border-cyan-500/30 bg-cyan-500/10 px-2 py-1 flex items-center gap-1">
                                <Package :size="10" /> {{ product.name }}
                            </span>
                            <span class="text-[8px] font-mono text-primary/50 uppercase tracking-widest">
                                UUID: {{ sku.id.substring(0,8) }}
                            </span>
                        </div>
                        <Link :href="route('admin.products.index')" class="text-[9px] font-mono text-muted-foreground hover:text-primary flex items-center gap-1 transition-colors uppercase">
                            <ArrowLeft :size="12" /> ABORTAR EDICIÓN
                        </Link>
                    </div>
                    
                    <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                        data-text="RECALIBRAR SKU">
                        RECALIBRAR SKU
                    </h1>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1 space-y-6">
                    <div class="border border-border/50 bg-background/50 backdrop-blur-sm p-6 relative group/img-card hover:border-primary/50 transition-colors">
                        <div class="absolute top-0 left-0 w-full h-[1px] bg-primary/30"></div>
                        <label class="text-[10px] font-mono font-black uppercase tracking-widest block mb-4 text-primary">/IMAGEN_ACTIVO</label>
                        
                        <input ref="skuImageInputRef" type="file" class="hidden" accept="image/*" @change="handleImage">
                        
                        <div @click="skuImageInputRef.click()" 
                             class="relative aspect-square border-2 border-dashed border-primary/30 bg-primary/5 flex items-center justify-center cursor-pointer overflow-hidden transition-all hover:border-primary group/upload">
                            
                            <img v-if="skuImagePreview" :src="skuImagePreview" class="w-full h-full object-cover group-hover/upload:scale-105 transition-transform duration-500">
                            
                            <div v-else class="flex flex-col items-center text-primary/50">
                                <UploadCloud :size="32" stroke-width="1.5" class="mb-2 group-hover/upload:text-primary transition-colors" />
                                <span class="text-[8px] font-mono uppercase tracking-widest">SELECCIONAR</span>
                            </div>

                            <div v-if="skuImagePreview" class="absolute inset-0 bg-background/80 opacity-0 group-hover/upload:opacity-100 flex items-center justify-center transition-opacity">
                                <ImageIcon class="text-primary" :size="24" />
                            </div>
                        </div>
                        <p class="text-[8px] font-mono text-muted-foreground mt-4 text-center leading-relaxed uppercase">
                            FORMATOS: JPG, PNG, WEBP. MÁX 2MB.<br>IMAGEN ESPECÍFICA PARA ESTA VARIANTE.
                        </p>
                    </div>

                    <div class="border border-cyan-500/30 bg-cyan-500/5 p-4 flex gap-3">
                        <Info class="text-cyan-500 shrink-0" :size="16" />
                        <div>
                            <h4 class="text-[9px] font-mono font-black uppercase text-cyan-500">TRAZABILIDAD FINANCIERA</h4>
                            <p class="text-[8px] font-mono text-muted-foreground mt-1 leading-relaxed uppercase">
                                MODIFICAR EL PRECIO BASE DESDE AQUÍ REESCRIBIRÁ EL VALOR POR DEFECTO. LAS REGLAS DE SUCURSAL SE MANTIENEN.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div :class="{ 'shake': isShaking }"
                             class="border border-border/50 bg-background shadow-2xl relative overflow-hidden group/form">
                            
                            <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                            <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                            
                            <div class="p-6 md:p-8 space-y-6">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2 space-y-1 relative">
                                        <label class="text-[9px] font-mono font-bold uppercase text-muted-foreground flex items-center gap-1">
                                            <Terminal :size="10" class="text-primary"/> IDENTIFICADOR <span class="text-destructive">*</span>
                                        </label>
                                        <input v-model="form.name" type="text" 
                                               class="w-full h-12 px-4 bg-background border border-border/50 font-mono text-sm font-bold focus:border-primary focus:shadow-neon-primary outline-none uppercase transition-all"
                                               :class="{'border-destructive text-destructive shadow-[0_0_10px_rgba(239,68,68,0.2)]': form.errors.name}">
                                        <span v-if="form.errors.name" class="text-[8px] text-destructive absolute -bottom-4 left-0 font-bold uppercase tracking-widest">{{ form.errors.name }}</span>
                                    </div>

                                    <div class="space-y-1 relative">
                                        <label class="text-[9px] font-mono font-bold uppercase text-muted-foreground flex items-center gap-1">
                                            <Barcode :size="10" class="text-primary"/> CÓDIGO_EAN
                                        </label>
                                        <input v-model="form.code" type="text" 
                                               class="w-full h-12 px-4 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none tracking-widest transition-all"
                                               :class="{'border-destructive text-destructive shadow-[0_0_10px_rgba(239,68,68,0.2)]': form.errors.code}">
                                        <span v-if="form.errors.code" class="text-[8px] text-destructive absolute -bottom-4 left-0 font-bold uppercase tracking-widest">{{ form.errors.code }}</span>
                                    </div>

                                    <div class="space-y-1 relative">
                                        <label class="text-[9px] font-mono font-bold uppercase text-cyan-500 flex items-center gap-1">
                                            <DollarSign :size="10" /> PRECIO_BASE <span class="text-destructive">*</span>
                                        </label>
                                        <input v-model="form.base_price" type="number" step="0.01" min="0"
                                               class="w-full h-12 px-4 bg-cyan-500/5 border border-cyan-500/30 text-cyan-500 font-mono text-lg font-black focus:border-cyan-500 outline-none text-right shadow-[inset_0_0_10px_rgba(6,182,212,0.1)] transition-all"
                                               :class="{'border-destructive text-destructive shadow-[0_0_10px_rgba(239,68,68,0.2)] bg-destructive/5': form.errors.base_price}">
                                        <span v-if="form.errors.base_price" class="text-[8px] text-destructive absolute -bottom-4 left-0 font-bold uppercase tracking-widest">{{ form.errors.base_price }}</span>
                                    </div>
                                </div>

                                <div class="border-t border-primary/20 my-6"></div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-1 relative">
                                        <label class="text-[9px] font-mono font-bold uppercase text-muted-foreground flex items-center gap-1">
                                            <Cuboid :size="10" class="text-primary"/> FACTOR_CONVERSIÓN (Uds) <span class="text-destructive">*</span>
                                        </label>
                                        <input v-model="form.conversion_factor" type="number" step="1" min="1"
                                               class="w-full h-12 px-4 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none text-center font-bold transition-all"
                                               :class="{'border-destructive text-destructive shadow-[0_0_10px_rgba(239,68,68,0.2)]': form.errors.conversion_factor}">
                                        <span v-if="form.errors.conversion_factor" class="text-[8px] text-destructive absolute -bottom-4 left-0 font-bold uppercase tracking-widest">{{ form.errors.conversion_factor }}</span>
                                    </div>

                                    <div class="space-y-1 relative">
                                        <label class="text-[9px] font-mono font-bold uppercase text-muted-foreground flex items-center gap-1">
                                            <Scale :size="10" class="text-primary"/> MASA_FÍSICA (Kg)
                                        </label>
                                        <input v-model="form.weight" type="number" step="0.001" min="0"
                                               class="w-full h-12 px-4 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none text-right transition-all"
                                               :class="{'border-destructive text-destructive shadow-[0_0_10px_rgba(239,68,68,0.2)]': form.errors.weight}">
                                        <span v-if="form.errors.weight" class="text-[8px] text-destructive absolute -bottom-4 left-0 font-bold uppercase tracking-widest">{{ form.errors.weight }}</span>
                                    </div>
                                </div>

                                <div @click="form.is_active = !form.is_active" 
                                     class="mt-6 p-4 border flex items-center justify-between cursor-pointer transition-colors relative overflow-hidden group/toggle"
                                     :class="form.is_active ? 'bg-cyan-500/5 border-cyan-500/30' : 'bg-background border-border/50 hover:border-primary/30'">
                                    
                                    <div class="flex items-center gap-3 relative z-10">
                                        <component :is="form.is_active ? Wifi : WifiOff" :class="form.is_active ? 'text-cyan-500' : 'text-muted-foreground'" :size="18" />
                                        <span class="text-[10px] font-mono font-black uppercase tracking-widest" :class="form.is_active ? 'text-cyan-500' : 'text-muted-foreground'">
                                            ESTADO: {{ form.is_active ? 'ACTIVO_EN_LÍNEA' : 'FUERA_DE_SERVICIO' }}
                                        </span>
                                    </div>
                                    <div :class="`w-10 h-5 border border-border/50 relative z-10`">
                                        <div :class="`absolute top-0.5 left-0.5 w-4 h-4 transition-all duration-300 ${form.is_active ? 'translate-x-5 bg-cyan-500 shadow-[0_0_8px_hsl(var(--primary))]' : 'translate-x-0 bg-muted-foreground'}`"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row justify-end gap-4 mt-8">
                            <Link :href="route('admin.products.index')" 
                                  class="px-8 py-3 border border-border/50 text-muted-foreground font-mono text-[10px] text-center hover:bg-background hover:text-foreground transition-colors uppercase tracking-widest">
                                CANCELAR
                            </Link>
                            <button type="submit" :disabled="form.processing" 
                                    class="px-12 py-3 bg-primary text-background font-mono font-black text-[10px] uppercase tracking-widest shadow-neon-primary hover:bg-primary/90 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                                <Cpu v-if="form.processing" :size="14" class="animate-spin" />
                                <Save v-else :size="14" /> 
                                EJECUTAR_ACTUALIZACIÓN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}

.shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }

.glitch-text { position: relative; animation: glitch-skew 4s infinite linear alternate-reverse; }
.glitch-text::before, .glitch-text::after { content: attr(data-text); position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8; }
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }

@keyframes glitch-skew {
    0%, 20%, 22%, 80%, 82%, 100% { transform: skew(0deg); }
    21% { transform: skew(2deg); }
    81% { transform: skew(-2deg); }
}

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); } 20% { clip-path: inset(50% 0 10% 0); } 40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); } 80% { clip-path: inset(30% 0 40% 0); } 100% { clip-path: inset(40% 0 20% 0); }
}

@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); } 20% { clip-path: inset(20% 0 50% 0); } 40% { clip-path: inset(70% 0 5% 0); }
    60% { clip-path: inset(10% 0 70% 0); } 80% { clip-path: inset(40% 0 30% 0); } 100% { clip-path: inset(30% 0 40% 0); }
}

.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }
</style>