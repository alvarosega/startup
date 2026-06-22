<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Megaphone, Sliders, Image, Plus, Trash2, Save, X, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    creatives: Array,
    campaigns: Array,
    placements: Array,
    branches: Array,
    categories: Array,
    brands: Array,
    bundles: Array,
    skus: Array
});

const activeTab = ref('creatives'); // 'creatives', 'campaigns', 'placements'
const showCreativeModal = ref(false);
const editingCreative = ref(null);

// Control de Opción A para el Destino de Redirección Inteligente del Banner
const destinationType = ref('sku'); // 'sku', 'category', 'brand', 'bundle', 'none'

const creativeForm = useForm({
    campaign_id: '',
    placement_id: '',
    branch_id: '',
    name: '',
    image_mobile: null,
    image_desktop: null,
    action_type: 'NAVIGATE',
    sort_order: 0,
    is_active: true,
    sku_id: null,
    category_id: null,
    brand_id: null,
    bundle_id: null
});

const openCreativeModal = (creative = null) => {
    if (creative) {
        editingCreative.value = creative;
        creativeForm.campaign_id = creative.campaign_id;
        creativeForm.placement_id = creative.placement_id;
        creativeForm.branch_id = creative.branch_id;
        creativeForm.name = creative.name;
        creativeForm.action_type = creative.action_type;
        creativeForm.sort_order = creative.sort_order;
        creativeForm.is_active = Boolean(creative.is_active);
        
        // Resolver tipo de destino guardado (Opción A)
        if (creative.sku_id) { destinationType.value = 'sku'; creativeForm.sku_id = creative.sku_id; }
        else if (creative.category_id) { destinationType.value = 'category'; creativeForm.category_id = creative.category_id; }
        else if (creative.brand_id) { destinationType.value = 'brand'; creativeForm.brand_id = creative.brand_id; }
        else if (creative.bundle_id) { destinationType.value = 'bundle'; creativeForm.bundle_id = creative.bundle_id; }
        else { destinationType.value = 'none'; }
    } else {
        editingCreative.value = null;
        creativeForm.reset();
        destinationType.value = 'sku';
    }
    showCreativeModal.value = true;
};

const closeCreativeModal = () => {
    showCreativeModal.value = false;
    editingCreative.value = null;
    creativeForm.reset();
};

const saveCreative = () => {
    // Limpiar campos excluidos antes de despachar al backend (Garantía Opción A)
    creativeForm.sku_id = destinationType.value === 'sku' ? creativeForm.sku_id : null;
    creativeForm.category_id = destinationType.value === 'category' ? creativeForm.category_id : null;
    creativeForm.brand_id = destinationType.value === 'brand' ? creativeForm.brand_id : null;
    creativeForm.bundle_id = destinationType.value === 'bundle' ? creativeForm.bundle_id : null;

    if (editingCreative.value) {
        creativeForm.transform((data) => ({ ...data, _method: 'PUT' }))
            .post(route('admin.retail-media.ad-creatives.update', editingCreative.value.id), {
                onSuccess: () => closeCreativeModal()
            });
    } else {
        creativeForm.post(route('admin.retail-media.ad-creatives.store'), {
            onSuccess: () => closeCreativeModal()
        });
    }
};

