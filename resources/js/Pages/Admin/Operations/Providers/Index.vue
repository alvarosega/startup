<script setup>
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Edit, Trash2, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({ 
    providers: Object,
    filters: Object,
    can_manage: Boolean 
});

const search = ref(props.filters.search || '');

watch(search, debounce((val) => {
    router.get(route('admin.operations.providers.index'), { search: val }, { 
        preserveState: true, replace: true, preserveScroll: true
    });
}, 300));

const handleDelete = (provider) => {
    if (confirm(`AUDIT_CONFIRM: ¿Eliminar socio comercial de forma permanente: ${provider.company_name}?`)) {
        router.delete(route('admin.operations.providers.destroy', provider.id));
    }
};
</script>

<template>
    <Head title="Proveedores" />
    <AdminLayout>

        <div class="p-4 max-w-[1600px] mx-auto space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white dark:bg-neutral-900 p-3 border border-neutral-200 dark:border-neutral-800 shadow-sm select-none rounded-md">
                <div class="relative w-full md:w-96">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400" :size="16" />
                    <input 
                        v-model="search"
                        type="text" 
                        class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md pl-9 pr-4 py-1.5 text-xs font-mono uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors placeholder:text-neutral-400/50" 
                        placeholder="Buscar por Tax ID, Nombre o Código..." 
                    />
                </div>

                <Link v-if="can_manage" 
                      :href="route('admin.operations.providers.create')" 
                      class="w-full md:w-auto bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 px-4 py-1.5 border border-transparent rounded-md transition-colors text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2">
                    <Plus :size="16" />
                    REGISTRAR PROVEEDOR
                </Link>
            </div>

            <div class="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md shadow-sm overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/70 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-800 uppercase text-[10px] tracking-widest text-neutral-400 dark:text-neutral-500 font-mono select-none">
                            <th class="p-3 font-black">Internal Code</th>
                            <th class="p-3 font-black">Razón Social / Tax ID</th>
                            <th class="p-3 font-black">Contacto / Email</th>
                            <th class="p-3 font-black w-24 text-center">Estado</th>
                            <th class="p-3 font-black w-32">Lead Time</th>
                            <th class="p-3 font-black w-24 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        <tr v-for="provider in providers.items" :key="provider.id" class="hover:bg-neutral-50/40 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="p-3 font-mono text-xs text-neutral-900 dark:text-neutral-100 select-all">
                                {{ provider.internal_code || 'N/A' }}
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-neutral-900 dark:text-neutral-50 uppercase tracking-tight text-xs">{{ provider.company_name }}</div>
                                <div class="text-[10px] text-neutral-400 font-mono mt-0.5 select-all">{{ provider.tax_id }}</div>
                            </td>
                            <td class="p-3">
                                <div class="text-xs text-neutral-800 dark:text-neutral-200 uppercase">{{ provider.contact_name || '---' }}</div>
                                <div class="text-[11px] text-neutral-400 font-mono mt-0.5 select-all lowercase">{{ provider.email_orders }}</div>
                            </td>
                            <td class="p-3 text-center select-none">
                                <span :class="provider.is_active 
                                    ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/40' 
                                    : 'bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800/40'"
                                    class="px-2 py-0.5 text-[10px] font-mono font-bold border rounded-sm">
                                    {{ provider.is_active ? 'ACTIVE' : 'LOCKED' }}
                                </span>
                            </td>
                            <td class="p-3 text-xs text-neutral-600 dark:text-neutral-400 font-mono">
                                {{ provider.lead_time_days }} días
                            </td>
                            <td class="p-3 text-center select-none">
                                <div class="flex justify-center gap-1">
                                    <Link :href="route('admin.operations.providers.edit', provider.id)" 
                                          class="p-1.5 border border-neutral-200 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-400 hover:text-neutral-900 dark:hover:text-white rounded-md transition-colors">
                                        <Edit :size="14" />
                                    </Link>
                                    <button @click="handleDelete(provider)" 
                                            class="p-1.5 border border-neutral-200 dark:border-neutral-800 text-neutral-300 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-md transition-colors">
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="providers.items.length === 0">
                            <td colspan="6" class="p-16 text-center text-neutral-400 dark:text-neutral-500 font-mono text-xs uppercase tracking-wider select-none">
                                <span class="material-symbols-rounded text-3xl text-neutral-300 block mb-1">search_off</span>
                                NO_DATA: No se encontraron registros para la consulta actual.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center pt-2 select-none">
                <div class="text-[10px] text-neutral-400 font-mono uppercase tracking-widest">
                    Protocolo: Cursor-Based Pagination
                </div>
                <div class="flex gap-1">
                    <Link 
                        v-if="providers.meta.prev_cursor" 
                        :href="route('admin.operations.providers.index', { cursor: providers.meta.prev_cursor, search: search })"
                        class="p-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-400 hover:text-neutral-900 dark:hover:text-white rounded-md shadow-sm transition-colors">
                        <ChevronLeft :size="16" />
                    </Link>
                    <div v-else class="p-2 border border-neutral-200 dark:border-neutral-800 opacity-20 cursor-not-allowed bg-white dark:bg-neutral-900 text-neutral-400 rounded-md shadow-sm">
                        <ChevronLeft :size="16" />
                    </div>

                    <Link 
                        v-if="providers.meta.next_cursor" 
                        :href="route('admin.operations.providers.index', { cursor: providers.meta.next_cursor, search: search })"
                        class="p-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-400 hover:text-neutral-900 dark:hover:text-white rounded-md shadow-sm transition-colors">
                        <ChevronRight :size="16" />
                    </Link>
                    <div v-else class="p-2 border border-neutral-200 dark:border-neutral-800 opacity-20 cursor-not-allowed bg-white dark:bg-neutral-900 text-neutral-400 rounded-md shadow-sm">
                        <ChevronRight :size="16" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>