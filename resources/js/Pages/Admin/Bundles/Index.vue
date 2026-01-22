<script setup>
    import { Head, Link, router } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue'; // Asumo que tienes un Layout Admin
    import { Package, Plus, Edit, Trash2, Eye } from 'lucide-vue-next';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    
    const props = defineProps({
        bundles: Array // Viene del BundleController@index
    });
    
    const deleteBundle = (id) => {
        if (confirm('¿Estás seguro de eliminar este pack?')) {
            router.delete(route('admin.bundles.destroy', id));
        }
    };
    </script>
    
    <template>
        <AdminLayout>
            <Head title="Gestión de Packs" />
    
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-black text-gray-800 flex items-center gap-2">
                    <Package class="text-blue-600" /> Packs & Bundles
                </h1>
                <Link :href="route('admin.bundles.create')">
                    <BaseButton class="flex items-center gap-2">
                        <Plus :size="18" /> Nuevo Pack
                    </BaseButton>
                </Link>
            </div>
    
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase font-bold text-xs">
                        <tr>
                            <th class="p-4">Nombre</th>
                            <th class="p-4">Precio</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-center">Popularidad</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="bundle in bundles" :key="bundle.id" class="border-b border-gray-50 hover:bg-gray-50 transition">
                            <td class="p-4">
                                <div class="font-bold text-gray-800">{{ bundle.name }}</div>
                                <div class="text-xs text-gray-400">{{ bundle.skus_count || 0 }} productos incluidos</div>
                            </td>
                            <td class="p-4">
                                <span v-if="bundle.fixed_price" class="font-bold text-green-600">Bs {{ bundle.fixed_price }}</span>
                                <span v-else class="text-gray-400 italic text-xs">Calculado (Suma de items)</span>
                            </td>
                            <td class="p-4">
                                <span v-if="bundle.is_active" class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-bold">Activo</span>
                                <span v-else class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-bold">Inactivo</span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1 text-yellow-500 font-bold">
                                    <span>★</span> {{ parseFloat(bundle.reviews_avg_rating || 0).toFixed(1) }}
                                </div>
                                <div class="text-[10px] text-gray-400">({{ bundle.reviews_count || 0 }} votos)</div>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <Link :href="route('admin.bundles.edit', bundle.id)" class="inline-block text-gray-400 hover:text-blue-600">
                                    <Edit :size="18" />
                                </Link>
                                <button @click="deleteBundle(bundle.id)" class="inline-block text-gray-400 hover:text-red-600">
                                    <Trash2 :size="18" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="bundles.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400">No hay packs creados aún.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    </template>