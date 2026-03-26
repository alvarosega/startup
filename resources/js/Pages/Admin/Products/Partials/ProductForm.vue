<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { 
    Package, ArrowRight, UploadCloud, Wine, Tag, Layers,
    Cpu, Terminal, Wifi, WifiOff, AlertTriangle, CheckCircle2,
    FileText, Save, Loader2
} from 'lucide-vue-next';
import axios from 'axios';
import debounce from 'lodash/debounce';

const props = defineProps({
    product: { type: Object, default: null }, // Nulo para Create, Objeto para Edit
    brands: Array,
    categories: Array,
    idempKey: String // Generada en el padre
});

const isEdit = computed(() => !!props.product);

// 1. INICIALIZAR FORMULARIO (Identidad Inmutable)
const form = useForm({
    name: props.product?.name ?? '',
    brand_id: props.product?.brand_id ?? '',
    category_id: props.product?.category_id ?? '',
    description: props.product?.description ?? '',
    image: null,
    is_active: props.product ? !!props.product.is_active : true,
    is_alcoholic: props.product ? !!props.product.is_alcoholic : false,
    _method: props.product ? 'PUT' : 'POST' // Spoofing para manejo de archivos
});

// 2. ESTADOS DE VALIDACIÓN
const isNameAvailable = ref(true);
const isCheckingName = ref(false);
const imagePreview = ref(props.product?.image_url ?? null);

const checkName = debounce(async (name) => {
    if (name.length < 3 || (isEdit.value && name === props.product.name)) return;
    isCheckingName.value = true;
    try {
        const { data } = await axios.get(route('admin.products.check-name'), { params: { name } });
        isNameAvailable.value = data.available;
        if (!data.available) form.setError('name', '// NOMBRE_NO_DISPONIBLE_EN_SISTEMA');
        else form.clearErrors('name');
    } catch (e) { console.error("IDENT_FAIL"); }
    finally { isCheckingName.value = false; }
}, 500);

watch(() => form.name, (val) => {
    isNameAvailable.value = true;
    checkName(val);
});

// 3. MÉTODOS OPERATIVOS
const handleImage = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
};

const submit = () => {
    const url = isEdit.value ? route('admin.products.update', props.product.id) : route('admin.products.store');
    
    form.post(url, {
        headers: { 'X-Idempotency-Key': props.idempKey },
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // No hacemos redirect aquí, Inertia lo hace desde el controlador
        }
    });
};
</script>

<template>
    <div class="border border-border/50 bg-background relative overflow-hidden group/form shadow-2xl">
        <div class="absolute top-0 left-0 w-1 h-full bg-primary/20"></div>
        <div class="p-8 space-y-8 relative z-10">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="md:col-span-2 space-y-2">
                    <label class="text-[10px] font-mono font-black text-primary uppercase flex items-center gap-2">
                        <Terminal :size="12" /> // NOMBRE_MAESTRO_UNICO
                    </label>
                    <div class="relative">
                        <Package :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="form.name" type="text"
                               class="w-full pl-10 pr-12 py-4 bg-background border border-primary/20 font-mono text-xl font-black uppercase outline-none focus:border-primary transition-all"
                               :class="{'border-destructive': form.errors.name || !isNameAvailable}" />
                        <div class="absolute right-4 top-1/2 -translate-y-1/2">
                            <Loader2 v-if="isCheckingName" class="animate-spin text-primary" :size="18" />
                            <CheckCircle2 v-else-if="form.name && isNameAvailable" class="text-cyan-500" :size="18" />
                        </div>
                    </div>
                    <p v-if="form.errors.name" class="text-[9px] font-mono text-destructive uppercase tracking-widest">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-mono font-black text-primary uppercase flex items-center gap-2">
                        <Tag :size="12" /> // MARCA_ASOCIADA
                    </label>
                    <select v-model="form.brand_id" class="w-full px-4 py-3 bg-background border border-primary/20 font-mono text-xs uppercase outline-none focus:border-primary appearance-none custom-select">
                        <option value="" disabled>-- SELECCIONAR_MARCA --</option>
                        <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-mono font-black text-primary uppercase flex items-center gap-2">
                        <Layers :size="12" /> // CATEGORIA_RAIZ
                    </label>
                    <select v-model="form.category_id" class="w-full px-4 py-3 bg-background border border-primary/20 font-mono text-xs uppercase outline-none focus:border-primary appearance-none custom-select">
                        <option value="" disabled>-- SELECCIONAR_CATEGORIA --</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-mono font-black text-primary uppercase">// IDENTIDAD_VISUAL</label>
                    <div @click="$refs.imgInput.click()" class="aspect-square border-2 border-dashed border-primary/20 bg-primary/5 flex items-center justify-center cursor-pointer hover:border-primary transition-all relative overflow-hidden group/img">
                        <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-contain p-2 transition-transform duration-500 group-hover/img:scale-105" />
                        <div v-else class="flex flex-col items-center gap-2 text-primary/40">
                            <UploadCloud :size="32" />
                            <span class="text-[8px] font-mono font-black tracking-widest">SUBIR_ACTIVO</span>
                        </div>
                        <input ref="imgInput" type="file" class="hidden" accept="image/*" @change="handleImage" />
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-mono font-black text-primary uppercase flex items-center gap-2"><FileText :size="12" /> // ESPECIFICACIONES</label>
                        <textarea v-model="form.description" rows="6" class="w-full bg-background border border-primary/20 px-4 py-3 font-mono text-xs uppercase outline-none focus:border-primary resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div @click="form.is_active = !form.is_active" class="border p-4 cursor-pointer transition-all flex items-center justify-between"
                             :class="form.is_active ? 'border-cyan-500/30 bg-cyan-500/5 text-cyan-500' : 'border-primary/20 text-muted-foreground'">
                            <span class="text-[9px] font-mono font-black uppercase tracking-tighter">SISTEMA_ACTIVO</span>
                            <component :is="form.is_active ? Wifi : WifiOff" :size="14" />
                        </div>
                        <div @click="form.is_alcoholic = !form.is_alcoholic" class="border p-4 cursor-pointer transition-all flex items-center justify-between"
                             :class="form.is_alcoholic ? 'border-warning/30 bg-warning/5 text-warning' : 'border-primary/20 text-muted-foreground'">
                            <span class="text-[9px] font-mono font-black uppercase tracking-tighter">CONTENIDO_ETANOL</span>
                            <Wine :size="14" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 bg-primary/5 border-t border-primary/20 flex justify-between items-center">
            <button type="button" @click="router.get(route('admin.products.index'))" class="px-6 py-2 border border-destructive/50 text-destructive text-[10px] font-mono font-black hover:bg-destructive hover:text-white transition-all">
                ABORTAR_MISION
            </button>
            <button @click="submit" :disabled="form.processing || !isNameAvailable" class="px-10 py-3 bg-primary text-background text-xs font-black uppercase tracking-widest shadow-neon-primary disabled:opacity-30 flex items-center gap-3 transition-all active:scale-95">
                <span v-if="form.processing" class="flex items-center gap-2"><Loader2 class="animate-spin" :size="16" /> SINCRONIZANDO...</span>
                <span v-else class="flex items-center gap-2"><Save :size="16" /> {{ isEdit ? 'ACTUALIZAR_MAESTRO' : 'INYECTAR_AL_CATALOGO' }}</span>
            </button>
        </div>
    </div>
</template>