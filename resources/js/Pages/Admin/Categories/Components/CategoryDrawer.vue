<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    category: Object,
    parents: Array
});

const emit = defineEmits(['close']);

// Excluir el nodo actual para impedir autorreferencia jerárquica
const availableParents = computed(() => {
    if (!props.category) return props.parents;
    return props.parents.filter(parent => parent.id !== props.category.id);
});

// Congelar si el nodo ya actúa como madre en el catálogo
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
    bg_color: '#4b5563',
    description: '',
    seo_title: '',
    seo_description: '',
    image: null,
    icon: null
});

// Sincronización del estado de hidratación al abrir el panel lateral
import { watch } from 'vue';
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        if (props.category) {
            form.name = props.category.name;
            form.slug = props.category.slug;
            form.parent_id = props.category.parent_id || '';
            form.external_code = props.category.external_code || '';
            form.tax_classification = props.category.tax_classification || '';
            form.requires_age_check = !!props.category.requires_age_check;
            form.is_active = !!props.category.is_active;
            form.is_featured = !!props.category.is_featured;
            form.bg_color = props.category.bg_color || '#4b5563';
            form.description = props.category.description || '';
            form.seo_title = props.category.seo_title || '';
            form.seo_description = props.category.seo_description || '';
            form.image = null;
            form.icon = null;
        } else {
            form.reset();
        }
    }
});

