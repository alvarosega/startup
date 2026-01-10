<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import debounce from 'lodash/debounce';

    const props = defineProps({ brands: Array });
    const search = ref('');
    const filteredBrands = ref(props.brands);

    // Búsqueda instantánea en cliente
    watch(search, debounce((val) => {
        const lower = val.toLowerCase();
        filteredBrands.value = props.brands.filter(b => 
            b.name.toLowerCase().includes(lower) || 
            (b.provider && b.provider.commercial_name.toLowerCase().includes(lower))
        );
    }, 300));
    
    const deleteItem = (brand) => {
        if(confirm(`¿Eliminar la marca ${brand.name}?`)) {
            router.delete(route('admin.brands.destroy', brand.id));
        }
    };
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Marcas Comerciales</h1>
                <p class="text-gray-400 text-sm">Portafolio y Distribución Oficial</p>
            </div>
            
            <div class="flex gap-4 w-full md:w-auto">
                <input v-model="search" type="text" placeholder="Buscar Marca..." class="bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded-lg focus:border-blue-500 outline-none w-full md:w-64">
                <Link :href="route('admin.brands.create')" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition whitespace-nowrap">
                    + Nueva Marca
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="brand in filteredBrands" :key="brand.id" class="bg-gray-800 border border-gray-700 rounded-lg p-5 flex items-start space-x-4 hover:bg-gray-750 transition shadow-lg group">
                
                <div class="w-16 h-16 bg-white rounded-lg flex-shrink-0 flex items-center justify-center p-1 overflow-hidden border-2 border-transparent group-hover:border-blue-500 transition">
                    <img v-if="brand.image_url" :src="brand.image_url" :alt="brand.name" class="max-w-full max-h-full object-contain">
                    <span v-else class="text-gray-400 text-xs font-bold">S/I</span>
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <h3 class="text-lg font-bold text-white truncate">{{ brand.name }}</h3>
                        <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
                             <Link :href="route('admin.brands.edit', brand.id)" class="text-blue-400 hover:text-white font-bold text-xs uppercase">Editar</Link>
                             <button @click="deleteItem(brand)" class="text-red-500 hover:text-white font-bold text-xs uppercase">X</button>
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wide font-bold">Distribuidor</p>
                    <p class="text-sm text-blue-300 truncate">
                        {{ brand.provider ? brand.provider.commercial_name : '--- Huérfana ---' }}
                    </p>

                    <div class="mt-3 flex items-center gap-2 flex-wrap">
                        <span v-if="brand.origin_country_code" class="px-1.5 py-0.5 rounded bg-gray-700 text-gray-300 text-[10px] font-mono border border-gray-600">
                            {{ brand.origin_country_code }}
                        </span>
                        <span class="px-1.5 py-0.5 rounded bg-purple-900/40 text-purple-300 text-[10px] border border-purple-800">
                            {{ brand.tier }}
                        </span>
                        <span v-if="!brand.is_active" class="px-1.5 py-0.5 rounded bg-red-900/40 text-red-300 text-[10px] border border-red-800">
                            INACTIVA
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div v-if="filteredBrands.length === 0" class="text-center py-12 text-gray-500">
            No se encontraron marcas que coincidan.
        </div>
    </AdminLayout>
</template>