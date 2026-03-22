<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Verifica tu ruta de alias
import { Plus } from 'lucide-vue-next';

const props = defineProps({
    items: Object,
    filters: Object
});

const creativesList = computed(() => props.items.data || []);
</script>

<template>
    <Head title="Gestión de Ads - Retail Media" />

    <AdminLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-foreground">Retail Media: Creativos</h1>
                <Link :href="route('admin.retail-media.ad-creatives.create')" 
                      class="flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:opacity-90 transition-all">
                    <Plus :size="18" />
                    Nuevo Banner
                </Link>
            </div>

            <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted/50 text-muted-foreground font-medium border-b border-border">
                        <tr>
                            <th class="px-4 py-3">Creativo</th>
                            <th class="px-4 py-3">Ubicación</th>
                            <th class="px-4 py-3">Campañas / Marca</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="item in creativesList" :key="item.id" class="hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-4 font-medium">{{ item.name }}</td>
                            <td class="px-4 py-4 text-xs font-mono uppercase">{{ item.placement.code }}</td>
                            <td class="px-4 py-4">
                                <div class="font-medium text-foreground">{{ item.campaign.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ item.campaign.provider }}</div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 rounded-full text-[10px] font-bold uppercase">
                                    {{ item.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="creativesList.length === 0" class="p-12 text-center text-muted-foreground">
                    No se encontraron creativos para mostrar.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>