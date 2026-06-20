<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { 
    Plus, Trash2, Save, Barcode, DollarSign, 
    Box, Cuboid, Scale, ImageIcon, Info, Cpu, Terminal,
    ArrowLeft, UploadCloud, Wifi, WifiOff
} from 'lucide-vue-next';

const props = defineProps({
    product: Object,     // Contexto del Maestro (obligatorio)
    sku: { type: Object, default: null }, // Si existe, entra en MODO_EDICIÓN_INDIVIDUAL
    idempKey: String     // Llave de idempotencia para creación
});

const isEdit = computed(() => !!props.sku);
const previews = ref([]);

// --- PROTOCOLO DE INICIALIZACIÓN ---
const form = useForm({
    _method: isEdit.value ? 'PUT' : 'POST',
    // Si es edición, enviamos un objeto plano. Si es creación, una matriz 'skus'.
    ...(isEdit.value ? {
        name: props.sku.name,
        code: props.sku.code,
        base_price: props.sku.base_price,
        conversion_factor: props.sku.conversion_factor,
        weight: props.sku.weight,
        is_active: !!props.sku.is_active,
        image: null
    } : {
        skus: [{ 
            name: `${props.product.name} (VARIANTE)`, 
            code: '', 
            base_price: 0, 
            conversion_factor: 1, 
            weight: 0, 
            image: null,
            is_active: true
        }]
    })
});

onMounted(() => {
    if (isEdit.value) previews.value = [props.sku.image_url];
    else previews.value = [null];
});

// --- LÓGICA OPERATIVA ---
const addNode = () => {
    form.skus.unshift({ 
        name: `${props.product.name} (NUEVA)`, 
        code: '', base_price: 0, conversion_factor: 1, weight: 0, image: null, is_active: true 
    });
    previews.value.unshift(null);
};

const removeNode = (index) => {
    if (form.skus.length > 1) {
        form.skus.splice(index, 1);
        previews.value.splice(index, 1);
    }
};

const handleImage = (e, index = 0) => {
    const file = e.target.files[0];
    if (!file) return;
    
    if (isEdit.value) form.image = file;
    else form.skus[index].image = file;

    previews.value[index] = URL.createObjectURL(file);
};

