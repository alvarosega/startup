<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    form: {
        type: Object,
        required: true
    },
    parents: {
        type: Array,
        required: true
    },
    isEdit: {
        type: Boolean,
        required: true
    }
});

defineEmits(['submit']);

const activeTab = ref('general');
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="space-y-6 max-w-4xl">
        
        <div class="flex border-b border-border select-none">
            <button 
                type="button" 
                @click="activeTab = 'general'"
                :class="[activeTab === 'general' ? 'border-primary text-primary font-bold' : 'border-transparent text-muted-foreground font-semibold hover:text-foreground']"
                class="border-b-2 px-4 py-2.5 text-xs uppercase tracking-wider transition-colors"
            >
                Parámetros Operativos
            </button>
            <button 
                type="button" 
                @click="activeTab = 'media'"
                :class="[activeTab === 'media' ? 'border-primary text-primary font-bold' : 'border-transparent text-muted-foreground font-semibold hover:text-foreground']"
                class="border-b-2 px-4 py-2.5 text-xs uppercase tracking-wider transition-colors"
            >
                Identidad Visual (Multimedia)
            </button>
            <button 
                type="button" 
                @click="activeTab = 'seo'"
                :class="[activeTab === 'seo' ? 'border-primary text-primary font-bold' : 'border-transparent text-muted-foreground font-semibold hover:text-foreground']"
                class="border-b-2 px-4 py-2.5 text-xs uppercase tracking-wider transition-colors"
            >
                Indexación Metadatos SEO
            </button>
        </div>

        <div v-show="activeTab === 'general'" class="space-y-5 bg-card border border-border p-5 rounded-md shadow-flat">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Nombre de Categoría *</label>
                    <input v-model="form.name" type="text" required class="admin-input" :class="{ 'border-error': form.errors.name }" placeholder="Bebidas Carbonatadas" />
                    <p v-if="form.errors.name" class="text-error text-xs font-medium mt-1">{{ form.errors.name }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Slug de Acceso (URL)</label>
                    <input v-model="form.slug" type="text" class="admin-input font-mono" :class="{ 'border-error': form.errors.slug }" placeholder="bebidas-carbonatadas" />
                    <p v-if="form.errors.slug" class="text-error text-xs font-medium mt-1">{{ form.errors.slug }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Jerarquía (Categoría Padre)</label>
                    <select v-model="form.parent_id" class="admin-input" :class="{ 'border-error': form.errors.parent_id }">
                        <option value="">Nodo Raíz (Nivel Superior)</option>
                        <option v-for="parent in parents" :key="parent.id" :value="parent.id">
                            {{ parent.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.parent_id" class="text-error text-xs font-medium mt-1">{{ form.errors.parent_id }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Código de Integración Externo</label>
                    <input v-model="form.external_code" type="text" class="admin-input font-mono" :class="{ 'border-error': form.errors.external_code }" placeholder="ERP-CAT-89" />
                    <p v-if="form.errors.external_code" class="text-error text-xs font-medium mt-1">{{ form.errors.external_code }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Clasificación Impositiva</label>
                    <input v-model="form.tax_classification" type="text" class="admin-input font-mono" :class="{ 'border-error': form.errors.tax_classification }" placeholder="IVA_13" />
                    <p v-if="form.errors.tax_classification" class="text-error text-xs font-medium mt-1">{{ form.errors.tax_classification }}</p>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Descripción del Nodo</label>
                <textarea v-model="form.description" rows="3" class="admin-input" :class="{ 'border-error': form.errors.description }" placeholder="Detalles de clasificación interna..."></textarea>
                <p v-if="form.errors.description" class="text-error text-xs font-medium mt-1">{{ form.errors.description }}</p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-6 pt-2">
                <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" />
                    <span class="text-xs font-bold text-foreground uppercase tracking-wide">Categoría Visible y Habilitada</span>
                </label>
                <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                    <input v-model="form.is_featured" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" />
                    <span class="text-xs font-bold text-foreground uppercase tracking-wide">Destacar en Panel Frontend</span>
                </label>
                <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                    <input v-model="form.requires_age_check" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" />
                    <span class="text-xs font-bold text-foreground uppercase tracking-wide text-warning">Requiere Verificación de Edad</span>
                </label>
            </div>
            <p v-if="form.errors.category" class="text-error text-xs font-bold mt-2 flex items-center gap-1">
                <span class="material-symbols-rounded text-sm shrink-0">error</span>
                <span>{{ form.errors.category }}</span>
            </p>
        </div>

        <div v-show="activeTab === 'media'" class="space-y-5 bg-card border border-border p-5 rounded-md shadow-flat">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Imagen de Banner (Máx 2MB)</label>
                    <input 
                        type="file" 
                        accept="image/*"
                        class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-secondary-foreground file:cursor-pointer hover:file:bg-neutral-200" 
                        @input="form.image = $event.target.files[0]"
                    />
                    <p v-if="form.errors.image" class="text-error text-xs font-medium mt-1">{{ form.errors.image }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Icono de Acceso (Máx 512KB)</label>
                    <input 
                        type="file" 
                        accept="image/*"
                        class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-secondary-foreground file:cursor-pointer hover:file:bg-neutral-200" 
                        @input="form.icon = $event.target.files[0]"
                    />
                    <p v-if="form.errors.icon" class="text-error text-xs font-medium mt-1">{{ form.errors.icon }}</p>
                </div>
            </div>

            <div class="space-y-1.5 max-w-xs">
                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Color de Fondo de Marca (HEX)</label>
                <div class="flex gap-2">
                    <input v-model="form.bg_color" type="color" class="w-10 h-9 p-0 bg-transparent border-0 cursor-pointer" />
                    <input v-model="form.bg_color" type="text" class="admin-input font-mono uppercase" max="7" placeholder="#FFFFFF" />
                </div>
                <p v-if="form.errors.bg_color" class="text-error text-xs font-medium mt-1">{{ form.errors.bg_color }}</p>
            </div>
        </div>

        <div v-show="activeTab === 'seo'" class="space-y-5 bg-card border border-border p-5 rounded-md shadow-flat">
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Título de Indexación Meta SEO (Máx 60 carac.)</label>
                <input v-model="form.seo_title" type="text" max="60" class="admin-input" :class="{ 'border-error': form.errors.seo_title }" placeholder="Comprar Bebidas Online | E-Commerce" />
                <p v-if="form.errors.seo_title" class="text-error text-xs font-medium mt-1">{{ form.errors.seo_title }}</p>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Descripción Meta SEO (Máx 160 carac.)</label>
                <textarea v-model="form.seo_description" rows="3" max="160" class="admin-input" :class="{ 'border-error': form.errors.seo_description }" placeholder="Adquiera una amplia selección de bebidas carbonatadas con envío inmediato..."></textarea>
                <p v-if="form.errors.seo_description" class="text-error text-xs font-medium mt-1">{{ form.errors.seo_description }}</p>
            </div>
        </div>

        <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
            <Link 
                :href="route('admin.catalog.categories.index')" 
                class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200"
                :class="{ 'pointer-events-none opacity-50': form.processing }"
            >
                Cancelar
            </Link>
            
            <button
                type="submit"
                :disabled="form.processing"
                class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider"
            >
                <span class="material-symbols-rounded text-base shrink-0">save</span>
                <span>{{ form.processing ? 'Persistiendo Estructura...' : 'Guardar Parámetros' }}</span>
            </button>
        </div>
    </form>
</template>