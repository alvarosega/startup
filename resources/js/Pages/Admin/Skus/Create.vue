<script setup>
import { ref } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Plus, Trash2, Save, Barcode, DollarSign, 
    Box, Cuboid, Scale, ImageIcon, Info, Cpu, Terminal
} from 'lucide-vue-next';

const props = defineProps({ product: Object });

const skuImagePreviews = ref([null]);

// FORMULARIO ALINEADO CON EL DTO: 'base_price' en lugar de 'price'
const form = useForm({
    skus: [
        { 
            name: `${props.product.name} (UNIDAD)`, 
            code: '', 
            base_price: 0, 
            conversion_factor: 1, 
            weight: 0, 
            image: null 
        }
    ]
});

const addSku = () => {
    form.skus.unshift({ 
        name: `${props.product.name} (VARIANTE)`, 
        code: '', 
        base_price: 0, 
        conversion_factor: 1, 
        weight: 0, 
        image: null 
    });
    skuImagePreviews.value.unshift(null);
};

const removeSku = (index) => {
    if (form.skus.length > 1) {
        form.skus.splice(index, 1);
        skuImagePreviews.value.splice(index, 1);
    }
};

const handleSkuImage = (e, index) => {
    const file = e.target.files[0];
    if (!file) return;
    form.skus[index].image = file;
    const reader = new FileReader();
    reader.onload = (ev) => skuImagePreviews.value[index] = ev.target.result;
    reader.readAsDataURL(file);
};

const isShaking = ref(false);

