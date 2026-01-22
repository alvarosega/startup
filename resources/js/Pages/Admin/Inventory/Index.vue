<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import debounce from 'lodash/debounce';
    
    const props = defineProps({
        inventory: Object, // Paginado y Agrupado
        branches: Array,
        filters: Object
    });
    
    const search = ref(props.filters.search || '');
    const branchId = ref(props.filters.branch_id || '');
    
    // Actualización automática de filtros (Debounce de 300ms)
    const updateParams = debounce(() => {
        router.get(route('admin.inventory.index'), { 
            search: search.value, 
            branch_id: branchId.value 
        }, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
    
    watch([search, branchId], updateParams);
    
    // Helper para formato de moneda
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(amount);
    };
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Kardex de Inventario</h1>
                <p class="text-gray-400 text-sm">Stock físico consolidado por sucursal</p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                <select v-model="branchId" class="bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded focus:border-blue-500 outline-none cursor-pointer">
                    <option value="">Todas las Sucursales</option>
                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
                
                <input v-model="search" type="text" placeholder="Buscar SKU, producto..." class="bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded focus:border-blue-500 outline-none w-full md:w-64">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            
            <div v-for="(item, index) in inventory.data" :key="index" 
                 class="bg-gray-800 rounded-lg p-4 border border-gray-700 shadow-lg flex flex-col md:flex-row justify-between items-center relative overflow-hidden group transition hover:border-blue-500/50">
                
                <div class="absolute left-0 top-0 bottom-0 w-1 transition-colors duration-300" 
                     :class="{
                        'bg-red-500': item.total_quantity <= 5,
                        'bg-yellow-500': item.total_quantity > 5 && item.total_quantity <= 20,
                        'bg-green-500': item.total_quantity > 20
                     }"></div>

                <div class="pl-4 flex-1 w-full">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-500 border border-gray-700 px-1 rounded">
                            {{ item.branch?.name || 'Sucursal Desconocida' }}
                        </span>
                        <span v-if="item.sku?.product?.brand" class="text-[10px] text-blue-400 font-bold">
                            {{ item.sku.product.brand.name }}
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-bold text-white leading-tight">
                        {{ item.sku?.product?.name || 'Producto Eliminado' }}
                    </h3>
                    
                    <div class="mt-1 flex items-center gap-3 text-sm font-mono text-gray-400">
                        <span class="text-blue-300">{{ item.sku?.name }}</span>
                        <span class="text-gray-600">|</span>
                        <span>EAN: {{ item.sku?.code || 'S/N' }}</span>
                    </div>
                </div>

                <div class="mt-4 md:mt-0 flex items-center gap-8 text-right w-full md:w-auto justify-between md:justify-end px-4 md:px-0">
                    
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] text-gray-500 uppercase font-bold">Costo Prom.</span>
                        <span class="text-sm text-gray-300 font-mono">
                            {{ formatCurrency(item.avg_cost || 0) }}
                        </span>
                    </div>

                    <div class="flex flex-col items-end">
                        <span class="text-[10px] text-gray-500 uppercase font-bold">Disponible</span>
                        <span class="text-3xl font-bold text-white tracking-tight">
                            {{ parseInt(item.total_quantity) }}
                        </span>
                        <span v-if="item.total_reserved > 0" class="text-[10px] text-yellow-500">
                            {{ item.total_reserved }} Reservados
                        </span>
                    </div>
                </div>
            </div>
            
            <div v-if="inventory.data.length === 0" class="flex flex-col items-center justify-center py-16 bg-gray-800 rounded border border-gray-700 border-dashed">
                <div class="text-4xl mb-4">📦</div>
                <h3 class="text-lg font-bold text-white">No hay stock disponible</h3>
                <p class="text-gray-500 text-sm mb-4">No se encontraron productos con los filtros seleccionados.</p>
                
                <Link :href="route('admin.purchases.create')" class="text-blue-400 hover:text-white underline text-sm">
                    Registrar una nueva compra
                </Link>
            </div>

        </div>
        
        <div class="mt-6 flex justify-center" v-if="inventory.links && inventory.links.length > 3">
            <div class="flex gap-1">
                <template v-for="(link, k) in inventory.links" :key="k">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" 
                          class="px-3 py-1 rounded text-xs transition border border-gray-700" 
                          :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'"/>
                    <span v-else v-html="link.label" class="px-3 py-1 rounded text-xs text-gray-600 bg-gray-800 border border-gray-800"></span>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>
