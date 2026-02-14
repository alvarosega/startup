<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import PersonalInfoSection from '@/Components/Profile/PersonalInfoSection.vue';
import AddressesSection from '@/Components/Profile/AddressesSection.vue';
import SecuritySection from '@/Components/Profile/SecuritySection.vue';

// 1. Recibimos 'addresses' como prop independiente (Correcto)
const props = defineProps({
    user: { type: Object, default: () => ({}) },
    addresses: { type: Array, default: () => [] }, // <--- AQUÍ ESTÁN TUS DATOS
    activeBranches: { type: Array, default: () => [] }, 
});

const safeUser = computed(() => {
    // safeUser ya NO tiene direcciones dentro, solo datos personales
    return props.user || {};
});
</script>

<template>
    <Head title="Mi Perfil" />

    <ShopLayout :is-profile-section="true">
        <div class="space-y-6 max-w-4xl mx-auto pb-20">
            
            <div class="mb-4 px-2">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Mi Cuenta</h1>
                <p class="text-sm text-gray-500">Gestiona tus datos personales y ubicaciones.</p>
            </div>

            <PersonalInfoSection :user="safeUser" />

            <AddressesSection 
                :addresses="addresses" 
                :activeBranches="activeBranches" 
            />

            <SecuritySection />

        </div>
    </ShopLayout>
</template>