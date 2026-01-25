<!-- resources/js/Components/CategoryCard.vue -->
<script setup>
import { 
    Folder, AlertCircle, Eye, EyeOff, Hash, CornerDownRight, 
    Layers, Shield, Star, Calendar, TrendingUp, BarChart3,
    FileText, Image as ImageIcon, Globe, Lock
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    category: {
        type: Object,
        required: true
    },
    canManage: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['delete']);

// Información calculada
const statusColor = computed(() => 
    props.category.is_active ? 'success' : 'error'
);

const statusIcon = computed(() => 
    props.category.is_active ? Eye : EyeOff
);

const typeIcon = computed(() => 
    props.category.parent_id ? CornerDownRight : Layers
);

const typeLabel = computed(() => 
    props.category.parent_id ? 'Subcategoría' : 'Categoría Padre'
);

const typeColor = computed(() => 
    props.category.parent_id ? 'secondary' : 'primary'
);
</script>

<template>
    <div class="card group hover:shadow-lg hover:border-primary/50 transition-all duration-300 flex flex-col h-full overflow-hidden relative">
        <!-- Badge de estado en esquina superior derecha -->
        <div class="absolute top-3 right-3 z-10">
            <span :class="`badge badge-${statusColor}`" class="!px-2 !py-1 !text-xs font-bold uppercase">
                <component :is="statusIcon" class="inline mr-1" :size="10" />
                {{ category.is_active ? 'Activo' : 'Inactivo' }}
            </span>
        </div>

        <!-- Indicador de restricción de edad -->
        <div v-if="category.requires_age_check" 
             class="absolute top-3 left-3 z-10">
            <span class="badge badge-error !px-2 !py-1 !text-xs font-bold">
                <AlertCircle :size="10" class="mr-1" /> +18
            </span>
        </div>

        <!-- Header con imagen/icono y tipo -->
        <div class="card-header !pb-3 !pt-6">
            <div class="flex items-center gap-4">
                <!-- Imagen/Icono -->
                <div class="relative">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-primary/10 to-secondary/10 border-2 border-input flex items-center justify-center overflow-hidden group-hover:scale-105 transition-transform duration-300">
                        <img v-if="category.image_url" 
                             :src="category.image_url" 
                             class="w-full h-full object-cover">
                        <div v-else class="flex items-center justify-center p-3">
                            <component :is="typeIcon" :size="24" class="text-primary" />
                        </div>
                    </div>
                    
                    <!-- Badge de tipo -->
                    <div class="absolute -bottom-2 -right-2">
                        <span :class="`badge badge-${typeColor}`" class="!px-3 !py-1 !text-xs font-bold shadow-sm">
                            {{ typeLabel }}
                        </span>
                    </div>
                </div>

                <!-- Información principal -->
                <div class="flex-1 min-w-0">
                    <h3 class="font-display font-bold text-xl text-foreground leading-tight line-clamp-2 group-hover:text-primary transition-colors">
                        {{ category.name }}
                    </h3>
                    
                    <!-- Código y slug -->
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <div v-if="category.external_code" class="flex items-center gap-1 text-xs font-mono bg-muted/50 px-2 py-1 rounded">
                            <Hash :size="10" />
                            <span class="text-foreground font-bold">{{ category.external_code }}</span>
                        </div>
                        
                        <div class="flex items-center gap-1 text-xs text-muted-foreground">
                            <Globe :size="10" />
                            <span class="font-medium">/{{ category.slug }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="card-content !pt-0 space-y-4">
            <!-- Descripción -->
            <div v-if="category.description" class="bg-muted/20 p-3 rounded-lg border border-input/30">
                <p class="text-sm text-muted-foreground line-clamp-3">
                    {{ category.description }}
                </p>
            </div>

            <!-- Estadísticas y metadatos -->
            <div class="grid grid-cols-2 gap-3">
                <!-- Subcategorías -->
                <div class="flex items-center gap-2 text-xs">
                    <div class="p-2 rounded-lg bg-primary/10 text-primary">
                        <Layers :size="14" />
                    </div>
                    <div>
                        <div class="font-bold text-foreground">{{ category.children_count || 0 }}</div>
                        <div class="text-muted-foreground">Subcat.</div>
                    </div>
                </div>

                <!-- Fecha de creación -->
                <div v-if="category.created_at" class="flex items-center gap-2 text-xs">
                    <div class="p-2 rounded-lg bg-secondary/10 text-secondary">
                        <Calendar :size="14" />
                    </div>
                    <div>
                        <div class="font-bold text-foreground">
                            {{ new Date(category.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' }) }}
                        </div>
                        <div class="text-muted-foreground">Creado</div>
                    </div>
                </div>

                <!-- Destacado -->
                <div v-if="category.is_featured" class="col-span-2 flex items-center gap-2 text-xs">
                    <div class="p-2 rounded-lg bg-warning/10 text-warning">
                        <Star :size="14" />
                    </div>
                    <div class="flex-1">
                        <div class="font-bold text-foreground">Destacada</div>
                        <div class="text-muted-foreground">Visible en inicio</div>
                    </div>
                </div>

                <!-- Clasificación fiscal -->
                <div v-if="category.tax_classification" class="col-span-2 flex items-center gap-2 text-xs mt-2 pt-2 border-t border-input/30">
                    <div class="p-2 rounded-lg bg-info/10 text-info">
                        <FileText :size="14" />
                    </div>
                    <div class="flex-1">
                        <div class="font-bold text-foreground line-clamp-1" :title="category.tax_classification">
                            {{ category.tax_classification }}
                        </div>
                        <div class="text-muted-foreground">Clasificación fiscal</div>
                    </div>
                </div>
            </div>

            <!-- SEO Preview -->
            <div v-if="category.seo_title || category.seo_description" class="mt-3 pt-3 border-t border-input/30">
                <div class="flex items-center gap-2 mb-2">
                    <TrendingUp :size="12" class="text-success" />
                    <span class="text-xs font-bold text-success uppercase tracking-wide">SEO</span>
                </div>
                
                <div v-if="category.seo_title" class="text-xs font-medium text-foreground line-clamp-2 mb-1">
                    {{ category.seo_title }}
                </div>
                
                <div v-if="category.seo_description" class="text-xs text-muted-foreground line-clamp-2">
                    {{ category.seo_description }}
                </div>
            </div>
        </div>

        <!-- Footer con acciones y padre -->
        <div class="card-footer !pt-4 bg-gradient-to-t from-muted/30 to-transparent">
            <div class="flex justify-between items-center">
                <!-- Información del padre -->
                <div class="text-xs text-muted-foreground">
                    <div v-if="category.parent" class="flex items-center gap-2">
                        <div class="flex items-center gap-1">
                            <CornerDownRight :size="10" class="text-secondary" />
                            <span class="font-medium text-foreground">{{ category.parent.name }}</span>
                        </div>
                    </div>
                    <div v-else class="flex items-center gap-2">
                        <Shield :size="10" class="text-primary" />
                        <span class="font-medium text-foreground">Categoría Raíz</span>
                    </div>
                </div>

                <!-- Acciones -->
                <div v-if="canManage" class="flex items-center gap-1">
                    <Link :href="route('admin.categories.edit', category.id)" 
                          class="btn btn-ghost btn-sm !px-3 !py-1.5 hover:bg-primary/10 hover:text-primary transition-all group/edit"
                          title="Editar categoría">
                        <svg class="w-4 h-4 group-hover/edit:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </Link>
                    
                    <button @click="$emit('delete', category)" 
                            class="btn btn-ghost btn-sm !px-3 !py-1.5 hover:bg-error/10 hover:text-error transition-all group/delete"
                            title="Eliminar categoría">
                        <svg class="w-4 h-4 group-hover/delete:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>