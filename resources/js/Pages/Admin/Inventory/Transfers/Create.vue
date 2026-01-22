<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { computed } from 'vue';
    
    const props = defineProps({
        branches: Array,
        inventory: Array // Inventario consolidado (stock > 0)
    });
    
    const form = useForm({
        origin_branch_id: '',
        destination_branch_id: '',
        notes: '',
        items: [{ sku_id: '', quantity: 1 }]
    });
    
    // Filtramos productos disponibles SOLO en la sucursal de origen
    const availableProducts = computed(() => {
        if (!form.origin_branch_id) return [];
        return props.inventory.filter(i => i.branch_id === form.origin_branch_id);
    });
    
    // Obtener Stock Máximo de un item seleccionado
    const getMaxStock = (skuId) => {
        const item = availableProducts.value.find(i => i.id === skuId);
        return item ? parseFloat(item.stock_real) : 0;
    };

    const addItem = () => form.items.push({ sku_id: '', quantity: 1 });
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
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-white mb-6">Nueva Transferencia</h1>
            
            <div v-if="$page.props.errors.error" class="bg-red-500/20 border border-red-500 text-red-200 p-4 rounded mb-6 text-sm font-bold">
                ⚠ {{ $page.props.errors.error }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="bg-gray-800 p-6 rounded border border-gray-700 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Origen (Sale Stock)</label>
                        <select v-model="form.origin_branch_id" @change="form.items = [{ sku_id: '', quantity: 1 }]" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-purple-500 outline-none">
                            <option value="" disabled>Seleccionar Sucursal...</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <p v-if="form.errors.origin_branch_id" class="text-red-500 text-xs mt-1">Requerido</p>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Destino (Entra Stock)</label>
                        <select v-model="form.destination_branch_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-purple-500 outline-none">
                            <option value="" disabled>Seleccionar Sucursal...</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <p v-if="form.errors.destination_branch_id" class="text-red-500 text-xs mt-1">Requerido</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Observaciones</label>
                        <textarea v-model="form.notes" rows="2" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-purple-500 outline-none" placeholder="Ej: Envío urgente para evento..."></textarea>
                    </div>
                </div>

                <div class="bg-gray-800 p-6 rounded border border-gray-700">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-purple-400 font-bold uppercase text-sm">Productos a Enviar</h3>
                        <button type="button" @click="addItem" class="text-xs bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded transition border border-gray-600">
                            + Agregar Producto
                        </button>
                    </div>

                    <div v-if="!form.origin_branch_id" class="text-center py-4 text-gray-500 italic text-sm">
                        Selecciona una sucursal de origen para ver el stock disponible.
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="(item, index) in form.items" :key="index" class="flex gap-4 items-start bg-gray-900/50 p-2 rounded border border-gray-700">
                            
                            <div class="flex-1">
                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">Producto</label>
                                <select v-model="item.sku_id" class="w-full bg-gray-800 border border-gray-600 text-white rounded p-1.5 text-sm focus:border-purple-500 outline-none">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="p in availableProducts" :key="p.id" :value="p.id">
                                        {{ p.product_name }} - {{ p.sku_name }} (Disp: {{ parseInt(p.stock_real) }})
                                    </option>
                                </select>
                                <p v-if="form.errors[`items.${index}.sku_id`]" class="text-red-500 text-[10px]">Requerido</p>
                            </div>

                            <div class="w-24">
                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">Cantidad</label>
                                <input v-model.number="item.quantity" type="number" min="1" :max="getMaxStock(item.sku_id)" class="w-full bg-gray-800 border border-gray-600 text-white rounded p-1.5 text-sm text-center focus:border-purple-500 outline-none">
                            </div>

                            <button type="button" @click="removeItem(index)" class="text-gray-500 hover:text-red-500 font-bold px-2 mt-6">✕</button>
                        </div>
                    </div>
                    
                    <p v-if="form.errors.items" class="text-red-500 text-xs mt-2">{{ form.errors.items }}</p>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-gray-700">
                    <Link :href="route('admin.transfers.index')" class="px-6 py-3 text-gray-400 font-bold hover:text-white transition">Cancelar</Link>
                    <button type="submit" :disabled="form.processing || !form.origin_branch_id" class="bg-purple-600 hover:bg-purple-500 text-white font-bold py-3 px-8 rounded shadow-lg transition disabled:opacity-50">
                        Confirmar Envío
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>