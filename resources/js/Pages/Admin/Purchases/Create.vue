<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { computed } from 'vue';
    
    const props = defineProps({
        providers: Array,
        branches: Array,
        skus: Array // Contiene 'default_provider_id'
    });
    
    const form = useForm({
        provider_id: '',
        branch_id: '',
        document_number: '',
        purchase_date: new Date().toISOString().substr(0, 10),
        notes: '',
        items: [
            { sku_id: '', quantity: 1, unit_cost: 0, expiration_date: '' }
        ]
    });
    
    // Lógica Inteligente: Al cambiar un SKU, sugerir proveedor
    const onItemSkuChange = (skuId) => {
        // Solo sugerimos si el campo proveedor está vacío (para no sobreescribir elección manual)
        if (!form.provider_id && skuId) {
            const selectedSku = props.skus.find(s => s.id === skuId);
            if (selectedSku && selectedSku.default_provider_id) {
                form.provider_id = selectedSku.default_provider_id;
            }
        }
    };
    
    const addItem = () => {
        form.items.push({ sku_id: '', quantity: 1, unit_cost: 0, expiration_date: '' });
    };
    
    const removeItem = (index) => {
        if (form.items.length > 1) form.items.splice(index, 1);
    };
    
    const totalAmount = computed(() => {
        return form.items.reduce((sum, item) => sum + (item.quantity * item.unit_cost), 0);
    });
    
    const submit = () => {
        form.post(route('admin.purchases.store'));
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-6xl mx-auto">
                
                <div v-if="Object.keys(form.errors).length > 0" class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded text-sm">
                    <p class="font-bold">Error al procesar:</p>
                    <ul class="list-disc list-inside">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>
    
                <h1 class="text-2xl font-bold text-white mb-6">Registrar Compra (Ingreso de Stock)</h1>
    
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Destino (Sucursal)</label>
                                <select v-model="form.branch_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-green-500 outline-none">
                                    <option value="" disabled>-- Seleccionar --</option>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                            </div>
    
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Proveedor</label>
                                <select v-model="form.provider_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-green-500 outline-none">
                                    <option value="" disabled>-- Seleccionar --</option>
                                    <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.commercial_name }}</option>
                                </select>
                            </div>
    
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Fecha Compra</label>
                                <input v-model="form.purchase_date" type="date" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-green-500 outline-none">
                            </div>
    
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nro. Factura / Recibo</label>
                                <input v-model="form.document_number" type="text" placeholder="Ej: F-00123" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-green-500 outline-none">
                            </div>
                        </div>
                    </div>
    
                    <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-lg overflow-hidden">
                        <div class="p-4 bg-gray-750 border-b border-gray-700 flex justify-between items-center">
                            <h2 class="text-blue-400 font-bold text-sm uppercase">Items de la Compra</h2>
                            <button type="button" @click="addItem" class="bg-gray-700 hover:bg-gray-600 text-white text-xs px-3 py-2 rounded font-bold border border-gray-600 transition">
                                + Agregar Fila
                            </button>
                        </div>
    
                        <table class="w-full text-left text-sm text-gray-300">
                            <thead class="bg-gray-900 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Producto / SKU</th>
                                    <th class="px-4 py-3 w-32">Cantidad</th>
                                    <th class="px-4 py-3 w-32">Costo Unit. (Bs)</th>
                                    <th class="px-4 py-3 w-40">Vencimiento</th>
                                    <th class="px-4 py-3 w-32 text-right">Subtotal</th>
                                    <th class="px-4 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-750">
                                    <td class="px-4 py-2">
                                        <select v-model="item.sku_id" @change="onItemSkuChange(item.sku_id)" class="w-full bg-transparent border-none text-white focus:ring-0 cursor-pointer">
                                            <option value="" class="bg-gray-800 text-gray-500">-- Buscar SKU --</option>
                                            <option v-for="sku in skus" :key="sku.id" :value="sku.id" class="bg-gray-800">
                                                {{ sku.full_name }} ({{ sku.code }})
                                            </option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input v-model.number="item.quantity" type="number" min="1" class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-center focus:border-green-500 outline-none">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input v-model.number="item.unit_cost" type="number" step="0.01" class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-right focus:border-green-500 outline-none">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input v-model="item.expiration_date" type="date" class="w-full bg-gray-900 border border-gray-600 rounded px-2 py-1 text-xs focus:border-green-500 outline-none">
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono text-white">
                                        {{ (item.quantity * item.unit_cost).toFixed(2) }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button v-if="form.items.length > 1" @click="removeItem(index)" class="text-red-500 hover:text-red-300 font-bold">✕</button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-900 font-bold">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-right text-gray-400">TOTAL COMPRA:</td>
                                    <td class="px-4 py-3 text-right text-green-400 text-lg">{{ totalAmount.toFixed(2) }} Bs</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
    
                    <div class="flex justify-end gap-4">
                        <Link :href="route('admin.dashboard')" class="px-6 py-3 text-gray-400 font-bold hover:text-white">Cancelar</Link>
                        <button type="submit" :disabled="form.processing" class="bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-8 rounded shadow-lg transition">
                            <span v-if="form.processing">Procesando...</span>
                            <span v-else>Confirmar Ingreso</span>
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    </template>