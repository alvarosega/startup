<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
// CAMBIO 1: Importamos ShopLayout
import ShopLayout from '@/Layouts/ShopLayout.vue'; 
import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
import { ArrowLeft, Save } from 'lucide-vue-next';

const props = defineProps({
    address: Object,
    activeBranches: Array
});

const form = useForm({
    alias: props.address.alias,
    address: props.address.address,
    details: props.address.details || '',
    latitude: parseFloat(props.address.latitude),
    longitude: parseFloat(props.address.longitude),
    branch_id: props.address.branch_id,
    is_default: props.address.is_default // Importante mantener esto
});

const submit = () => {
    form.put(route('addresses.update', props.address.id));
};
</script>

<template>
    <Head title="Editar Dirección" />

    <ShopLayout :isProfileSection="true">
        <div class="flex items-center gap-4 mb-6">
            <Link :href="route('addresses.index')" class="p-2 hover:bg-gray-100 rounded-full transition text-gray-600">
                <ArrowLeft :size="20" />
            </Link>
            <h1 class="text-xl font-black text-gray-800 tracking-tight">Editar Dirección</h1>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <form @submit.prevent="submit" class="p-6 md:p-8 space-y-6">
                
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 text-sm text-blue-700">
                    <p><strong>Nota:</strong> Al editar, el sistema guardará una nueva versión de tu dirección para mantener el historial de tus pedidos anteriores.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Ubicación</label>
                    <div class="rounded-lg overflow-hidden border border-gray-300">
                        <ClientLocationPicker
                            v-model:modelValueLat="form.latitude"
                            v-model:modelValueLng="form.longitude"
                            v-model:modelValueAddress="form.address"
                            v-model:modelValueBranchId="form.branch_id"
                            :activeBranches="props.activeBranches"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Alias</label>
                        <input v-model="form.alias" type="text" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <div v-if="form.errors.alias" class="text-red-500 text-xs mt-1">{{ form.errors.alias }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Detalles / Referencia</label>
                        <input v-model="form.details" type="text" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <div v-if="form.errors.details" class="text-red-500 text-xs mt-1">{{ form.errors.details }}</div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg transition flex items-center gap-2">
                        <Save :size="18" />
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </ShopLayout>
</template>