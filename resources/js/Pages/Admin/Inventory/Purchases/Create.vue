<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { computed } from 'vue';
    
    const props = defineProps({
        branches: Array,
        providers: Array,
        skus: Array
    });
    
    const form = useForm({
        branch_id: '',
        provider_id: '',
        document_number: '',
        purchase_date: new Date().toISOString().split('T')[0],
        notes: '',
        items: [ createEmptyItem() ]
    });
    
    function createEmptyItem() {
        return { 
            sku_id: '',             // El SKU real que vamos a guardar (Caja o Botella)
            
            // Inputs del usuario
            quantity_input: 1,      // Cantidad física (Ej: 10 Cajas)
            total_cost_input: 0,    // Costo total de la línea (Ej: 100 Bs)
            
            // Datos informativos
            factor: 1,
            sku_name: '',
            
            // Datos finales para Backend (Calculados)
            quantity: 1,            // Será igual al input
            unit_cost: 0,           // Costo por unidad de ese SKU (Costo Caja)
            expiration_date: null 
        };
    }
    
    const addItem = () => form.items.push(createEmptyItem());
    const removeItem = (index) => {
        if (form.items.length > 1) form.items.splice(index, 1);
    };
    
    // --- CORRECCIÓN: NO CONVERTIR AUTOMÁTICAMENTE ---
    const onSkuChange = (item) => {
        const skuInfo = props.skus.find(s => s.id === item.sku_id);
        
        if (skuInfo) {
            item.factor = skuInfo.factor;
            item.sku_name = skuInfo.full_name;
            // YA NO buscamos la unidad base. Guardamos lo que se compró.
        }
        calculateRow(item);
    };
    
    const calculateRow = (item) => {
        // La cantidad a guardar es EXACTAMENTE la que puso el usuario
        // Si puso 10 cajas, guardamos 10 (del SKU caja)
        item.quantity = item.quantity_input || 0;
        
        // Costo Unitario = Costo Total Línea / Cantidad
        // Si gasté 100 Bs en 10 Cajas, cada Caja cuesta 10 Bs.
        if (item.quantity > 0 && item.total_cost_input > 0) {
            item.unit_cost = item.total_cost_input / item.quantity;
        } else {
            item.unit_cost = 0;
        }
    };
    
    const grandTotal = computed(() => {
        return form.items.reduce((acc, item) => acc + (item.total_cost_input || 0), 0);
    });
    
    const submit = () => {
        // Enviamos el formulario directo, ya está limpio
        form.post(route('admin.purchases.store'), {
            preserveScroll: true,
            onError: (e) => console.error(e)
        });
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-full mx-auto px-4">
            
            <div class="flex justify-between items-end mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-white">Registrar Ingreso (Multi-Presentación)</h1>
                    <p class="text-gray-400 text-sm">El stock se guardará en la presentación seleccionada (Caja, Pack o Unidad).</p>
                </div>
                <div class="text-right bg-gray-800 p-3 rounded border border-gray-700">
                    <p class="text-[10px] text-gray-500 uppercase font-bold">Total Factura</p>
                    <p class="text-2xl font-mono text-green-400">Bs {{ grandTotal.toFixed(2) }}</p>
                </div>
            </div>

            <div v-if="form.errors.error" class="mb-4 bg-red-900/30 border-l-4 border-red-500 text-red-200 p-4 font-bold text-sm">
                {{ form.errors.error }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="bg-gray-800 p-5 rounded-lg border border-gray-700 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Proveedor</label>
                        <select v-model="form.provider_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-sm focus:border-blue-500 outline-none">
                            <option value="" disabled>Seleccionar...</option>
                            <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.commercial_name }}</option>
                        </select>
                        <p v-if="form.errors.provider_id" class="text-red-500 text-[10px] mt-1">Requerido</p>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nro Factura / Doc</label>
                        <input v-model="form.document_number" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-sm focus:border-blue-500 outline-none">
                        <p v-if="form.errors.document_number" class="text-red-500 text-[10px] mt-1">Requerido</p>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Sucursal Destino</label>
                        <select v-model="form.branch_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-sm focus:border-blue-500 outline-none">
                            <option value="" disabled>Seleccionar...</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <p v-if="form.errors.branch_id" class="text-red-500 text-[10px] mt-1">Requerido</p>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Fecha Compra</label>
                        <input v-model="form.purchase_date" type="date" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-sm focus:border-blue-500 outline-none">
                    </div>
                </div>

                <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden shadow-lg">
                    <div class="p-3 bg-gray-900 border-b border-gray-700 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-green-400 uppercase">Detalle de Productos</h3>
                        <button type="button" @click="addItem" class="bg-gray-700 hover:bg-gray-600 text-white text-xs px-3 py-1.5 rounded font-bold transition border border-gray-600">
                            + Fila
                        </button>
                    </div>

                    <table class="w-full text-left">
                        <thead class="bg-gray-900 text-gray-500 text-[10px] uppercase font-bold">
                            <tr>
                                <th class="px-3 py-2 w-64">Producto (Presentación)</th>
                                <th class="px-3 py-2 w-24 text-center">Cant.</th>
                                <th class="px-3 py-2 w-32 text-right">Costo Total Fila</th>
                                <th class="px-3 py-2 w-32 text-right bg-gray-900/50 text-blue-300">Costo Unit.</th>
                                <th class="px-3 py-2 w-32">Vencimiento</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 text-xs">
                            <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-750">
                                
                                <td class="p-2">
                                    <select v-model="item.sku_id" @change="onSkuChange(item)" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-1.5 focus:border-green-500 outline-none">
                                        <option value="" disabled>Buscar producto...</option>
                                        <option v-for="sku in skus" :key="sku.id" :value="sku.id">
                                            {{ sku.full_name }} {{ sku.factor > 1 ? '(x'+sku.factor+')' : '(Unid)' }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors[`items.${index}.sku_id`]" class="text-red-500 text-[9px] mt-1">Requerido</p>
                                </td>

                                <td class="p-2">
                                    <input v-model.number="item.quantity_input" @input="calculateRow(item)" type="number" min="1" class="w-full bg-gray-900 border border-gray-600 text-white font-bold rounded p-1.5 text-center focus:border-green-500 outline-none">
                                    <div class="text-[9px] text-gray-500 text-center mt-0.5">
                                        {{ item.factor > 1 ? 'Packs/Cajas' : 'Unidades' }}
                                    </div>
                                </td>

                                <td class="p-2">
                                    <input v-model.number="item.total_cost_input" @input="calculateRow(item)" type="number" step="0.01" class="w-full bg-gray-900 border border-gray-600 text-yellow-400 font-bold rounded p-1.5 text-right focus:border-yellow-500 outline-none">
                                </td>

                                <td class="p-2 text-right bg-blue-900/10">
                                    <div class="font-mono text-gray-300">{{ item.unit_cost.toFixed(2) }}</div>
                                    <div class="text-[9px] text-gray-500">c/u</div>
                                </td>

                                <td class="p-2">
                                    <input v-model="item.expiration_date" type="date" class="w-full bg-gray-900 border border-gray-600 text-gray-300 rounded p-1 focus:border-green-500 outline-none">
                                </td>

                                <td class="p-2 text-center">
                                    <button type="button" @click="removeItem(index)" class="text-gray-500 hover:text-red-500 font-bold text-sm px-2">✕</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="p-2 bg-gray-900 text-center" v-if="form.items.length === 0">
                        <span class="text-gray-500 text-xs">Agrega items para comenzar.</span>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-gray-700">
                    <Link :href="route('admin.purchases.index')" class="px-6 py-3 text-gray-400 font-bold hover:text-white transition">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-10 rounded shadow-lg transition disabled:opacity-50">
                        <span v-if="form.processing">Procesando...</span>
                        <span v-else>Confirmar Ingreso</span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>