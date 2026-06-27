<script setup>
import { useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    purchase: {
        type: Object,
        required: true
    }
});

// Construcción del payload basado exclusivamente en la deconstrucción asociativa de CompletePurchaseDataDTO
const form = useForm({
    items: props.purchase.items.map(item => ({
        sku_id: item.sku_id,
        lot_code: '',
        expiration_date: ''
    }))
});

/**
 * Ejecuta el envío de mutación a COMPLETED mediante la API de persistencia PUT.
 */
const submitConsolidation = () => {
    form.put(route('admin.purchases.complete', { purchase: props.purchase.id }));
};

/**
 * Ejecuta la revocación de la compra pendiente desde el cockpit de edición.
 */
const triggerCancelFromEdit = () => {
    if (window.confirm('¿Desea abortar y cancelar permanentemente este asiento documental pendiente?')) {
        router.post(route('admin.purchases.cancel', { purchase: props.purchase.id }));
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Consolidación Física de Carga Pendiente
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <div class="bg-card border border-border p-4 rounded-md shadow-flat space-y-4 font-mono text-xs">
                <div class="border-b border-border pb-1.5 font-sans font-bold uppercase text-muted-foreground tracking-wider text-[11px]">
                    Cabecera del Asiento Compra
                </div>
                <div class="space-y-2">
                    <div>
                        <span class="text-muted-foreground block font-sans font-semibold text-[10px] uppercase">Nro. Factura / Remisión</span>
                        <span class="text-foreground font-bold text-sm">{{ purchase.document_number }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block font-sans font-semibold text-[10px] uppercase">Sucursal Destino</span>
                        <span class="text-foreground font-semibold">{{ purchase.branch_name }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block font-sans font-semibold text-[10px] uppercase">Socio Comercial</span>
                        <span class="text-foreground font-semibold">{{ purchase.provider_name }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block font-sans font-semibold text-[10px] uppercase">Fecha Emisión Emisor</span>
                        <span class="text-foreground">{{ purchase.purchase_date }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block font-sans font-semibold text-[10px] uppercase">Término Comercial</span>
                        <span class="text-foreground font-bold text-info">{{ purchase.payment_type }}</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-border">
                    <button type="button" @click="triggerCancelFromEdit" class="w-full px-2 py-2 bg-destructive/5 text-destructive border border-destructive/20 rounded font-sans font-bold text-xs uppercase tracking-wider hover:bg-destructive/10 transition-colors">
                        Cancelar Compra Documental
                    </button>
                </div>
            </div>

            <div class="lg:col-span-2 bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
                <div class="border-b border-border pb-2">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Inyección de Capas de Lotes FIFO</h3>
                    <p class="text-[11px] text-muted-foreground mt-0.5">Asigne de forma obligatoria el lote e ID de vencimiento real provisto por el fabricante.</p>
                </div>

                <form @submit.prevent="submitConsolidation" class="space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-border text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
                                    <th class="pb-2">Especificación del Artículo</th>
                                    <th class="pb-2 w-24 text-right">Cant. Pactada</th>
                                    <th class="pb-2 w-28 pl-4">Código Lote Manual *</th>
                                    <th class="pb-2 w-32 pl-3">Fecha Expiración *</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in purchase.items" :key="item.sku_id" class="border-b border-border/40 last:border-0 align-top">
                                    <td class="py-3 pr-2">
                                        <div class="text-xs text-foreground font-bold">{{ item.product_name }}</div>
                                        <div class="text-[10px] text-muted-foreground font-mono font-medium">Presentación: {{ item.sku_name || 'Única' }} [EAN: {{ item.sku_code }}]</div>
                                    </td>
                                    <td class="py-3 font-mono text-xs text-right font-semibold text-foreground/80 pt-4">
                                        {{ item.quantity.toFixed(3) }}
                                    </td>
                                    
                                    <td class="py-2 pl-4 w-40">
                                        <input v-model="form.items[index].lot_code" type="text" required maxlength="32" class="admin-input text-xs font-mono" :class="{ 'border-error': form.errors[`items.${index}.lot_code`] }" placeholder="LOT-99A" />
                                        <p v-if="form.errors[`items.${index}.lot_code`]" class="text-error text-[10px] font-medium mt-0.5 max-w-[150px] truncate">
                                            {{ form.errors[`items.${index}.lot_code`] }}
                                        </p>
                                    </td>
                                    <td class="py-2 pl-3 w-40">
                                        <input v-model="form.items[index].expiration_date" type="date" required class="admin-input text-xs font-mono" :class="{ 'border-error': form.errors[`items.${index}.expiration_date`] }" />
                                        <p v-if="form.errors[`items.${index}.expiration_date`]" class="text-error text-[10px] font-medium mt-0.5">Requerido</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                        <Link :href="route('admin.purchases.index')" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                            Volver
                        </Link>
                        <button type="submit" :disabled="form.processing" class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider">
                            <span class="material-symbols-rounded text-base">inventory_2</span>
                            <span>{{ form.processing ? 'Consolidando Lotes...' : 'Inyectar Stock a Almacén' }}</span>
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </AdminLayout>
</template>