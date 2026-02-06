<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import ShopLayout from '@/Layouts/ShopLayout.vue'; // CAMBIADO
    import UserAddressesMap from '@/Components/Maps/UserAddressesMap.vue';
    import { router } from '@inertiajs/vue3';
    import { MapPin, Plus, Edit2, Trash2, Star } from 'lucide-vue-next'; // <--- Agrega Star

    // Función para la acción
    const setAsDefault = (id) => {
        router.patch(route('addresses.set-default', id));
    };
    const props = defineProps({
        addresses: { type: Array, default: () => [] }
    });
</script>
    
<template>
    <Head title="Mis Direcciones" />
    <ShopLayout :is-profile-section="true">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-black text-gray-800 tracking-tight">Mis Direcciones</h1>
            
            <Link 
                v-if="addresses.length < 3" 
                :href="route('addresses.create')" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase flex items-center gap-2 hover:bg-blue-500 transition shadow-md"
            >
                <Plus :size="16" /> Nueva
            </Link>
            
            <span v-else class="text-xs text-amber-600 font-bold bg-amber-50 px-3 py-1 rounded-full border border-amber-100">
                Máximo de direcciones alcanzado (3/3)
            </span>
        </div>

        <div v-if="addresses.length > 0" class="mb-8">
            <UserAddressesMap :addresses="addresses" />
        </div>

        <div v-if="addresses.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"><MapPin class="text-gray-400" :size="32" /></div>
            <h3 class="font-bold text-gray-900 mb-1">No tienes direcciones guardadas</h3>
            <p class="text-xs text-gray-500 max-w-xs mx-auto mb-6">Agrega tu casa u oficina para agilizar tus envíos.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="addr in addresses" :key="addr.id" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:border-blue-300 transition group relative">
                <div class="flex items-start gap-3">
                    <div class="mt-1 p-2 rounded-lg" :class="addr.branch_id ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600'">
                        <MapPin :size="20" />
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-gray-800">{{ addr.alias }}</h4>
                            
                            <div class="flex gap-2 items-center">
                                <button 
                                    v-if="!addr.is_default" 
                                    @click="setAsDefault(addr.id)"
                                    title="Marcar como principal"
                                    class="text-gray-300 hover:text-yellow-500 transition"
                                >
                                    <Star :size="18" />
                                </button>
                                
                                <div v-else class="text-yellow-500">
                                    <Star :size="18" fill="currentColor" />
                                </div>

                                <div class="w-px h-4 bg-gray-200 mx-1"></div> <Link :href="route('addresses.edit', addr.id)" class="text-gray-400 hover:text-blue-600 transition"><Edit2 :size="16" /></Link>
                                <Link :href="route('addresses.destroy', addr.id)" method="delete" as="button" class="text-gray-400 hover:text-red-600 transition"><Trash2 :size="16" /></Link>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 leading-snug mt-1 line-clamp-2">{{ addr.address }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span v-if="!addr.branch_id" class="text-[10px] bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded font-bold">Sin Cobertura</span>
                            <span v-if="addr.is_default" class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-bold">Principal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </ShopLayout>
</template>