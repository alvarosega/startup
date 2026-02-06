<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Save, ArrowLeft, Package, Plus, Trash2, MapPin, 
    Image as ImageIcon, DollarSign, FileText, Layers, Upload
} from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

const props = defineProps({
    bundle: Object,
    skus: Object,
    branches: Array
});

const bundleData = props.bundle.data || props.bundle;

const form = useForm({
    _method: 'PUT',
    branch_id: bundleData.branch_id || '',
    name: bundleData.name,
    description: bundleData.description,
    fixed_price: bundleData.fixed_price,
    is_active: Boolean(bundleData.is_active),
    image: null,
    items: bundleData.items.map(i => ({ sku_id: i.sku_id, quantity: i.quantity }))
});

if (form.items.length === 0) form.items.push({ sku_id: '', quantity: 1 });

// --- LÓGICA IMAGEN ---
const imageInputRef = ref(null);
const imagePreview = ref(null);

onMounted(() => { 
    if (bundleData.image_path) {
        imagePreview.value = bundleData.image_path.startsWith('http') 
            ? bundleData.image_path 
            : `/storage/${bundleData.image_path}`; 
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

const submit = () => {
    form.post(route('admin.bundles.update', bundleData.id), { forceFormData: true, preserveScroll: true });
};
</script>

<template>
    <AdminLayout>
        <Head title="Editar Pack" />

        <div class="max-w-7xl mx-auto pb-40 md:pb-12">
            
            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur border-b border-border px-4 py-4 mb-6 flex flex-col gap-4 shadow-sm transition-all duration-300">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.bundles.index')" class="btn btn-ghost btn-sm btn-square -ml-2 text-muted-foreground hover:bg-muted">
                            <ArrowLeft :size="22" />
                        </Link>
                        <div>
                            <h1 class="text-xl font-display font-black text-foreground leading-none tracking-tight">
                                Editar Pack
                            </h1>
                            <p class="text-xs font-mono text-primary mt-1 truncate max-w-[200px]">{{ bundleData.name }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 bg-muted/30 p-1 rounded-lg border border-border">
                        <span class="text-[10px] font-bold uppercase px-2" :class="form.is_active ? 'text-primary' : 'text-muted-foreground'">
                            {{ form.is_active ? 'Visible' : 'Oculto' }}
                        </span>
                        <input type="checkbox" v-model="form.is_active" class="toggle toggle-primary toggle-sm" />
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="px-4 md:px-0 grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <div class="lg:col-span-5 space-y-6">
                    <div class="card bg-card border border-border shadow-sm overflow-hidden relative">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="p-4 border-b border-border bg-muted/20 flex items-center gap-2">
                            <Package :size="18" class="text-primary"/>
                            <h3 class="font-bold text-foreground text-sm uppercase tracking-wide">Información Base</h3>
                        </div>

                        <div class="p-5 space-y-5">
                            
                            <div class="space-y-2">
                                <label class="form-label text-xs uppercase font-bold flex items-center gap-1">
                                    Sucursal Objetivo <span class="text-error">*</span>
                                </label>
                                <div class="relative group">
                                    <MapPin :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors"/>
                                    <select v-model="form.branch_id" class="form-input w-full pl-10 h-12 bg-background appearance-none cursor-pointer" :class="{ 'border-error': form.errors.branch_id }">
                                        <option value="" disabled>Selecciona ubicación...</option>
                                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
                                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1L5 5L9 1"/></svg>
                                    </div>
                                </div>
                                <p v-if="form.errors.branch_id" class="form-error">{{ form.errors.branch_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <label class="form-label text-xs uppercase font-bold">Nombre del Pack</label>
                                <input v-model="form.name" type="text" class="form-input w-full font-bold text-lg" />
                                <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <label class="form-label text-xs uppercase font-bold">Imagen Promocional</label>
                                <div @click="triggerImageInput" 
                                     class="w-full aspect-video bg-muted/10 border-2 border-dashed border-border rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-all group overflow-hidden relative">
                                    
                                    <template v-if="imagePreview">
                                        <img :src="imagePreview" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="text-white font-bold text-xs flex items-center gap-2"><ImageIcon :size="16"/> Cambiar Imagen</span>
                                        </div>
                                    </template>
                                    
                                    <template v-else>
                                        <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                            <Upload :size="20" class="text-muted-foreground group-hover:text-primary"/>
                                        </div>
                                        <span class="text-xs text-muted-foreground font-medium">Toca para subir</span>
                                    </template>
                                    
                                    <input ref="imageInputRef" type="file" accept="image/*" class="hidden" @change="handleImageChange" />
                                </div>
                                <button v-if="imagePreview" type="button" @click.stop="clearImage" class="text-[10px] text-error hover:underline flex items-center gap-1">
                                    <Trash2 :size="12"/> Quitar imagen actual
                                </button>
                            </div>

                            <div class="space-y-2">
                                <label class="form-label text-xs uppercase font-bold">Descripción</label>
                                <div class="relative">
                                    <FileText :size="18" class="absolute left-3 top-3 text-muted-foreground"/>
                                    <textarea v-model="form.description" class="form-input w-full pl-10 resize-none text-sm" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="form-label text-xs uppercase font-bold flex justify-between">
                                    Precio Fijo 
                                    <span class="text-[10px] text-muted-foreground font-normal normal-case">(Dejar vacío para sumar items)</span>
                                </label>
                                <div class="relative group">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-bold group-focus-within:text-primary transition-colors">Bs</span>
                                    <input v-model="form.fixed_price" type="number" step="0.01" class="form-input w-full pl-9 font-mono text-lg font-bold" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-6">
                    <div class="card bg-card border border-border shadow-sm h-full flex flex-col">
                        
                        <div class="p-4 border-b border-border bg-muted/20 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <Layers :size="18" class="text-primary"/>
                                <h3 class="font-bold text-foreground text-sm uppercase tracking-wide">Contenido del Pack</h3>
                            </div>
                            <span class="badge badge-outline text-[10px]">{{ form.items.length }} Items</span>
                        </div>

                        <div class="p-5 flex-1 space-y-4">
                            
                            <TransitionGroup name="list" tag="div" class="space-y-3">
                                <div v-for="(item, index) in form.items" :key="index" 
                                     class="flex flex-col sm:flex-row gap-3 items-start sm:items-center p-3 rounded-xl border border-border bg-background hover:border-primary/30 transition-colors shadow-sm group">
                                    
                                    <div class="flex-1 w-full sm:w-auto space-y-1">
                                        <label class="text-[10px] text-muted-foreground font-bold uppercase ml-1">Producto</label>
                                        <select v-model="item.sku_id" class="form-input w-full h-10 text-xs bg-muted/10 cursor-pointer" :class="{'border-error': form.errors[`items.${index}.sku_id`]}">
                                            <option value="" disabled>Seleccionar item...</option>
                                            <option v-for="sku in skus.data" :key="sku.id" :value="sku.id">
                                                {{ sku.name }} ({{ sku.code }})
                                            </option>
                                        </select>
                                    </div>

                                    <div class="w-full sm:w-24 flex flex-col space-y-1">
                                        <label class="text-[10px] text-muted-foreground font-bold uppercase ml-1 text-center">Cant.</label>
                                        <div class="flex items-center">
                                            <button type="button" @click="item.quantity > 1 ? item.quantity-- : null" class="w-8 h-10 border border-r-0 border-border rounded-l-lg bg-muted/20 hover:bg-muted flex items-center justify-center">-</button>
                                            <input v-model="item.quantity" type="number" min="1" class="w-full h-10 border-y border-border text-center font-bold text-sm focus:outline-none focus:border-primary bg-background" />
                                            <button type="button" @click="item.quantity++" class="w-8 h-10 border border-l-0 border-border rounded-r-lg bg-muted/20 hover:bg-muted flex items-center justify-center">+</button>
                                        </div>
                                    </div>

                                    <button type="button" @click="removeItem(index)" 
                                            class="btn btn-square btn-ghost h-10 w-10 text-muted-foreground hover:text-error hover:bg-error/10 mt-auto sm:mt-5"
                                            :disabled="form.items.length === 1"
                                            :class="{'opacity-50 cursor-not-allowed': form.items.length === 1}">
                                        <Trash2 :size="18"/>
                                    </button>
                                </div>
                            </TransitionGroup>

                            <button type="button" @click="addItem" class="w-full py-3 border-2 border-dashed border-border rounded-xl flex items-center justify-center gap-2 text-muted-foreground hover:text-primary hover:border-primary/50 hover:bg-primary/5 transition-all">
                                <Plus :size="18" />
                                <span class="text-sm font-bold">Agregar otro producto</span>
                            </button>

                        </div>
                    </div>
                </div>

                <div class="lg:col-span-12 mt-4">
                    <button type="submit" :disabled="form.processing" 
                            class="btn btn-primary w-full h-14 text-lg font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all active:scale-[0.99]">
                        <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                        <span v-else class="flex items-center gap-2">
                            <Save :size="20" /> Actualizar Pack
                        </span>
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}
</style>