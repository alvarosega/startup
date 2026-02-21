<script setup>
import { ref, watch } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, Plus, ChevronDown, ChevronUp, Edit3, Trash2, 
    AlertTriangle, CheckCircle2, Filter, PackageSearch, Barcode,
    Layers, Tag
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    filters: Object,
    brands: Array,
    categories: Array
});

// --- ESTADO ---
const search = ref(props.filters.search || '');
const expandedRows = ref(new Set());

// --- LÓGICA DE ACORDEÓN ---
const toggleRow = (id) => {
    if (expandedRows.value.has(id)) expandedRows.value.delete(id);
    else expandedRows.value.add(id);
};

// --- BÚSQUEDA REACTIVA ---
watch(search, debounce((value) => {
    router.get(route('admin.products.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const deleteProduct = (id) => {
    if (confirm('¿Mover producto y variantes a la papelera?')) {
        router.delete(route('admin.products.destroy', id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Catálogo de Productos" />

        <div class="max-w-7xl mx-auto space-y-6 pb-20">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tighter">Catálogo</h1>
                    <p class="text-xs font-bold text-muted-foreground uppercase">Gestión de Productos y Variantes</p>
                </div>
                <Link :href="route('admin.products.create')" class="btn btn-primary shadow-lg shadow-primary/20">
                    <Plus :size="18" class="mr-2" /> Nuevo Producto
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-card p-4 rounded-2xl border border-border shadow-sm">
                <div class="relative md:col-span-2">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                    <input v-model="search" type="text" placeholder="Buscar por nombre o EAN de variante..." 
                           class="form-input pl-10 h-11 bg-background border-none ring-1 ring-border focus:ring-2 focus:ring-primary">
                </div>
                <select class="form-input h-11" @change="e => router.get(route('admin.products.index'), { status: e.target.value })">
                    <option value="">Cualquier Salud</option>
                    <option value="incomplete">Incompletos (Sin SKUs)</option>
                    <option value="complete">Completos</option>
                </select>
                <div class="flex items-center justify-center text-xs font-black uppercase text-muted-foreground gap-2">
                    <Filter :size="14" /> Filtros Avanzados
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-border bg-background shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-muted/30 border-b border-border">
                        <tr class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                            <th class="px-6 py-4 w-12 text-center">Salud</th>
                            <th class="px-6 py-4">Producto / Maestro</th>
                            <th class="px-6 py-4">Categoría / Marca</th>
                            <th class="px-6 py-4 text-center">Variantes</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/50">
                        <template v-for="product in products.data" :key="product.id">
                            <tr :class="[
                                'hover:bg-muted/10 transition-colors',
                                product.skus_count === 0 ? 'border-l-4 border-l-error bg-error/5' : 'border-l-4 border-l-transparent'
                            ]">
                                <td class="px-6 py-4 text-center">
                                    <AlertTriangle v-if="product.skus_count === 0" class="text-error mx-auto" :size="20" />
                                    <CheckCircle2 v-else class="text-success mx-auto" :size="20" />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-lg bg-muted flex items-center justify-center overflow-hidden border border-border">
                                            <img v-if="product.image_url" :src="product.image_url" class="object-cover w-full h-full">
                                            <PackageSearch v-else :size="20" class="text-muted-foreground/40" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-black uppercase tracking-tight">{{ product.name }}</p>
                                            <p class="text-[10px] font-mono text-muted-foreground">{{ product.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold uppercase flex items-center gap-1">
                                            <Layers :size="10" /> {{ product.category?.name || '---' }}
                                        </span>
                                        <span class="text-[10px] font-bold text-muted-foreground uppercase flex items-center gap-1">
                                            <Tag :size="10" /> {{ product.brand?.name || '---' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="toggleRow(product.id)" 
                                            class="badge gap-2 cursor-pointer hover:bg-primary hover:text-white transition-all py-3 px-4"
                                            :class="product.skus_count === 0 ? 'badge-error' : 'badge-outline'">
                                        {{ product.skus_count }} Variantes
                                        <ChevronDown v-if="!expandedRows.has(product.id)" :size="14" />
                                        <ChevronUp v-else :size="14" />
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('admin.products.skus.create', product.id)" 
                                              class="btn btn-ghost btn-xs text-primary" title="Añadir Variantes">
                                            <Plus :size="16" />
                                        </Link>
                                        <Link :href="route('admin.products.edit', product.id)" 
                                              class="btn btn-ghost btn-xs" title="Editar Maestro">
                                            <Edit3 :size="16" />
                                        </Link>
                                        <button @click="deleteProduct(product.id)" 
                                                class="btn btn-ghost btn-xs text-error" title="Eliminar">
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="expandedRows.has(product.id)" class="bg-muted/5 animate-in fade-in zoom-in-95">
                                <td colspan="5" class="px-6 py-4">
                                    <div class="border-l-2 border-primary/30 ml-8 pl-6 space-y-3">
                                        <div v-if="product.skus.length === 0" class="py-4 text-xs font-bold text-error flex items-center gap-2">
                                            <AlertTriangle :size="14" /> Este producto no puede activarse sin variantes.
                                        </div>
                                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            <div v-for="sku in product.skus" :key="sku.id" 
                                                 class="bg-background border border-border p-3 rounded-xl flex justify-between items-center group">
                                                <div class="flex items-center gap-3">
                                                    <Barcode :size="14" class="text-muted-foreground" />
                                                    <div>
                                                        <p class="text-[11px] font-black uppercase">{{ sku.name }}</p>
                                                        <p class="text-[9px] font-mono text-muted-foreground">{{ sku.code || 'SIN EAN' }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <span class="text-xs font-black text-success">${{ sku.price }}</span>
                                                    <Link :href="route('admin.skus.edit', sku.id)" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <Edit3 :size="12" class="text-muted-foreground" />
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.form-input { @apply rounded-xl border-border text-sm; }
.badge-error { @apply bg-error/10 text-error border-error/20 font-black; }
</style>