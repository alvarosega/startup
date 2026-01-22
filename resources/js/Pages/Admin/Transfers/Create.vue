<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import axios from 'axios';
    import debounce from 'lodash/debounce';
    
    const props = defineProps({
        branches: Array
    });
    
    const form = useForm({
        origin_branch_id: '',
        destination_branch_id: '',
        observaciones: '',
        items: []
    });
    
    // --- Buscador de Productos ---
    const searchQuery = ref('');
    const searchResults = ref([]);
    
    const performSearch = debounce(async (query) => {
        if (!query) { searchResults.value = []; return; }
        try {
            // Usamos el mismo buscador global
            const response = await axios.get(route('admin.inventory.search'), { params: { q: query } });
            searchResults.value = response.data;
        } catch (error) { console.error(error); }
    }, 300);
    
    watch(searchQuery, (newVal) => performSearch(newVal));
    
    const addItem = (sku) => {
        if (form.items.find(i => i.sku_id === sku.id)) return;
        form.items.push({
            sku_id: sku.id,
            full_name: sku.full_name,
            codigo: sku.codigo,
            cantidad: 1
        });
        searchQuery.value = ''; searchResults.value = [];
    };
    
    const removeItem = (index) => form.items.splice(index, 1);
    
    const submit = () => {
        if (form.origin_branch_id === form.destination_branch_id) {
            alert('El origen y el destino no pueden ser iguales.');
            return;
        }
        form.post(route('admin.transfers.store'));
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-5xl mx-auto">
                <h1 class="text-2xl font-bold text-white mb-6">Nuevo Traslado entre Sucursales</h1>
    
                <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-6">
                        <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                            <h2 class="text-lg font-bold text-blue-400 mb-4">Ruta de Envío</h2>
                            
                            <div class="mb-4">
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Origen (Sale de)</label>
                                <select v-model="form.origin_branch_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3">
                                    <option value="" disabled>Selecciona...</option>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                                <p v-if="form.errors.origin_branch_id" class="text-red-500 text-xs mt-1">{{ form.errors.origin_branch_id }}</p>
                            </div>
    
                            <div class="mb-4">
                                <div class="flex justify-center text-2xl text-gray-500">⬇️</div>
                            </div>
    
                            <div class="mb-4">
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Destino (Llega a)</label>
                                <select v-model="form.destination_branch_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3">
                                    <option value="" disabled>Selecciona...</option>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                                <p v-if="form.errors.destination_branch_id" class="text-red-500 text-xs mt-1">{{ form.errors.destination_branch_id }}</p>
                            </div>
    
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Observaciones</label>
                                <textarea v-model="form.observaciones" rows="2" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3"></textarea>
                            </div>
                        </div>
    
                        <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 relative">
                            <label class="block text-green-400 text-xs uppercase font-bold mb-2">Agregar Productos a la Carga</label>
                            <input type="text" v-model="searchQuery" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 pl-10 focus:border-green-500 outline-none" placeholder="Buscar...">
                            
                            <div v-if="searchResults.length > 0" class="mt-2 bg-gray-900 border border-gray-600 rounded max-h-60 overflow-y-auto absolute w-full left-0 z-50 shadow-xl">
                                <div v-for="sku in searchResults" :key="sku.id" @click="addItem(sku)" class="p-3 hover:bg-gray-800 cursor-pointer border-b border-gray-800 flex justify-between">
                                    <span class="text-white text-sm">{{ sku.full_name }}</span>
                                    <span class="text-green-400 font-bold">+</span>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="lg:col-span-2 bg-gray-800 p-6 rounded-lg border border-gray-700 flex flex-col h-full">
                        <h2 class="text-lg font-bold text-white mb-4">Manifiesto de Carga ({{ form.items.length }} Items)</h2>
                        
                        <div class="flex-1 overflow-y-auto mb-4 bg-gray-900/50 rounded border border-gray-700">
                            <table class="w-full text-left">
                                <thead class="bg-gray-700 text-gray-300 text-xs uppercase">
                                    <tr>
                                        <th class="p-3">Producto</th>
                                        <th class="p-3 w-32 text-center">Cant. a Enviar</th>
                                        <th class="p-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <tr v-for="(item, index) in form.items" :key="index">
                                        <td class="p-3">
                                            <p class="text-white text-sm font-bold">{{ item.full_name }}</p>
                                            <p class="text-xs text-gray-500">{{ item.codigo }}</p>
                                        </td>
                                        <td class="p-3">
                                            <input type="number" v-model="item.cantidad" min="1" class="w-full bg-gray-900 border border-blue-500 rounded p-1 text-white text-center font-bold">
                                        </td>
                                        <td class="p-3 text-center">
                                            <button @click="removeItem(index)" class="text-red-500 font-bold">✕</button>
                                        </td>
                                    </tr>
                                    <tr v-if="form.items.length === 0">
                                        <td colspan="3" class="p-10 text-center text-gray-500 italic">Camión vacío.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
    
                        <div v-if="form.errors.error" class="p-3 mb-2 bg-red-900/50 border border-red-500 text-red-200 text-sm rounded">
                            ⚠️ {{ form.errors.error }}
                        </div>
    
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 w-full rounded-lg shadow-lg">
                            Confirmar Envío y Descontar Stock
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    </template>