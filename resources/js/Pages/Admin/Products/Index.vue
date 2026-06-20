<script setup>
import { ref } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Tag, Plus, Search } from 'lucide-vue-next';

import ProductRow from './Components/ProductRow.vue';
import PriceManagerModal from './Components/PriceManagerModal.vue';

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

const isPriceModalOpen = ref(false);
const activeSku = ref(null);

const handleFilter = () => {
    router.get(route('admin.products.index'), {
        search: search.value,
        category: selectedCategory.value,
        brand: selectedBrand.value,
        status: selectedStatus.value
    }, { preserveState: true, replace: true });
};

const openPriceModal = (sku) => {
    activeSku.value = sku;
    isPriceModalOpen.value = true;
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
                <Link v-if="can_manage" :href="route('admin.products.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90">
                    <Plus :size="16" /> Materializar Producto
                </Link>
            </div>

            <div class="bg-card border border-border rounded-xl p-4 flex flex-col lg:flex-row gap-3 shadow-sm">
                <div class="flex items-center gap-2 flex-1 bg-background border border-border rounded-lg px-3 py-1.5 focus-within:ring-1 focus-within:ring-primary">
                    <Search :size="16" class="text-muted-foreground" />
                    <input v-model="search" @input="handleFilter" type="text" placeholder="Buscar..." class="w-full bg-transparent text-sm text-foreground outline-none border-none p-0 focus:ring-0" />
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <select v-model="selectedCategory" @change="handleFilter" class="bg-background border border-border rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Pasillos</option>
                        <option v-for="cat in options.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <select v-model="selectedBrand" @change="handleFilter" class="bg-background border border-border rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Marcas</option>
                        <option v-for="br in options.brands" :key="br.id" :value="br.id">{{ br.name }}</option>
                    </select>
                    <select v-model="selectedStatus" @change="handleFilter" class="bg-background border border-border rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-1 focus:ring-primary col-span-2 sm:col-span-1">
                        <option value="">Estado</option>
                        <option value="complete">Completos</option>
                        <option value="incomplete">Incompletos</option>
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
                        <ProductRow 
                            v-for="prod in products.data" 
                            :key="prod.id" 
                            :product="prod" 
                            :can_manage="can_manage"
                            @delete-product="destroyProduct"
                            @delete-sku="destroySku"
                            @manage-prices="openPriceModal"
                        />
                        <tr v-if="products.data.length === 0">
                            <td colspan="7" class="p-8 text-center text-muted-foreground">Ningún registro encontrado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <PriceManagerModal 
            :show="isPriceModalOpen" 
            :sku="activeSku" 
            :branches="options.branches" 
            @close="isPriceModalOpen = false" 
        />
    </AdminLayout>
</template>