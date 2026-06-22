<script setup>
defineProps({ skus: Array });
const emit = defineEmits(['manage-prices', 'delete-sku']);
</script>

<template>
    <div class="w-full border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 overflow-hidden rounded-sm select-none shadow-sm">
        <div class="px-3 py-1.5 bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 flex items-center gap-1.5">
            <span class="material-symbols-rounded text-neutral-400 dark:text-neutral-500 text-sm">inventory</span>
            <span class="text-[10px] font-mono font-black text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Desglose Técnico de Presentaciones (SKU)</span>
        </div>
        
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-neutral-50/50 dark:bg-neutral-900/30 font-bold text-neutral-400 dark:text-neutral-500 border-b border-neutral-200 dark:border-neutral-800/40 text-[10px] uppercase font-mono">
                    <th class="p-2 pl-3">Denominación Logística</th>
                    <th class="p-2 w-36 font-mono">Código EAN/UPC</th>
                    <th class="p-2 text-right w-28 font-mono">Precio Ref.</th>
                    <th class="p-2 text-right w-24 font-mono">F. Conv</th>
                    <th class="p-2 text-right w-24 font-mono">Masa (Kg)</th>
                    <th class="p-2 text-center w-24">Estado</th>
                    <th class="p-2 text-center w-32">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800/60 font-mono text-xs text-neutral-900 dark:text-neutral-50">
                <tr v-for="sku in skus" :key="sku.id" class="hover:bg-neutral-50/40 dark:hover:bg-neutral-900/40 transition-colors">
                    <td class="p-2 pl-3 font-sans font-medium text-neutral-800 dark:text-neutral-200 uppercase tracking-tight select-all">{{ sku.name }}</td>
                    <td class="p-2 font-bold text-neutral-900 dark:text-white select-all text-xs">{{ sku.code }}</td>
                    <td class="p-2 text-right text-neutral-900 dark:text-neutral-50 font-semibold select-all">Bs. {{ Number(sku.base_price).toFixed(2) }}</td>
                    <td class="p-2 text-right text-neutral-400 dark:text-neutral-500 select-all">{{ Number(sku.conversion_factor).toFixed(3) }}</td>
                    <td class="p-2 text-right text-neutral-400 dark:text-neutral-500 select-all">{{ Number(sku.weight).toFixed(3) }}</td>
                    <td class="p-2 text-center select-none">
                        <span :class="sku.is_active 
                            ? 'inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40' 
                            : 'inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40'" class="px-1.5 py-0.5">
                            {{ sku.is_active ? 'ONLINE' : 'LOCKED' }}
                        </span>
                    </td>
                    <td class="p-2 text-center select-none">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" @click="emit('manage-prices', sku)" 
                                    class="px-2 py-0.5 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-sm font-sans font-bold text-[10px] uppercase tracking-wider text-neutral-900 dark:text-neutral-50 inline-flex items-center gap-1 transition-colors">
                                <span class="material-symbols-rounded text-sm text-neutral-900 dark:text-white">universal_currency_alt</span>
                                <span>Precios</span>
                            </button>
                            <button type="button" @click="emit('delete-sku', sku.id)" 
                                    class="p-1 text-neutral-300 hover:text-rose-600 dark:hover:text-rose-400 rounded-sm hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors flex items-center justify-center">
                                <span class="material-symbols-rounded text-base">delete_sweep</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="!skus || skus.length === 0">
                    <td colspan="7" class="p-4 text-center text-neutral-400 dark:text-neutral-500 font-sans text-xs italic uppercase tracking-wider bg-neutral-50/10 dark:bg-neutral-900/5">
                        El nodo maestro no posee presentaciones asignadas en la base relacional.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>