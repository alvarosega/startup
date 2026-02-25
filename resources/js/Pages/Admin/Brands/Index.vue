<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, Image as ImageIcon, Factory, 
    Tag, Edit, Trash2, Globe, LayoutGrid, CheckCircle2, AlertCircle
} from 'lucide-vue-next';

// DEFINICIÓN DE PROPS (Arquitectura de Paginación)
const props = defineProps({ 
    brands: Object, // Viene de BrandResource::collection() con paginación
    filters: Object,
    can_manage: Boolean
});

const search = ref(props.filters?.search || '');

// REGLA DE RENDIMIENTO: Filtrado Server-side via Action ListBrands
watch(search, debounce((val) => {
    router.get(route('admin.brands.index'), { search: val }, { 
        preserveState: true, 
        replace: true, 
        preserveScroll: true 
    });
}, 400));

const deleteItem = (brand) => {
    if(confirm(`¿Eliminar la marca ${brand.name}? Esta acción es irreversible.`)) {
        router.delete(route('admin.brands.destroy', brand.id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Gestión de Marcas" />

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-foreground tracking-tighter flex items-center gap-3">
                    Marcas
                    <span class="text-xs font-bold text-muted-foreground bg-muted px-2 py-1 rounded-full border border-border/50">
                        {{ brands.meta?.total || 0 }}
                    </span>
                </h1>
                <p class="text-muted-foreground text-sm mt-1 font-medium">Control de portafolio y vinculación de proveedores.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <div class="relative w-full md:w-80 group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Search :size="16" class="text-muted-foreground group-focus-within:text-primary transition-colors" />
                    </div>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Buscar por nombre de marca..." 
                        class="pl-10 w-full bg-background border border-border rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm h-11"
                    >
                </div>

                <Link 
                    v-if="can_manage" 
                    :href="route('admin.brands.create')" 
                    class="btn btn-primary h-11 px-6 shadow-lg shadow-primary/20 flex items-center justify-center gap-2"
                >
                    <Plus :size="18" stroke-width="3" />
                    <span class="font-bold">Nueva Marca</span>
                </Link>
            </div>
        </div>

        <div v-if="brands.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
            <div 
                v-for="brand in brands.data" 
                :key="brand.id" 
                class="group bg-card border border-border rounded-2xl overflow-hidden hover:border-primary/40 hover:shadow-xl transition-all duration-300 flex flex-col"
            >
                <div :class="brand.is_active ? 'bg-success/10' : 'bg-destructive/10'" class="h-1.5 w-full"></div>

                <div class="p-5 flex-1">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-muted rounded-xl border border-border flex items-center justify-center shrink-0 shadow-inner group-hover:scale-105 transition-transform overflow-hidden">
                            <img 
                                v-if="brand.image_path" 
                                :src="brand.image_path" 
                                class="w-full h-full object-cover"
                            />
                            <ImageIcon v-else :size="20" class="text-muted-foreground/30" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <h3 class="font-black text-foreground truncate text-base leading-none mb-1">
                                    {{ brand.name }}
                                </h3>
                                <div v-if="can_manage" class="flex gap-1">
                                    <Link :href="route('admin.brands.edit', brand.id)" class="p-1.5 rounded-lg text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors">
                                        <Edit :size="14" />
                                    </Link>
                                    <button @click="deleteItem(brand)" class="p-1.5 rounded-lg text-muted-foreground hover:text-error hover:bg-error/5 transition-colors">
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </div>
                            <p class="text-[10px] font-mono text-muted-foreground/60 uppercase tracking-widest">{{ brand.slug }}</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-muted-foreground flex items-center gap-2 font-medium">
                                <Factory :size="14" class="text-primary/50" /> Proveedor
                            </span>
                            <span class="font-bold text-foreground truncate max-w-[140px]">{{ brand.provider_name || 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-muted-foreground flex items-center gap-2 font-medium">
                                <LayoutGrid :size="14" class="text-primary/50" /> Categoría
                            </span>
                            <span class="bg-primary/5 text-primary px-2 py-0.5 rounded font-bold">{{ brand.category_name || 'General' }}</span>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-3 bg-muted/30 border-t border-border/50 flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <CheckCircle2 v-if="brand.is_active" :size="14" class="text-success" />
                        <AlertCircle v-else :size="14" class="text-destructive" />
                        <span class="text-[10px] font-black uppercase tracking-tighter" :class="brand.is_active ? 'text-success' : 'text-destructive'">
                            {{ brand.is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <a v-if="brand.website" :href="brand.website" target="_blank" class="text-muted-foreground hover:text-primary transition-colors">
                        <Globe :size="14" />
                    </a>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-32 bg-muted/10 border-2 border-dashed border-border rounded-3xl">
            <Tag :size="64" class="text-muted-foreground/20 mb-4" />
            <h3 class="text-xl font-bold text-foreground">Sin marcas registradas</h3>
            <p class="text-sm text-muted-foreground mt-1">Empieza creando una marca para tu catálogo.</p>
        </div>

        <div v-if="brands.links && brands.links.length > 3" class="mt-10 flex justify-center">
            <nav class="flex gap-1">
                <template v-for="(link, k) in brands.links" :key="k">
                    <Link 
                        v-if="link.url" 
                        :href="link.url" 
                        v-html="link.label" 
                        class="px-4 py-2 text-sm font-bold rounded-xl border transition-all"
                        :class="link.active ? 'bg-primary border-primary text-white shadow-lg shadow-primary/20' : 'bg-card border-border text-muted-foreground hover:border-primary/50'"
                    />
                </template>
            </nav>
        </div>
    </AdminLayout>
</template>