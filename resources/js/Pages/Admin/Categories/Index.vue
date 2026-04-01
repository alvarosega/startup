<script setup>
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Settings, Trash2, ArrowUpDown, ChevronLeft, ChevronRight, Wifi, WifiOff } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    categories: Object, // Trae data, next_page_url, prev_page_url
    filters: Object,
    can_manage: Boolean
});

const search = ref(props.filters?.search || '');

watch(search, debounce((val) => {
    router.get(route('admin.categories.index'), { search: val }, { 
        preserveState: true, replace: true, preserveScroll: true
    });
}, 400));

const deleteCategory = (category) => {
    if (confirm(`AUDIT_ALERT: ¿Neutralizar categoría ${category.name}?`)) {
        router.delete(route('admin.categories.destroy', category.id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Control de Categorías" />

        <div class="p-4 max-w-[1600px] mx-auto space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-card p-3 border border-border">
                <div class="relative w-full md:w-96">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="16" />
                    <input v-model="search" type="text" placeholder="Buscar por Nombre o Código..."
                           class="w-full pl-9 pr-4 py-1.5 bg-background border border-border text-sm font-mono focus:ring-1 focus:ring-primary outline-none" />
                </div>

                <Link v-if="can_manage" :href="route('admin.categories.create')" 
                      class="w-full md:w-auto bg-primary text-primary-foreground px-4 py-1.5 text-xs font-black uppercase flex items-center justify-center gap-2 hover:opacity-90">
                    <Plus :size="16" stroke-width="3" />
                    AÑADIR CATEGORÍA
                </Link>
            </div>

            <div class="border border-border bg-card overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-muted/50 border-b border-border uppercase font-mono text-muted-foreground">
                            <th class="p-3 font-bold border-r border-border/50">Orden</th>
                            <th class="p-3 font-bold border-r border-border/50">Jerarquía / Nombre</th>
                            <th class="p-3 font-bold border-r border-border/50">Código Ext.</th>
                            <th class="p-3 font-bold border-r border-border/50">Estado</th>
                            <th class="p-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border font-medium">
                        <tr v-for="category in categories.data" :key="category.id" class="hover:bg-muted/30 transition-none">
                            <td class="p-3 font-mono text-primary w-16 text-center">
                                {{ String(category.sort_order).padStart(2, '0') }}
                            </td>
                            <td class="p-3">
                                <div v-if="category.parent" class="text-[10px] text-primary uppercase font-mono mb-0.5">
                                    {{ category.parent.name }} >
                                </div>
                                <div class="font-bold text-sm uppercase tracking-tight">{{ category.name }}</div>
                                <div class="text-[10px] text-muted-foreground font-mono italic">{{ category.slug }}</div>
                            </td>
                            <td class="p-3 font-mono">{{ category.external_code || '---' }}</td>
                            <td class="p-3">
                                <span :class="['flex items-center gap-1.5 font-bold uppercase text-[10px]', category.is_active ? 'text-success' : 'text-destructive']">
                                    <component :is="category.is_active ? Wifi : WifiOff" :size="12" />
                                    {{ category.is_active ? 'Online' : 'Offline' }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <Link :href="route('admin.categories.sku-order', category.id)" class="p-1.5 border border-border hover:bg-muted text-muted-foreground" title="Reordenar SKUs">
                                        <ArrowUpDown :size="14" />
                                    </Link>
                                    <Link :href="route('admin.categories.edit', category.id)" class="p-1.5 border border-border hover:bg-muted text-muted-foreground">
                                        <Settings :size="14" />
                                    </Link>
                                    <button @click="deleteCategory(category)" class="p-1.5 border border-border hover:bg-destructive hover:text-destructive-foreground text-muted-foreground">
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categories.data.length === 0">
                            <td colspan="5" class="p-12 text-center text-muted-foreground font-mono uppercase italic">No se detectaron registros en este sector del catálogo.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center py-2 border-t border-border/50">
                <div class="text-[10px] font-mono text-muted-foreground uppercase">Protocolo: Cursor-Based Hierarchy Scan</div>
                <div class="flex gap-1">
                    <Link v-if="categories.prev_page_url" :href="categories.prev_page_url" class="p-2 border border-border bg-card hover:bg-muted transition-none">
                        <ChevronLeft :size="16" />
                    </Link>
                    <div v-else class="p-2 border border-border opacity-20 cursor-not-allowed bg-card"><ChevronLeft :size="16" /></div>
                    
                    <Link v-if="categories.next_page_url" :href="categories.next_page_url" class="p-2 border border-border bg-card hover:bg-muted transition-none">
                        <ChevronRight :size="16" />
                    </Link>
                    <div v-else class="p-2 border border-border opacity-20 cursor-not-allowed bg-card"><ChevronRight :size="16" /></div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>