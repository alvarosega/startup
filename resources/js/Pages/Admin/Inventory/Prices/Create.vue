<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    branches: {
        type: Array,
        required: true
    },
    skus: {
        type: Array,
        required: true
    }
});

// Inicialización del payload bajo los tipos escalares del StorePriceRequest
const form = useForm({
    branch_id: '',
    sku_id: '',
    type: 'REGULAR',
    list_price: 0.00,
    final_price: 0.00,
    min_quantity: 1,
    priority: 1,
    valid_from: '',
    valid_to: ''
});

/**
 * Regla de validación reactiva en cliente: Bloquea si el precio final excede el sugerido de lista.
 */
const isPriceInvalid = computed(() => {
    return Number(form.final_price) > Number(form.list_price);
});

/**
 * Transforma y limpia las estampas cronológicas HTML sustituyendo el delimitador "T" 
 * para cumplir de forma estricta con la regla del backend format:Y-m-d H:i:s
 */
const submit = () => {
    if (isPriceInvalid.value) return;

    form.transform((data) => ({
        ...data,
        valid_from: data.valid_from ? data.valid_from.replace('T', ' ') : '',
        valid_to: data.valid_to ? data.valid_to.replace('T', ' ') : null
    })).post(route('admin.prices.store'));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Inyección de Regla Tarifaria Comercial
        </template>

        <div class="max-w-3xl bg-card border border-border p-6 rounded-md shadow-flat">
            <form @submit.prevent="submit" class="space-y-5">
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Sucursal / Ámbito Regional *</label>
                        <select v-model="form.branch_id" required class="admin-input" :class="{ 'border-error': form.errors.branch_id }">
                            <option value="" disabled>Seleccione nodo...</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <p v-if="form.errors.branch_id" class="text-error text-[11px] font-medium mt-1">{{ form.errors.branch_id }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Variante Comercial (SKU) *</label>
                        <select v-model="form.sku_id" required class="admin-input" :class="{ 'border-error': form.errors.sku_id }">
                            <option value="" disabled>Seleccione variante...</option>
                            <option v-for="s in skus" :key="s.id" :value="s.id">{{ s.display }}</option>
                        </select>
                        <p v-if="form.errors.sku_id" class="text-error text-[11px] font-medium mt-1">{{ form.errors.sku_id }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Segmentación del Canal *</label>
                        <select v-model="form.type" required class="admin-input">
                            <option value="REGULAR">REGULAR (Público General)</option>
                            <option value="WHOLESALE">WHOLESALE (Mayorista)</option>
                            <option value="PROMOTION">PROMOTION (Campaña / Descuento)</option>
                            <option value="DISTRIBUTOR">DISTRIBUTOR (Distribuidor Autorizado)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Precio de Lista *</label>
                        <input v-model.number="form.list_price" type="number" step="0.01" min="0.00" required class="admin-input font-mono text-right" :class="{ 'border-error': form.errors.list_price }" />
                        <p v-if="form.errors.list_price" class="text-error text-[11px] font-medium mt-1">{{ form.errors.list_price }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Precio Final de Facturación *</label>
                        <input v-model.number="form.final_price" type="number" step="0.01" min="0.00" required class="admin-input font-mono text-right" :class="{ 'border-error': form.errors.final_price || isPriceInvalid }" />
                        <p v-if="form.errors.final_price" class="text-error text-[11px] font-medium mt-1">{{ form.errors.final_price }}</p>
                        <p v-else-if="isPriceInvalid" class="text-error text-[11px] font-bold mt-1 flex items-center gap-0.5">
                            <span class="material-symbols-rounded text-xs shrink-0">warning</span>
                            <span>Excede el precio lista.</span>
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Volumen Mínimo Requerido *</label>
                        <input v-model.number="form.min_quantity" type="number" min="1" required class="admin-input font-mono text-center" :class="{ 'border-error': form.errors.min_quantity }" />
                        <p v-if="form.errors.min_quantity" class="text-error text-[11px] font-medium mt-1">{{ form.errors.min_quantity }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Índice Jerarquía Prioridad *</label>
                        <input v-model.number="form.priority" type="number" min="1" required class="admin-input font-mono text-center" :class="{ 'border-error': form.errors.priority }" />
                        <p v-if="form.errors.priority" class="text-error text-[11px] font-medium mt-1">{{ form.errors.priority }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-border/60 pt-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Apertura de Ventana (Valid From) *</label>
                        <input 
                            v-model="form.valid_from" 
                            type="datetime-local" 
                            step="1" 
                            required 
                            class="admin-input font-mono text-xs uppercase" 
                            :class="{ 'border-error': form.errors.valid_from }" 
                        />
                        <p v-if="form.errors.valid_from" class="text-error text-[11px] font-medium mt-1">{{ form.errors.valid_from }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Cierre de Ventana (Valid To - Opcional)</label>
                        <input 
                            v-model="form.valid_to" 
                            type="datetime-local" 
                            step="1" 
                            class="admin-input font-mono text-xs uppercase" 
                            :class="{ 'border-error': form.errors.valid_to }" 
                        />
                        <p v-if="form.errors.valid_to" class="text-error text-[11px] font-medium mt-1">{{ form.errors.valid_to }}</p>
                    </div>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <Link :href="route('admin.prices.index')" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                        Cancelar
                    </Link>
                    <button 
                        type="submit" 
                        :disabled="form.processing || isPriceInvalid" 
                        class="admin-btn-primary text-xs font-bold uppercase tracking-wider transition-opacity disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <span>{{ form.processing ? 'Persistiendo Tarifa...' : 'Sincronizar Estructura' }}</span>
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>