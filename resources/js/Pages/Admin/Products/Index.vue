<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import debounce from 'lodash/debounce';

    const props = defineProps({ 
        products: Object, // Objeto paginado
        filters: Object 
    });

    const search = ref(props.filters.search || '');

    watch(search, debounce((val) => {
        router.get(route('admin.products.index'), { search: val }, { 
            preserveState: true, replace: true, preserveScroll: true 
        });
    }, 300));

    const deleteProduct = (id) => {
        if(confirm('⚠ ATENCIÓN: Eliminar el Producto Maestro archivará TODOS sus SKUs y precios asociados. ¿Continuar?')) {
            router.delete(route('admin.products.destroy', id));
        }
    };
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Catálogo Maestro</h1>
                <p class="text-gray-400 text-sm">Fichas de productos y sus presentaciones</p>
            </div>
            
            <div class="flex gap-4 w-full md:w-auto">
                <input v-model="search" type="text" placeholder="Buscar producto, marca..." class="bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded-lg focus:border-blue-500 outline-none w-full md:w-64">
                
                <Link :href="route('admin.products.create')" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition whitespace-nowrap">
                    + Nuevo Producto
                </Link>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-xl overflow-hidden border border-gray-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-900 text-gray-400 text-xs uppercase font-bold">
                        <tr>
                            <th class="px-6 py-4 w-20">Imagen</th>
                            <th class="px-6 py-4">Producto (Concepto)</th>
                            <th class="px-6 py-4">Marca / Categoría</th>
                            <th class="px-6 py-4 text-center">Variantes (SKUs)</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 text-sm text-gray-300">
                        <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-750 transition group">
                            
                            <td class="px-6 py-4">
                                <div class="w-12 h-12 rounded-lg bg-white p-1 flex items-center justify-center overflow-hidden border border-gray-600">
                                    <img v-if="product.image_url" :src="product.image_url" class="max-w-full max-h-full object-contain">
                                    <span v-else class="text-gray-400 text-[10px] font-bold">N/A</span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-bold text-white text-base">{{ product.name }}</div>
                                <div class="text-xs text-gray-500 mt-1 truncate max-w-xs">{{ product.description || 'Sin descripción' }}</div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-bold text-blue-300">{{ product.brand ? product.brand.name : '---' }}</div>
                                <div class="text-xs text-gray-400">{{ product.category ? product.category.name : '---' }}</div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center bg-gray-700 text-white font-bold px-3 py-1 rounded-full text-xs border border-gray-600 shadow-sm min-w-[30px]">
                                    {{ product.skus_count || 0 }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span v-if="product.is_active" class="w-3 h-3 rounded-full bg-green-500 inline-block" title="Activo"></span>
                                <span v-else class="w-3 h-3 rounded-full bg-red-500 inline-block" title="Inactivo"></span>
                            </td>

                            <td class="px-6 py-4 text-right space-x-3">
                                <Link :href="route('admin.products.edit', product.id)" class="text-blue-400 hover:text-white font-bold text-xs uppercase hover:underline">
                                    Editar
                                </Link>
                                <button @click="deleteProduct(product.id)" class="text-red-500 hover:text-red-400 font-bold text-xs uppercase hover:underline">
                                    Borrar
                                </button>
                            </td>
                        </tr>
                        
                        <tr v-if="products.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No hay productos registrados. Comienza creando uno.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 bg-gray-850 border-t border-gray-700 flex justify-center" v-if="products.links.length > 3">
                <div class="flex gap-1">
                    <template v-for="(link, k) in products.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1 rounded text-xs transition" 
                              :class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-400 hover:bg-gray-600'"/>
                        <span v-else v-html="link.label" class="px-3 py-1 rounded text-xs text-gray-600 bg-gray-800"></span>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>