<script setup>
import { watch, computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    brand: Object,
    options: Object
});

const emit = defineEmits(['close']);

const imagePreview = ref(null);
const isManualSlug = ref(false);

const form = useForm({
    name: '',
    slug: '',
    parent_id: '',
    provider_id: '',
    category_id: '',
    website: '',
    description: '',
    is_active: true,
    is_featured: false,
    bg_color: '#6366F1',
    image: null
});

// Watcher para autogeneración determinista de slugs
watch(() => form.name, (newName) => {
    if (!isManualSlug.value && !props.brand) {
        form.slug = newName
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
    }
});

// Previsualización y carga segura de imágenes
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
};

// Sincronización robusta basada en identificadores relacionales directos o mapeos defensivos
watch(
    () => [props.show, props.brand],
    ([isOpen, currentBrand]) => {
        if (!isOpen) return;
        form.clearErrors();

        if (currentBrand) {
            isManualSlug.value = true;
            form.name = currentBrand.name;
            form.slug = currentBrand.slug;
            form.parent_id = currentBrand.parent_id || '';
            
            // Resolución polinómica de IDs para evitar fallos de hidratación
            form.provider_id = currentBrand.provider_id || currentBrand.provider?.id || props.options.providers.find(p => p.name === currentBrand.provider_name)?.id || '';
            form.category_id = currentBrand.category_id || currentBrand.category?.id || props.options.categories.find(c => c.name === currentBrand.category_name)?.id || '';
            
            form.website = currentBrand.website || '';
            form.description = currentBrand.description || '';
            form.is_active = !!currentBrand.is_active;
            form.is_featured = !!currentBrand.is_featured;
            form.bg_color = currentBrand.bg_color || '#6366F1';
            form.image = null;
            imagePreview.value = currentBrand.image_url || null;
        } else {
            isManualSlug.value = false;
            form.reset();
            imagePreview.value = null;
            
            // Forzar preselección de primer nodo del catálogo para mitigar envíos nulos involuntarios
            form.provider_id = props.options.providers[0]?.id || '';
            form.category_id = props.options.categories[0]?.id || '';
        }
    },
    { immediate: true }
);

const filteredParents = computed(() => {
    if (!props.brand) return props.options.parents;
    return props.options.parents.filter(parent => parent.id !== props.brand.id);
});

