<script setup>
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Edit, Trash2, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({ 
    providers: Object, // Trae: data, next_page_url, prev_page_url
    filters: Object,
    can_manage: Boolean 
});

const search = ref(props.filters.search || '');

// REGLA 4: Búsqueda eficiente con preservación de estado
watch(search, debounce((val) => {
    router.get(route('admin.providers.index'), { search: val }, { 
        preserveState: true, replace: true, preserveScroll: true
    });
}, 300));

const handleDelete = (provider) => {
    if (confirm(`AUDIT_CONFIRM: ¿Eliminar nodo ${provider.tax_id}?`)) {
        router.delete(route('admin.providers.destroy', provider.id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Proveedores" />

        <div class="p-4 max-w-[1600px] mx-auto space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-card p-3 border border-border">
                <div class="relative w-full md:w-96">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="16" />
                    <input 
                        v-model="search"
                        type="text" 
                        class="w-full pl-9 pr-4 py-1.5 bg-background border border-border text-sm focus:ring-1 focus:ring-primary outline-none" 
                        placeholder="Buscar por Tax ID, Nombre o Código..." 
                    />
                </div>

                <Link v-if="can_manage" 
                      :href="route('admin.providers.create')" 
                      class="w-full md:w-auto bg-primary text-primary-foreground px-4 py-1.5 text-sm font-bold flex items-center justify-center gap-2 hover:opacity-90">
                    <Plus :size="16" />
                    REGISTRAR PROVEEDOR
                </Link>
            </div>

            <div class="border border-border bg-card overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-muted/50 border-b border-border uppercase text-[10px] tracking-widest text-muted-foreground">
                            <th class="p-3 font-bold">Internal Code</th>
                            <th class="p-3 font-bold">Razón Social / Tax ID</th>
                            <th class="p-3 font-bold">Contacto / Email</th>
                            <th class="p-3 font-bold">Estado</th>
                            <th class="p-3 font-bold">Lead Time</th>
                            <th class="p-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="provider in providers.data" :key="provider.id" class="hover:bg-muted/30 transition-none">
                            <td class="p-3 font-mono text-xs text-primary">
                                {{ provider.internal_code || 'N/A' }}
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-foreground">{{ provider.company_name }}</div>
                                <div class="text-[10px] text-muted-foreground font-mono">{{ provider.tax_id }}</div>
                            </td>
                            <td class="p-3">
                                <div class="text-xs">{{ provider.contact_name || '---' }}</div>
                                <div class="text-xs text-muted-foreground">{{ provider.email_orders }}</div>
                            </td>
                            <td class="p-3">
                                <span :class="[
                                    'px-2 py-0.5 text-[10px] font-bold border',
                                    provider.is_active 
                                        ? 'bg-success/10 text-success border-success/20' 
                                        : 'bg-destructive/10 text-destructive border-destructive/20'
                                ]">
                                    {{ provider.is_active ? 'ACTIVE' : 'INACTIVE' }}
                                </span>
                            </td>
                            <td class="p-3 text-xs">
                                {{ provider.lead_time_days }} días
                            </td>
                            <td class="p-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <Link :href="route('admin.providers.edit', provider.id)" 
                                          class="p-1.5 border border-border hover:bg-muted text-muted-foreground transition-none">
                                        <Edit :size="14" />
                                    </Link>
                                    <button @click="handleDelete(provider)" 
                                            class="p-1.5 border border-border hover:bg-destructive hover:text-destructive-foreground text-muted-foreground transition-none">
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="providers.data.length === 0">
                            <td colspan="6" class="p-12 text-center text-muted-foreground italic text-xs">
                                NO_DATA: No se encontraron registros para la consulta actual.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center pt-2">
                <div class="text-[10px] text-muted-foreground font-mono uppercase">
                    Protocolo: Cursor-Based Pagination
                </div>
                <div class="flex gap-1">
                    <Link 
                        v-if="providers.prev_page_url" 
                        :href="providers.prev_page_url"
                        class="p-2 border border-border bg-card hover:bg-muted transition-none">
                        <ChevronLeft :size="16" />
                    </Link>
                    <div v-else class="p-2 border border-border opacity-20 cursor-not-allowed bg-card">
                        <ChevronLeft :size="16" />
                    </div>

                    <Link 
                        v-if="providers.next_page_url" 
                        :href="providers.next_page_url"
                        class="p-2 border border-border bg-card hover:bg-muted transition-none">
                        <ChevronRight :size="16" />
                    </Link>
                    <div v-else class="p-2 border border-border opacity-20 cursor-not-allowed bg-card">
                        <ChevronRight :size="16" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>