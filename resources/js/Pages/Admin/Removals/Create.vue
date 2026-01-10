<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import axios from 'axios';
    import debounce from 'lodash/debounce';
    
    const props = defineProps({
        branches: Array,
        motivos: Object // Recibimos el Enum de motivos
    });
    
    const form = useForm({
        branch_id: '',
        sku_id: '',
        cantidad: 1,
        motivo: '',
        observaciones: '',
        evidencia: null
    });
    
    // --- Lógica del Buscador de Productos (Igual que en Compras) ---
    const searchQuery = ref('');
    const searchResults = ref([]);
    const selectedProduct = ref(null);
    
    const performSearch = debounce(async (query) => {
        if (!query) { searchResults.value = []; return; }
        try {
            const response = await axios.get(route('admin.inventory.search'), { params: { q: query } });
            searchResults.value = response.data;
        } catch (error) { console.error(error); }
    }, 300);
    
    watch(searchQuery, (newVal) => performSearch(newVal));
    
    const selectProduct = (sku) => {
        form.sku_id = sku.id;
        selectedProduct.value = sku;
        searchQuery.value = '';
        searchResults.value = [];
    };
    
    const submit = () => {
        form.post(route('admin.removals.store'));
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-2xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-white">Reportar Baja / Merma</h1>
                    <Link :href="route('admin.removals.index')" class="text-gray-400 hover:text-white">Cancelar</Link>
                </div>
    
                <form @submit.prevent="submit" class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-xl space-y-6">
                    
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Sucursal</label>
                        <select v-model="form.branch_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 focus:border-red-500 outline-none">
                            <option value="" disabled>Selecciona la sucursal...</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <p v-if="form.errors.branch_id" class="text-red-500 text-xs mt-1">{{ form.errors.branch_id }}</p>
                    </div>
    
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Producto Afectado</label>
                        
                        <div v-if="!selectedProduct" class="relative">
                            <input type="text" v-model="searchQuery" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 pl-10 focus:border-red-500 outline-none" placeholder="Buscar producto...">
                            <span class="absolute left-3 top-3.5 text-gray-500">🔍</span>
    
                            <div v-if="searchResults.length > 0" class="mt-1 bg-gray-900 border border-gray-600 rounded max-h-48 overflow-y-auto absolute w-full z-50 shadow-xl">
                                <div v-for="sku in searchResults" :key="sku.id" @click="selectProduct(sku)" class="p-3 hover:bg-gray-800 cursor-pointer border-b border-gray-800">
                                    <p class="text-white font-bold text-sm">{{ sku.full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ sku.codigo }}</p>
                                </div>
                            </div>
                        </div>
    
                        <div v-else class="flex justify-between items-center p-3 bg-gray-900 border border-red-500/50 rounded">
                            <div>
                                <p class="text-white font-bold">{{ selectedProduct.full_name }}</p>
                                <p class="text-xs text-gray-400">{{ selectedProduct.codigo }}</p>
                            </div>
                            <button type="button" @click="selectedProduct = null; form.sku_id = ''" class="text-red-400 hover:text-white text-sm font-bold">Cambiar</button>
                        </div>
                        <p v-if="form.errors.sku_id" class="text-red-500 text-xs mt-1">{{ form.errors.sku_id }}</p>
                    </div>
    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Cantidad</label>
                            <input v-model="form.cantidad" type="number" min="1" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 focus:border-red-500 outline-none">
                            <p v-if="form.errors.cantidad" class="text-red-500 text-xs mt-1">{{ form.errors.cantidad }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Motivo</label>
                            <select v-model="form.motivo" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 focus:border-red-500 outline-none">
                                <option value="" disabled>Selecciona...</option>
                                <option v-for="(label, key) in motivos" :key="key" :value="key">{{ label }}</option>
                            </select>
                            <p v-if="form.errors.motivo" class="text-red-500 text-xs mt-1">{{ form.errors.motivo }}</p>
                        </div>
                    </div>
    
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Observaciones / Justificación</label>
                        <textarea v-model="form.observaciones" rows="2" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3" placeholder="Ej: Se cayó la caja al descargar..."></textarea>
                        <p v-if="form.errors.observaciones" class="text-red-500 text-xs mt-1">{{ form.errors.observaciones }}</p>
                    </div>
    
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Evidencia (Foto Opcional)</label>
                        <input type="file" @input="form.evidencia = $event.target.files[0]" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-700 file:text-white hover:file:bg-gray-600"/>
                    </div>
    
                    <div v-if="form.errors.error" class="p-3 bg-red-900/50 border border-red-500 text-red-200 rounded text-sm">
                        ⚠️ {{ form.errors.error }}
                    </div>
    
                    <div class="flex justify-end pt-4 border-t border-gray-700">
                        <button type="submit" :disabled="form.processing" class="bg-red-600 hover:bg-red-500 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition">
                            Registrar Solicitud
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    </template>