<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    
    const props = defineProps({
        category: Object,
        parents: Array
    });
    
    const categoryType = ref(props.category.parent_id ? 'child' : 'parent');
    
    const form = useForm({
        _method: 'PUT', // Vital para subir archivos en Update
        name: props.category.name,
        parent_id: props.category.parent_id || '',
        external_code: props.category.external_code || '',
        tax_classification: props.category.tax_classification || '',
        requires_age_check: !!props.category.requires_age_check,
        is_active: !!props.category.is_active,
        is_featured: !!props.category.is_featured,
        seo_title: props.category.seo_title || '',
        seo_description: props.category.seo_description || '',
        description: props.category.description || '',
        image: null,
    });
    
    watch(categoryType, (val) => {
        if (val === 'parent') form.parent_id = '';
    });
    
    const submit = () => {
        form.post(route('admin.categories.update', props.category.id), { forceFormData: true });
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-white mb-6">Editar: {{ category.name }}</h1>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-3">Jerarquía</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div @click="categoryType = 'parent'" 
                                 class="cursor-pointer border-2 rounded-lg p-3 text-center transition"
                                 :class="categoryType === 'parent' ? 'border-blue-500 bg-blue-900/20' : 'border-gray-600 bg-gray-800 opacity-50'">
                                <span class="font-bold text-white text-sm">Categoría Principal</span>
                            </div>
                            <div @click="categoryType = 'child'" 
                                 class="cursor-pointer border-2 rounded-lg p-3 text-center transition"
                                 :class="categoryType === 'child' ? 'border-blue-500 bg-blue-900/20' : 'border-gray-600 bg-gray-800 opacity-50'">
                                <span class="font-bold text-white text-sm">Subcategoría</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                        <h2 class="text-lg font-bold text-blue-400 mb-4 pb-2 border-b border-gray-700">Datos</h2>
                        
                        <div v-if="categoryType === 'child'" class="mb-4 bg-blue-900/20 p-4 rounded border border-blue-800/50">
                            <label class="block text-blue-300 text-xs uppercase font-bold mb-2">Mover a:</label>
                            <select v-model="form.parent_id" class="w-full bg-gray-900 border border-blue-500 text-white rounded p-3 outline-none">
                                <option value="" disabled>-- Selecciona --</option>
                                <option v-for="cat in parents" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p v-if="form.errors.parent_id" class="text-red-400 text-xs mt-1">{{ form.errors.parent_id }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nombre</label>
                                <input v-model="form.name" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 outline-none">
                                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Código ERP</label>
                                <input v-model="form.external_code" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 outline-none">
                                <p v-if="form.errors.external_code" class="text-red-500 text-xs mt-1">{{ form.errors.external_code }}</p>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Descripción</label>
                            <textarea v-model="form.description" rows="2" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 outline-none"></textarea>
                        </div>

                        <div class="mb-4 bg-gray-900 p-4 rounded border border-gray-600">
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-3">Imagen</label>
                            <div class="flex items-center gap-4">
                                <div v-if="category.image_url" class="shrink-0">
                                    <p class="text-[10px] text-gray-500 mb-1">Actual:</p>
                                    <img :src="category.image_url" class="h-20 w-20 object-cover rounded border border-gray-600">
                                </div>
                                <div class="w-full">
                                    <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:bg-gray-700 file:text-white cursor-pointer border border-gray-600 rounded bg-gray-800"/>
                                    <p class="text-[10px] text-gray-500 mt-1">Subir nueva para reemplazar</p>
                                </div>
                            </div>
                            <p v-if="form.errors.image" class="text-red-500 text-xs mt-1">{{ form.errors.image }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg sticky top-6">
                        <h2 class="text-lg font-bold text-yellow-400 mb-4 pb-2 border-b border-gray-700">Ajustes</h2>
                        <div class="space-y-4">
                            <label class="flex items-center space-x-3 p-3 rounded bg-gray-900 border border-gray-700 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded text-blue-600 bg-gray-800 border-gray-600">
                                <span class="text-gray-300 font-bold text-sm">Activo</span>
                            </label>
                            <label class="flex items-center space-x-3 p-3 rounded bg-gray-900 border border-gray-700 cursor-pointer">
                                <input v-model="form.is_featured" type="checkbox" class="w-5 h-5 rounded text-yellow-500 bg-gray-800 border-gray-600">
                                <span class="text-gray-300 font-bold text-sm">Destacado</span>
                            </label>
                            <label class="flex items-center space-x-3 p-3 rounded bg-gray-900 border border-gray-700 cursor-pointer">
                                <input v-model="form.requires_age_check" type="checkbox" class="w-5 h-5 rounded text-red-500 bg-gray-800 border-gray-600">
                                <span class="text-gray-300 font-bold text-sm">Requiere +18 años</span>
                            </label>

                             <div class="pt-4 border-t border-gray-700">
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Clasif. Fiscal</label>
                                <input v-model="form.tax_classification" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-sm outline-none">
                            </div>
                        </div>

                         <div class="mt-6 pt-6 border-t border-gray-700">
                            <h3 class="text-green-400 font-bold text-sm mb-3">SEO</h3>
                            <div class="space-y-3">
                                <input v-model="form.seo_title" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-xs">
                                <textarea v-model="form.seo_description" rows="2" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 text-xs"></textarea>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col gap-3">
                            <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-4 rounded shadow-lg transition">
                                Actualizar Registro
                            </button>
                            <Link :href="route('admin.categories.index')" class="w-full text-center text-gray-400 hover:text-white py-2 text-sm">Cancelar</Link>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>