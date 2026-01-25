<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Package, Plus, Search, Edit, Trash2, 
    Tag, Layers, Image as ImageIcon, CheckCircle, 
    XCircle, ChevronDown, ChevronRight, Barcode, Box, Cuboid
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    filters: Object
});

const search = ref(props.filters?.search || '');
const expandedRows = ref([]);

watch(search, debounce((val) => {
    router.get(route('admin.products.index'), { search: val }, {
        preserveState: true, replace: true, preserveScroll: true
    });
}, 300));

const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
    }
};

const deleteProduct = (id, name) => {
    if (confirm(`⚠ ATENCIÓN: Eliminar "${name}" borrará permanentemente todas sus variantes. ¿Proceder?`)) {
        router.delete(route('admin.products.destroy', id));
    }
};

// Helper para mostrar imagen
// Helper para obtener la URL correcta de la imagen
const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    
    // Si ya es una URL completa
    if (imagePath.startsWith('http')) return imagePath;
    
    // Si ya empieza con /storage, no duplicar
    if (imagePath.startsWith('/storage/')) return imagePath;
    
    // Si ya tiene storage/ sin la barra inicial
    if (imagePath.startsWith('storage/')) return `/${imagePath}`;
    
    // Caso por defecto: agregar /storage/
    return `/storage/${imagePath}`;
};
</script>

