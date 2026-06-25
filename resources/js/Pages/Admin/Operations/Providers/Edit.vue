<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProviderForm from '@/Pages/Admin/Operations/Providers/Partials/ProviderForm.vue';

const props = defineProps({
    provider: {
        type: Object,
        required: true
    }
});

// Hidratación reactiva de la instanciauseForm con el mapeo del controlador
const form = useForm({
    company_name: props.provider.company_name,
    commercial_name: props.provider.commercial_name || '',
    tax_id: props.provider.tax_id,
    internal_code: props.provider.internal_code || '',
    contact_name: props.provider.contact_name || '',
    email_orders: props.provider.email_orders || '',
    phone: props.provider.phone || '',
    address: props.provider.address || '',
    city: props.provider.city || '',
    lead_time_days: Number(props.provider.lead_time_days),
    min_order_value: Number(props.provider.min_order_value),
    credit_days: Number(props.provider.credit_days),
    credit_limit: Number(props.provider.credit_limit),
    is_active: Boolean(props.provider.is_active),
    notes: props.provider.notes || ''
});

/**
 * Despacha la mutación de datos relacionales al backend.
 */
const submit = () => {
    form.put(route('admin.operations.providers.update', props.provider.id));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Sincronizar Historial de Proveedor
        </template>

        <ProviderForm 
            :form="form"
            :is-edit="true"
            @submit="submit"
        />
    </AdminLayout>
</template>