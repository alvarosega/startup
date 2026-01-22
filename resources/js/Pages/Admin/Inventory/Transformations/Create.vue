<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { computed } from 'vue';
    
    const props = defineProps({
        branches: Array,
        inventory_sources: Array, 
        all_skus: Array
    });
    
    const form = useForm({
        branch_id: '',
        source_sku_id: '',
        destination_sku_id: '',
        quantity: 1, 
        notes: ''
    });
    
    // 1. Filtrar Origen: Solo lo que hay en la sucursal seleccionada
    const filteredSources = computed(() => {
        if (!form.branch_id) return [];
        return props.inventory_sources.filter(item => item.branch_id === form.branch_id);
    });

    // 2. Filtrar Destino: Debe ser del mismo producto PERO más pequeño
    // Ej: Si Origen es Bipack (2), Destino puede ser Unidad (1).
    const availableDestinations = computed(() => {
        if (!form.source_sku_id) return [];
        
        const source = filteredSources.value.find(s => s.id === form.source_sku_id);
        if (!source) return [];
        
        return props.all_skus.filter(s => 
            s.product_id === source.product_id && 
            parseFloat(s.conversion_factor) < parseFloat(source.factor) // <--- LA CLAVE
        );
    });
    
    const maxStock = computed(() => {
        const source = filteredSources.value.find(s => s.id === form.source_sku_id);
        return source ? parseFloat(source.stock_real) : 0;
    });

    // Cálculo dinámico de conversión
    const conversionMath = computed(() => {
        if (!form.source_sku_id || !form.destination_sku_id) return null;

        const source = filteredSources.value.find(s => s.id === form.source_sku_id);
        const dest = props.all_skus.find(s => s.id === form.destination_sku_id);
        
        if (!source || !dest) return null;

        // Ej: Bipack(2) -> Unidad(1). Ratio = 2/1 = 2.
        const ratio = parseFloat(source.factor) / parseFloat(dest.conversion_factor);
        const total = (form.quantity * ratio);
        
        return { ratio, total, destName: dest.name };
    });

    const submit = () => {
        if (!conversionMath.value) return;
        if(confirm(`Transformar ${form.quantity} packs en ${conversionMath.value.total} unidades?`)) {
            form.post(route('admin.transformations.store'));
        }
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-white mb-6">Desagregación de Stock</h1>
            
            <div v-if="$page.props.errors.error" class="bg-red-500 text-white p-4 rounded mb-4">
                ⚠ {{ $page.props.errors.error }}
            </div>

            <form @submit.prevent="submit" class="bg-gray-800 p-8 rounded-lg border border-gray-700 shadow-xl">
                
                <div class="mb-6">
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">1. ¿Dónde y Qué vas a abrir?</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select v-model="form.branch_id" @change="form.source_sku_id = ''" class="bg-gray-900 border border-gray-600 text-white rounded p-3 outline-none focus:border-blue-500">
                            <option value="" disabled>Seleccionar Sucursal...</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>

                        <select v-model="form.source_sku_id" :disabled="!form.branch_id" class="bg-gray-900 border border-gray-600 text-white rounded p-3 outline-none focus:border-blue-500 disabled:opacity-50">
                            <option value="" disabled>Seleccionar Pack Disponible...</option>
                            <option v-for="s in filteredSources" :key="s.id" :value="s.id">
                                {{ s.product_name }} - {{ s.sku_name }} (Stock: {{ s.stock_real }})
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="form.source_sku_id" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center bg-gray-900/50 p-6 rounded border border-gray-700">
                    
                    <div class="text-center">
                        <label class="block text-gray-400 text-[10px] uppercase font-bold mb-2">Cantidad a Abrir</label>
                        <input v-model.number="form.quantity" type="number" min="1" :max="maxStock" class="w-full bg-gray-800 border border-red-500 text-white text-center font-bold text-2xl rounded p-2 outline-none">
                        <p class="text-[10px] text-gray-500 mt-1">Máx: {{ maxStock }}</p>
                    </div>

                    <div class="text-center text-gray-500">
                        <div class="text-2xl">➔ se convierten en ➔</div>
                    </div>

                    <div>
                        <label class="block text-gray-400 text-[10px] uppercase font-bold mb-2">Convertir a</label>
                        <select v-model="form.destination_sku_id" class="w-full bg-gray-800 border border-green-500 text-white rounded p-2 outline-none">
                            <option value="" disabled>Elegir Unidad...</option>
                            <option v-for="s in availableDestinations" :key="s.id" :value="s.id">
                                {{ s.name }} (x{{ parseInt(s.conversion_factor) }})
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="conversionMath" class="mt-6 p-4 bg-green-900/20 border border-green-800 rounded text-center animate-pulse">
                    <p class="text-green-400 font-bold text-lg">
                        ✅ Resultado: {{ form.quantity }} Packs generarán <span class="text-2xl text-white">{{ conversionMath.total }}</span> {{ conversionMath.destName }}
                    </p>
                    <p class="text-xs text-green-300/60 mt-1">El stock se actualizará y el costo se promediará automáticamente.</p>
                </div>

                <div class="flex justify-end gap-4 mt-6 pt-6 border-t border-gray-700">
                    <Link :href="route('admin.transformations.index')" class="px-6 py-3 text-gray-400 font-bold hover:text-white transition">Cancelar</Link>
                    <button type="submit" :disabled="form.processing || !conversionMath" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-8 rounded shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Confirmar Transformación
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>