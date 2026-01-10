<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import debounce from 'lodash/debounce';
    
    const props = defineProps({ skus: Object, filters: Object });
    const search = ref(props.filters.search || '');
    
    watch(search, debounce((val) => {
        router.get(route('admin.skus.index'), { search: val }, { preserveState: true, replace: true, preserveScroll: true });
    }, 300));
    
    const deleteSku = (id) => {
        if(confirm('¿Confirmar archivado? El SKU dejará de estar visible pero mantendrá su historial.')) {
            router.delete(route('admin.skus.destroy', id));
        }
    };
    </script>
    
    <template>
        <AdminLayout>
            
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Maestro de SKUs</h1>
                    <p class="text-gray-400 text-sm">Base de datos de códigos y presentaciones</p>
                </div>
                
                <div class="flex gap-4 w-full md:w-auto">
                    <input v-model="search" type="text" placeholder="Buscar código, nombre..." class="bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded focus:border-blue-500 outline-none w-full md:w-64">
                    
                    <Link :href="route('admin.products.create')" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded font-bold shadow whitespace-nowrap text-sm">
                        + Nuevo Producto
                    </Link>
                </div>
            </div>
    
            <div class="bg-gray-800 rounded shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-900 text-gray-400 text-xs uppercase">
                            <tr>
                                <th class="px-6 py-3">Código (EAN)</th>
                                <th class="px-6 py-3">Descripción</th>
                                <th class="px-6 py-3">Jerarquía</th>
                                <th class="px-6 py-3 text-center">Logística</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 text-sm text-gray-300">
                            <tr v-for="sku in skus.data" :key="sku.id" class="hover:bg-gray-750 transition">
                                <td class="px-6 py-4 font-mono text-blue-400 font-bold">
                                    {{ sku.code || '---' }}
                                </td>
    
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white text-base">{{ sku.product_name }}</div>
                                    <div class="text-xs text-green-400 mt-1 font-bold">
                                        ↳ {{ sku.name }}
                                    </div>
                                </td>
    
                                <td class="px-6 py-4">
                                    <div class="text-white font-bold">{{ sku.brand_name }}</div>
                                    <div class="text-xs text-gray-500">{{ sku.category_name }}</div>
                                </td>
    
                                <td class="px-6 py-4 text-center">
                                    <span v-if="parseFloat(sku.factor) > 1" class="bg-blue-900 text-blue-200 px-2 py-1 rounded text-xs font-bold border border-blue-700">
                                        Pack x{{ parseFloat(sku.factor) }}
                                    </span>
                                    <span v-else class="text-gray-500 text-xs border border-gray-700 px-2 py-1 rounded">
                                        Unidad (x1)
                                    </span>
                                </td>
    
                                <td class="px-6 py-4 text-right space-x-3">
                                    <Link :href="route('admin.products.edit', sku.product_id)" class="text-blue-400 hover:text-white font-bold hover:underline">
                                        Editar
                                    </Link>
                                    <button @click="deleteSku(sku.id)" class="text-red-500 hover:text-red-400 font-bold hover:underline">
                                        Borrar
                                    </button>
                                </td>
                            </tr>
                            
                            <tr v-if="skus.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron SKUs registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 bg-gray-850 border-t border-gray-700 flex justify-center" v-if="skus.links && skus.links.length > 3">
                    <div class="flex gap-1">
                        <template v-for="(link, k) in skus.links" :key="k">
                            <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1 rounded text-xs transition" 
                                  :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-400 hover:bg-gray-600'"/>
                            <span v-else v-html="link.label" class="px-3 py-1 rounded text-xs text-gray-600 bg-gray-800"></span>
                        </template>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </template>