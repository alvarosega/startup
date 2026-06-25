<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BranchForm from '@/Pages/Admin/Operations/Branches/Partials/BranchForm.vue';

// Instanciación del formulario nativo con el contrato exacto del StoreBranchRequest
const form = useForm({
    name: '',
    city: 'La Paz',
    phone: '',
    address: '',
    latitude: null,
    longitude: null,
    coverage_polygon: [],
    is_active: true,
    is_default: false,
    delivery_base_fee: 0.00,
    delivery_price_per_km: 0.00,
    surge_multiplier: 1.00,
    min_order_amount: 0.00,
    small_order_fee: 0.00,
    base_service_fee_percentage: 0.00
});

/**
 * Ejecuta la inserción del nuevo nodo logístico aplicando el interceptor de cierre de anillo GIS.
 */
const submit = () => {
    // Interceptor: Cierre automático topológico exigido por MySQL GIS [longitud, latitud]
    form.transform((data) => {
        let polygon = [...data.coverage_polygon];
        if (polygon.length >= 3) {
            const first = polygon[0];
            const last = polygon[polygon.length - 1];
            if (first[0] !== last[0] || first[1] !== last[1]) {
                polygon.push([first[0], first[1]]);
            }
        }
        return {
            ...data,
            coverage_polygon: polygon
        };
    });

    form.post(route('admin.operations.branches.store'));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Materializar Nodo de Sucursal
        </template>

        <BranchForm 
            :form="form" 
            :is-edit="false" 
            @submit="submit" 
        />
    </AdminLayout>
</template>