const deleteCreative = (id) => {
    if (confirm('¿ELIMINAR BANNER? Se removerán los binarios físicos del storage.')) {
        router.delete(route('admin.retail-media.ad-creatives.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Consola de Monetización y Retail Media" />
    <AdminLayout>
        <template #header>
            <div class="select-none font-sans">
                <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Suite de Retail Media</h1>
                <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-mono tracking-wider uppercase mt-0.5">Control paramétrico de campañas B2B, slots de monetización y banners informativos</p>
            </div>
        </template>

        <div class="space-y-4 font-mono text-xs">
            <div class="border-b border-neutral-200 dark:border-neutral-800 flex justify-between items-center select-none">
                <div class="flex gap-2 text-[10px] font-black uppercase">
                    <button @click="activeTab = 'creatives'" :class="activeTab === 'creatives' ? 'border-neutral-900 text-neutral-900 border-b-2 dark:border-white dark:text-white' : 'text-neutral-400'" class="pb-2 px-2 flex items-center gap-1.5 transition-all"><Image :size="13"/> Banners (Creativos)</button>
                    <button @click="activeTab = 'campaigns'" :class="activeTab === 'campaigns' ? 'border-neutral-900 text-neutral-900 border-b-2 dark:border-white dark:text-white' : 'text-neutral-400'" class="pb-2 px-2 flex items-center gap-1.5 transition-all"><Megaphone :size="13"/> Campañas Corporativas</button>
                    <button @click="activeTab = 'placements'" :class="activeTab === 'placements' ? 'border-neutral-900 text-neutral-900 border-b-2 dark:border-white dark:text-white' : 'text-neutral-400'" class="pb-2 px-2 flex items-center gap-1.5 transition-all"><Sliders :size="13"/> Slots (Posicionamientos)</button>
                </div>
                <button v-if="activeTab === 'creatives'" @click="openCreativeModal()" class="mb-2 bg-neutral-900 text-white dark:bg-white dark:text-neutral-950 px-3 py-1.5 text-[10px] font-bold rounded uppercase flex items-center gap-1"><Plus :size="12"/> INYECTAR_BANNER</button>
            </div>

            <div v-if="activeTab === 'creatives'" class="border border-neutral-200 dark:border-neutral-800 rounded-md bg-white dark:bg-neutral-900 shadow-sm overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/70 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-800 text-[10px] font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 select-none">
                            <th class="p-3 w-1/4">CREATIVO_DESIGNACIÓN</th>
                            <th class="p-3 w-40">SLOT (POSITION)</th>
                            <th class="p-3 w-44">NODO LOGÍSTICO</th>
                            <th class="p-3">REDIRECCIÓN DESTINO (ON_CLICK)</th>
                            <th class="p-3 w-20 text-center">ORDEN</th>
                            <th class="p-3 w-24 text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        <tr v-for="cr in creatives" :key="cr.id" class="hover:bg-neutral-50/40 dark:hover:bg-neutral-800/10 transition-colors">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative group select-none shrink-0">
                                        <img :src="cr.image_mobile_url" class="w-12 h-7 rounded border border-neutral-200 bg-neutral-100 dark:border-neutral-700 object-cover" />
                                        <img v-if="cr.image_desktop_url" :src="cr.image_desktop_url" class="absolute hidden group-hover:block z-50 max-w-xs border rounded shadow-flat top-8 left-0 bg-white dark:bg-neutral-900 p-1" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-neutral-900 dark:text-white truncate font-bold uppercase tracking-tight select-all">{{ cr.name }}</div>
                                        <div class="text-[9px] text-neutral-400 truncate uppercase mt-0.5 select-none">Camp: {{ cr.campaign_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 font-bold text-[11px] text-neutral-700 dark:text-neutral-300 select-all">{{ cr.placement_name }}</td>
                            <td class="p-3 text-neutral-400 uppercase select-none">{{ cr.branch_name }}</td>
                            <td class="p-3">
                                <div v-if="cr.sku_id" class="text-neutral-800 dark:text-neutral-200 font-bold truncate">[PROD]: {{ cr.sku_name }}</div>
                                <div v-else-if="cr.bundle_id" class="text-indigo-600 dark:text-indigo-400 font-bold truncate">[COMBO]: {{ cr.bundle_name }}</div>
                                <div v-else-if="cr.category_id" class="text-neutral-500 truncate">[CAT]: {{ cr.category_name }}</div>
                                <div v-else-if="cr.brand_id" class="text-neutral-500 truncate">[BRAND]: {{ cr.brand_name }}</div>
                                <div v-else class="text-neutral-400 italic select-none">INFORMATIVO (SIN REDIRECCIÓN)</div>
                            </td>
                            <td class="p-3 text-center font-bold text-neutral-900 dark:text-white select-all">{{ cr.sort_order }}</td>
                            <td class="p-3 text-right select-none">
                                <div class="flex justify-end gap-1.5">
                                    <button @click="openCreativeModal(cr)" class="text-neutral-400 hover:text-neutral-900 dark:hover:text-white p-1 rounded border border-transparent hover:border-neutral-200 dark:hover:border-neutral-700 transition-all">Editar</button>
                                    <button @click="deleteCreative(cr.id)" class="text-neutral-300 hover:text-rose-600 p-1 rounded border border-transparent hover:border-rose-200 dark:hover:border-rose-900/40 transition-all">Borrar</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="p-8 border border-dashed border-neutral-200 dark:border-neutral-800 text-center text-neutral-400 rounded-md bg-white dark:bg-neutral-900">
                Módulos de Campaña y Placements Operativos en Backend. Interfaces de configuración CRUD síncronas omitidas en este bloque.
            </div>
        </div>

        <div v-if="showCreativeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-950/40 font-mono text-xs select-none">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md shadow-flat w-full max-w-2xl overflow-hidden flex flex-col justify-between">
                <div class="flex justify-between items-center bg-neutral-50 dark:bg-neutral-950/40 border-b border-neutral-200 dark:border-neutral-800 p-3">
                    <h3 class="text-[11px] font-black uppercase text-neutral-900 dark:text-white tracking-wider italic">// PARÁMETROS_DEL_CREATIVO</h3>
                    <button @click="closeCreativeModal" class="p-1 rounded text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800"><X :size="14"/></button>
                </div>

                <form @submit.prevent="saveCreative" class="p-4 space-y-3 max-h-[75vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Campaña Madre *</label>
                            <select v-model="creativeForm.campaign_id" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none">
                                <option v-for="cp in campaigns" :key="cp.id" :value="cp.id">{{ cp.name.toUpperCase() }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Slot Publicitario *</label>
                            <select v-model="creativeForm.placement_id" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none">
                                <option v-for="pl in placements" :key="pl.id" :value="pl.id">{{ pl.name.toUpperCase() }} [{{ pl.code }}]</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Sucursal Despliegue *</label>
                            <select v-model="creativeForm.branch_id" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none">
                                <option v-for="br in branches" :key="br.id" :value="br.id">{{ br.name.toUpperCase() }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Designación Corta *</label>
                            <input v-model="creativeForm.name" type="text" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none" placeholder="BANN_HERO_GIN_01" />
                        </div>
                    </div>

                    <div class="border border-neutral-200 dark:border-neutral-800 rounded p-3 bg-neutral-50 dark:bg-neutral-950/40 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="font-bold text-indigo-500">// TIPO_DESTINO_FILTRO</label>
                                <select v-model="destinationType" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none">
                                    <option value="none">INFORMATIVO (ESTÁTICO)</option>
                                    <option value="sku">PRODUCTO INDIVIDUAL (SKU)</option>
                                    <option value="bundle">COMBO AGRUPADO (BUNDLE)</option>
                                    <option value="category">CATEGORÍA COMPLETA</option>
                                    <option value="brand">MARCA CORPORATIVA</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="font-bold text-neutral-400">Comportamiento Evento</label>
                                <select v-model="creativeForm.action_type" :disabled="destinationType === 'none'" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none disabled:opacity-35">
                                    <option value="NAVIGATE">NAVEGAR / REDIRIGIR PANTALLA</option>
                                    <option value="ADD_TO_CART" :disabled="!['sku', 'bundle'].includes(destinationType)">INYECCIÓN DIRECTA AL CARRITO</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="destinationType !== 'none'" class="space-y-1 pt-1 border-t border-neutral-200/60 dark:border-neutral-800/60">
                            <label class="font-bold text-neutral-900 dark:text-white uppercase">Seleccione Entidad Destino Objetivo *</label>
                            
                            <select v-if="destinationType === 'sku'" v-model="creativeForm.sku_id" required class="w-full bg-white dark:bg-neutral-900 border border-amber-500/40 rounded px-2 py-1.5 text-neutral-900 dark:text-neutral-50 font-bold uppercase outline-none">
                                <option value="null" disabled>Seleccione SKU del maestro comercial...</option>
                                <option v-for="s in skus" :key="s.id" :value="s.id">{{ s.name }} [{{ s.code }}]</option>
                            </select>

                            <select v-if="destinationType === 'bundle'" v-model="creativeForm.bundle_id" required class="w-full bg-white dark:bg-neutral-900 border border-amber-500/40 rounded px-2 py-1.5 text-neutral-900 dark:text-neutral-50 font-bold uppercase outline-none">
                                <option value="null" disabled>Seleccione Macro Combo Promocional...</option>
                                <option v-for="b in bundles" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>

                            <select v-if="destinationType === 'category'" v-model="creativeForm.category_id" required class="w-full bg-white dark:bg-neutral-900 border border-amber-500/40 rounded px-2 py-1.5 text-neutral-900 dark:text-neutral-50 font-bold uppercase outline-none">
                                <option value="null" disabled>Seleccione Nodo del Árbol de Categorías...</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name.toUpperCase() }}</option>
                            </select>

                            <select v-if="destinationType === 'brand'" v-model="creativeForm.brand_id" required class="w-full bg-white dark:bg-neutral-900 border border-amber-500/40 rounded px-2 py-1.5 text-neutral-900 dark:text-neutral-50 font-bold uppercase outline-none">
                                <option value="null" disabled>Seleccione Sello de Marca...</option>
                                <option v-for="br in brands" :key="br.id" :value="br.id">{{ br.name.toUpperCase() }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Imagen Mobile (3:2 Aspect Ratio) *</label>
                            <input type="file" accept="image/jpeg,image/png,image/webp" @input="creativeForm.image_mobile = $event.target.files[0]" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-neutral-500 text-[11px]" />
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Imagen Desktop (16:9 Banner Wide)</label>
                            <input type="file" accept="image/jpeg,image/png,image/webp" @input="creativeForm.image_desktop = $event.target.files[0]" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-neutral-500 text-[11px]" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 border-t border-neutral-200 dark:border-neutral-800 pt-3">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Orden Prioridad (Sort)</label>
                            <input v-model.number="creativeForm.sort_order" type="number" min="0" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-right text-neutral-900 dark:text-neutral-50" />
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Estado Banner</label>
                            <select v-model="creativeForm.is_active" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 font-bold text-neutral-900 dark:text-neutral-50">
                                <option :value="true">VISIBLE EN CONSUMIDOR</option>
                                <option :value="false">CONFINADO / BAJA</option>
                            </select>
                        </div>
                    </div>

                    <div class="border-t border-neutral-200 dark:border-neutral-800 pt-3 flex justify-end gap-2">
                        <button type="button" @click="closeCreativeModal" class="px-4 py-1.5 border border-neutral-200 dark:border-neutral-800 text-neutral-500 rounded uppercase text-[10px] font-bold">Cancelar</button>
                        <button type="submit" :disabled="creativeForm.processing" class="px-6 py-1.5 bg-neutral-900 text-white dark:bg-white dark:text-neutral-950 font-bold uppercase rounded text-[10px] flex items-center gap-1">
                            <Save :size="12"/> COMPROMETER_BANNER
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>