const submit = () => {
    if (props.brand) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('admin.catalog.brands.update', props.brand.id), {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(route('admin.catalog.brands.store'), {
            onSuccess: () => emit('close'),
        });
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-neutral-950/40 dark:bg-neutral-950/60 transition-opacity" @click="emit('close')"></div>

        <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-xl bg-white dark:bg-neutral-900 border-l border-neutral-200 dark:border-neutral-800 shadow-2xl flex flex-col h-full text-neutral-900 dark:text-neutral-50">
                
                <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 flex items-center justify-between select-none">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-neutral-900 dark:text-white text-xl">workspace_premium</span>
                        <div>
                            <h2 class="text-sm font-bold uppercase tracking-tight text-neutral-900 dark:text-neutral-50">
                                {{ brand ? 'Modificar Atributos de Firma' : 'Registrar Nueva Firma Comercial' }}
                            </h2>
                            <p class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mt-0.5">Configuración y enlace atómico de la marca</p>
                        </div>
                    </div>
                    <button @click="emit('close')" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center border border-neutral-200 dark:border-neutral-800">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submit" class="flex-1 overflow-y-auto p-5 space-y-5">
                    
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Nombre de la Marca Comercial *</label>
                        <input v-model="form.name" type="text" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-mono uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" placeholder="Ej. COCA-COLA" />
                        <span v-if="form.errors.name" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.name }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Identificador Slug (URL)</label>
                            <input v-model="form.slug" type="text" @input="isManualSlug = true" placeholder="Conversión automática si se omite" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-mono text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" />
                            <span v-if="form.errors.slug" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.slug }}</span>
                        </div>

                        <div class="space-y-1.5 select-none">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Firma de Color Comercial</label>
                            <div class="flex items-center gap-2 border border-neutral-200 dark:border-neutral-800 rounded-md px-2 bg-neutral-50 dark:bg-neutral-950 h-[34px]">
                                <input v-model="form.bg_color" type="color" class="w-6 h-6 border-0 p-0 cursor-pointer bg-transparent" />
                                <span class="text-xs font-mono font-bold uppercase text-neutral-900 dark:text-neutral-50">{{ form.bg_color }}</span>
                            </div>
                            <span v-if="form.errors.bg_color" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.bg_color }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-neutral-50 dark:bg-neutral-950 p-3 rounded-md border border-neutral-200 dark:border-neutral-800 select-none">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Proveedor Responsable *</label>
                            <select v-model="form.provider_id" required class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-bold uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                                <option v-for="prov in options.providers" :key="prov.id" :value="prov.id">{{ prov.name.toUpperCase() }}</option>
                            </select>
                            <span v-if="form.errors.provider_id" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.provider_id }}</span>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Pasillo de Catálogo Base *</label>
                            <select v-model="form.category_id" required class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-bold uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                                <option v-for="cat in options.categories" :key="cat.id" :value="cat.id">{{ cat.name.toUpperCase() }}</option>
                            </select>
                            <span v-if="form.errors.category_id" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.category_id }}</span>
                        </div>

                        <div class="md:col-span-2 border-t border-neutral-200 dark:border-neutral-800 pt-2.5 mt-1 space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Estructura Umbrella (Firma Matriz)</label>
                            <select v-model="form.parent_id" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-bold uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                                <option value="">Ninguna — Es una firma comercial independiente</option>
                                <option v-for="parent in filteredParents" :key="parent.id" :value="parent.id">{{ parent.name.toUpperCase() }}</option>
                            </select>
                            <span v-if="form.errors.parent_id" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.parent_id }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Portal Web Oficial de la Marca</label>
                            <input v-model="form.website" type="url" placeholder="https://www.marca.com" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-mono text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" />
                            <span v-if="form.errors.website" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.website }}</span>
                        </div>

                        <div class="space-y-1.5 select-none">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Logotipo Corporativo (Max 2MB)</label>
                            <div class="border border-dashed border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 rounded-md p-3 flex items-center gap-3 relative min-h-[64px]">
                                <img v-if="imagePreview" :src="imagePreview" class="w-10 h-10 object-cover rounded border border-neutral-200 dark:border-neutral-800 shrink-0" />
                                <span v-else class="material-symbols-rounded text-neutral-400 text-xl shrink-0">add_photo_alternate</span>
                                <input type="file" @change="handleImageUpload" accept="image/png,image/webp,image/svg+xml,image/jpeg" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-medium text-neutral-900 dark:text-neutral-50 truncate">{{ form.image ? form.image.name : 'Sustituir o cargar logotipo comercial' }}</p>
                                    <p class="text-[9px] text-neutral-400 dark:text-neutral-500 font-mono">Formatos: PNG, WEBP, SVG</p>
                                </div>
                            </div>
                            <span v-if="form.errors.image" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.image }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-neutral-50 dark:bg-neutral-950 p-3 rounded-md border border-neutral-200 dark:border-neutral-800 select-none">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_active" type="checkbox" class="w-4 h-4 border-neutral-300 dark:border-neutral-700 rounded-sm text-neutral-950 dark:text-white bg-white dark:bg-neutral-900 focus:ring-0" />
                            <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">Habilitada para Exhibición</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_featured" type="checkbox" class="w-4 h-4 border-neutral-300 dark:border-neutral-700 rounded-sm text-neutral-950 dark:text-white bg-white dark:bg-neutral-900 focus:ring-0" />
                            <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">Destacar en Home</span>
                        </label>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Reseña o Descripción Institucional</label>
                        <textarea v-model="form.description" rows="3" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-mono uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors resize-none" placeholder="Información comercial del fabricante..."></textarea>
                        <span v-if="form.errors.description" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.description }}</span>
                    </div>
                </form>

                <div class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 flex items-center justify-end gap-2 select-none shrink-0">
                    <button type="button" @click="emit('close')" class="px-3 py-1.5 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs font-bold uppercase tracking-wider transition-colors">
                        Cancelar
                    </button>
                    <button type="button" @click="submit" :disabled="form.processing" class="bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 px-4 py-1.5 border border-transparent rounded-md transition-colors text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 disabled:opacity-50">
                        <span class="material-symbols-rounded text-sm">sync</span>
                        <span>Sincronizar Firma</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>