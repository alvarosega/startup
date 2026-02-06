<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';

// CORRECCIÓN: Cambiamos 'profile' por 'Profile' (P mayúscula)
// Vite requiere que coincida exactamente con el nombre de la carpeta en tu disco.
import PersonalInfoSection from '@/Components/Profile/PersonalInfoSection.vue';
import AddressesSection from '@/Components/Profile/AddressesSection.vue';
import SecuritySection from '@/Components/Profile/SecuritySection.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
    activeBranches: { type: Array, default: () => [] }, 
});

const safeUser = computed(() => {
    return props.user || { addresses: [], profile: {} };
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
                :addresses="safeUser.addresses || []" 
                :activeBranches="activeBranches" 
            />

            <SecuritySection />

        </div>
    </ShopLayout>
</template>