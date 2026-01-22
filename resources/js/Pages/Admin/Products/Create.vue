<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { computed } from 'vue';
    
    const props = defineProps({
        brands: Array,
        categories: Array
    });
    
    const form = useForm({
        // Datos del Padre (Producto)
        name: '',
        brand_id: '',
        category_id: '',
        description: '',
        image: null,
        is_active: true,
        is_alcoholic: false,
        
        // Datos de los Hijos (SKUs) - Mínimo 1
        skus: [
            { name: '', code: '', price: 0, conversion_factor: 1, weight: 0 }
        ]
    });
    
    // Helper: Mostrar info del proveedor al elegir marca
    const selectedBrandInfo = computed(() => {
        if (!form.brand_id) return null;
        return props.brands.find(b => b.id === form.brand_id);
    });
    
    // Gestión Dinámica de Filas SKU
    const addSku = () => {
        form.skus.push({ name: '', code: '', price: 0, conversion_factor: 1, weight: 0 });
    };
    
    const removeSku = (index) => {
        if (form.skus.length > 1) form.skus.splice(index, 1);
    };
    
    // Copiar nombre padre a SKU (UX helper)
    const copyNameToSku = () => {
        if(form.name && form.skus[0].name === '') {
            form.skus[0].name = form.name;
        }
    }

    const submit = () => {
        form.post(route('admin.products.store'), { forceFormData: true });
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-6xl mx-auto">
            
            <div class="mb-6 flex justify-between items-end">
                <div>
                    <h1 class="text-2xl font-bold text-white">Alta de Producto Maestro</h1>
                    <p class="text-gray-400 text-sm">Define el producto base y sus presentaciones de venta.</p>
                </div>
            </div>

            <div v-if="Object.keys(form.errors).length > 0" class="mb-6 bg-red-900/30 border-l-4 border-red-500 text-red-200 p-4 rounded">
                <p class="font-bold mb-1">⛔ Error de Validación:</p>
                <ul class="list-disc list-inside text-xs">
                    <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                    <h2 class="text-blue-400 font-bold text-sm uppercase mb-4 border-b border-gray-700 pb-2 flex items-center gap-2">
                        <span>1. Definición del Producto</span>
                        <span class="text-gray-500 text-[10px] normal-case">(Concepto General)</span>
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        
                        <div class="md:col-span-3">
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Imagen Principal</label>
                            <div class="border-2 border-dashed border-gray-600 rounded-lg p-4 text-center hover:bg-gray-750 transition cursor-pointer relative h-40 flex flex-col items-center justify-center">
                                <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"/>
                                <span v-if="!form.image" class="text-4xl text-gray-600 mb-2">📷</span>
                                <span v-if="!form.image" class="text-xs text-gray-400">Clic para subir</span>
                                <span v-else class="text-green-400 text-xs font-bold">Imagen Seleccionada</span>
                            </div>
                        </div>

                        <div class="md:col-span-9 grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <div class="md:col-span-2">
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nombre del Producto *</label>
                                <input v-model="form.name" @blur="copyNameToSku" type="text" placeholder="Ej: Ron Abuelo 12 Años" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 focus:border-blue-500 outline-none text-lg font-bold">
                            </div>

                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Marca *</label>
                                <select v-model="form.brand_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 focus:border-blue-500 outline-none">
                                    <option value="" disabled>-- Selecciona --</option>
                                    <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                                <p v-if="selectedBrandInfo" class="text-[10px] text-gray-500 mt-1">
                                    Proveedor: <span class="text-blue-300">{{ selectedBrandInfo.provider_id ? 'Asignado' : 'Sin Proveedor' }}</span>
                                </p>
                            </div>

                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Categoría *</label>
                                <select v-model="form.category_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 focus:border-blue-500 outline-none">
                                    <option value="" disabled>-- Selecciona --</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Descripción (Marketing)</label>
                                <textarea v-model="form.description" rows="2" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 focus:border-blue-500 outline-none text-sm"></textarea>
                            </div>

                            <div class="flex items-center gap-4 mt-2">
                                <label class="flex items-center space-x-2 cursor-pointer bg-gray-900 p-2 rounded border border-gray-700">
                                    <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded bg-gray-700 border-gray-500 text-blue-600">
                                    <span class="text-gray-300 text-xs font-bold">Activo</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer bg-gray-900 p-2 rounded border border-gray-700">
                                    <input v-model="form.is_alcoholic" type="checkbox" class="w-4 h-4 rounded bg-gray-700 border-gray-500 text-yellow-500">
                                    <span class="text-gray-300 text-xs font-bold">Bebida Alcohólica (+18)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-lg overflow-hidden">
                    <div class="p-4 bg-gray-900 border-b border-gray-700 flex justify-between items-center">
                        <h2 class="text-green-400 font-bold text-sm uppercase flex items-center gap-2">
                            <span>2. Presentaciones de Venta (SKUs)</span>
                            <span class="bg-gray-700 text-white px-2 rounded-full text-xs">{{ form.skus.length }}</span>
                        </h2>
                        <button type="button" @click="addSku" class="bg-green-700 hover:bg-green-600 text-white text-xs px-4 py-2 rounded font-bold transition flex items-center gap-1">
                            <span>+</span> Agregar Variante
                        </button>
                    </div>

                    <div class="p-4 space-y-3">
                        <div class="grid grid-cols-12 gap-4 text-[10px] uppercase font-bold text-gray-500 px-2">
                            <div class="col-span-4">Nombre Presentación</div>
                            <div class="col-span-2">Código (EAN)</div>
                            <div class="col-span-2 text-center">Unidades (Factor)</div>
                            <div class="col-span-2 text-center">Peso (Kg)</div>
                            <div class="col-span-2 text-right">Precio Base (Bs)</div>
                        </div>

                        <div v-for="(sku, index) in form.skus" :key="index" class="relative group">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center bg-gray-750 p-3 rounded border border-gray-600 hover:border-green-500 transition">
                                
                                <button v-if="form.skus.length > 1" type="button" @click="removeSku(index)" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow hover:bg-red-500 z-10">✕</button>

                                <div class="md:col-span-4">
                                    <input v-model="sku.name" type="text" placeholder="Ej: Botella 750ml" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-sm focus:border-green-500 outline-none">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <input v-model="sku.code" type="text" placeholder="EAN-13" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-sm font-mono focus:border-green-500 outline-none">
                                </div>

                                <div class="md:col-span-2">
                                    <input v-model.number="sku.conversion_factor" type="number" min="1" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-sm text-center focus:border-green-500 outline-none" title="Unidades que contiene este SKU (Ej: Caja=6)">
                                </div>

                                <div class="md:col-span-2">
                                    <input v-model.number="sku.weight" type="number" step="0.01" min="0" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-sm text-center focus:border-green-500 outline-none">
                                </div>

                                <div class="md:col-span-2">
                                    <input v-model.number="sku.price" type="number" step="0.01" min="0" class="w-full bg-gray-900 border border-gray-600 text-yellow-400 font-bold rounded p-2 text-sm text-right focus:border-yellow-500 outline-none">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-900 p-3 text-center">
                        <p class="text-xs text-gray-500 italic">Define al menos una presentación (Unidad Básica).</p>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-gray-700">
                    <Link :href="route('admin.products.index')" class="px-6 py-3 text-gray-400 font-bold hover:text-white transition">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-10 rounded shadow-lg transition disabled:opacity-50">
                        <span v-if="form.processing">Guardando...</span>
                        <span v-else>Guardar Catálogo</span>
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>