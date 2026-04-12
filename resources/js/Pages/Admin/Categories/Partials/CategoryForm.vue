<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
//import ImageUploader from '@/Components/ImageUploader.vue';
import { Save, AlertOctagon, Info, ChevronLeft, LayoutGrid, Database, Share2 } from 'lucide-vue-next';

const props = defineProps({
    category: Object,
    parents: Array
});

const isEdit = computed(() => !!props.category);
const catData = computed(() => props.category?.data || props.category);

// Filtrado preventivo: No permitir seleccionarse a sí mismo como padre
const availableParents = computed(() => {
    if (!isEdit.value) return props.parents;
    return props.parents.filter(p => p.id !== catData.value.id);
});

const form = useForm({
    _method: isEdit.value ? 'PUT' : 'POST',
    name: catData.value?.name || '',
    parent_id: catData.value?.parent_id || '',
    external_code: catData.value?.external_code || '',
    description: catData.value?.description || '',
    bg_color: catData.value?.bg_color || '#3b82f6',
    tax_classification: catData.value?.tax_classification || '',
    requires_age_check: !!catData.value?.requires_age_check,
    is_active: isEdit.value ? !!catData.value?.is_active : true,
    is_featured: !!catData.value?.is_featured,
    image: null,
    icon: null,
    seo_title: catData.value?.seo_title || '',
    seo_description: catData.value?.seo_description || '',
    version: catData.value?.version || 0, // OBLIGATORIO: Control de Concurrencia
});

const submit = () => {
    const url = isEdit.value 
        ? route('admin.categories.update', catData.value.id) 
        : route('admin.categories.store');

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center bg-card p-4 border border-border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 text-primary border border-primary/20">
                    <LayoutGrid :size="20" />
                </div>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-tighter">
                        {{ isEdit ? 'Actualizar Nodo Jerárquico' : 'Definir Nueva Categoría' }}
                    </h2>
                    <p class="text-[10px] font-mono text-muted-foreground uppercase italic">
                        Protocolo: Supply_Chain_Hierarchy_v1.2 // Version: {{ form.version }}
                    </p>
                </div>
            </div>
            <Link :href="route('admin.categories.index')" class="text-[10px] font-mono font-bold hover:text-primary transition-colors flex items-center gap-2">
                <ChevronLeft :size="14" /> REGRESAR_AL_RADAR
            </Link>
        </div>

        <div v-if="form.hasErrors" class="bg-destructive/10 border border-destructive p-4">
            <div class="flex items-start gap-3 text-destructive">
                <AlertOctagon :size="18" />
                <div class="flex-1">
                    <h3 class="text-xs font-black uppercase">Fallo de Validación Atómica</h3>
                    <ul class="mt-1 space-y-1">
                        <li v-for="(error, field) in form.errors" :key="field" class="text-[9px] font-mono uppercase italic">
                            // ERROR_{{ field.toUpperCase() }}: {{ error }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-card border border-border p-5 space-y-4 relative">
                        <div class="text-[10px] font-mono font-bold text-primary flex items-center gap-2 mb-2">
                            <Database :size="14" /> IDENTIDAD_MAESTRA
                        </div>
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Nombre de Categoría *</label>
                                <input v-model="form.name" type="text" class="w-full bg-background border border-border p-2 text-sm font-bold uppercase outline-none focus:border-primary" />
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Categoría Superior (Parent)</label>
                                    <select v-model="form.parent_id" class="w-full bg-background border border-border p-2 text-xs uppercase outline-none focus:border-primary appearance-none">
                                        <option value="">-- CATEGORÍA RAÍZ (ROOT) --</option>
                                        <option v-for="p in availableParents" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Código ERP / Externo</label>
                                    <input v-model="form.external_code" type="text" class="w-full bg-background border border-border p-2 text-xs font-mono outline-none focus:border-primary uppercase" />
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Descripción Técnica</label>
                                <textarea v-model="form.description" rows="3" class="w-full bg-background border border-border p-2 text-xs resize-none outline-none focus:border-primary"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-muted/20 border border-border p-5 space-y-4">
                        <div class="text-[10px] font-mono font-bold text-muted-foreground flex items-center gap-2 mb-2">
                            <Share2 :size="14" /> OPTIMIZACIÓN_BUSQUEDA
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <input v-model="form.seo_title" type="text" placeholder="SEO_TITLE" class="w-full bg-background border border-border p-2 text-xs outline-none focus:border-primary uppercase" />
                            <textarea v-model="form.seo_description" placeholder="SEO_DESCRIPTION" class="w-full bg-background border border-border p-2 text-xs outline-none focus:border-primary resize-none uppercase" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-card border border-border p-5 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Banner Principal</label>
                            <ImageUploader v-model="form.image" :existing-image="catData?.image_url" class="h-32 border-dashed border-2 border-border" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Icono de Navegación</label>
                            <ImageUploader v-model="form.icon" :existing-image="catData?.icon_url" class="h-20 border-dashed border-2 border-border" />
                        </div>
                    </div>

                    <div class="bg-card border border-border p-5 space-y-4">
                        <div class="text-[10px] font-mono font-bold text-muted-foreground flex items-center gap-2 mb-2">
                            <Info :size="14" /> ATRIBUTOS_DE_ESTADO
                        </div>
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-primary" />
                                <span class="text-[10px] font-bold uppercase group-hover:text-primary transition-colors">Estado Activo (Visibilidad)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" v-model="form.is_featured" class="w-4 h-4 accent-primary" />
                                <span class="text-[10px] font-bold uppercase group-hover:text-primary transition-colors">Categoría Destacada (Home)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" v-model="form.requires_age_check" class="w-4 h-4 accent-primary" />
                                <span class="text-[10px] font-bold uppercase group-hover:text-primary transition-colors">Restricción de Edad (+18)</span>
                            </label>
                        </div>
                        <div class="pt-4 border-t border-border space-y-2">
                            <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Color de Identidad</label>
                            <input v-model="form.bg_color" type="color" class="w-full h-10 bg-background border border-border p-1 cursor-pointer" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-primary/5 border border-primary/20 p-6 flex justify-between items-center">
                <div class="text-[9px] font-mono text-muted-foreground uppercase italic">
                    Sincronización Atómica // Hostinger_Shared_Ready
                </div>
                <button type="submit" :disabled="form.processing" class="px-12 py-3 bg-primary text-primary-foreground font-black text-xs uppercase tracking-widest flex items-center gap-3 hover:opacity-90 disabled:opacity-50 transition-all">
                    <Save v-if="!form.processing" :size="16" />
                    <span v-else class="w-4 h-4 border-2 border-primary-foreground/30 border-t-primary-foreground animate-spin rounded-full"></span>
                    {{ isEdit ? 'ACTUALIZAR_NODO' : 'REGISTRAR_NODO' }}
                </button>
            </div>
        </form>
    </div>
</template>