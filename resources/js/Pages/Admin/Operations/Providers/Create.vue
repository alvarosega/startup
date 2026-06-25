<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProviderForm from '@/Pages/Admin/Operations/Providers/Partials/ProviderForm.vue';

// Inicialización del formulario basándose en las restricciones del StoreProviderRequest
const form = useForm({
    company_name: '',
    commercial_name: '',
    tax_id: '',
    internal_code: '',
    contact_name: '',
    email_orders: '',
    phone: '',
    address: '',
    city: '',
    lead_time_days: 1, // Sincronizado a un valor inicial seguro según la regla min:1 del update
    min_order_value: 0.00,
    credit_days: 0,
    credit_limit: 0.00,
    is_active: true,
    notes: ''
});

/**
 * Ejecuta la inserción del nuevo proveedor a la base de datos de abastecimiento.
 */
const submit = () => {
    form.post(route('admin.operations.providers.store'));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Incorporar Socio Comercial
        </template>

        <ProviderForm 
            :form="form"
            :is-edit="false"
            @submit="submit"
        />
    </AdminLayout>
</template>