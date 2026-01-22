<script setup>
    import BasePagination from '@/Components/Base/BasePagination.vue';
    
    const props = defineProps({
        rows: {
            type: Object, // Objeto paginado de Laravel (data, links, meta)
            required: true,
        },
        cols: {
            type: Array, // [{ key: 'sku', label: 'SKU' }, { key: 'status', label: 'Estado' }]
            required: true,
        },
    });
    </script>
    
    <template>
        <div class="w-full">
            
            <div class="hidden md:block bg-skin-fill-card shadow-sm border border-skin-border rounded-global overflow-hidden">
                <table class="min-w-full divide-y divide-skin-border">
                    <thead class="bg-skin-fill-hover">
                        <tr>
                            <th v-for="col in cols" :key="col.key" 
                                scope="col" 
                                class="px-6 py-3 text-left text-xs font-bold text-skin-muted uppercase tracking-wider">
                                {{ col.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-skin-border bg-skin-fill-card">
                        <tr v-for="row in rows.data" :key="row.id" class="hover:bg-skin-fill/50 transition-colors">
                            <td v-for="col in cols" :key="col.key" class="px-6 py-4 whitespace-nowrap text-sm text-skin-base">
                                <slot :name="col.key" :item="row">
                                    {{ row[col.key] }}
                                </slot>
                            </td>
                        </tr>
                        
                        <tr v-if="rows.data.length === 0">
                            <td :colspan="cols.length" class="px-6 py-10 text-center text-skin-muted text-sm">
                                No se encontraron registros.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
    
            <div class="md:hidden space-y-4">
                <div v-for="row in rows.data" :key="row.id" class="bg-skin-fill-card shadow-sm border border-skin-border rounded-global p-4">
                    <div class="space-y-3">
                        <div v-for="col in cols" :key="col.key" class="flex justify-between items-center border-b border-skin-border last:border-0 pb-2 last:pb-0">
                            <span class="text-xs font-bold text-skin-muted uppercase">{{ col.label }}</span>
                            <span class="text-sm text-skin-base text-right font-medium">
                                <slot :name="col.key" :item="row">
                                    {{ row[col.key] }}
                                </slot>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div v-if="rows.data.length === 0" class="text-center py-10 text-skin-muted text-sm">
                    No se encontraron registros.
                </div>
            </div>
    
            <div class="mt-4">
                <BasePagination :links="rows.links" />
            </div>
        </div>
    </template>