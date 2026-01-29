<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Map, Plus, Edit, Trash2, Layers } from 'lucide-vue-next';

defineProps({
    zones: Array // Viene del controlador: id, name, hex_color, categories_count...
});

const deleteZone = (id) => {
    if (confirm('¿Estás seguro de eliminar esta zona? Las categorías asignadas quedarán sin zona.')) {
        router.delete(route('admin.market-zones.destroy', id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Zonas de Mercado" />
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-foreground flex items-center gap-2">
                    <Map class="text-primary" /> Zonas del Mapa
                </h1>
                <p class="text-sm text-muted-foreground">Define las áreas visuales de tu "Ciudad Logística".</p>
            </div>
            <Link :href="route('admin.market-zones.create')" class="btn btn-primary gap-2 shadow-lg hover:shadow-xl transition-all">
                <Plus :size="18" /> Nueva Zona
            </Link>
        </div>

        <div class="card overflow-hidden border border-border shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-muted/50 border-b border-border text-left">
                        <tr>
                            <th class="p-4 font-bold text-sm text-muted-foreground uppercase tracking-wider">Zona</th>
                            <th class="p-4 font-bold text-sm text-muted-foreground uppercase tracking-wider">Color</th>
                            <th class="p-4 font-bold text-sm text-muted-foreground uppercase tracking-wider">SVG ID</th>
                            <th class="p-4 font-bold text-sm text-muted-foreground uppercase tracking-wider">Categorías</th>
                            <th class="p-4 font-bold text-sm text-muted-foreground uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="zone in zones" :key="zone.id" class="group hover:bg-muted/20 transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-foreground">{{ zone.name }}</div>
                                <div class="text-xs text-muted-foreground line-clamp-1">{{ zone.description || 'Sin descripción' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full border border-border shadow-sm" 
                                         :style="{ backgroundColor: zone.hex_color }"></div>
                                    <span class="text-xs font-mono text-muted-foreground">{{ zone.hex_color }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <code class="bg-muted px-2 py-1 rounded text-xs font-mono text-foreground border border-border">
                                    #{{ zone.svg_id }}
                                </code>
                            </td>
                            <td class="p-4">
                                <div class="badge badge-outline gap-1 pl-1 pr-2">
                                    <Layers :size="12"/> 
                                    <span class="font-bold">{{ zone.categories_count }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.market-zones.edit', zone.id)" 
                                          class="btn btn-ghost btn-sm btn-square hover:text-primary hover:bg-primary/10">
                                        <Edit :size="16" />
                                    </Link>
                                    <button @click="deleteZone(zone.id)" 
                                            class="btn btn-ghost btn-sm btn-square hover:text-error hover:bg-error/10">
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-if="zones.length === 0" class="py-12 text-center text-muted-foreground">
                <Map :size="48" class="mx-auto mb-3 opacity-20" />
                <p>No has configurado ninguna zona todavía.</p>
            </div>
        </div>
    </AdminLayout>
</template>