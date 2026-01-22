<script setup>
    import { ref, watch } from 'vue';
    import { Head, router } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Search, Save, Loader2, MapPin, Package, Globe } from 'lucide-vue-next';
    import { debounce } from 'lodash';
    
    const props = defineProps({
        stockByBranch: Object, // Objeto Agrupado: { 'Sucursal A': [items], 'Sucursal B': [items] }
        branches: Array,
        filters: Object
    });
    
    const search = ref(props.filters.search || '');
    const selectedBranch = ref(props.filters.branch_id || '');
    
    // Filtros con debounce
    watch([search, selectedBranch], debounce(() => {
        router.get(route('admin.prices.index'), { 
            search: search.value, 
            branch_id: selectedBranch.value 
        }, { preserveState: true, replace: true });
    }, 500));
    
    const processingId = ref(null);
    
    // --- HELPERS DE PRECIOS ---
    
    // 1. Obtener precio ESPECÍFICO de la sucursal (si existe)
    const getBranchPrice = (item) => {
        // Buscamos en el array de precios que ya filtró el backend (SoftDeletes activos)
        const priceObj = item.sku.prices.find(p => p.branch_id === item.branch_id);
        return priceObj ? parseFloat(priceObj.final_price).toFixed(2) : null;
    };
    
    // 2. Obtener precio BASE NACIONAL (siempre debería existir)
    const getBasePrice = (item) => {
        const priceObj = item.sku.prices.find(p => p.branch_id === null);
        return priceObj ? parseFloat(priceObj.final_price).toFixed(2) : '0.00';
    };
    
    // 3. Guardar cambios
    const savePrice = (item, newPrice) => {
        if (!newPrice) return;
        
        // Clave única para el spinner de carga
        const uniqueKey = `${item.branch_id}-${item.sku_id}`;
        processingId.value = uniqueKey;
    
        router.post(route('admin.prices.store'), {
            sku_id: item.sku_id,
            branch_id: item.branch_id, // Enviamos explícitamente la sucursal del grupo
            final_price: newPrice
        }, {
            preserveScroll: true,
            onFinish: () => processingId.value = null,
            onSuccess: () => item.temp_price = '' // Limpiar input tras éxito
        });
    };
    </script>
    
    <script>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    export default { layout: AdminLayout }
    </script>
    
    <template>
        <Head title="Precios por Sucursal" />
    
        <div class="space-y-6">
            
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-4 justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <MapPin class="text-blue-600" /> Precios en Existencias
                    </h2>
                    <p class="text-xs text-gray-500">Solo se muestran productos con stock físico en la sucursal.</p>
                </div>
    
                <div class="flex gap-3 w-full sm:w-auto">
                    <select v-model="selectedBranch" class="rounded-lg border-gray-300 text-xs focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas las Sucursales</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                    </select>
                    <div class="relative w-full">
                        <input v-model="search" type="text" placeholder="Buscar producto o SKU..." class="pl-8 rounded-lg border-gray-300 text-xs w-full focus:ring-blue-500">
                        <Search class="absolute left-2.5 top-2.5 text-gray-400" :size="14" />
                    </div>
                </div>
            </div>
    
            <div v-if="Object.keys(stockByBranch).length > 0">
                
                <div v-for="(items, branchName) in stockByBranch" :key="branchName" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    
                    <div class="bg-gray-50 px-6 py-3 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-black text-sm text-gray-700 uppercase tracking-wider flex items-center gap-2">
                            <MapPin :size="16" /> {{ branchName }}
                        </h3>
                        <span class="text-xs font-bold bg-white px-2 py-1 rounded border border-gray-200 text-gray-500">
                            {{ items.length }} Productos
                        </span>
                    </div>
    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-white text-[10px] uppercase font-bold text-gray-400 border-b border-gray-50">
                                <tr>
                                    <th class="px-6 py-2">Producto</th>
                                    <th class="px-6 py-2">SKU / Código</th>
                                    <th class="px-6 py-2 text-center">Precio Vigente</th>
                                    <th class="px-6 py-2 text-right">Nuevo Precio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="item in items" :key="item.sku_id + '-' + item.branch_id" class="hover:bg-blue-50/30 transition">
                                    
                                    <td class="px-6 py-3">
                                        <div class="font-bold text-gray-800">{{ item.sku.product.name }}</div>
                                        <div class="text-[10px] text-gray-400">{{ item.sku.name }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-3">
                                        <span class="font-mono text-xs bg-gray-100 px-1 rounded">{{ item.sku.code || 'S/N' }}</span>
                                    </td>
    
                                    <td class="px-6 py-3 text-center">
                                        
                                        <div v-if="getBranchPrice(item)" class="flex flex-col items-center">
                                            <span class="text-sm font-bold text-green-600">Bs {{ getBranchPrice(item) }}</span>
                                            <span class="text-[9px] text-gray-400 flex items-center gap-1 mt-0.5 opacity-70" title="Precio Base Nacional de Referencia">
                                                <Globe :size="8" /> Base: {{ getBasePrice(item) }}
                                            </span>
                                        </div>
                                        
                                        <div v-else class="flex flex-col items-center">
                                            <div class="flex items-center gap-1 bg-orange-50 px-2 py-1 rounded border border-orange-100 text-orange-600">
                                                <Globe :size="10" />
                                                <span class="text-xs font-bold">Bs {{ getBasePrice(item) }}</span>
                                            </div>
                                            <span class="text-[9px] text-gray-400 mt-1">Usa Base Nacional</span>
                                        </div>
                                    </td>
    
                                    <td class="px-6 py-3 flex justify-end items-center gap-2">
                                        <div class="relative w-28">
                                            <span class="absolute left-2 top-1.5 text-gray-400 font-bold text-xs">Bs</span>
                                            <input 
                                                type="number" step="0.01" 
                                                class="w-full pl-6 py-1 text-xs font-bold text-right rounded border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-200"
                                                :placeholder="getBranchPrice(item) || getBasePrice(item)" 
                                                v-model="item.temp_price"
                                                @keyup.enter="savePrice(item, item.temp_price)"
                                            />
                                        </div>
                                        <button 
                                            @click="savePrice(item, item.temp_price)"
                                            :disabled="!item.temp_price || processingId === `${item.branch_id}-${item.sku_id}`"
                                            class="p-1.5 bg-gray-900 text-white rounded hover:bg-blue-600 disabled:opacity-50 transition shadow-sm"
                                            title="Guardar Precio"
                                        >
                                            <Loader2 v-if="processingId === `${item.branch_id}-${item.sku_id}`" class="animate-spin" :size="14" />
                                            <Save v-else :size="14" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
    
            </div>
    
            <div v-else class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-200">
                <Package class="mx-auto text-gray-300 mb-2" :size="48" />
                <p class="text-gray-500 font-bold">No hay inventario registrado.</p>
                <p class="text-xs text-gray-400">Registra una compra ("Ingresos") para empezar a gestionar precios por sucursal.</p>
            </div>
    
        </div>
    </template>