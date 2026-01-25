<script setup>
    import { Head, useForm, Link } from '@inertiajs/vue3';
    import ShopLayout from '@/Layouts/ShopLayout.vue'; // CAMBIADO
    import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
    import { ArrowLeft, Save } from 'lucide-vue-next';
    
    const props = defineProps({ activeBranches: { type: Array, required: true } });
    
    const form = useForm({
        alias: '', address: '', details: '',
        latitude: -16.5000, longitude: -68.1500, branch_id: null 
    });
    
    const submit = () => form.post(route('addresses.store'));
</script>
    
<template>
    <Head title="Nueva Dirección" />
    <ShopLayout :is-profile-section="true">
        <div class="flex items-center gap-4 mb-6">
            <Link :href="route('addresses.index')" class="p-2 hover:bg-gray-100 rounded-full transition text-gray-600"><ArrowLeft :size="20" /></Link>
            <h1 class="text-xl font-black text-gray-800 tracking-tight">Agregar Nueva Dirección</h1>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <form @submit.prevent="submit" class="p-6 md:p-8 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">1. Ubica tu dirección</label>
                    <div class="rounded-lg overflow-hidden border border-gray-300 h-[300px]">
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
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nombre (Alias)</label>
                        <input v-model="form.alias" type="text" placeholder="Ej: Casa" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <div v-if="form.errors.alias" class="text-red-500 text-xs mt-1">{{ form.errors.alias }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Detalles</label>
                        <input v-model="form.details" type="text" placeholder="Ej: Portón rojo" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Dirección Detectada</label>
                    <input v-model="form.address" type="text" class="w-full bg-gray-50 border-gray-300 rounded-lg text-gray-600 text-sm cursor-not-allowed" readonly>
                </div>
                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-500/30 transition flex items-center gap-2">
                        <Save :size="18" /> Guardar
                    </button>
                </div>
            </form>
        </div>
    </ShopLayout>
</template>