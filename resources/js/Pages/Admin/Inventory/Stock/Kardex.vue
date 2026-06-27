<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    sku_id: {
        type: String,
        required: true
    },
    kardex: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <AdminLayout>
        <template #header>
            Línea de Tiempo del Kardex Operativo
        </template>

        <div class="space-y-4">
            <div class="flex items-center justify-between bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="text-xs text-muted-foreground font-medium">
                    Rastro de auditoría inmutable O(1) asignado a la variante ID: <span class="font-mono font-bold text-foreground">{{ sku_id }}</span>
                </div>
                <Link :href="route('admin.inventory.index')" class="px-3 py-1.5 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200">
                    Volver a Saldos
                </Link>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Estampa de Tiempo</th>
                            <th class="admin-table-th">Tipo Protocolo</th>
                            <th class="admin-table-th">Código Lote Interno</th>
                            <th class="admin-table-th text-right">Variación Cantidad</th>
                            <th class="admin-table-th text-right">Balance Resultante</th>
                            <th class="admin-table-th">Referencia Operativa</th>
                            <th class="admin-table-th">Motivo del Ajuste</th>
                            <th class="admin-table-th text-right">Administrador</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="kardex.data.length === 0">
                            <td colspan="8" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se registran movimientos históricos en los libros para este recurso.
                            </td>
                        </tr>
                        <tr v-for="mov in kardex.data" :key="mov.id" class="admin-table-tr text-xs">
                            <td class="admin-table-td font-mono text-muted-foreground whitespace-nowrap">
                                {{ mov.created_at }}
                            </td>
                            <td class="admin-table-td font-bold tracking-wider uppercase text-[11px]">
                                {{ mov.type }}
                            </td>
                            <td class="admin-table-td font-mono font-semibold text-foreground">
                                {{ mov.lot_code || '—' }}
                            </td>
                            <td class="admin-table-td font-mono text-right font-bold" :class="mov.quantity > 0 ? 'text-success' : 'text-destructive'">
                                {{ mov.quantity > 0 ? '+' : '' }}{{ mov.quantity.toFixed(3) }}
                            </td>
                            <td class="admin-table-td font-mono text-right font-bold text-foreground">
                                {{ mov.balance_after.toFixed(3) }}
                            </td>
                            <td class="admin-table-td text-muted-foreground font-medium max-w-xs truncate">
                                {{ mov.reference || '—' }}
                            </td>
                            <td class="admin-table-td text-muted-foreground italic max-w-xs truncate">
                                {{ mov.reason || '—' }}
                            </td>
                            <td class="admin-table-td text-right font-semibold text-foreground whitespace-nowrap">
                                {{ mov.admin_name || 'Sistema / Automatización' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="kardex.prev || kardex.next" class="flex items-center justify-end bg-card p-4 border border-border rounded-md shadow-flat gap-2">
                <Link v-if="kardex.prev" :href="route('admin.inventory.kardex', { skuId: sku_id })" :data="{ cursor: kardex.prev }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Anteriores
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Anteriores
                </div>

                <Link v-if="kardex.next" :href="route('admin.inventory.kardex', { skuId: sku_id })" :data="{ cursor: kardex.next }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Siguientes
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Siguientes
                </div>
            </div>
        </div>
    </AdminLayout>
</template>