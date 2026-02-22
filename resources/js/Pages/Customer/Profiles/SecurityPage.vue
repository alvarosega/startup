<script setup>
import { Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import AddressManager from './Partials/AddressManager.vue'; // Opcional: mover la lógica a un partial

const props = defineProps({
    addresses: Array,
    activeBranches: Array
});
</script>

<template>
    <Head title="Mis Direcciones - Electric Luxury" />

    <ShopLayout :is-profile-section="true">
        <div class="max-w-4xl mx-auto pb-20 px-4">
            <div class="mb-8">
                <h1 class="text-3xl font-black text-navy tracking-tighter uppercase italic">Mis Direcciones</h1>
                <p class="text-sm text-muted-foreground uppercase tracking-widest font-bold">Gestión de puntos de entrega</p>
            </div>

            <AddressManager :addresses="addresses" :activeBranches="activeBranches" />
        </div>
    </ShopLayout>
</template>