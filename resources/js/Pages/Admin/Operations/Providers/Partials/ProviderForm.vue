<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    form: {
        type: Object,
        required: true
    },
    isEdit: {
        type: Boolean,
        required: true
    }
});

defineEmits(['submit']);
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="space-y-6 max-w-4xl">
        
        <div class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
            <div class="border-b border-border pb-2">
                <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Estructura Corporativa e Impuestos</h3>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Razón Social *</label>
                    <input v-model="form.company_name" type="text" required class="admin-input" :class="{ 'border-error': form.errors.company_name }" placeholder="Proveedor de Alimentos S.A." />
                    <p v-if="form.errors.company_name" class="text-error text-xs font-medium mt-1">{{ form.errors.company_name }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Nombre Comercial</label>
                    <input v-model="form.commercial_name" type="text" class="admin-input" :class="{ 'border-error': form.errors.commercial_name }" placeholder="Alimentos Express" />
                    <p v-if="form.errors.commercial_name" class="text-error text-xs font-medium mt-1">{{ form.errors.commercial_name }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">NIT / Identificación Fiscal *</label>
                    <input v-model="form.tax_id" type="text" required class="admin-input font-mono" :class="{ 'border-error': form.errors.tax_id }" placeholder="1020304050" />
                    <p v-if="form.errors.tax_id" class="text-error text-xs font-medium mt-1">{{ form.errors.tax_id }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Código Interno de Abastecimiento</label>
                    <input v-model="form.internal_code" type="text" class="admin-input font-mono" :class="{ 'border-error': form.errors.internal_code }" placeholder="PROV-0019" />
                    <p v-if="form.errors.internal_code" class="text-error text-xs font-medium mt-1">{{ form.errors.internal_code }}</p>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
            <div class="border-b border-border pb-2">
                <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Contacto y Logística de Despacho</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Nombre del Contacto</label>
                    <input v-model="form.contact_name" type="text" class="admin-input" :class="{ 'border-error': form.errors.contact_name }" placeholder="Gerente de Ventas" />
                    <p v-if="form.errors.contact_name" class="text-error text-xs font-medium mt-1">{{ form.errors.contact_name }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Correo para Órdenes de Compra</label>
                    <input v-model="form.email_orders" type="email" class="admin-input font-mono" :class="{ 'border-error': form.errors.email_orders }" placeholder="pedidos@proveedor.com" />
                    <p v-if="form.errors.email_orders" class="text-error text-xs font-medium mt-1">{{ form.errors.email_orders }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Teléfono Operativo</label>
                    <input v-model="form.phone" type="text" class="admin-input font-mono" :class="{ 'border-error': form.errors.phone }" placeholder="+51999888777" />
                    <p v-if="form.errors.phone" class="text-error text-xs font-medium mt-1">{{ form.errors.phone }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-3 space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Dirección de Matriz / Almacén</label>
                    <input v-model="form.address" type="text" class="admin-input" :class="{ 'border-error': form.errors.address }" placeholder="Zona Industrial Sec. B Lote 4" />
                    <p v-if="form.errors.address" class="text-error text-xs font-medium mt-1">{{ form.errors.address }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Ciudad</label>
                    <input v-model="form.city" type="text" class="admin-input" :class="{ 'border-error': form.errors.city }" placeholder="Santa Cruz" />
                    <p v-if="form.errors.city" class="text-error text-xs font-medium mt-1">{{ form.errors.city }}</p>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
            <div class="border-b border-border pb-2">
                <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Condiciones Comerciales y Lead Time</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Tiempo de Entrega (Días) *</label>
                    <input v-model.number="form.lead_time_days" type="number" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.lead_time_days }" />
                    <p v-if="form.errors.lead_time_days" class="text-error text-xs font-medium mt-1">{{ form.errors.lead_time_days }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Valor Mínimo de Pedido *</label>
                    <input v-model.number="form.min_order_value" type="number" step="0.01" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.min_order_value }" />
                    <p v-if="form.errors.min_order_value" class="text-error text-xs font-medium mt-1">{{ form.errors.min_order_value }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Días de Crédito *</label>
                    <input v-model.number="form.credit_days" type="number" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.credit_days }" />
                    <p v-if="form.errors.credit_days" class="text-error text-xs font-medium mt-1">{{ form.errors.credit_days }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Límite de Crédito *</label>
                    <input v-model.number="form.credit_limit" type="number" step="0.01" min="0" required class="admin-input font-mono" :class="{ 'border-error': form.errors.credit_limit }" />
                    <p v-if="form.errors.credit_limit" class="text-error text-xs font-medium mt-1">{{ form.errors.credit_limit }}</p>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
            <div class="border-b border-border pb-2">
                <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Observaciones Internas</h3>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Notas de Abastecimiento</label>
                <textarea v-model="form.notes" rows="3" class="admin-input" :class="{ 'border-error': form.errors.notes }" placeholder="Especificaciones sobre empaque, horarios de recepción..."></textarea>
                <p v-if="form.errors.notes" class="text-error text-xs font-medium mt-1">{{ form.errors.notes }}</p>
            </div>

            <div class="pt-2">
                <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4 transition-colors" />
                    <span class="text-xs font-bold text-foreground uppercase tracking-wide">Proveedor Habilitado para Órdenes de Compra</span>
                </label>
                <p v-if="form.errors.is_active" class="text-error text-xs font-medium mt-1">{{ form.errors.is_active }}</p>
            </div>
        </div>

        <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
            <Link 
                :href="route('admin.operations.providers.index')"
                class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200 dark:hover:bg-neutral-800 transition-colors"
                :class="{ 'pointer-events-none opacity-50': form.processing }"
            >
                Cancelar
            </Link>
            
            <button
                type="submit"
                :disabled="form.processing"
                class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider"
            >
                <span class="material-symbols-rounded text-base shrink-0">save</span>
                <span>{{ form.processing ? 'Sincronizando...' : 'Guardar Proveedor' }}</span>
            </button>
        </div>
    </form>
</template>