<template>
    <AdminLayout>
        <Head title="Inventario Maestro" />

        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-foreground tracking-tight">Catálogo de Productos</h1>
                    <p class="text-muted-foreground mt-1 text-sm">Gestión jerárquica de Productos Maestros y sus Presentaciones (SKUs)</p>
                </div>
                
                <Link 
                    :href="route('admin.products.create')" 
                    class="btn btn-primary btn-md flex items-center gap-2"
                >
                    <Plus :size="16" />
                    <span>Nuevo Producto</span>
                </Link>
            </div>

            <!-- Barra de búsqueda -->
            <div class="card">
                <div class="relative w-full md:w-96">
                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground" :size="18" />
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Buscar producto, marca, código..." 
                        class="w-full pl-10 pr-4 py-2.5 bg-background border border-input rounded-lg text-sm focus:ring-2 focus:ring-ring focus:border-primary outline-none transition-all"
                    />
                </div>
            </div>

            <!-- Tabla de productos -->
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px]">
                        <thead class="bg-muted/50 border-b border-border">
                            <tr>
                                <th class="w-12 px-6 py-4"></th>
                                <th class="px-6 py-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                    Producto Maestro
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                    Clasificación
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-muted-foreground uppercase tracking-wider text-center">
                                    SKUs
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-muted-foreground uppercase tracking-wider text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-border/50">
                            <template v-for="product in products.data" :key="product.id">
                                <!-- Fila principal -->
                                <tr 
                                    class="group hover:bg-muted/30 transition-colors cursor-pointer animate-in"
                                    @click="toggleRow(product.id)"
                                >
                                    <td class="px-6 py-4">
                                        <button class="p-1 rounded-md hover:bg-muted transition">
                                            <ChevronDown 
                                                v-if="expandedRows.includes(product.id)" 
                                                :size="20" 
                                                class="text-primary"
                                            />
                                            <ChevronRight v-else :size="20" class="text-muted-foreground" />
                                        </button>
                                    </td>
                                    
                                    <!-- Información del producto -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-lg bg-card border border-border overflow-hidden shrink-0 flex items-center justify-center">
                                                <img 
                                                    v-if="getImageUrl(product.image_url)" 
                                                    :src="getImageUrl(product.image_url)" 
                                                    class="w-full h-full object-cover"
                                                    @error="e => { e.target.style.display = 'none'; }"
                                                />
                                                <ImageIcon 
                                                    v-else 
                                                    class="text-muted-foreground/30" 
                                                    :size="20" 
                                                />
                                            </div>
                                            <div>
                                                <div class="font-bold text-foreground text-sm group-hover:text-primary transition-colors">
                                                    {{ product.name }}
                                                </div>
                                                <div class="text-[11px] text-muted-foreground font-mono mt-0.5">
                                                    ID: {{ product.id.substring(0,8) }}...
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Clasificación -->
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5">
                                            <span 
                                                class="badge badge-outline text-xs inline-flex items-center gap-1.5 w-fit"
                                            >
                                                <Tag :size="10" />
                                                {{ product.brand?.name || 'Sin marca' }}
                                            </span>
                                            <span 
                                                class="badge badge-outline text-xs inline-flex items-center gap-1.5 w-fit"
                                            >
                                                <Layers :size="10" />
                                                {{ product.category?.name || 'Sin categoría' }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Conteo de SKUs -->
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-bold text-xs border border-primary/20">
                                            {{ product.skus_count }}
                                        </span>
                                    </td>

                                    <!-- Estado -->
                                    <td class="px-6 py-4">
                                        <span 
                                            v-if="product.is_active" 
                                            class="badge badge-success text-xs inline-flex items-center gap-1"
                                        >
                                            <CheckCircle :size="12" />
                                            Activo
                                        </span>
                                        <span 
                                            v-else 
                                            class="badge badge-outline text-xs inline-flex items-center gap-1"
                                        >
                                            <XCircle :size="12" />
                                            Inactivo
                                        </span>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2" @click.stop>
                                            <Link 
                                                :href="route('admin.products.edit', product.id)" 
                                                class="p-2 rounded-lg text-muted-foreground hover:text-primary hover:bg-primary/10 transition-colors"
                                                title="Editar"
                                            >
                                                <Edit :size="18" />
                                            </Link>
                                            <button 
                                                @click="deleteProduct(product.id, product.name)" 
                                                class="p-2 rounded-lg text-muted-foreground hover:text-error hover:bg-error/10 transition-colors"
                                                title="Eliminar"
                                            >
                                                <Trash2 :size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Fila expandida con SKUs -->
                                <tr v-if="expandedRows.includes(product.id)" class="bg-muted/10">
                                    <td colspan="6" class="p-0 border-b border-border/50">
                                        <div class="px-6 py-4">
                                            <div class="card border border-border/50">
                                                <div class="px-4 py-3 bg-muted/20 border-b border-border/50 flex items-center gap-2">
                                                    <Barcode :size="16" class="text-muted-foreground"/>
                                                    <span class="text-xs font-bold text-foreground uppercase tracking-wide">
                                                        Variantes de Venta (SKUs)
                                                    </span>
                                                </div>
                                                
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-sm">
                                                        <thead>
                                                            <tr class="text-left text-xs uppercase text-muted-foreground bg-card">
                                                                <th class="px-4 py-2 font-bold w-16">Imagen</th>
                                                                <th class="px-4 py-2 font-bold">Nombre Variante</th>
                                                                <th class="px-4 py-2 font-bold">Código EAN</th>
                                                                <th class="px-4 py-2 font-bold text-center">Unidad de Medida</th>
                                                                <th class="px-4 py-2 font-bold text-right">Precio Base</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-border/30">
                                                            <tr 
                                                                v-for="sku in product.skus" 
                                                                :key="sku.id" 
                                                                class="hover:bg-muted/20 transition-colors"
                                                            >
                                                                <!-- Imagen SKU -->
                                                                <td class="px-4 py-3">
                                                                    <div class="w-8 h-8 rounded bg-card border border-border overflow-hidden flex items-center justify-center">
                                                                        <img 
                                                                            v-if="getImageUrl(sku.image_url)" 
                                                                            :src="getImageUrl(sku.image_url)" 
                                                                            class="w-full h-full object-cover"
                                                                            @error="e => { e.target.style.display = 'none'; }"
                                                                        />
                                                                        <ImageIcon 
                                                                            v-else 
                                                                            :size="14" 
                                                                            class="text-muted-foreground/30" 
                                                                        />
                                                                    </div>
                                                                </td>
                                                                
                                                                <!-- Nombre -->
                                                                <td class="px-4 py-3 font-medium text-foreground">
                                                                    {{ sku.name }}
                                                                </td>
                                                                
                                                                <!-- Código EAN -->
                                                                <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                                                    {{ sku.code || '---' }}
                                                                </td>
                                                                
                                                                <!-- Unidad de medida -->
                                                                <td class="px-4 py-3 text-center">
                                                                    <span 
                                                                        v-if="parseFloat(sku.conversion_factor) === 1" 
                                                                        class="badge badge-primary text-xs inline-flex items-center gap-1"
                                                                    >
                                                                        <Cuboid :size="10" />
                                                                        Unidad
                                                                    </span>
                                                                    <span 
                                                                        v-else 
                                                                        class="badge badge-secondary text-xs inline-flex items-center gap-1"
                                                                    >
                                                                        <Box :size="10" />
                                                                        Pack x{{ parseFloat(sku.conversion_factor) }}
                                                                    </span>
                                                                </td>
                                                                
                                                                <!-- Precio -->
                                                                <td class="px-4 py-3 text-right font-mono font-bold text-success">
                                                                    Bs {{ parseFloat(sku.price).toFixed(2) }}
                                                                </td>
                                                            </tr>
                                                            
                                                            <!-- Estado vacío -->
                                                            <tr v-if="!product.skus || product.skus.length === 0">
                                                                <td colspan="5" class="px-4 py-4 text-center text-sm text-muted-foreground">
                                                                    No hay variantes registradas
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- Estado vacío -->
                            <tr v-if="products.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <Package :size="48" class="mb-4 text-muted-foreground/30" />
                                        <p class="font-medium text-foreground">Sin resultados</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ search ? 'No hay productos que coincidan con la búsqueda' : 'No hay productos registrados' }}
                                        </p>
                                        <Link 
                                            v-if="!search"
                                            :href="route('admin.products.create')"
                                            class="btn btn-outline btn-sm mt-4"
                                        >
                                            Crear primer producto
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div 
                    v-if="products.meta?.last_page > 1" 
                    class="px-6 py-4 border-t border-border flex justify-end gap-1"
                >
                    <Link
                        v-for="(link, k) in products.meta.links"
                        :key="k"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                        :class="{
                            'bg-primary text-primary-foreground': link.active,
                            'text-muted-foreground hover:text-foreground hover:bg-muted': !link.active && link.url,
                            'text-muted-foreground/50 pointer-events-none': !link.url
                        }"
                        :preserve-scroll="true"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.animate-in {
    animation: fadeIn 0.2s var(--ease-smooth);
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>