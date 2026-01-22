<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, computed, onMounted, watch } from 'vue';
    import { Plus, Trash2, ArrowRight, Package, AlertCircle, MapPin, Loader2 } from 'lucide-vue-next';
    import axios from 'axios';

    const props = defineProps({
        origins: { type: Array, default: () => [] },
        destinations: { type: Array, default: () => [] },
        // Ya no necesitamos 'skus' aquí porque los cargaremos dinámicamente
    });

    const form = useForm({
        origin_branch_id: '',
        destination_branch_id: '',
        notes: '',
        items: [ createEmptyItem() ]
    });

    // --- ESTADO LOCAL ---
    const branchProducts = ref([]); // Aquí guardaremos los productos de la sucursal seleccionada
    const isLoadingProducts = ref(false);

    // --- LÓGICA DE CARGA DINÁMICA ---
    
    // Función que carga los productos cuando cambia el origen
    const loadOriginProducts = async () => {
        if (!form.origin_branch_id) {
            branchProducts.value = [];
            return;
        }

        isLoadingProducts.value = true;
        try {
            const response = await axios.get(route('admin.inventory.stock-by-branch', form.origin_branch_id));
            branchProducts.value = response.data;
            
            // Limpiamos los items seleccionados porque ya no son válidos para la nueva sucursal
            form.items = [createEmptyItem()];
        } catch (error) {
            console.error("Error cargando inventario:", error);
            branchProducts.value = [];
        } finally {
            isLoadingProducts.value = false;
        }
    };

    // Watcher: Cada vez que cambia el origen, recargamos la lista de productos
    watch(() => form.origin_branch_id, loadOriginProducts);

    // Al montar: Si es Branch Admin, ya tiene un ID pre-seleccionado, cargamos sus productos
    onMounted(() => {
        if (props.origins.length === 1) {
            form.origin_branch_id = props.origins[0].id;
            // El watcher se disparará automáticamente, o podemos llamar directo si es necesario
        }
    });

    // Filtro de destinos (Igual que antes)
    const availableDestinations = computed(() => {
        return props.destinations.filter(b => b.id !== form.origin_branch_id);
    });

    // --- LÓGICA DE ITEMS ---

    function createEmptyItem() {
        return { sku_id: '', quantity: 1, max_stock: 0 };
    }

    const addItem = () => form.items.push(createEmptyItem());
    const removeItem = (index) => form.items.splice(index, 1);

    // Al seleccionar un producto del dropdown, actualizamos su stock máximo en la fila
    const onProductSelect = (item) => {
        const selectedProduct = branchProducts.value.find(p => p.id === item.sku_id);
        if (selectedProduct) {
            item.max_stock = parseFloat(selectedProduct.available_stock);
            // Auto-ajuste si el usuario ya tenía un número alto
            if (item.quantity > item.max_stock) item.quantity = item.max_stock;
        } else {
            item.max_stock = 0;
        }
    };

    const submit = () => {
        form.post(route('admin.transfers.store'));
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            
            <div class="mb-6">
                <h1 class="text-2xl font-black text-skin-base tracking-tight">Nueva Guía de Remisión</h1>
                <p class="text-skin-muted text-sm mt-1">Transferencia de mercadería entre almacenes.</p>
            </div>

            <div v-if="$page.props.errors.error" class="bg-skin-danger/10 text-skin-danger p-4 rounded-global mb-6 font-bold border border-skin-danger/20 flex items-center gap-2">
                <AlertCircle :size="20" /> {{ $page.props.errors.error }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="bg-skin-fill-card p-6 rounded-global border border-skin-border shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-11 gap-4 items-center">
                        
                        <div class="md:col-span-5 relative">
                            <label class="block text-[10px] text-skin-muted uppercase font-bold tracking-wider mb-2 flex items-center gap-1">
                                <MapPin :size="10" /> Origen (Salida)
                            </label>
                            
                            <div class="relative">
                                <select v-model="form.origin_branch_id" 
                                        :disabled="origins.length === 1"
                                        class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 text-sm focus:ring-2 focus:ring-skin-primary/50 outline-none appearance-none font-bold disabled:opacity-70 disabled:cursor-not-allowed">
                                    <option value="" disabled>Seleccionar Origen...</option>
                                    <option v-for="b in origins" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                                <div v-if="origins.length === 1" class="absolute right-3 top-3 text-skin-muted">🔒</div>
                            </div>
                            <p v-if="form.errors.origin_branch_id" class="text-skin-danger text-xs mt-1 font-bold">Requerido</p>
                        </div>

                        <div class="md:col-span-1 flex justify-center text-skin-muted pt-6">
                            <ArrowRight :size="24" />
                        </div>

                        <div class="md:col-span-5">
                            <label class="block text-[10px] text-skin-muted uppercase font-bold tracking-wider mb-2 flex items-center gap-1">
                                <MapPin :size="10" /> Destino (Entrada)
                            </label>
                            <select v-model="form.destination_branch_id" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 text-sm focus:ring-2 focus:ring-skin-primary/50 outline-none">
                                <option value="" disabled>Seleccionar Destino...</option>
                                <option v-for="b in availableDestinations" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                            <p v-if="form.errors.destination_branch_id" class="text-skin-danger text-xs mt-1 font-bold">Requerido</p>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-skin-border">
                        <label class="block text-[10px] text-skin-muted uppercase font-bold tracking-wider mb-2">Notas / Observaciones</label>
                        <input v-model="form.notes" type="text" placeholder="Motivo del traslado..." class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-2 text-sm focus:ring-2 focus:ring-skin-primary/50 outline-none">
                    </div>
                </div>

                <div class="bg-skin-fill-card rounded-global border border-skin-border shadow-sm overflow-hidden">
                    <div class="p-4 bg-skin-fill border-b border-skin-border flex justify-between items-center">
                        <h3 class="text-xs font-black text-skin-muted uppercase tracking-widest flex items-center gap-2">
                            <Package :size="14" /> Mercadería a Enviar
                        </h3>
                        <div class="flex items-center gap-2">
                            <span v-if="isLoadingProducts" class="text-[10px] text-skin-primary flex items-center gap-1 animate-pulse">
                                <Loader2 :size="12" class="animate-spin"/> Cargando inventario...
                            </span>
                            
                            <button type="button" @click="addItem" :disabled="!form.origin_branch_id || isLoadingProducts" class="text-skin-primary hover:text-skin-primary-hover text-xs font-bold flex items-center gap-1 transition disabled:opacity-50">
                                <Plus :size="14" /> Agregar Ítem
                            </button>
                        </div>
                    </div>

                    <div class="p-4 space-y-3" v-if="form.origin_branch_id">
                        
                        <div v-if="!isLoadingProducts && branchProducts.length === 0" class="text-center p-6 bg-skin-fill/20 rounded border border-dashed border-skin-border text-skin-muted text-sm">
                            Esta sucursal no tiene productos con stock disponible para transferir.
                        </div>

                        <div v-else v-for="(item, index) in form.items" :key="index" class="flex gap-4 items-start bg-skin-fill/30 p-3 rounded-global border border-skin-border group hover:border-skin-primary/30 transition">
                            
                            <div class="flex-1">
                                <label class="text-[10px] text-skin-muted uppercase font-bold block mb-1">Producto</label>
                                <select v-model="item.sku_id" @change="onProductSelect(item)" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded p-2 text-sm focus:border-skin-primary outline-none">
                                    <option value="" disabled>Seleccionar producto...</option>
                                    <option v-for="p in branchProducts" :key="p.id" :value="p.id">
                                        {{ p.product_name }} - {{ p.sku_name }} (Stock: {{ parseInt(p.available_stock) }})
                                    </option>
                                </select>
                                <p v-if="form.errors[`items.${index}.sku_id`]" class="text-skin-danger text-[10px] font-bold mt-1">Selecciona un producto</p>
                            </div>

                            <div class="w-24">
                                <label class="text-[10px] text-skin-muted uppercase font-bold block mb-1">Cantidad</label>
                                <input v-model.number="item.quantity" 
                                       type="number" 
                                       min="1" 
                                       :max="item.max_stock" 
                                       class="w-full bg-skin-fill border border-skin-border text-skin-base font-bold text-center rounded p-2 text-sm focus:border-skin-primary outline-none"
                                       placeholder="1">
                                <div class="text-[9px] text-right mt-1 text-skin-muted">
                                    Máx: {{ item.max_stock }}
                                </div>
                            </div>

                            <button type="button" @click="removeItem(index)" class="text-skin-muted hover:text-skin-danger self-center p-2 rounded hover:bg-skin-danger/10 transition mt-4">
                                <Trash2 :size="18" />
                            </button>
                        </div>
                    </div>

                    <div v-else class="p-8 text-center text-skin-muted opacity-50 italic">
                        Selecciona una sucursal de origen para cargar el inventario disponible.
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-skin-border">
                    <Link :href="route('admin.transfers.index')" class="px-6 py-3 text-skin-muted font-bold hover:text-skin-base transition">Cancelar</Link>
                    <button type="submit" :disabled="form.processing || !form.origin_branch_id || !form.destination_branch_id || branchProducts.length === 0" class="bg-skin-primary hover:brightness-110 text-skin-primary-text font-bold py-3 px-8 rounded-global shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="form.processing">Enviando...</span>
                        <span v-else>Confirmar Transferencia</span>
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>