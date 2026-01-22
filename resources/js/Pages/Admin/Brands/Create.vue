<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    
    const props = defineProps({
        providers: Array,
        categories: Array
    });
    
    const form = useForm({
        name: '', 
        provider_id: '',
        manufacturer: '',
        origin_country_code: '',
        tier: 'Standard',
        website: '',
        image: null,
        is_active: true,
        is_featured: false,
        categories: [] // Array de IDs para la tabla pivot
    });
    
    const submit = () => {
        form.post(route('admin.brands.store'), { forceFormData: true });
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-5xl mx-auto">
            <h1 class="text-2xl font-bold text-white mb-6">Registrar Marca</h1>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg h-fit">
                    <h2 class="text-blue-400 font-bold text-sm uppercase mb-4 pb-2 border-b border-gray-700">Identidad & Origen</h2>
                    
                    <div class="mb-4">
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nombre Comercial *</label>
                        <input v-model="form.name" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 focus:border-blue-500 outline-none placeholder-gray-600" placeholder="Ej: Johnnie Walker">
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Distribuidor Oficial *</label>
                        <select v-model="form.provider_id" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 focus:border-blue-500 outline-none">
                            <option value="" disabled>-- Seleccionar Proveedor --</option>
                            <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.commercial_name }}</option>
                        </select>
                        <p v-if="form.errors.provider_id" class="text-red-500 text-xs mt-1">{{ form.errors.provider_id }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">País Origen (ISO)</label>
                            <input v-model="form.origin_country_code" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 text-center uppercase focus:border-blue-500 outline-none" maxlength="2" placeholder="GB">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Segmento (Tier)</label>
                            <select v-model="form.tier" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-3 focus:border-blue-500 outline-none">
                                <option value="Economy">Económica</option>
                                <option value="Standard">Estándar</option>
                                <option value="Premium">Premium</option>
                                <option value="Luxury">Lujo</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Logo / Isotipo</label>
                        <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:bg-gray-700 file:text-white cursor-pointer border border-gray-600 rounded bg-gray-900"/>
                        <p v-if="form.errors.image" class="text-red-500 text-xs mt-1">{{ form.errors.image }}</p>
                    </div>
                </div>

                <div class="flex flex-col gap-6">
                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg flex-1">
                        <h2 class="text-green-400 font-bold text-sm uppercase mb-4 pb-2 border-b border-gray-700">Categorías de Venta</h2>
                        <p class="text-gray-500 text-xs mb-3">Selecciona en qué categorías aparecerá esta marca.</p>

                        <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto custom-scrollbar pr-2">
                            <label v-for="cat in categories" :key="cat.id" 
                                   class="flex items-center gap-3 p-2 rounded border cursor-pointer transition text-sm select-none"
                                   :class="form.categories.includes(cat.id) ? 'bg-blue-900/30 border-blue-500 text-white' : 'bg-gray-900 border-gray-700 text-gray-400 hover:bg-gray-700'">
                                <input type="checkbox" :value="cat.id" v-model="form.categories" class="rounded bg-gray-800 border-gray-600 text-blue-600 focus:ring-0">
                                <span class="truncate">{{ cat.name }}</span>
                            </label>
                        </div>
                        <p v-if="form.categories.length === 0" class="text-yellow-500 text-xs mt-2 italic">⚠ Marca sin categorías asignadas.</p>
                    </div>

                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                        <h2 class="text-yellow-400 font-bold text-sm uppercase mb-4 pb-2 border-b border-gray-700">Ajustes</h2>
                        <div class="space-y-3">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded bg-gray-700 border-gray-500 text-blue-600">
                                <span class="text-gray-300 font-bold text-sm">Marca Activa</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input v-model="form.is_featured" type="checkbox" class="w-5 h-5 rounded bg-gray-700 border-gray-500 text-yellow-500">
                                <span class="text-gray-300 font-bold text-sm">Destacada en Home</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Link :href="route('admin.brands.index')" class="px-6 py-3 text-gray-400 font-bold hover:text-white transition">Cancelar</Link>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-8 rounded shadow-lg transition disabled:opacity-50">
                            Guardar Marca
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }
</style>