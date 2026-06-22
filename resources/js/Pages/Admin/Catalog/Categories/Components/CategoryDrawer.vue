<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    category: Object,
    parents: Array
});

const emit = defineEmits(['close']);

const imagePreview = ref(null);
const iconPreview = ref(null);
const isManualSlug = ref(false);

const availableParents = computed(() => {
    if (!props.category) return props.parents;
    return props.parents.filter(parent => parent.id !== props.category.id);
});

const isParentSelectDisabled = computed(() => {
    return props.category && props.category.children && props.category.children.length > 0;
});

const form = useForm({
    name: '',
    slug: '',
    parent_id: '',
    external_code: '',
    tax_classification: '',
    requires_age_check: false,
    is_active: true,
    is_featured: false,
    bg_color: '#6366F1',
    description: '',
    seo_title: '',
    seo_description: '',
    image: null,
    icon: null
});

// Watcher para autogeneración inteligente del slug operativo
watch(() => form.name, (newName) => {
    if (!isManualSlug.value && !props.category) {
        form.slug = newName
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
    }
});

// Previsualización de archivos en memoria local
const handleImageUpload = (event, type) => {
    const file = event.target.files[0];
    if (!file) return;

    if (type === 'image') {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    } else if (type === 'icon') {
        form.icon = file;
        iconPreview.value = URL.createObjectURL(file);
    }
};

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        form.clearErrors();
        if (props.category) {
            isManualSlug.value = true;
            form.name = props.category.name;
            form.slug = props.category.slug;
            form.parent_id = props.category.parent_id || '';
            form.external_code = props.category.external_code || '';
            form.tax_classification = props.category.tax_classification || '';
            form.requires_age_check = !!props.category.requires_age_check;
            form.is_active = !!props.category.is_active;
            form.is_featured = !!props.category.is_featured;
            form.bg_color = props.category.bg_color || '#6366F1';
            form.description = props.category.description || '';
            form.seo_title = props.category.seo_title || '';
            form.seo_description = props.category.seo_description || '';
            form.image = null;
            form.icon = null;
            imagePreview.value = props.category.image_url || null;
            iconPreview.value = props.category.icon_url || null;
        } else {
            isManualSlug.value = false;
            form.reset();
            imagePreview.value = null;
            iconPreview.value = null;
        }
    }
});

