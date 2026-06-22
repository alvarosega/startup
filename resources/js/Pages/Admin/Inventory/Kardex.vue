<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, ChevronLeft, ChevronRight, CornerDownRight, History } from 'lucide-vue-next';

defineProps({
    sku: Object,
    branch: Object,
    movements: Object
});

const getTypeStyle = (type) => {
    switch (type) {
        case 'PURCHASE_INTAKE': return 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800';
        case 'REGULAR_RESERVATION': return 'bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800';
        case 'RESERVATION_EXPIRY': return 'bg-sky-50 dark:bg-sky-950/20 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800';
        case 'SAFETY_RESCUE': return 'bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800';
        case 'QUARANTINE_ISOLATION': return 'bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800';
        default: return 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600';
    }
};
</script>

<template>
    <Head :title="`Kardex - ${sku.name}`" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4 select-none">
                <Link :href="route('admin.inventory.index', { branch_id: branch.id })" class="p-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">
                    <ArrowLeft :size="18" />
                </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Libro Mayor // Kardex</h1>
                    <p class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 mt-0.5 uppercase">
                        Auditoría Cronológica Estricta: {{ sku.name }} | {{ branch.name }}
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md p-4 flex flex-col sm:flex-row justify-between gap-4 select-none shadow-sm">
                <div class="font-mono text-xs space-y-1">
                    <div class="text-neutral-400">SKU_REF_ID: <span class="text-neutral-900 dark:text-white select-all">{{ sku.id }}</span></div>
                    <div class="text-neutral-400">EAN_BARCODE: <span class="text-neutral-900 dark:text-white font-bold select-all">{{ sku.code }}</span></div>
                </div>
                <div class="bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800/60 px-3 py-2 rounded flex items-center gap-2 text-[10px] font-mono font-black uppercase text-neutral-500">
                    <History :size="16" class="text-neutral-400" /> SECUENCIA CRONOLÓGICA ACTIVA
                </div>
            </div>

            <div class="border border-neutral-200 dark:border-neutral-800 rounded-md bg-white dark:bg-neutral-900 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-neutral-50/70 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-800 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 select-none">
                            <th class="p-3 w-40">TIMESTAMP (UTC)</th>
                            <th class="p-3 w-48">EVENTO_LOGÍSTICO</th>
                            <th class="p-3 text-right font-mono w-28">VARIACIÓN</th>
                            <th class="p-3 w-44">REFERENCIA_DOC</th>
                            <th class="p-3">RAZÓN / JUSTIFICACIÓN AUDITABLE</th>
                            <th class="p-3 w-40">OPERADOR_ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800 font-mono text-xs">
                        <tr v-for="mv in movements.items" :key="mv.id" class="hover:bg-neutral-50/40 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="p-3 text-neutral-400 select-all">{{ mv.created_at }}</td>
                            <td class="p-3 select-none">
                                <span :class="getTypeStyle(mv.type)" class="text-[9px] px-1.5 py-0.5 font-bold uppercase tracking-tight rounded-sm">
                                    {{ mv.type }}
                                </span>
                            </td>
                            <td class="p-3 text-right font-bold select-all" :class="['PURCHASE_INTAKE', 'RESERVATION_EXPIRY'].includes(mv.type) ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                {{ ['PURCHASE_INTAKE', 'RESERVATION_EXPIRY'].includes(mv.type) ? '+' : '-' }}{{ Number(mv.quantity).toFixed(3) }}
                            </td>
                            <td class="p-3 text-neutral-900 dark:text-neutral-200 select-all text-[11px] font-bold">{{ mv.reference || '---' }}</td>
                            <td class="p-3 text-neutral-500 dark:text-neutral-400 select-all font-sans lowercase leading-tight">{{ mv.reason }}</td>
                            <td class="p-3 text-neutral-400 truncate uppercase max-w-[140px] select-none">{{ mv.operator_name }}</td>
                        </tr>
                        <tr v-if="movements.items.length === 0">
                            <td colspan="6" class="p-12 text-center text-neutral-400 select-none italic font-sans">
                                Ningún movimiento registrado en el Kardex para el horizonte temporal configurado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center pt-1 select-none font-mono">
                <div class="text-[9px] text-neutral-400 uppercase tracking-widest">Kardex Cursor Isolation Engine</div>
                <div class="flex gap-1">
                    <Link v-if="movements.meta.prev_cursor" :href="route('admin.inventory.kardex', sku.id, { branch_id: branch.id, cursor: movements.meta.prev_cursor })" class="p-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-400 hover:text-neutral-900 dark:hover:text-white rounded-md transition-colors"><ChevronLeft :size="14" /></Link>
                    <div v-else class="p-2 border border-neutral-200 dark:border-neutral-800 opacity-20 bg-neutral-100 dark:bg-neutral-950 rounded-md cursor-not-allowed"><ChevronLeft :size="14" /></div>
                    
                    <Link v-if="movements.meta.next_cursor" :href="route('admin.inventory.kardex', sku.id, { branch_id: branch.id, cursor: movements.meta.next_cursor })" class="p-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-400 hover:text-neutral-900 dark:hover:text-white rounded-md transition-colors"><ChevronRight :size="14" /></Link>
                    <div v-else class="p-2 border border-neutral-200 dark:border-neutral-800 opacity-20 bg-neutral-100 dark:bg-neutral-950 rounded-md cursor-not-allowed"><ChevronRight :size="14" /></div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>