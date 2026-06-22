<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Receipt, FileText, ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineProps({
    purchases: Object
});
</script>

<template>
    <Head title="Abastecimiento - Historial de Compras" />
    <AdminLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 select-none">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Ingresos de Stock</h1>
                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-mono tracking-wider uppercase mt-0.5">Historial documental de adquisiciones y verificación de lotes</p>
                </div>
                <Link :href="route('admin.purchases.create')" class="bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 px-4 py-2 border border-transparent rounded-md transition-colors text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2">
                    <Plus :size="16" /> REGISTRAR_COMPRA_LOTE
                </Link>
            </div>
        </template>

        <div class="space-y-4">
            <div class="border border-neutral-200 dark:border-neutral-800 rounded-md bg-white dark:bg-neutral-900 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-neutral-50/70 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-800 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 select-none">
                            <th class="p-3 w-40">FECHA_COMPRA</th>
                            <th class="p-3 w-48">DOCUMENTO / FACTURA</th>
                            <th class="p-3">SOCIO COMERCIAL (PROVEEDOR)</th>
                            <th class="p-3 min-w-[160px]">NODO DESTINO (ALMACÉN)</th>
                            <th class="p-3 w-32">MÉTODO PAGO</th>
                            <th class="p-3 w-32 text-center">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        <tr v-for="pc in purchases.items" :key="pc.id" class="hover:bg-neutral-50/40 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="p-3 font-mono text-neutral-400 select-all">{{ pc.purchase_date }}</td>
                            <td class="p-3 font-mono font-bold text-neutral-900 dark:text-white select-all flex items-center gap-1.5">
                                <Receipt :size="14" class="text-neutral-400 shrink-0" /> {{ pc.document_number }}
                            </td>
                            <td class="p-3 uppercase font-medium text-neutral-800 dark:text-neutral-100 select-all">{{ pc.provider_name }}</td>
                            <td class="p-3 uppercase text-neutral-500 dark:text-neutral-400 select-all">{{ pc.branch_name }}</td>
                            <td class="p-3 font-mono select-none">
                                <span :class="pc.payment_type === 'CREDIT' ? 'text-amber-600 dark:text-amber-400' : 'text-neutral-600 dark:text-neutral-400'" class="text-[10px] font-black">
                                    {{ pc.payment_type }}
                                </span>
                            </td>
                            <td class="p-3 text-center select-none">
                                <span class="bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40 px-2 py-0.5 text-[9px] font-mono font-bold uppercase rounded-sm">
                                    {{ pc.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="purchases.items.length === 0">
                            <td colspan="6" class="p-16 text-center text-neutral-400 font-mono select-none">
                                <FileText class="mx-auto text-neutral-300 dark:text-neutral-700 mb-1" :size="28" />
                                No existen ingresos de stock cargados en el nodo relacional.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center select-none font-mono">
                <div class="text-[9px] text-neutral-400 uppercase tracking-widest">Purchase Ledger Cursor Feed</div>
                <div class="flex gap-1">
                    <Link v-if="purchases.meta.prev_cursor" :href="route('admin.purchases.index', { cursor: purchases.meta.prev_cursor })" class="p-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md"><ChevronLeft :size="14" /></Link>
                    <div v-else class="p-2 border border-neutral-200 dark:border-neutral-800 opacity-20 bg-neutral-100 dark:bg-neutral-950 rounded-md cursor-not-allowed"><ChevronLeft :size="14" /></div>
                    
                    <Link v-if="purchases.meta.next_cursor" :href="route('admin.purchases.index', { cursor: purchases.meta.next_cursor })" class="p-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md"><ChevronRight :size="14" /></Link>
                    <div v-else class="p-2 border border-neutral-200 dark:border-neutral-800 opacity-20 bg-neutral-100 dark:bg-neutral-950 rounded-md cursor-not-allowed"><ChevronRight :size="14" /></div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>