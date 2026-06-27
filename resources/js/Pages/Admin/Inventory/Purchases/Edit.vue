<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    purchase: {
        type: Object,
        required: true
    }
});

// Inicialización acoplada al contrato exacto de validación del CompletePurchaseRequest
const form = useForm({
    items: props.purchase.items.map(item => ({
        sku_id: item.sku_id,
        sku_code: item.sku_code,
        product_name: item.product_name,
        sku_name: item.sku_name,
        quantity: item.quantity,
        cost_price: item.cost_price,
        lot_code: '',
        expiration_date: ''
    }))
});

/**
 * Despacha la consolidación de capas físicas FIFO aplicando el mapeo plano hacia el DTO asociativo.
 */
const submit = () => {
    form.put(route('admin.purchases.complete', { purchase: props.purchase.id }));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Cockpit de Recepción Física FIFO
        </template>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start max-w-5xl">
            <div class="lg:col-span-1 bg-card border border-border p-4 rounded-md shadow-flat space-y-4 font-mono text-xs select-none">
                <div class="border-b border-border pb-2 font-sans">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Documento Base Inmutable</h3>
                </div>

                <div>
                    <span class="text-muted-foreground block font-sans text-[10px] uppercase font-bold tracking-wider">Nro. Documento</span>
                    <span class="text-foreground font-bold text-sm">{{ purchase.document_number }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block font-sans text-[10px] uppercase font-bold tracking-wider">Fecha del Registro</span>
                    <span class="text-foreground font-semibold">{{ purchase.purchase_date }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block font-sans text-[10px] uppercase font-bold tracking-wider">Almacén de Destino</span>
                    <span class="text-foreground font-semibold font-sans">{{ purchase.branch_name }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block font-sans text-[10px] uppercase font-bold tracking-wider">Socio Comercial</span>
                    <span class="text-foreground font-semibold font-sans">{{ purchase.provider_name }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block font-sans text-[10px] uppercase font-bold tracking-wider">Término Comercial</span>
                    <span class="text-foreground font-bold text-xs text-primary">{{ purchase.payment_type }}</span>
                </div>
                <div class="pt-2 border-t border-border/60 font-sans">
                    <span class="text-[10px] bg-amber-500/10 text-amber-600 font-bold px-2 py-0.5 rounded uppercase tracking-wider inline-block">
                        Estado Actual: {{ purchase.status }}
                    </span>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-4">
                <div class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
                    <div class="border-b border-border pb-2">
                        <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Especificación Física de Capas de Lote</h3>
                        <p class="text-[11px] text-muted-foreground mt-0.5">Asigne de forma obligatoria el código de lote y su vencimiento. Las cantidades y costos se mantienen bloqueados por auditoría.</p>
                    </div>

                    <div class="space-y-4">
                        <div 
                            v-for="(item, index) in form.items" 
                            :key="item.sku_id"
                            class="p-4 bg-neutral-50/60 border border-border/80 rounded-md space-y-3"
                        >
                            <div class="flex items-start justify-between gap-4 text-xs select-none">
                                <div>
                                    <span class="text-[10px] font-mono bg-neutral-200 px-1.5 py-0.5 rounded text-muted-foreground font-bold tracking-tight mr-1.5">{{ item.sku_code }}</span>
                                    <span class="text-foreground font-bold">{{ item.product_name }}</span>
                                    <span class="text-muted-foreground text-[11px] block mt-0.5">{{ item.sku_name }}</span>
                                </div>
                                <div class="text-right font-mono space-y-0.5">
                                    <div><span class="text-muted-foreground text-[10px] font-sans">QTY:</span> <span class="font-bold text-foreground">{{ item.quantity.toFixed(3) }}</span></div>
                                    <div><span class="text-muted-foreground text-[10px] font-sans">COST:</span> <span class="font-bold text-foreground">{{ item.cost_price.toFixed(2) }}</span></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-border/40">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Código de Lote Manual *</label>
                                    <input 
                                        v-model="item.lot_code" 
                                        type="text" 
                                        required 
                                        class="admin-input text-xs font-mono uppercase"
                                        :class="{ 'border-error': form.errors[`items.${index}.lot_code`] }"
                                        placeholder="EJM: LOT-CAN-90"
                                    />
                                    <p v-if="form.errors[`items.${index}.lot_code`]" class="text-error text-[11px] font-medium mt-0.5">{{ form.errors[`items.${index}.lot_code`] }}</p>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Fecha de Expiración (FEFO)</label>
                                    <input 
                                        v-model="item.expiration_date" 
                                        type="date" 
                                        class="admin-input text-xs"
                                        :class="{ 'border-error': form.errors[`items.${index}.expiration_date`] }"
                                    />
                                    <p v-if="form.errors[`items.${index}.expiration_date`]" class="text-error text-[11px] font-medium mt-0.5">{{ form.errors[`items.${index}.expiration_date`] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-border/60 flex items-center justify-end gap-3">
                        <Link :href="route('admin.purchases.index')" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider">
                            <span class="material-symbols-rounded text-base">inventory</span>
                            <span>{{ form.processing ? 'Consolidando Existencias...' : 'Consolidar Ingreso Físico' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>