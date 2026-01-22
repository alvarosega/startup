<script setup>
    import { useForm } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import { Plus, Trash2, Save, ArrowLeft } from 'lucide-vue-next';
    import { Link } from '@inertiajs/vue3';
    
    const props = defineProps({
        bundle: Object,
        currentItems: Array,
        skus: Array 
    });
    
    const form = useForm({
        name: props.bundle.name,
        description: props.bundle.description,
        fixed_price: props.bundle.fixed_price,
        is_active: Boolean(props.bundle.is_active),
        items: props.currentItems // Cargamos los items existentes
    });
    
    const addItem = () => form.items.push({ sku_id: '', quantity: 1 });
    const removeItem = (index) => form.items.splice(index, 1);
    const submit = () => form.put(route('admin.bundles.update', props.bundle.id));
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center gap-4 mb-6">
                    <Link :href="route('admin.bundles.index')" class="text-gray-400 hover:text-gray-600"><ArrowLeft /></Link>
                    <h1 class="text-2xl font-black text-gray-800">Editar Pack: {{ bundle.name }}</h1>
                </div>
    
                <form @submit.prevent="submit" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <BaseInput v-model="form.name" label="Nombre del Pack" />
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Precio Fijo</label>
                            <input v-model="form.fixed_price" type="number" step="0.50" class="w-full border-gray-300 rounded-lg text-sm" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Descripción</label>
                            <textarea v-model="form.description" class="w-full border-gray-300 rounded-lg text-sm" rows="3"></textarea>
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-2 cursor-pointer bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
                                <input type="checkbox" v-model="form.is_active" class="rounded text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm font-bold text-gray-700">Pack Activo</span>
                            </label>
                        </div>
                    </div>
    
                    <div class="border-t border-gray-100 pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-700">Contenido del Pack</h3>
                            <button type="button" @click="addItem" class="text-xs bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg font-bold hover:bg-blue-100 flex items-center gap-1">
                                <Plus :size="14"/> Agregar Producto
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(item, index) in form.items" :key="index" class="flex gap-3 items-start">
                                <div class="flex-1">
                                    <select v-model="item.sku_id" class="w-full border-gray-300 rounded-lg text-sm" required>
                                        <option value="" disabled>Seleccionar...</option>
                                        <option v-for="sku in skus" :key="sku.id" :value="sku.id">{{ sku.name }}</option>
                                    </select>
                                </div>
                                <div class="w-24">
                                    <input v-model="item.quantity" type="number" min="1" class="w-full border-gray-300 rounded-lg text-sm text-center" required />
                                </div>
                                <button type="button" @click="removeItem(index)" class="p-2 text-gray-400 hover:text-red-500 transition"><Trash2 :size="18" /></button>
                            </div>
                        </div>
                    </div>
    
                    <div class="pt-6 border-t border-gray-100 flex justify-end">
                        <BaseButton :isLoading="form.processing" class="flex items-center gap-2">
                            <Save :size="18" /> Actualizar Pack
                        </BaseButton>
                    </div>
                </form>
            </div>
        </AdminLayout>
    </template>