const submit = () => {
    if (props.category) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('admin.categories.update', props.category.id), {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(route('admin.categories.store'), {
            onSuccess: () => emit('close'),
        });
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-neutral-950/40 transition-opacity" @click="emit('close')"></div>

        <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-xl bg-card border-l border-border shadow-flat flex flex-col h-full text-foreground">
                
                <div class="px-5 py-4 border-b border-border bg-neutral-50/50 dark:bg-neutral-900/20 flex items-center justify-between select-none">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-primary text-xl">folder_managed</span>
                        <div>
                            <h2 class="text-sm font-bold uppercase tracking-tight text-foreground">
                                {{ category ? 'Modificar Atributos de Nodo' : 'Materializar Nueva Categoría' }}
                            </h2>
                            <p class="text-[10px] font-mono text-muted-foreground uppercase tracking-wider mt-0.5">Configuración atómica del catálogo</p>
                        </div>
                    </div>
                    <button @click="emit('close')" class="text-muted-foreground hover:text-foreground p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center border border-border/40">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submit" class="flex-1 overflow-y-auto p-5 space-y-5 no-scrollbar">
                    
                    <div v-if="Object.keys(form.errors).length" class="p-3 bg-error/10 border border-error/20 text-error rounded-md space-y-1 text-xs select-none font-mono">
                        <div v-for="(error, key) in form.errors" :key="key" class="flex items-start gap-1.5">
                            <span class="material-symbols-rounded text-sm shrink-0 mt-0.5">error</span>
                            <span>{{ error }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Denominación del Nodo *</label>
                            <input v-model="form.name" type="text" required class="admin-input font-mono uppercase text-xs" placeholder="Ej. LÁCTEOS" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Slug Operativo (URL)</label>
                                <input v-model="form.slug" type="text" placeholder="Auto-morfismo si se omite" class="admin-input font-mono text-xs" />
                            </div>

                            <div class="space-y-1.5 select-none">
                                <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Dependencia Jerárquica</label>
                                <select v-model="form.parent_id" :disabled="isParentSelectDisabled" class="admin-input text-xs font-bold uppercase py-1.5">
                                    <option value="">Establecer como Raíz (Pasillo)</option>
                                    <option v-for="parent in availableParents" :key="parent.id" :value="parent.id">
                                        {{ parent.name.toUpperCase() }}
                                    </option>
                                </select>
                                <p v-if="isParentSelectDisabled" class="text-[9px] font-mono font-bold text-warning uppercase mt-1">
                                    Bloqueo: El nodo ya posee sub-categorías asignadas.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 bg-neutral-50/50 dark:bg-neutral-900/20 p-3 rounded-md border border-border/60 select-none">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_active" type="checkbox" class="w-4 h-4 border-input rounded-sm text-primary bg-card focus:ring-0" />
                            <span class="text-xs font-medium text-foreground">Venta Activa</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_featured" type="checkbox" class="w-4 h-4 border-input rounded-sm text-primary bg-card focus:ring-0" />
                            <span class="text-xs font-medium text-foreground">Destacada</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.requires_age_check" type="checkbox" class="w-4 h-4 border-input rounded-sm text-primary bg-card focus:ring-0" />
                            <span class="text-xs font-medium text-foreground">Control +18</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Código de Sincronización ERP</label>
                            <input v-model="form.external_code" type="text" class="admin-input font-mono text-xs uppercase" placeholder="N/A" />
                        </div>
                        <div class="space-y-1.5 select-none">
                            <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Firma de Color</label>
                            <div class="flex items-center gap-2 border border-input rounded-md px-2 bg-card h-[34px]">
                                <input v-model="form.bg_color" type="color" class="w-6 h-6 border-0 p-0 cursor-pointer bg-transparent" />
                                <span class="text-xs font-mono font-bold uppercase text-foreground">{{ form.bg_color }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 select-none">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Banner Principal (Góndola)</label>
                            <div class="border border-dashed border-border bg-neutral-50/50 dark:bg-neutral-900/10 rounded-md p-3 flex items-center gap-3 relative">
                                <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                                <span class="material-symbols-rounded text-muted-foreground text-xl shrink-0">landscape</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-medium text-foreground truncate">{{ form.image ? form.image.name : 'Seleccionar Medio (Max 2MB)' }}</p>
                                    <p class="text-[9px] text-muted-foreground font-mono">Formatos estandarizados de imagen</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Ícono de Navegación App</label>
                            <div class="border border-dashed border-border bg-neutral-50/50 dark:bg-neutral-900/10 rounded-md p-3 flex items-center gap-3 relative">
                                <input type="file" @input="form.icon = $event.target.files[0]" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                                <span class="material-symbols-rounded text-muted-foreground text-xl shrink-0">category</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-medium text-foreground truncate">{{ form.icon ? form.icon.name : 'Seleccionar Ícono (Max 512KB)' }}</p>
                                    <p class="text-[9px] text-muted-foreground font-mono">Imágenes transparentes optimizadas</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Descripción Operacional</label>
                        <textarea v-model="form.description" rows="2" class="admin-input resize-none text-xs font-mono uppercase" placeholder="Metadatos informativos internos..."></textarea>
                    </div>

                    <div class="pt-4 border-t border-border space-y-4">
                        <div class="flex items-center gap-2 select-none">
                            <span class="material-symbols-rounded text-muted-foreground text-lg">gavel</span>
                            <h3 class="text-[10px] font-mono font-black text-muted-foreground tracking-wider uppercase">Vectores Fiscales & Indexación SEO</h3>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Clasificación Impuestos / ERP Matrix</label>
                            <input v-model="form.tax_classification" type="text" placeholder="Ej. IVA_GENERAL_BO" class="admin-input font-mono text-xs uppercase" />
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Título de Indexación Meta SEO</label>
                                <input v-model="form.seo_title" type="text" class="admin-input text-xs" placeholder="Denominación para motores de búsqueda" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Descripción de Indexación Meta SEO</label>
                                <textarea v-model="form.seo_description" rows="2" class="admin-input resize-none text-xs" placeholder="Resumen estratégico de indexación orgánica..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="px-5 py-3 border-t border-border bg-neutral-50/50 dark:bg-neutral-900/20 flex items-center justify-end gap-2 select-none shrink-0">
                    <button type="button" @click="emit('close')" class="px-3 py-1.5 border border-border bg-card rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs font-bold uppercase tracking-wider transition-colors">
                        Cancelar
                    </button>
                    <button type="button" @click="submit" :disabled="form.processing" class="admin-btn-primary px-4 py-1.5 text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-sm">sync</span>
                        <span>Sincronizar Nodo</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>