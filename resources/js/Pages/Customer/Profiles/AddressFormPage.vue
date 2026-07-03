<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import LocationWorkflow from '@/Components/Customer/Maps/LocationWorkflow.vue';

const props = defineProps({
    address: { type: Object, default: null }, 
    activeBranches: { type: Array, required: true },
    collidingBranches: { type: Array, default: () => [] }
});

const isEditing = !!props.address;

// Alineación estricta de las llaves del useForm con el payload de AddressRequest
const form = useForm({
    alias: props.address?.alias || '', 
    address: props.address?.address || '', 
    reference: props.address?.reference || '', // Sincronizado bajo el campo formal reference
    latitude: props.address?.latitude ? parseFloat(props.address.latitude) : -16.49969, 
    longitude: props.address?.longitude ? parseFloat(props.address.longitude) : -68.12154, 
    branch_id: props.address?.branch_id || null, 
});

const submitAddress = () => {
    if (isEditing) {
        form.put(route('customer.profile.addresses.update', { id: props.address.id }));
    } else {
        form.post(route('customer.profile.addresses.store'));
    }
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Dirección' : 'Nueva Dirección'" />
    
    <ShopLayout :is-profile-section="true">
        <div class="max-w-3xl mx-auto h-[calc(100svh-80px)] flex flex-col py-6 px-4">
            
            <div class="mb-6 shrink-0">
                <h1 class="text-3xl font-bold text-zinc-900 tracking-tight italic uppercase">
                    {{ isEditing ? 'Editar Ubicación' : 'Nueva Ubicación' }}
                </h1>
                <p class="text-zinc-500 text-sm mt-1">Configura tu punto de despacho logístico.</p>
            </div>

            <!-- Inyección del Orquestador Logístico Unificado -->
            <div class="flex-1 bg-white border border-zinc-200 rounded-[2.5rem] overflow-hidden shadow-sm flex flex-col relative">
                <LocationWorkflow 
                    :form="form"
                    :activeBranches="activeBranches"
                    :collidingBranches="collidingBranches"
                    :submitLabel="isEditing ? 'Actualizar Dirección' : 'Guardar Dirección'"
                    @next="submitAddress"
                    @back="goBack"
                />
            </div>

        </div>
    </ShopLayout>
</template>