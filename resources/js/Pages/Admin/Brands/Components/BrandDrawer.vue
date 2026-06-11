<script setup>
import { watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, Save, AlertCircle, Globe } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    brand: Object,
    options: Object
});

const emit = defineEmits(['close']);

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

// Observador de doble factor: elimina condiciones de carrera en el canvas de marcas
watch(
    () => [props.show, props.brand],
    ([isOpen, currentBrand]) => {
        if (!isOpen) return;

        if (currentBrand) {
            form.name = currentBrand.name;
            form.slug = currentBrand.slug;
            form.parent_id = currentBrand.parent_id || '';
            form.provider_id = props.options.providers.find(p => p.name === currentBrand.provider_name)?.id || '';
            form.category_id = props.options.categories.find(c => c.name === currentBrand.category_name)?.id || '';
            form.website = currentBrand.website || '';
            form.description = currentBrand.description || '';
            form.is_active = currentBrand.is_active;
            form.is_featured = currentBrand.is_featured;
            form.bg_color = currentBrand.bg_color;
            form.image = null;
        } else {
            // Vaciado manual explícito inmitigable
            form.name = '';
            form.slug = '';
            form.parent_id = '';
            form.provider_id = props.options.providers[0]?.id || '';
            form.category_id = props.options.categories[0]?.id || '';
            form.website = '';
            form.description = '';
            form.is_active = true;
            form.is_featured = false;
            form.bg_color = '#6366F1';
            form.image = null;
            form.clearErrors();
        }
    },
    { immediate: true }
);

// Algoritmo Frontend: Excluirse de la lista de padres potenciales para evitar bucles cíclicos
const filteredParents = computed(() => {
    if (!props.brand) return props.options.parents;
    return props.options.parents.filter(parent => parent.id !== props.brand.id);
});

const submit = () => {
    if (props.brand) {
        // Enmascaramiento Mandatorio para transmisiones multipart en Laravel (PUT Bypass)
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('admin.brands.update', props.brand.id), {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(route('admin.brands.store'), {
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
                            {{ brand ? 'Modificar Atributos de Firma' : 'Registrar Nueva Firma Comercial' }}
                        </h2>
                        <p class="text-xs text-muted-foreground">Configuración y enlace atómico de la marca</p>
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
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Nombre de la Marca *</label>
                            <input v-model="form.name" type="text" required class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Identificador Slug (URL)</label>
                            <input v-model="form.slug" type="text" placeholder="Autogenerado por conversión" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none" />
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Color de Resplandor (Hex)</label>
                            <div class="flex gap-2">
                                <input v-model="form.bg_color" type="color" class="w-10 h-9 bg-background border border-border rounded-lg p-0.5 cursor-pointer outline-none" />
                                <input v-model="form.bg_color" type="text" pattern="^#([A-Fa-f0-9]{6})$" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground font-mono focus:ring-1 focus:ring-primary outline-none" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-muted/10 p-4 rounded-xl border border-border/60">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Proveedor Corporativo *</label>
                            <select v-model="form.provider_id" required class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none">
                                <option v-for="prov in options.providers" :key="prov.id" :value="prov.id">{{ prov.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Pasillo Principal *</label>
                            <select v-model="form.category_id" required class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none">
                                <option v-for="cat in options.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>

                        <div class="col-span-2 border-t border-border/60 pt-3 mt-1">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Estructura Umbrella (Marca Padre)</label>
                            <select v-model="form.parent_id" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none">
                                <option value="">Ninguna — Es una firma raíz independiente</option>
                                <option v-for="parent in filteredParents" :key="parent.id" :value="parent.id">{{ parent.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Sitio Web Oficial</label>
                            <div class="flex items-center bg-background border border-border rounded-lg px-3 focus-within:ring-1 focus-within:ring-primary">
                                <Globe :size="16" class="text-muted-foreground mr-2 shrink-0" />
                                <input v-model="form.website" type="url" placeholder="https://ejemplo.com" class="w-full bg-transparent border-none p-0 py-2 text-sm text-foreground outline-none focus:ring-0" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Logotipo Comercial (WebP/PNG — Max 2MB)</label>
                            <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-muted/20 p-4 rounded-xl border border-border/60">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_active" type="checkbox" class="rounded border-border text-primary focus:ring-primary bg-background w-4 h-4" />
                            <span class="text-sm text-foreground select-none">Habilitada para Venta</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_featured" type="checkbox" class="rounded border-border text-primary focus:ring-primary bg-background w-4 h-4" />
                            <span class="text-sm text-foreground select-none">Destacar en Vitrina</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Reseña o Descripción Institucional</label>
                        <textarea v-model="form.description" rows="3" class="w-full bg-background border border-border rounded-lg px-3 py-2 text-sm text-foreground focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
                    </div>
                </form>

                <div class="px-6 py-4 bg-muted/30 border-t border-border flex items-center justify-end gap-3">
                    <button type="button" @click="emit('close')" class="px-4 py-2 bg-background border border-border text-sm font-medium rounded-lg hover:bg-muted text-foreground transition-colors">
                        Cancelar
                    </button>
                    <button type="button" @click="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 shadow-sm transition-colors disabled:opacity-50">
                        <Save :size="16" />
                        Sincronizar Firma
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>