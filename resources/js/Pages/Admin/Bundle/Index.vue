<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Plus, Edit2, Trash2, X, Search, CheckSquare, 
    Square, Layers, Tag, Image as ImageIcon, Save, AlertCircle 
} from 'lucide-vue-next';

const props = defineProps({
    bundles: { type: Array, required: true },
    availableSkus: { type: Array, required: true }
});

// Estados de control del modal e interfaz
const isModalOpen = ref(false);
const isEditing = ref(false);
const currentBundleId = ref(null);
const skuSearchQuery = ref('');
const imagePreview = ref(null);

// Formulario reactivo de Inertia acoplado al DTO del backend
const form = useForm({
    _method: 'POST', // Control nativo para spoofing de archivos en Laravel
    name: '',
    type: 'OFFER',
    is_active: true,
    image: null,
    sku_ids: []
});

// Buscador predictivo de SKUs disponibles (filtra por nombre o código)
const filteredAvailableSkus = computed(() => {
    const query = skuSearchQuery.value.toLowerCase().trim();
    if (!query) return props.availableSkus;
    return props.availableSkus.filter(sku => 
        sku.name.toLowerCase().includes(query) || 
        sku.code.toLowerCase().includes(query)
    );
});

// Mapeo detallado de los SKUs que ya están seleccionados dentro del formulario
const selectedSkusDetails = computed(() => {
    return props.availableSkus.filter(sku => form.sku_ids.includes(sku.id));
});

// Controladores de selección (Toggle de ítems)
const toggleSkuSelection = (skuId) => {
    const index = form.sku_ids.indexOf(skuId);
    if (index === -1) {
        form.sku_ids.push(skuId);
    } else {
        form.sku_ids.splice(index, 1);
    }
};

const removeSku = (skuId) => {
    const index = form.sku_ids.indexOf(skuId);
    if (index !== -1) {
        form.sku_ids.splice(index, 1);
    }
};

// Procesamiento de imágenes (Previsualización local segura)
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    form.image = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

// Disparadores del ciclo modal
const openCreateModal = () => {
    isEditing.value = false;
    currentBundleId.value = null;
    imagePreview.value = null;
    skuSearchQuery.value = '';
    form.reset();
    form.clearErrors();
    form._method = 'POST';
    isModalOpen.value = true;
};

