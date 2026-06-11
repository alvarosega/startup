<script setup>
import { ref } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Tag, Plus, Edit, Trash2, Search, Coins,
    ChevronDown, ChevronRight, Package, Eye, EyeOff
} from 'lucide-vue-next';

const props = defineProps({
    products: Object,
    filters: Object,
    options: Object,
    can_manage: Boolean
});

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const selectedBrand = ref(props.filters.brand || '');
const selectedStatus = ref(props.filters.status || '');

// Registro indexado de filas maestras expandidas
const expandedRows = ref([]);

const handleFilter = () => {
    router.get(route('admin.products.index'), {
        search: search.value,
        category: selectedCategory.value,
        brand: selectedBrand.value,
        status: selectedStatus.value
    }, {
        preserveState: true,
        replace: true
    });
};

const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
    }
};

const destroyProduct = (id, name) => {
    if (confirm(`¿Proceder con la remoción atómica del maestro y SKUs de: ${name}?`)) {
        router.delete(route('admin.products.destroy', id));
    }
};

const destroySku = (id) => {
    if (confirm('¿Remover esta variante física del catálogo permanente?')) {
        router.delete(route('admin.products.skus.destroy', id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Catálogo - Maestros de Producto" />

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-card p-4 rounded-xl border border-border shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg text-primary">
                        <Tag :size="24" />
                    </div>
                    <div>
                        <h1 class="font-sans font-bold text-xl text-foreground">Maestros de Producto</h1>
                        <p class="text-xs text-muted-foreground">Estructuras base del inventario comercializable</p>
                    </div>
                </div>
                
                <Link v-if="can_manage" :href="route('admin.products.create')" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg shadow-sm hover:bg-primary/90 transition-colors">
                    <Plus :size="16" />
                    Materializar Producto
                </Link>
            </div>

            <div class="bg-card border border-border rounded-xl p-4 flex flex-col lg:flex-row gap-3 shadow-sm">
                <div class="flex items-center gap-2 flex-1 bg-background border border-border rounded-lg px-3 py-1.5 focus-within:ring-1 focus-within:ring-primary">
                    <Search :size="16" class="text-muted-foreground" />
                    <input v-model="search" @input="handleFilter" type="text" placeholder="Buscar por concordancia o EAN de variante..." class="w-full bg-transparent text-sm text-foreground outline-none border-none p-0 focus:ring-0" />
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <select v-model="selectedCategory" @change="handleFilter" class="bg-background border border-border rounded-lg px-3 py-1.5 text-sm text-foreground outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Todos los Pasillos</option>
                        <option v-for="cat in options.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>

                    <select v-model="selectedBrand" @change="handleFilter" class="bg-background border border-border rounded-lg px-3 py-1.5 text-sm text-foreground outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Todas las Marcas</option>
                        <option v-for="br in options.brands" :key="br.id" :value="br.id">{{ br.name }}</option>
                    </select>

                    <select v-model="selectedStatus" @change="handleFilter" class="bg-background border border-border rounded-lg px-3 py-1.5 text-sm text-foreground outline-none focus:ring-1 focus:ring-primary col-span-2 sm:col-span-1">
                        <option value="">Estado de Integridad</option>
                        <option value="complete">Catálogo Completo</option>
                        <option value="incomplete">Faltan Variantes</option>
                    </select>
                </div>
            </div>

            <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-muted/40 text-muted-foreground text-xs font-semibold uppercase border-b border-border">
                            <th class="p-4 w-12 text-center"></th>
                            <th class="p-4">Producto Maestro</th>
                            <th class="p-4">Línea / Marca</th>
                            <th class="p-4">Pasillo</th>
                            <th class="p-4 text-center">Variantes</th>
                            <th class="p-4 w-24 text-center">Estado</th>
                            <th class="p-4 w-24 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <template v-for="prod in products.data" :key="prod.id">
                            <tr class="hover:bg-muted/10 transition-colors border-b border-border/60">
                                <td class="p-4 text-center">
                                    <button @click="toggleRow(prod.id)" class="p-1 text-muted-foreground hover:text-foreground hover:bg-muted rounded transition-colors" :title="expandedRows.includes(prod.id) ? 'Colapsar variantes' : 'Desplegar variantes inline'">
                                        <ChevronDown v-if="expandedRows.includes(prod.id)" :size="16" />
                                        <ChevronRight v-else :size="16" />
                                    </button>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-muted rounded border border-border overflow-hidden shrink-0 flex items-center justify-center">
                                            <img v-if="prod.image_url" :src="prod.image_url" class="w-full h-full object-cover" />
                                            <Tag v-else :size="16" class="text-muted-foreground/40" />
                                        </div>
                                        <div>
                                            <span @click="toggleRow(prod.id)" class="font-semibold text-foreground block cursor-pointer hover:text-primary transition-colors select-none">{{ prod.name }}</span>
                                            <span v-if="prod.is_alcoholic" class="text-[9px] bg-red-500/10 text-red-500 px-1.5 py-0.5 rounded font-bold uppercase border border-red-500/20 inline-block mt-0.5">Control +18</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-muted-foreground text-xs uppercase tracking-tight font-medium">{{ prod.brand_name }}</td>
                                <td class="p-4 text-muted-foreground text-xs">{{ prod.category_name }}</td>
                                <td class="p-4 text-center font-mono font-bold">
                                    <span @click="toggleRow(prod.id)" :class="prod.skus_count === 0 ? 'text-amber-500 bg-amber-500/10 border-amber-500/20 px-2 py-0.5 rounded border' : 'bg-muted text-muted-foreground px-2 py-0.5 rounded border border-border cursor-pointer'" class="text-xs select-none">
                                        {{ prod.skus_count }} SKUs
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span :class="prod.is_active ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-destructive/10 text-destructive border-destructive/20'" class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full border">
                                        {{ prod.is_active ? 'Activo' : 'Oculto' }}
                                    </span>
                                </td>
                                <td class="p-4 text-right space-x-1 whitespace-nowrap">
                                    <Link :href="route('admin.products.edit', prod.id)" class="inline-flex items-center p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-md transition-colors" title="Abrir Workspace">
                                        <Edit :size="15" />
                                    </Link>
                                    <button v-if="can_manage" @click="destroyProduct(prod.id, prod.name)" class="inline-flex items-center p-1.5 text-destructive hover:bg-destructive/10 rounded-md transition-colors" title="Baja permanente">
                                        <Trash2 :size="15" />
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="expandedRows.includes(prod.id)" class="bg-muted/20">
                                <td colspan="7" class="p-4 pl-12 bg-muted/30">
                                    <div class="border border-border/80 rounded-xl bg-card overflow-hidden shadow-inner">
                                        <div class="px-4 py-2.5 bg-muted/40 border-b border-border flex items-center gap-1.5">
                                            <Package :size="14" class="text-muted-foreground" />
                                            <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Desglose de Presentaciones Comerciales</span>
                                        </div>
                                        <table class="w-full text-left border-collapse text-xs">
                                            <thead class="bg-muted/10 font-semibold text-muted-foreground border-b border-border/60">
                                                <tr>
                                                    <th class="p-3">Descripción / Unidad de Variante</th>
                                                    <th class="p-3 font-mono w-40">Código EAN</th>
                                                    <th class="p-3 text-right w-32">Precio Ref. Global</th>
                                                    <th class="p-3 text-right w-24">F. Conv</th>
                                                    <th class="p-3 text-right w-24">Peso Kg</th>
                                                    <th class="p-3 text-center w-24">Visibilidad</th>
                                                    <th class="p-3 text-right w-36">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-border/60">
                                                <tr v-for="sku in prod.skus" :key="sku.id" class="hover:bg-muted/30 transition-colors">
                                                    <td class="p-3 font-medium text-foreground">{{ sku.name }}</td>
                                                    <td class="p-3 font-mono font-bold text-primary tracking-tight">{{ sku.code }}</td>
                                                    <td class="p-3 text-right font-mono text-foreground/80">{{ sku.base_price.toFixed(2) }}</td>
                                                    <td class="p-3 text-right font-mono">{{ sku.conversion_factor.toFixed(3) }}</td>
                                                    <td class="p-3 text-right font-mono">{{ sku.weight.toFixed(3) }}</td>
                                                    <td class="p-3 text-center">
                                                        <span :class="sku.is_active ? 'bg-emerald-500/10 text-emerald-600' : 'bg-destructive/10 text-destructive'" class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold rounded border border-transparent">
                                                            {{ sku.is_active ? 'Activo' : 'Oculto' }}
                                                        </span>
                                                    </td>
                                                    <td class="p-3 text-right whitespace-nowrap space-x-1">
                                                        <Link :href="route('admin.products.edit', prod.id) + '?tab=2'" class="inline-flex items-center gap-1 px-2 py-1 bg-muted border border-border hover:border-primary/40 text-foreground hover:text-primary rounded font-medium transition-colors">
                                                            <Coins :size="12" />
                                                            Precios y Atributos
                                                        </Link>
                                                        <button type="button" @click="destroySku(sku.id)" class="p-1 text-destructive hover:bg-destructive/10 rounded">
                                                            <Trash2 :size="13" />
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr v-if="prod.skus && prod.skus.length === 0">
                                                    <td colspan="7" class="p-4 text-center text-muted-foreground italic">
                                                        El maestro no posee presentaciones. Abra el panel lateral para inyectar variantes.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-if="products.data.length === 0">
                            <td colspan="7" class="p-8 text-center text-muted-foreground">
                                Ningún producto maestro intersecta los parámetros de búsqueda.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>