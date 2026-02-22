<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import debounce from 'lodash/debounce';
    
    const props = defineProps({
        inventory: Object, // Trae data, prev_page_url, next_page_url
        branches: Array, 
        filters: Object
    });
    
    const search = ref(props.filters.search || '');
    const branchId = ref(props.filters.branch_id || '');
    
    const updateParams = debounce(() => {
        router.get(route('admin.inventory.index'), { 
            search: search.value, 
            branch_id: branchId.value 
        }, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
    
    watch([search, branchId], updateParams);
    
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(amount);
    };
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Kardex de Inventario</h1>
                <p class="text-gray-400 text-xs uppercase tracking-widest font-semibold">Stock Físico Consolidado</p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <select v-if="branches.length > 1" v-model="branchId" 
                        class="bg-gray-900 border border-gray-700 text-gray-300 text-sm px-4 py-2 rounded shadow-inner focus:ring-1 focus:ring-blue-500 outline-none cursor-pointer appearance-none">
                    <option value="">Todas las Sucursales</option>
                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
                
                <div class="relative">
                    <input v-model="search" type="text" placeholder="Buscar por SKU o Producto..." 
                           class="bg-gray-900 border border-gray-700 text-white text-sm px-4 py-2 pl-10 rounded focus:ring-1 focus:ring-blue-500 outline-none w-full md:w-72 transition-all">
                    <span class="absolute left-3 top-2.5 text-gray-500 text-xs">🔍</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <div v-for="(item, index) in inventory.data" :key="index" 
                 class="bg-gray-800/50 backdrop-blur-sm rounded border border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-center relative overflow-hidden group hover:border-blue-500/30 transition-all duration-300">
                
                <div class="absolute left-0 top-0 bottom-0 w-1.5" 
                     :class="{
                        'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]': item.total_quantity <= 5,
                        'bg-yellow-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]': item.total_quantity > 5 && item.total_quantity <= 20,
                        'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]': item.total_quantity > 20
                     }"></div>

                <div class="pl-6 py-4 flex-1 w-full">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[9px] uppercase font-black tracking-tighter text-gray-400 bg-gray-900 px-2 py-0.5 rounded border border-gray-700">
                            {{ item.branch_name }}
                        </span>
                        <span v-if="item.brand_name" class="text-[10px] text-blue-400 font-bold uppercase tracking-wider">
                            {{ item.brand_name }}
                        </span>
                    </div>
                    
                    <h3 class="text-base font-bold text-white leading-tight mb-1 group-hover:text-blue-400 transition-colors">
                        {{ item.product_name }}
                    </h3>
                    
                    <div class="flex items-center gap-4 text-xs font-mono text-gray-500">
                        <span class="text-blue-300/80">{{ item.sku_name }}</span>
                        <span class="text-gray-700">/</span>
                        <span class="bg-gray-900 px-1.5 rounded">EAN: {{ item.sku_code || 'N/A' }}</span>
                    </div>
                </div>

                <div class="bg-gray-900/30 md:bg-transparent w-full md:w-auto flex items-center gap-10 py-4 px-8 border-t md:border-t-0 border-gray-700/50">
                    <div class="flex flex-col items-end">
                        <span class="text-[9px] text-gray-500 uppercase font-black tracking-widest">Costo Ponderado</span>
                        <span class="text-sm text-gray-300 font-mono font-bold">
                            {{ formatCurrency(item.avg_cost) }}
                        </span>
                    </div>

                    <div class="flex flex-col items-end min-w-[100px]">
                        <span class="text-[9px] text-gray-500 uppercase font-black tracking-widest">Disponible</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-white tabular-nums tracking-tighter">
                                {{ item.total_quantity }}
                            </span>
                            <span class="text-[10px] text-gray-500 font-bold">U.</span>
                        </div>
                        <span v-if="item.total_reserved > 0" class="text-[9px] text-yellow-500 font-bold uppercase animate-pulse">
                            🔒 {{ item.total_reserved }} Reservados
                        </span>
                    </div>
                </div>
            </div>
            
            <div v-if="inventory.data.length === 0" class="flex flex-col items-center justify-center py-20 bg-gray-900/50 rounded border border-gray-800 border-dashed">
                <span class="text-5xl mb-4 grayscale">📦</span>
                <h3 class="text-lg font-bold text-gray-400 uppercase tracking-tighter">Sin existencias</h3>
                <p class="text-gray-600 text-xs">Ajuste los filtros para ver otros resultados.</p>
            </div>
        </div>
        
        <div class="mt-8 flex justify-center items-center gap-4" v-if="inventory.prev_page_url || inventory.next_page_url">
            <Link v-if="inventory.prev_page_url" :href="inventory.prev_page_url" 
                  class="px-6 py-2 bg-gray-900 text-white text-xs font-bold uppercase tracking-widest rounded border border-gray-700 hover:bg-gray-800 hover:border-blue-500 transition-all active:scale-95">
                « Anterior
            </Link>
            
            <div class="h-1 w-12 bg-gray-800 rounded-full"></div>

            <Link v-if="inventory.next_page_url" :href="inventory.next_page_url" 
                  class="px-6 py-2 bg-gray-900 text-white text-xs font-bold uppercase tracking-widest rounded border border-gray-700 hover:bg-gray-800 hover:border-blue-500 transition-all active:scale-95">
                Siguiente »
            </Link>
        </div>
    </AdminLayout>
</template>