const submit = () => {
    if (props.category) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('admin.catalog.categories.update', props.category.id), {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(route('admin.catalog.categories.store'), {
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
                        <span class="material-symbols-rounded text-neutral-900 dark:text-white text-xl">folder_managed</span>
                        <div>
                            <h2 class="text-sm font-bold uppercase tracking-tight text-neutral-900 dark:text-neutral-50">
                                {{ category ? 'Modificar Atributos de Nodo' : 'Materializar Nueva Categoría' }}
                            </h2>
                            <p class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mt-0.5">Configuración atómica del catálogo</p>
                        </div>
                    </div>
                    <button @click="emit('close')" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center border border-neutral-200 dark:border-neutral-800">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submit" class="flex-1 overflow-y-auto p-5 space-y-5">
                    
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Denominación del Nodo *</label>
                            <input v-model="form.name" type="text" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-mono uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" placeholder="Ej. LÁCTEOS" />
                            <span v-if="form.errors.name" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.name }}</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Slug Operativo (URL)</label>
                                <input v-model="form.slug" type="text" @input="isManualSlug = true" placeholder="Auto-morfismo si se omite" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-mono text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" />
                                <span v-if="form.errors.slug" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.slug }}</span>
                            </div>

                            <div class="space-y-1.5 select-none">
                                <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Dependencia Jerárquica</label>
                                <select v-model="form.parent_id" :disabled="isParentSelectDisabled" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-bold uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors disabled:opacity-50">
                                    <option value="">Establecer como Raíz (Pasillo)</option>
                                    <option v-for="parent in availableParents" :key="parent.id" :value="parent.id">
                                        {{ parent.name.toUpperCase() }}
                                    </option>
                                </select>
                                <p v-if="isParentSelectDisabled" class="text-[9px] font-mono font-bold text-amber-600 dark:text-amber-400 uppercase mt-1">
                                    Bloqueo: El nodo ya posee sub-categorías asignadas.
                                </p>
                                <span v-if="form.errors.parent_id" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.parent_id }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 bg-neutral-50 dark:bg-neutral-950 p-3 rounded-md border border-neutral-200 dark:border-neutral-800 select-none">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_active" type="checkbox" class="w-4 h-4 border-neutral-300 dark:border-neutral-700 rounded-sm text-neutral-950 dark:text-white bg-white dark:bg-neutral-900 focus:ring-0" />
                            <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">Venta Activa</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_featured" type="checkbox" class="w-4 h-4 border-neutral-300 dark:border-neutral-700 rounded-sm text-neutral-950 dark:text-white bg-white dark:bg-neutral-900 focus:ring-0" />
                            <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">Destacada</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.requires_age_check" type="checkbox" class="w-4 h-4 border-neutral-300 dark:border-neutral-700 rounded-sm text-neutral-950 dark:text-white bg-white dark:bg-neutral-900 focus:ring-0" />
                            <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">Control +18</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Código de Sincronización ERP</label>
                            <input v-model="form.external_code" type="text" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-mono uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" placeholder="N/A" />
                            <span v-if="form.errors.external_code" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.external_code }}</span>
                        </div>
                        <div class="space-y-1.5 select-none">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Firma de Color</label>
                            <div class="flex items-center gap-2 border border-neutral-200 dark:border-neutral-800 rounded-md px-2 bg-neutral-50 dark:bg-neutral-950 h-[34px]">
                                <input v-model="form.bg_color" type="color" class="w-6 h-6 border-0 p-0 cursor-pointer bg-transparent" />
                                <span class="text-xs font-mono font-bold uppercase text-neutral-900 dark:text-neutral-50">{{ form.bg_color }}</span>
                            </div>
                            <span v-if="form.errors.bg_color" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.bg_color }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 select-none">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Banner Principal (Góndola)</label>
                            <div class="border border-dashed border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 rounded-md p-3 flex items-center gap-3 relative min-h-[64px]">
                                <input type="file" @change="handleImageUpload($event, 'image')" accept="image/webp,image/jpeg,image/png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                                <img v-if="imagePreview" :src="imagePreview" class="w-10 h-10 object-cover rounded border border-neutral-200 dark:border-neutral-800 shrink-0" />
                                <span v-else class="material-symbols-rounded text-neutral-400 text-xl shrink-0">landscape</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-medium text-neutral-900 dark:text-neutral-50 truncate">{{ form.image ? form.image.name : 'Seleccionar Banner (Max 2MB)' }}</p>
                                    <p class="text-[9px] text-neutral-400 dark:text-neutral-500 font-mono">Formatos estandarizados de imagen</p>
                                </div>
                            </div>
                            <span v-if="form.errors.image" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.image }}</span>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Ícono de Navegación App</label>
                            <div class="border border-dashed border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 rounded-md p-3 flex items-center gap-3 relative min-h-[64px]">
                                <input type="file" @change="handleImageUpload($event, 'icon')" accept="image/webp,image/jpeg,image/png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                                <img v-if="iconPreview" :src="iconPreview" class="w-10 h-10 object-cover rounded border border-neutral-200 dark:border-neutral-800 shrink-0" />
                                <span v-else class="material-symbols-rounded text-neutral-400 text-xl shrink-0">category</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-medium text-neutral-900 dark:text-neutral-50 truncate">{{ form.icon ? form.icon.name : 'Seleccionar Ícono (Max 512KB)' }}</p>
                                    <p class="text-[9px] text-neutral-400 dark:text-neutral-500 font-mono">Imágenes transparentes optimizadas</p>
                                </div>
                            </div>
                            <span v-if="form.errors.icon" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.icon }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Descripción Operacional</label>
                        <textarea v-model="form.description" rows="2" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-mono uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors resize-none" placeholder="Metadatos informativos internos..."></textarea>
                        <span v-if="form.errors.description" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.description }}</span>
                    </div>

                    <div class="pt-4 border-t border-neutral-200 dark:border-neutral-800 space-y-4">
                        <div class="flex items-center gap-2 select-none">
                            <span class="material-symbols-rounded text-neutral-400 text-lg">gavel</span>
                            <h3 class="text-[10px] font-mono font-black text-neutral-400 dark:text-neutral-500 tracking-wider uppercase">Vectores Fiscales & Indexación SEO</h3>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Clasificación Impuestos / ERP Matrix</label>
                            <input v-model="form.tax_classification" type="text" placeholder="Ej. IVA_GENERAL_BO" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs font-mono uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" />
                            <span v-if="form.errors.tax_classification" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.tax_classification }}</span>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Título de Indexación Meta SEO</label>
                                <input v-model="form.seo_title" type="text" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" placeholder="Denominación para motores de búsqueda" />
                                <span v-if="form.errors.seo_title" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.seo_title }}</span>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold uppercase text-neutral-400 dark:text-neutral-500 tracking-wider">Descripción de Indexación Meta SEO</label>
                                <textarea v-model="form.seo_description" rows="2" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors resize-none" placeholder="Resumen estratégico de indexación orgánica..."></textarea>
                                <span v-if="form.errors.seo_description" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5">{{ form.errors.seo_description }}</span>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 flex items-center justify-end gap-2 select-none shrink-0">
                    <button type="button" @click="emit('close')" class="px-3 py-1.5 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs font-bold uppercase tracking-wider transition-colors">
                        Cancelar
                    </button>
                    <button type="button" @click="submit" :disabled="form.processing" class="bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 px-4 py-1.5 border border-transparent rounded-md transition-colors text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 disabled:opacity-50">
                        <span class="material-symbols-rounded text-sm">sync</span>
                        <span>Sincronizar Nodo</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>