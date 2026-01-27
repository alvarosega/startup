<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Package, Plus, Edit, Trash2, Search, MapPin } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    bundles: Object,
    branches: Array
});

const search = ref('');
const branchFilter = ref('');

// Búsqueda server-side
const updateParams = debounce(() => {
    router.get(route('admin.bundles.index'), { 
        search: search.value,
        branch_id: branchFilter.value 
    }, { preserveState: true, replace: true });
}, 300);

watch([search, branchFilter], updateParams);

const deleteBundle = (id) => {
    if (confirm('¿Estás seguro de eliminar este pack permanentemente?')) {
        router.delete(route('admin.bundles.destroy', id));
    }
};

const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    if (imagePath.startsWith('http')) return imagePath;
    return `/storage/${imagePath}`;
};
</script>

<template>
    <AdminLayout>
        <Head title="Gestión de Packs" />

        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-black text-foreground flex items-center gap-2">
                        <Package class="text-primary" :size="24" /> Packs & Bundles
                    </h1>
                    <p class="text-muted-foreground mt-1">Administra ofertas por sucursal</p>
                </div>
                
                <Link :href="route('admin.bundles.create')" class="btn btn-primary btn-md flex items-center gap-2">
                    <Plus :size="16" /> Nuevo Pack
                </Link>
            </div>

            <div class="flex gap-4 items-center bg-card p-4 rounded-lg border border-border shadow-sm">
                <div class="relative flex-1">
                    <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="search" type="text" placeholder="Buscar packs..." 
                           class="pl-10 w-full input input-bordered input-sm" />
                </div>
                <div class="relative w-64">
                    <MapPin :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                    <select v-model="branchFilter" class="pl-10 w-full select select-bordered select-sm">
                        <option value="">Todas las sucursales</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead class="bg-muted/50 border-b border-border">
                        <tr>
                            <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase">Pack</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase">Sucursal</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase">Contenido</th> <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase">Precio</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase">Estado</th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/50">
                        <tr v-for="bundle in bundles.data" :key="bundle.id" class="hover:bg-muted/30 transition-colors">
                            
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-lg bg-muted/20 border border-border overflow-hidden flex-shrink-0">
                                        <img v-if="getImageUrl(bundle.image_path)" :src="getImageUrl(bundle.image_path)" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground/40"><Package :size="20"/></div>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-foreground text-sm">{{ bundle.name }}</h3>
                                        <p class="text-xs text-muted-foreground line-clamp-1">{{ bundle.description }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                    <MapPin :size="14" />
                                    <span class="font-medium text-foreground">{{ bundle.branch?.name || '---' }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1 max-w-[200px]">
                                    <div v-for="sku in bundle.skus" :key="sku.id" class="text-xs flex justify-between items-center border-b border-border/50 pb-1 last:border-0 last:pb-0">
                                        <span class="text-muted-foreground truncate pr-2">{{ sku.name }}</span>
                                        <span class="font-bold text-foreground bg-muted px-1.5 rounded text-[10px]">x{{ sku.pivot.quantity }}</span>
                                    </div>
                                    <span v-if="!bundle.skus || bundle.skus.length === 0" class="text-xs text-muted-foreground italic">
                                        Sin items asignados
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span v-if="bundle.fixed_price" class="font-bold text-primary">Bs {{ parseFloat(bundle.fixed_price).toFixed(2) }}</span>
                                <span v-else class="text-xs italic text-muted-foreground">Dinámico</span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="badge text-xs" :class="bundle.is_active ? 'badge-success' : 'badge-outline'">
                                    {{ bundle.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <Link :href="route('admin.bundles.edit', bundle.id)" class="btn btn-ghost btn-sm btn-square"><Edit :size="16"/></Link>
                                    <button @click="deleteBundle(bundle.id)" class="btn btn-ghost btn-sm btn-square text-error"><Trash2 :size="16"/></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-if="bundles.links.length > 3" class="p-4 border-t border-border flex justify-center gap-1">
                <Link v-for="link in bundles.links" :key="link.label" :href="link.url" v-html="link.label"
                      class="btn btn-sm" :class="{'btn-primary': link.active, 'btn-ghost': !link.active, 'opacity-50': !link.url}" />
            </div>
        </div>
    </AdminLayout>
</template>