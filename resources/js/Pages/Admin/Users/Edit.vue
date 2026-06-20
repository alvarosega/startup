<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import CustomerForm from './Partials/CustomerForm.vue';

defineProps({ 
    user: Object, // Trae el UserEditResource
    branches: Array 
});
</script>

<template>
    <Head title="Editar Cliente" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.users.index')" class="p-2 hover:bg-zinc-100 rounded-xl transition-all"><ArrowLeft :size="20"/></Link>
                <h1 class="text-2xl font-black italic uppercase tracking-tighter">Modificar Parámetros de Socio</h1>
            </div>
        </template>
        <div class="py-8"><CustomerForm :user="user.data" :branches="branches" /></div>
    </AdminLayout>
</template>