const submit = () => {
    form.post(route('admin.products.skus.store', props.product.id), {
        forceFormData: true,
        onError: () => {
            isShaking.value = true;
            setTimeout(() => isShaking.value = false, 500);
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Módulo SKU: ${product.name}`" />
        
        <div class="max-w-7xl mx-auto pb-12 px-4 md:px-0">
            
            <div class="mb-8 border-b border-primary/30 pb-6 relative group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-[8px] font-mono text-cyan-500 border border-cyan-500/30 bg-cyan-500/10 px-2 py-1 flex items-center gap-1">
                                <Box :size="10" /> CREACIÓN EN LOTE (BULK)
                            </span>
                        </div>
                        <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                            data-text="VARIANTES SKU">
                            VARIANTES SKU
                        </h1>
                        <p class="text-[10px] font-mono text-foreground mt-2 uppercase flex items-center gap-2">
                            <span class="text-muted-foreground">MAESTRO:</span> <span class="font-bold text-primary">{{ product.name }}</span>
                        </p>
                    </div>
                    
                    <button @click="addSku" class="h-11 px-6 bg-primary/10 border border-primary text-primary font-mono text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-primary hover:text-background transition-all relative overflow-hidden group/add">
                        <Plus :size="16" /> AÑADIR NODO
                        <span class="absolute inset-0 bg-white/20 translate-y-full group-hover/add:translate-y-0 transition-transform"></span>
                    </button>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                
                <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-destructive/10 border border-destructive/50 text-destructive text-[10px] font-mono uppercase tracking-widest">
                    > FALLO EN LA VALIDACIÓN DE PARÁMETROS. REVISE LAS VARIANTES MARCADAS.
                </div>

                <div v-for="(sku, index) in form.skus" :key="index" 
                     :class="{ 'shake': isShaking }"
                     class="border border-border/50 bg-background/50 backdrop-blur-sm relative group/sku hover:border-primary/50 transition-colors">
                    
                    <div class="absolute top-0 left-0 w-1 h-full" :class="form.errors[`skus.${index}.name`] ? 'bg-destructive' : 'bg-primary/50'"></div>
                    
                    <button type="button" @click="removeSku(index)" v-if="form.skus.length > 1"
                            class="absolute top-0 right-0 p-2 bg-destructive text-destructive-foreground opacity-0 group-hover/sku:opacity-100 transition-opacity hover:bg-destructive/80">
                        <Trash2 :size="14" />
                    </button>

                    <div class="p-6 flex flex-col md:flex-row gap-6">
                        <div class="w-32 shrink-0">
                            <label class="text-[8px] font-mono text-primary uppercase tracking-widest mb-2 block">/IMAGEN</label>
                            <div class="aspect-square border border-dashed border-primary/30 bg-primary/5 flex items-center justify-center cursor-pointer overflow-hidden relative hover:border-primary transition-colors" @click="$refs.skuImg[index].click()">
                                <img v-if="skuImagePreviews[index]" :src="skuImagePreviews[index]" class="w-full h-full object-cover">
                                <div v-else class="flex flex-col items-center">
                                    <ImageIcon :size="24" class="text-primary/30 mb-1" />
                                    <span class="text-[7px] font-mono text-primary/50">SELECCIONAR</span>
                                </div>
                                <input ref="skuImg" type="file" class="hidden" accept="image/*" @change="e => handleSkuImage(e, index)">
                            </div>
                        </div>

                        <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-x-4 gap-y-6">
                            <div class="md:col-span-2 space-y-1 relative">
                                <label class="text-[9px] font-mono font-bold uppercase text-muted-foreground flex items-center gap-1">
                                    <Terminal :size="10" class="text-primary"/> IDENTIFICADOR <span class="text-destructive">*</span>
                                </label>
                                <input v-model="sku.name" type="text" 
                                    class="w-full h-10 px-3 bg-background border border-border/50 font-mono text-sm font-bold focus:border-primary focus:shadow-neon-primary outline-none uppercase"
                                    :class="{'border-destructive text-destructive shadow-[0_0_10px_rgba(239,68,68,0.2)]': form.errors[`skus.${index}.name`]}">
                                <span v-if="form.errors[`skus.${index}.name`]" class="text-[8px] text-destructive absolute -bottom-4 left-0 font-bold uppercase tracking-widest">{{ form.errors[`skus.${index}.name`] }}</span>
                            </div>
                            
                            <div class="space-y-1 relative">
                                <label class="text-[9px] font-mono font-bold uppercase text-muted-foreground flex items-center gap-1">
                                    <Barcode :size="10" class="text-primary"/> CÓDIGO_EAN
                                </label>
                                <input v-model="sku.code" type="text" 
                                    class="w-full h-10 px-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none tracking-widest"
                                    :class="{'border-destructive text-destructive shadow-[0_0_10px_rgba(239,68,68,0.2)]': form.errors[`skus.${index}.code`]}">
                                <span v-if="form.errors[`skus.${index}.code`]" class="text-[8px] text-destructive absolute -bottom-4 left-0 font-bold uppercase tracking-widest">{{ form.errors[`skus.${index}.code`] }}</span>
                            </div>

                            <div class="space-y-1 relative">
                                <label class="text-[9px] font-mono font-bold uppercase text-cyan-500 flex items-center gap-1">
                                    <DollarSign :size="10" /> PRECIO_BASE <span class="text-destructive">*</span>
                                </label>
                                <input v-model="sku.base_price" type="number" step="0.01" min="0"
                                    class="w-full h-10 px-3 bg-cyan-500/5 border border-cyan-500/30 text-cyan-500 font-mono text-sm font-black focus:border-cyan-500 outline-none text-right"
                                    :class="{'border-destructive text-destructive shadow-[0_0_10px_rgba(239,68,68,0.2)] bg-destructive/5': form.errors[`skus.${index}.base_price`]}">
                                <span v-if="form.errors[`skus.${index}.base_price`]" class="text-[8px] text-destructive absolute -bottom-4 left-0 font-bold uppercase tracking-widest">{{ form.errors[`skus.${index}.base_price`] }}</span>
                            </div>

                            <div class="md:col-span-2 space-y-1 relative">
                                <label class="text-[9px] font-mono font-bold uppercase text-muted-foreground flex items-center gap-1">
                                    <Cuboid :size="10" class="text-primary"/> FACTOR_CONVERSIÓN (Uds) <span class="text-destructive">*</span>
                                </label>
                                <input v-model="sku.conversion_factor" type="number" step="1" min="1"
                                    class="w-full h-10 px-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none text-center font-bold"
                                    :class="{'border-destructive text-destructive shadow-[0_0_10px_rgba(239,68,68,0.2)]': form.errors[`skus.${index}.conversion_factor`]}">
                                <span v-if="form.errors[`skus.${index}.conversion_factor`]" class="text-[8px] text-destructive absolute -bottom-4 left-0 font-bold uppercase tracking-widest">{{ form.errors[`skus.${index}.conversion_factor`] }}</span>
                            </div>

                            <div class="md:col-span-2 space-y-1 relative">
                                <label class="text-[9px] font-mono font-bold uppercase text-muted-foreground flex items-center gap-1">
                                    <Scale :size="10" class="text-primary"/> MASA_FÍSICA (Kg)
                                </label>
                                <input v-model="sku.weight" type="number" step="0.001" min="0"
                                    class="w-full h-10 px-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none text-right"
                                    :class="{'border-destructive text-destructive shadow-[0_0_10px_rgba(239,68,68,0.2)]': form.errors[`skus.${index}.weight`]}">
                                <span v-if="form.errors[`skus.${index}.weight`]" class="text-[8px] text-destructive absolute -bottom-4 left-0 font-bold uppercase tracking-widest">{{ form.errors[`skus.${index}.weight`] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 p-6 bg-background/80 backdrop-blur-sm border-t border-primary/30 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-2" :class="form.skus.length === 0 ? 'text-destructive' : 'text-primary'">
                        <Info :size="16" />
                        <span class="text-[9px] font-mono font-bold uppercase tracking-widest">
                            {{ form.skus.length === 0 ? 'ALERTA: EL MAESTRO QUEDARÁ INCOMPLETO SIN SKUS' : 'MATRIZ DE VARIANTES LISTA PARA INYECCIÓN' }}
                        </span>
                    </div>
                    
                    <div class="flex gap-4 w-full md:w-auto">
                        <Link :href="route('admin.products.index')" 
                              class="flex-1 md:flex-none px-6 py-3 border border-primary/30 text-primary font-mono text-[10px] text-center hover:bg-primary/5 transition-colors uppercase">
                            OMITIR FASE
                        </Link>
                        <button type="submit" 
                                :disabled="form.processing || form.skus.length === 0"
                                class="flex-1 md:flex-none px-10 py-3 bg-primary text-background font-mono font-black text-[10px] uppercase shadow-neon-primary hover:bg-primary/90 transition-all disabled:opacity-50 disabled:pointer-events-none flex items-center justify-center gap-2">
                            <Cpu v-if="form.processing" :size="14" class="animate-spin" />
                            <Save v-else :size="14" />
                            EJECUTAR INYECCIÓN
                        </button>
                    </div>
                </div>
            </form>
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

.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }
</style>