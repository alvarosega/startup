<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    form: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
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
                Configuración Estructural
            </button>
            <button 
                type="button" 
                @click="activeTab = 'media'"
                :class="[activeTab === 'media' ? 'border-primary text-primary font-bold' : 'border-transparent text-muted-foreground font-semibold hover:text-foreground']"
                class="border-b-2 px-4 py-2.5 text-xs uppercase tracking-wider transition-colors"
            >
                Identidad y Soporte Web
            </button>
        </div>

        <div v-show="activeTab === 'general'" class="space-y-5 bg-card border border-border p-5 rounded-md shadow-flat">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Nombre de la Marca *</label>
                    <input v-model="form.name" type="text" required class="admin-input" :class="{ 'border-error': form.errors.name }" placeholder="Coca-Cola" />
                    <p v-if="form.errors.name" class="text-error text-xs font-medium mt-1">{{ form.errors.name }}</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Slug Dinámico (URL)</label>
                    <input v-model="form.slug" type="text" class="admin-input font-mono" :class="{ 'border-error': form.errors.slug }" placeholder="coca-cola" />
                    <p v-if="form.errors.slug" class="text-error text-xs font-medium mt-1">{{ form.errors.slug }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Socio Comercial (Proveedor) *</label>
                    <select v-model="form.provider_id" required class="admin-input" :class="{ 'border-error': form.errors.provider_id }">
                        <option value="" disabled>Seleccione proveedor...</option>
                        <option v-for="p in options.providers" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <p v-if="form.errors.provider_id" class="text-error text-xs font-medium mt-1">{{ form.errors.provider_id }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Categoría de Enlace *</label>
                    <select v-model="form.category_id" required class="admin-input" :class="{ 'border-error': form.errors.category_id }">
                        <option value="" disabled>Seleccione categoría...</option>
                        <option v-for="c in options.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <p v-if="form.errors.category_id" class="text-error text-xs font-medium mt-1">{{ form.errors.category_id }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Jerarquía Corporativa (Corporación Padre)</label>
                    <select v-model="form.parent_id" class="admin-input" :class="{ 'border-error': form.errors.parent_id }">
                        <option value="">Marca de Nivel Superior (Matriz)</option>
                        <option v-for="b in options.parents" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                    <p v-if="form.errors.parent_id" class="text-error text-xs font-medium mt-1">{{ form.errors.parent_id }}</p>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Resumen Informativo / Descripción</label>
                <textarea v-model="form.description" rows="3" class="admin-input" :class="{ 'border-error': form.errors.description }" placeholder="Breve reseña sobre la marca o su alcance comercial..."></textarea>
                <p v-if="form.errors.description" class="text-error text-xs font-medium mt-1">{{ form.errors.description }}</p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-6 pt-2">
                <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" />
                    <span class="text-xs font-bold text-foreground uppercase tracking-wide">Habilitar Marca para Operaciones comerciales</span>
                </label>
                <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                    <input v-model="form.is_featured" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" />
                    <span class="text-xs font-bold text-foreground uppercase tracking-wide">Posicionar como Marca Destacada</span>
                </label>
            </div>
        </div>

        <div v-show="activeTab === 'media'" class="space-y-5 bg-card border border-border p-5 rounded-md shadow-flat">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Portal Web Oficial (URL)</label>
                    <input v-model="form.website" type="url" class="admin-input font-mono" :class="{ 'border-error': form.errors.website }" placeholder="https://www.coca-cola.com" />
                    <p v-if="form.errors.website" class="text-error text-xs font-medium mt-1">{{ form.errors.website }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Fondo de Contraste Corporativo (HEX)</label>
                    <div class="flex gap-2">
                        <input v-model="form.bg_color" type="color" class="w-10 h-9 p-0 bg-transparent border-0 cursor-pointer" />
                        <input v-model="form.bg_color" type="text" class="admin-input font-mono uppercase" max="7" placeholder="#FFFFFF" />
                    </div>
                    <p v-if="form.errors.bg_color" class="text-error text-xs font-medium mt-1">{{ form.errors.bg_color }}</p>
                </div>
            </div>

            <div class="space-y-2 max-w-md">
                <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Emblema de Marca / Logotipo (WebP, JPG, PNG - Máx 2MB)</label>
                <input 
                    type="file" 
                    accept="image/webp, image/jpeg, image/png"
                    class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-secondary-foreground file:cursor-pointer hover:file:bg-neutral-200" 
                    @input="form.image = $event.target.files[0]"
                />
                <p v-if="form.errors.image" class="text-error text-xs font-medium mt-1">{{ form.errors.image }}</p>
            </div>
        </div>

        <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
            <Link 
                :href="route('admin.catalog.brands.index')" 
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
                <span>{{ form.processing ? 'Sincronizando Archivos...' : 'Guardar Parámetros' }}</span>
            </button>
        </div>
    </form>
</template>