const openEditModal = (bundle) => {
    isEditing.value = true;
    currentBundleId.value = bundle.id;
    skuSearchQuery.value = '';
    form.clearErrors();
    
    form.name = bundle.name;
    form.type = bundle.type;
    form.is_active = bundle.is_active;
    form.image = null;
    form.sku_ids = bundle.skus.map(s => s.id);
    form._method = 'PUT'; // Forzar spoofing de método HTTP PUT para multipart/form-data
    
    imagePreview.value = bundle.image;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

// Operaciones de persistencia asíncronas
const submitForm = () => {
    if (isEditing.value) {
        // Al enviar archivos mediante PUT, Laravel exige mandarlo vía POST inyectando el parámetro _method
        form.post(route('bundles.update', currentBundleId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('bundles.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    }
};

const deleteBundle = (id) => {
    if (confirm('¿Está completamente seguro de eliminar este grupo/plantilla comercial? Esta acción limpiará las relaciones de catálogo.')) {
        router.delete(route('bundles.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Gestión de Ofertas y Plantillas Masivas
        </template>

        <div class="space-y-6">
            <div class="flex items-center justify-between bg-card border p-4 rounded-xl shadow-sm">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Estructuras de Campaña</h3>
                    <p class="text-xs text-muted-foreground">Agrupación de SKUs orientados a ofertas volumétricas independientes o inyecciones rápidas al carrito.</p>
                </div>
                <button @click="openCreateModal" class="btn-primary flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider">
                    <Plus :size="16" /> Crear Grupo
                </button>
            </div>

            <div class="border rounded-xl bg-card overflow-x-auto shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-muted/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                            <th class="p-4 w-20">Imagen</th>
                            <th class="p-4">Nombre de la Campaña</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4">Componentes</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y">
                        <tr v-for="bundle in bundles" :key="bundle.id" class="hover:bg-muted/30 transition-colors">
                            <td class="p-4">
                                <div class="w-12 h-12 rounded-lg border bg-muted flex items-center justify-center overflow-hidden">
                                    <img v-if="bundle.image" :src="bundle.image" class="w-full h-full object-cover" />
                                    <Layers v-else :size="18" class="text-muted-foreground/40" />
                                </div>
                            </td>
                            <td class="p-4 font-medium text-foreground">
                                {{ bundle.name }}
                                <div class="text-[11px] font-mono text-muted-foreground mt-0.5">ID: {{ bundle.id }}</div>
                            </td>
                            <td class="p-4">
                                <span v-if="bundle.type === 'OFFER'" class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-purple-500/10 text-purple-600 border border-purple-500/20">
                                    Oferta Volumétrica
                                </span>
                                <span v-else class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-blue-500/10 text-blue-600 border border-blue-500/20">
                                    Plantilla Rápida
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="font-bold text-foreground">{{ bundle.skus_count }}</span> 
                                <span class="text-xs text-muted-foreground"> SKUs enlazados</span>
                            </td>
                            <td class="p-4">
                                <span :class="bundle.is_active ? 'text-green-600' : 'text-muted-foreground'" class="text-xs font-bold uppercase tracking-wider">
                                    {{ bundle.is_active ? '● Activo' : '○ Inactivo' }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(bundle)" class="p-2 border rounded-lg hover:bg-muted text-muted-foreground transition-colors" title="Editar Estructura">
                                        <Edit2 :size="14" />
                                    </button>
                                    <button @click="deleteBundle(bundle.id)" class="p-2 border rounded-lg hover:bg-destructive/10 text-destructive transition-colors" title="Eliminar Registro">
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="bundles.length === 0">
                            <td colspan="6" class="p-8 text-center text-muted-foreground text-xs uppercase tracking-widest">
                                No se registran ofertas o plantillas comerciales en este nodo.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
                <div class="bg-card border rounded-2xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                    
                    <div class="flex items-center justify-between p-4 border-b">
                        <div class="flex items-center gap-2">
                            <Layers :size="18" class="text-primary" />
                            <h2 class="text-sm font-black uppercase tracking-wider">
                                {{ isEditing ? 'Modificar Parámetros de Estructura' : 'Definir Nueva Campaña / Agrupador' }}
                            </h2>
                        </div>
                        <button @click="closeModal" class="p-1.5 hover:bg-muted rounded-lg text-muted-foreground transition-colors">
                            <X :size="16" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto p-6 space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2 space-y-4">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-bold uppercase text-muted-foreground">Nombre Comercial</label>
                                    <input type="text" v-model="form.name" class="w-full border rounded-lg p-2.5 bg-background text-sm font-medium" placeholder="Ej: Especial Bodegas de Altura" required />
                                    <div v-if="form.errors.name" class="text-xs text-destructive font-semibold flex items-center gap-1 mt-1"><AlertCircle :size="12" /> {{ form.errors.name }}</div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1">
                                        <label class="text-xs font-bold uppercase text-muted-foreground">Tipo de Comportamiento</label>
                                        <select v-model="form.type" class="w-full border rounded-lg p-2.5 bg-background text-sm font-medium">
                                            <option value="OFFER">Oferta Volumétrica (Control de Mínimo)</option>
                                            <option value="TEMPLATE">Plantilla (Agregado Masivo de Ítems)</option>
                                        </select>
                                        <div v-if="form.errors.type" class="text-xs text-destructive font-semibold flex items-center gap-1 mt-1"><AlertCircle :size="12" /> {{ form.errors.type }}</div>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="text-xs font-bold uppercase text-muted-foreground">Estado Operativo</label>
                                        <div class="flex items-center h-full">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                                                <div class="w-11 h-6 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-background after:border-muted after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                                <span class="ml-3 text-xs font-bold uppercase tracking-wider text-foreground">
                                                    {{ form.is_active ? 'Habilitado' : 'Deshabilitado' }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Banner Promocional</label>
                                <div class="flex-1 border border-dashed rounded-xl p-4 flex flex-col items-center justify-center relative min-h-[140px] bg-muted/10 overflow-hidden group">
                                    <template v-if="imagePreview">
                                        <img :src="imagePreview" class="absolute inset-0 w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-150">
                                            <label class="bg-background text-foreground text-[11px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-lg cursor-pointer shadow-md">
                                                Cambiar Imagen
                                                <input type="file" @change="handleImageUpload" class="hidden" accept="image/*" />
                                            </label>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <ImageIcon :size="24" class="text-muted-foreground/40 mb-2" />
                                        <label class="text-[11px] bg-primary text-primary-foreground px-3 py-1.5 rounded-lg font-bold uppercase tracking-wider cursor-pointer shadow-sm">
                                            Cargar Archivo
                                            <input type="file" @change="handleImageUpload" class="hidden" accept="image/*" />
                                        </label>
                                        <span class="text-[9px] text-muted-foreground mt-2 uppercase">Max: 2MB (PNG, JPG, WEBP)</span>
                                    </template>
                                </div>
                                <div v-if="form.errors.image" class="text-xs text-destructive font-semibold flex items-center gap-1 mt-1"><AlertCircle :size="12" /> {{ form.errors.image }}</div>
                            </div>
                        </div>

                        <hr class="border-border" />

                        <div class="space-y-3">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-black uppercase text-primary tracking-widest flex items-center gap-1">
                                    <Search :size="14" /> Motor Predictivo de Selección de Catálogo
                                </label>
                                <div class="relative mt-1">
                                    <Search :size="16" class="absolute left-3 top-3 text-muted-foreground/50" />
                                    <input type="text" v-model="skuSearchQuery" class="w-full border rounded-lg pl-10 pr-4 py-2.5 bg-background text-sm font-medium" placeholder="Buscar SKUs activos por código de barra, código interno o descripción de producto..." />
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1.5 p-3 bg-muted/30 rounded-xl border min-h-[46px]">
                                <span v-for="sku in selectedSkusDetails" :key="sku.id" class="inline-flex items-center gap-1.5 bg-background border text-foreground px-2.5 py-1 rounded-md text-xs font-medium font-mono shadow-sm animate-in fade-in duration-100">
                                    <Tag :size="10" class="text-primary" />
                                    <span class="font-bold text-primary">[{{ sku.code }}]</span> {{ sku.name }}
                                    <button type="button" @click="removeSku(sku.id)" class="text-muted-foreground hover:text-destructive rounded-full p-0.5 hover:bg-muted transition-colors">
                                        <X :size="12" />
                                    </button>
                                </span>
                                <div v-if="form.sku_ids.length === 0" class="text-xs text-muted-foreground/60 italic p-0.5">
                                    Ningún SKU asignado a la estructura actualmente. Utilice la grilla inferior para inyectar componentes.
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-muted-foreground flex items-center gap-1">
                                Grilla de Asignación Masiva de Componentes ({{ filteredAvailableSkus.length }} ítems filtrados)
                            </label>
                            <div class="border rounded-xl max-h-[220px] overflow-y-auto bg-background divide-y shadow-inner">
                                <div v-for="sku in filteredAvailableSkus" :key="sku.id" 
                                    @click="toggleSkuSelection(sku.id)" 
                                    class="flex items-center justify-between p-3 cursor-pointer select-none transition-colors"
                                    :class="form.sku_ids.includes(sku.id) ? 'bg-primary/5 hover:bg-primary/10' : 'hover:bg-muted/50'"
                                >
                                    <div class="flex items-center gap-3">
                                        <button type="button" class="text-primary focus:outline-none">
                                            <CheckSquare v-if="form.sku_ids.includes(sku.id)" :size="18" />
                                            <Square v-else :size="18" class="text-muted-foreground/40" />
                                        </button>
                                        <div class="text-xs font-mono font-bold text-foreground">
                                            {{ sku.code }}
                                        </div>
                                        <div class="text-xs font-medium text-muted-foreground">
                                            {{ sku.name }}
                                        </div>
                                    </div>
                                    <div class="text-[10px] font-mono text-muted-foreground/50 pr-2">
                                        {{ sku.id.substring(0,8) }}...
                                    </div>
                                </div>
                                <div v-if="filteredAvailableSkus.length === 0" class="p-6 text-center text-xs text-muted-foreground uppercase tracking-wider">
                                    Ningún artículo en el catálogo coincide con la consulta ingresada.
                                </div>
                            </div>
                            <div v-if="form.errors.sku_ids" class="text-xs text-destructive font-semibold flex items-center gap-1 mt-1"><AlertCircle :size="12" /> {{ form.errors.sku_ids }}</div>
                        </div>
                    </form>

                    <div class="p-4 border-t bg-muted/30 flex items-center justify-end gap-3 rounded-b-2xl">
                        <button type="button" @click="closeModal" class="border rounded-lg px-4 py-2 text-xs font-bold uppercase tracking-wider bg-background text-foreground hover:bg-muted transition-colors">
                            Cancelar
                        </button>
                        <button type="button" @click="submitForm" :disabled="form.processing || form.sku_ids.length === 0" class="btn-primary flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider disabled:opacity-40">
                            <Save :size="14" /> {{ form.processing ? 'Sincronizando...' : isEditing ? 'Guardar Cambios' : 'Estructurar Grupo' }}
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
/* Customización del scroll interno para la grilla de checkboxes */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    @apply bg-transparent;
}
::-webkit-scrollbar-thumb {
    @apply bg-neutral-500/20 rounded-full hover:bg-neutral-500/40;
}
</style>