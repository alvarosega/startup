<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { onMounted } from 'vue';
    
    const props = defineProps({
        product: Object, // Incluye .skus y .prices
        brands: Array,
        categories: Array
    });
    
    // Función para formatear los SKUs que vienen del backend al formato del formulario
    const mapSkusToForm = (skus) => {
        return skus.map(sku => ({
            id: sku.id, // Importante para saber que es una actualización
            name: sku.name,
            code: sku.code || '',
            conversion_factor: parseFloat(sku.conversion_factor),
            weight: parseFloat(sku.weight),
            // Extraer el precio vigente (asumiendo que viene ordenado o es el único)
            price: sku.prices && sku.prices.length > 0 ? parseFloat(sku.prices[0].final_price) : 0
        }));
    };

    const form = useForm({
        _method: 'PUT',
        name: props.product.name,
        brand_id: props.product.brand_id,
        category_id: props.product.category_id,
        description: props.product.description || '',
        image: null,
        is_active: !!props.product.is_active,
        is_alcoholic: !!props.product.is_alcoholic,
        
        // Inicializamos con los SKUs existentes mapeados
        skus: mapSkusToForm(props.product.skus || [])
    });
    
    const addSku = () => {
        form.skus.push({ 
            id: null, // Null indica que es nuevo
            name: '', 
            code: '', 
            price: 0, 
            conversion_factor: 1, 
            weight: 0 
        });
    };
    
    const removeSku = (index) => {
        if (confirm('¿Eliminar esta variante? Se borrará al guardar.')) {
            form.skus.splice(index, 1);
        }
    };

    const submit = () => {
        form.post(route('admin.products.update', props.product.id), { 
            forceFormData: true,
            preserveScroll: true
        });
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-6xl mx-auto">
            
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-white">Editar Producto: {{ product.name }}</h1>
                <div class="flex gap-2">
                    <span v-if="product.is_active" class="bg-green-900 text-green-300 px-3 py-1 rounded text-xs font-bold border border-green-700">ACTIVO</span>
                    <span v-else class="bg-red-900 text-red-300 px-3 py-1 rounded text-xs font-bold border border-red-700">INACTIVO</span>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                        <h2 class="text-blue-400 font-bold text-sm uppercase mb-4 border-b border-gray-700 pb-2">Datos Principales</h2>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nombre del Producto</label>
                                <input v-model="form.name" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 outline-none font-bold focus:border-blue-500">
                                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Marca</label>
                                    <select v-model="form.brand_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 outline-none focus:border-blue-500">
                                        <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Categoría</label>
                                    <select v-model="form.category_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 outline-none focus:border-blue-500">
                                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Descripción</label>
                                <textarea v-model="form.description" rows="3" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 outline-none focus:border-blue-500"></textarea>
                            </div>

                            <div class="flex items-center gap-4 bg-gray-900 p-3 rounded border border-gray-600">
                                <div class="w-16 h-16 bg-white rounded flex items-center justify-center overflow-hidden shrink-0">
                                    <img v-if="product.image_url" :src="product.image_url" class="max-w-full max-h-full object-contain">
                                    <span v-else class="text-xs text-black font-bold">Sin Foto</span>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-gray-400 text-[10px] uppercase font-bold mb-1">Cambiar Imagen</label>
                                    <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="block w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:bg-gray-800 file:text-white cursor-pointer border border-gray-700"/>
                                </div>
                            </div>
                            
                            <div class="flex gap-4 pt-2">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input v-model="form.is_active" type="checkbox" class="rounded bg-gray-700 border-gray-500 text-blue-600">
                                    <span class="text-gray-300 text-sm">Activo</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input v-model="form.is_alcoholic" type="checkbox" class="rounded bg-gray-700 border-gray-500 text-yellow-500">
                                    <span class="text-gray-300 text-sm">Alcohol (+18)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                        <div class="flex justify-between items-center mb-4 border-b border-gray-700 pb-2">
                            <h2 class="text-green-400 font-bold text-sm uppercase">Variantes (SKUs)</h2>
                            <button type="button" @click="addSku" class="text-xs bg-green-700 hover:bg-green-600 text-white px-2 py-1 rounded transition">
                                + Agregar
                            </button>
                        </div>

                        <div class="space-y-4 max-h-[600px] overflow-y-auto custom-scrollbar pr-1">
                            <div v-for="(sku, index) in form.skus" :key="index" class="bg-gray-900 p-3 rounded border border-gray-600 relative group">
                                
                                <button type="button" @click="removeSku(index)" class="absolute top-2 right-2 text-gray-600 hover:text-red-500 font-bold text-xs" title="Eliminar variante">✕</button>
                                
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-gray-500 text-[10px] uppercase font-bold mb-1">Nombre Presentación</label>
                                        <input v-model="sku.name" type="text" class="w-full bg-gray-800 border border-gray-600 text-white rounded p-2 text-sm focus:border-green-500 outline-none">
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-gray-500 text-[10px] uppercase font-bold mb-1">Código</label>
                                            <input v-model="sku.code" type="text" class="w-full bg-gray-800 border border-gray-600 text-white rounded p-2 text-xs font-mono focus:border-green-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-gray-500 text-[10px] uppercase font-bold mb-1">Precio (Bs)</label>
                                            <input v-model="sku.price" type="number" step="0.01" class="w-full bg-gray-800 border border-gray-600 text-yellow-400 font-bold rounded p-2 text-sm text-right focus:border-yellow-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-gray-500 text-[10px] uppercase font-bold mb-1">Factor (Unid)</label>
                                            <input v-model="sku.conversion_factor" type="number" class="w-full bg-gray-800 border border-gray-600 text-white rounded p-2 text-xs text-center focus:border-green-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-gray-500 text-[10px] uppercase font-bold mb-1">Peso (Kg)</label>
                                            <input v-model="sku.weight" type="number" step="0.01" class="w-full bg-gray-800 border border-gray-600 text-white rounded p-2 text-xs text-center focus:border-green-500 outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="form.errors.skus" class="mt-2 text-red-500 text-xs">{{ form.errors.skus }}</div>
                    </div>
                </div>

                <div class="lg:col-span-3 flex justify-end gap-4 border-t border-gray-700 pt-6">
                    <Link :href="route('admin.products.index')" class="px-6 py-3 text-gray-400 font-bold hover:text-white transition">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-10 rounded shadow-lg transition disabled:opacity-50">
                        <span v-if="form.processing">Guardando...</span>
                        <span v-else>Actualizar Todo</span>
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }
</style>