const submit = () => {
    const url = isEdit.value 
        ? route('admin.skus.update', props.sku.id) 
        : route('admin.products.skus.store', props.product.id);

    form.post(url, {
        headers: { 'X-Idempotency-Key': props.idempKey },
        forceFormData: true,
        preserveScroll: true,
        onError: () => {
            const audio = new Audio('/assets/sounds/error.mp3'); // Opcional: Feedback auditivo militar
            audio.play().catch(() => {});
        }
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex justify-between items-end border-b border-primary/30 pb-4">
            <div>
                <h2 class="text-2xl font-black text-primary uppercase tracking-tighter">
                    {{ isEdit ? 'RECALIBRAR_NODO_SKU' : 'INYECCIÓN_MASIVA_DE_VARIANTES' }}
                </h2>
                <p class="text-[9px] font-mono text-muted-foreground uppercase flex items-center gap-2 mt-1">
                    <Cpu :size="10" /> CONTEXTO_MAESTRO: <span class="text-foreground font-bold">{{ product.name }}</span>
                </p>
            </div>
            <button v-if="!isEdit" @click="addNode" type="button"
                    class="h-10 px-4 bg-primary/10 border border-primary text-primary font-mono text-[9px] font-black uppercase hover:bg-primary hover:text-background transition-all">
                + AÑADIR_NODO_A_MATRIZ
            </button>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div v-for="(item, index) in (isEdit ? [form] : form.skus)" :key="index"
                 class="border border-primary/10 bg-background/40 backdrop-blur-md p-6 relative group/sku hover:border-primary/40 transition-all">
                
                <div class="absolute top-0 left-0 w-1 h-full" :class="isEdit ? 'bg-cyan-500' : 'bg-primary/40'"></div>
                
                <button v-if="!isEdit && form.skus.length > 1" type="button" @click="removeNode(index)"
                        class="absolute top-0 right-0 p-2 bg-destructive text-white opacity-0 group-hover/sku:opacity-100 transition-opacity">
                    <Trash2 :size="14" />
                </button>

                <div class="flex flex-col md:flex-row gap-8">
                    <div class="w-32 shrink-0">
                        <label class="text-[8px] font-mono text-primary uppercase mb-2 block tracking-widest">/IMG_VARIANTE</label>
                        <div @click="$refs.imgFiles[index].click()" 
                             class="aspect-square border-2 border-dashed border-primary/20 bg-primary/5 flex items-center justify-center cursor-pointer overflow-hidden hover:border-primary transition-all group/upload">
                            <img v-if="previews[index]" :src="previews[index]" class="w-full h-full object-contain p-1">
                            <UploadCloud v-else :size="24" class="text-primary/20" />
                            <input ref="imgFiles" type="file" class="hidden" accept="image/*" @change="e => handleImage(e, index)">
                        </div>
                    </div>

                    <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-2 space-y-1">
                            <label class="text-[9px] font-mono font-black text-muted-foreground uppercase flex items-center gap-1">
                                <Terminal :size="10" /> IDENTIFICADOR_SKU
                            </label>
                            <input v-model="item.name" type="text" 
                                   class="w-full h-10 px-3 bg-background border border-primary/20 font-mono text-sm focus:border-primary outline-none uppercase font-bold" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-mono font-black text-muted-foreground uppercase flex items-center gap-1">
                                <Barcode :size="10" /> CODIGO_EAN
                            </label>
                            <input v-model="item.code" type="text" :disabled="isEdit && item.code"
                                   class="w-full h-10 px-3 bg-background border border-primary/20 font-mono text-sm focus:border-primary outline-none tracking-widest disabled:opacity-50" 
                                   :placeholder="isEdit ? '' : '[AUTO-GEN]'" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-mono font-black text-cyan-500 uppercase flex items-center gap-1">
                                <DollarSign :size="10" /> PRECIO_REF
                            </label>
                            <input v-model="item.base_price" type="number" step="0.01"
                                   class="w-full h-10 px-3 bg-cyan-500/5 border border-cyan-500/20 text-cyan-500 font-mono text-sm font-black focus:border-cyan-500 outline-none text-right" />
                        </div>

                        <div class="md:col-span-2 grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[9px] font-mono font-black text-muted-foreground uppercase flex items-center gap-1"><Cuboid :size="10" /> FACTOR_CONV</label>
                                <input v-model="item.conversion_factor" type="number" class="w-full h-10 px-3 bg-background border border-primary/20 font-mono text-sm text-center outline-none focus:border-primary" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-mono font-black text-muted-foreground uppercase flex items-center gap-1"><Scale :size="10" /> MASA_KG</label>
                                <input v-model="item.weight" type="number" step="0.001" class="w-full h-10 px-3 bg-background border border-primary/20 font-mono text-sm text-right outline-none focus:border-primary" />
                            </div>
                        </div>

                        <div class="md:col-span-2 pt-5">
                            <div @click="item.is_active = !item.is_active" 
                                 class="h-10 px-4 border flex items-center justify-between cursor-pointer transition-all"
                                 :class="item.is_active ? 'border-cyan-500/30 bg-cyan-500/5 text-cyan-500' : 'border-primary/10 text-muted-foreground'">
                                <span class="text-[8px] font-mono font-black uppercase tracking-widest">ESTADO_OPERATIVO: {{ item.is_active ? 'ONLINE' : 'OFFLINE' }}</span>
                                <component :is="item.is_active ? Wifi : WifiOff" :size="14" />
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="form.errors[`skus.${index}.name`] || form.errors.name" class="mt-4 p-2 bg-destructive/10 text-destructive text-[8px] font-mono uppercase font-black">
                    > ERROR_PROTOCOLO: {{ form.errors[`skus.${index}.name`] || form.errors.name }}
                </div>
            </div>

            <div class="mt-8 p-6 bg-background/80 border border-primary/20 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 text-primary/60">
                    <Info :size="14" />
                    <span class="text-[8px] font-mono font-black uppercase tracking-widest">SISTEMA_DE_INYECCIÓN_ATÓMICA_ACTIVO</span>
                </div>
                
                <div class="flex gap-4 w-full md:w-auto">
                    <button type="button" @click="router.get(route('admin.products.index'))" 
                            class="px-8 py-3 border border-primary/20 text-primary font-mono text-[10px] font-black uppercase hover:bg-primary/5 transition-all">
                        CANCELAR
                    </button>
                    <button type="submit" :disabled="form.processing"
                            class="px-12 py-3 bg-primary text-background font-mono font-black text-[10px] uppercase shadow-neon-primary hover:bg-primary/90 transition-all disabled:opacity-30">
                        {{ form.processing ? 'SINCRONIZANDO...' : (isEdit ? 'ACTUALIZAR_VARIANTE' : 'EJECUTAR_INYECCIÓN') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>