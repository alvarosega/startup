<script setup>
import { watch, ref, computed } from 'vue'; // Se añade computed
import { useForm } from '@inertiajs/vue3';
import { X, Save, AlertCircle, ChevronDown, ChevronUp } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    category: Object,
    parents: Array
});

// Algoritmo Frontend: Excluir el nodo actual para impedir autorreferencia
const availableParents = computed(() => {
    if (!props.category) return props.parents;
    return props.parents.filter(parent => parent.id !== props.category.id);
});

// Algoritmo Frontend: Congelar si el nodo ya actúa como madre en el catálogo
const isParentSelectDisabled = computed(() => {
    return props.category && props.category.children && props.category.children.length > 0;
});
const emit = defineEmits(['close']);
const showAdvanced = ref(false);

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

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        showAdvanced.value = false;
        if (props.category) {
            form.name = props.category.name;
            form.slug = props.category.slug;
            form.parent_id = props.category.parent_id || '';
            form.external_code = props.category.external_code || '';
            form.tax_classification = props.category.tax_classification || '';
            form.requires_age_check = props.category.requires_age_check;
            form.is_active = props.category.is_active;
            form.is_featured = props.category.is_featured;
            form.bg_color = props.category.bg_color;
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
        <div class="absolute inset-0 bg-background/80 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>

        <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-xl bg-card border-l border-border shadow-2xl flex flex-col">
                <div class="px-6 py-5 bg-muted/30 border-b border-border flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">
                            {{ category ? 'Modificar Atributos' : 'Materializar Nueva Categoría' }}
                        </h2>
                        <p class="text-xs text-muted-foreground">Configuración atómica del nodo del catálogo</p>
                    </div>
                    <button @click="emit('close')" class="text-muted-foreground hover:text-foreground p-1.5 rounded-lg hover:bg-muted transition-colors">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="flex-1 overflow-y-auto p-6 space-y-5">
                    <div v-if="Object.keys(form.errors).length" class="p-3 bg-destructive/10 border border-destructive/20 text-destructive rounded-lg space-y-1 text-sm">
                        <div v-for="(error, key) in form.errors" :key="key" class="flex items-start gap-2">
                            <AlertCircle :size="16" class="shrink-0 mt-0.5" />
                            <span>{{ error }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Denominación *</label>
                            <input v-model="form.name" type="text" required class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Slug (Identificador de URL)</label>
                            <input v-model="form.slug" type="text" placeholder="Autogenerado si se omite" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Dependencia Jerárquica</label>
                            <select 
                                v-model="form.parent_id" 
                                :disabled="isParentSelectDisabled"
                                class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none disabled:opacity-60 disabled:bg-muted disabled:cursor-not-allowed"
                            >
                                <option value="">Establecer como Categoría Raíz</option>
                                <option v-for="parent in availableParents" :key="parent.id" :value="parent.id">
                                    {{ parent.name }}
                                </option>
                            </select>
                            <p v-if="isParentSelectDisabled" class="text-[11px] text-amber-500 font-medium mt-1">
                                Bloqueado: Este pasillo ya posee subcategorías dependientes activas.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 bg-muted/20 p-4 rounded-xl border border-border/60">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_active" type="checkbox" class="rounded border-border text-primary focus:ring-primary bg-background w-4 h-4" />
                            <span class="text-sm text-foreground select-none">Activa para Venta</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_featured" type="checkbox" class="rounded border-border text-primary focus:ring-primary bg-background w-4 h-4" />
                            <span class="text-sm text-foreground select-none">Destacada</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.requires_age_check" type="checkbox" class="rounded border-border text-primary focus:ring-primary bg-background w-4 h-4" />
                            <span class="text-sm text-foreground select-none">+18 Restricción</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Código de Integración Externa</label>
                            <input v-model="form.external_code" type="text" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground font-mono focus:ring-1 focus:ring-primary outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Color Identidad</label>
                            <input v-model="form.bg_color" type="color" class="w-full h-9 bg-background border border-border rounded-lg p-0.5 cursor-pointer outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Banner Visual (Max 2MB)</label>
                            <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Ícono de Navegación (Max 512KB)</label>
                            <input type="file" @input="form.icon = $event.target.files[0]" accept="image/*" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Descripción Operacional</label>
                        <textarea v-model="form.description" rows="2" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
                    </div>

                    <div class="border-t border-border pt-4">
                        <button type="button" @click="showAdvanced = !showAdvanced" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary uppercase tracking-wider">
                            <span>Parámetros Fiscales y SEO Opcionales</span>
                            <ChevronUp v-if="showAdvanced" :size="14" />
                            <ChevronDown v-else :size="14" />
                        </button>

                        <div v-show="showAdvanced" class="mt-4 space-y-4 border border-border/60 p-4 rounded-xl bg-muted/10">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Clasificación Impuestos / ERP</label>
                                <input v-model="form.tax_classification" type="text" placeholder="Ej. IVA_GENERAL" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Título Indexación SEO</label>
                                    <input v-model="form.seo_title" type="text" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none" />
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Descripción Indexación SEO</label>
                                    <textarea v-model="form.seo_description" rows="2" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="px-6 py-4 bg-muted/30 border-t border-border flex items-center justify-end gap-3">
                    <button type="button" @click="emit('close')" class="px-4 py-2 bg-background border border-border text-sm font-medium rounded-lg hover:bg-muted text-foreground transition-colors">
                        Cancelar
                    </button>
                    <button type="button" @click="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 shadow-sm transition-colors disabled:opacity-50">
                        <Save :size="16" />
                        Sincronizar Nodo
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>