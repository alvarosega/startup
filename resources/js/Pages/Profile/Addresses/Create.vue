<script setup>
    import { Head, useForm, Link } from '@inertiajs/vue3';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
    import { ArrowLeft, Save } from 'lucide-vue-next';
    
    const props = defineProps({
        activeBranches: { type: Array, required: true }
    });
    
    const form = useForm({
        alias: '',
        address: '', // Se llenará desde el mapa
        details: '',
        latitude: -16.5000, // Default La Paz (o tu ciudad base)
        longitude: -68.1500,
        branch_id: null 
    });
    
    const submit = () => {
        form.post(route('addresses.store'));
    };
    </script>
    
    <template>
        <Head title="Nueva Dirección" />
    
        <ProfileLayout>
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('addresses.index')" class="p-2 hover:bg-gray-100 rounded-full transition text-gray-600">
                    <ArrowLeft :size="20" />
                </Link>
                <h1 class="text-xl font-black text-gray-800 tracking-tight">Agregar Nueva Dirección</h1>
            </div>
    
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <form @submit.prevent="submit" class="p-6 md:p-8 space-y-6">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            1. Ubica tu dirección en el mapa
                        </label>
                        <div class="rounded-lg overflow-hidden border border-gray-300">
                            <ClientLocationPicker
                                v-model:modelValueLat="form.latitude"
                                v-model:modelValueLng="form.longitude"
                                v-model:modelValueAddress="form.address"
                                v-model:modelValueBranchId="form.branch_id"  :activeBranches="props.activeBranches"
                            />
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            * El sistema detectará automáticamente si tenemos cobertura.
                        </p>
                    </div>
    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nombre de la ubicación (Alias)</label>
                            <input 
                                v-model="form.alias" 
                                type="text" 
                                placeholder="Ej: Casa, Oficina, Novia..." 
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                            >
                            <div v-if="form.errors.alias" class="text-red-500 text-xs mt-1">{{ form.errors.alias }}</div>
                        </div>
    
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Referencia / Detalles</label>
                            <input 
                                v-model="form.details" 
                                type="text" 
                                placeholder="Ej: Apto 4B, timbre roto, portón negro" 
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                            >
                            <div v-if="form.errors.details" class="text-red-500 text-xs mt-1">{{ form.errors.details }}</div>
                        </div>
                    </div>
    
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Dirección Detectada</label>
                        <input 
                            v-model="form.address" 
                            type="text" 
                            class="w-full bg-gray-50 border-gray-300 rounded-lg text-gray-600 text-sm cursor-not-allowed"
                            readonly
                        >
                        <div v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</div>
                    </div>
    
                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-500/30 transition flex items-center gap-2"
                            :class="{ 'opacity-75 cursor-wait': form.processing }"
                        >
                            <Save :size="18" />
                            Guardar Dirección
                        </button>
                    </div>
    
                </form>
            </div>
        </ProfileLayout>
    </template>ffffffffffff