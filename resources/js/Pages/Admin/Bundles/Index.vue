<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Package, Plus, Edit, Trash2, Star, Image as ImageIcon, Search } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    bundles: Object
});

const search = ref('');
const filteredBundles = ref(props.bundles.data);

// Búsqueda en tiempo real
watch(search, debounce((val) => {
    const lower = val.toLowerCase();
    filteredBundles.value = props.bundles.data.filter(b => 
        b.name.toLowerCase().includes(lower) ||
        (b.description && b.description.toLowerCase().includes(lower))
    );
}, 300));

const deleteBundle = (id) => {
    if (confirm('¿Estás seguro de eliminar este pack permanentemente?')) {
        router.delete(route('admin.bundles.destroy', id));
    }
};

// Helper para mostrar imagen
const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    if (imagePath.startsWith('http')) return imagePath;
    return `/storage/${imagePath}`;
};
</script>

<template>
    <AdminLayout>
        <Head title="Gestión de Packs" />

        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-black text-foreground flex items-center gap-2">
                        <Package class="text-primary" :size="24" /> 
                        Packs & Bundles
                    </h1>
                    <p class="text-muted-foreground mt-1">Administra ofertas y agrupaciones de productos</p>
                </div>
                
                <Link :href="route('admin.bundles.create')" class="btn btn-primary btn-md flex items-center gap-2">
                    <Plus :size="16" /> Nuevo Pack
                </Link>
            </div>

            <!-- Barra de búsqueda -->
            <div class="relative max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Search :size="16" class="text-muted-foreground" />
                </div>
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Buscar packs por nombre o descripción..." 
                    class="pl-10 w-full bg-card border border-input text-foreground rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-ring focus:border-primary outline-none"
                />
            </div>
        </div>

        <!-- Tabla de packs -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-muted/50 border-b border-border">
                        <tr>
                            <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                Pack
                            </th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                Precio
                            </th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                Valoración
                            </th>
                            <th class="text-left px-6 py-4 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/50">
                        <tr 
                            v-for="bundle in filteredBundles" 
                            :key="bundle.id"
                            class="hover:bg-muted/30 transition-colors duration-150"
                        >
                            <!-- Información del pack -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-lg bg-muted/20 border border-border overflow-hidden flex-shrink-0">
                                        <img 
                                            v-if="getImageUrl(bundle.image_path)" 
                                            :src="getImageUrl(bundle.image_path)" 
                                            :alt="bundle.name"
                                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                            @error="e => e.target.style.display = 'none'"
                                        />
                                        <div 
                                            v-else
                                            class="w-full h-full flex items-center justify-center text-muted-foreground/40"
                                        >
                                            <Package :size="20" />
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-foreground leading-tight">{{ bundle.name }}</h3>
                                        <p v-if="bundle.description" class="text-sm text-muted-foreground line-clamp-1 mt-1">
                                            {{ bundle.description }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="badge badge-primary text-xs font-bold">
                                                {{ bundle.skus_count || 0 }} items
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Precio -->
                            <td class="px-6 py-4">
                                <div v-if="bundle.fixed_price" class="flex flex-col">
                                    <span class="text-lg font-bold text-primary">
                                        Bs {{ parseFloat(bundle.fixed_price).toFixed(2) }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">Precio fijo</span>
                                </div>
                                <div v-else class="text-sm text-muted-foreground italic">
                                    Precio dinámico
                                </div>
                            </td>

                            <!-- Estado -->
                            <td class="px-6 py-4">
                                <span 
                                    class="badge inline-flex items-center gap-1.5"
                                    :class="bundle.is_active ? 'badge-success' : 'badge-outline'"
                                >
                                    <span 
                                        class="w-2 h-2 rounded-full"
                                        :class="bundle.is_active ? 'bg-success' : 'bg-muted-foreground'"
                                    ></span>
                                    {{ bundle.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <!-- Valoración -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center text-amber-500">
                                        <Star :size="14" fill="currentColor" />
                                        <span class="ml-1 font-bold text-foreground">
                                            {{ parseFloat(bundle.reviews_avg_rating || 0).toFixed(1) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        ({{ bundle.reviews_count || 0 }})
                                    </div>
                                </div>
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <Link 
                                        :href="route('admin.bundles.edit', bundle.id)"
                                        class="p-2 rounded-lg text-muted-foreground hover:text-primary hover:bg-primary/10 transition-colors"
                                        title="Editar"
                                    >
                                        <Edit :size="18" />
                                    </Link>
                                    <button
                                        @click="deleteBundle(bundle.id)"
                                        class="p-2 rounded-lg text-muted-foreground hover:text-error hover:bg-error/10 transition-colors"
                                        title="Eliminar"
                                    >
                                        <Trash2 :size="18" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Estado vacío -->
            <div 
                v-if="filteredBundles.length === 0"
                class="py-16 flex flex-col items-center justify-center text-center text-muted-foreground"
            >
                <Package :size="48" class="mb-4 opacity-30" />
                <p class="font-medium">No se encontraron packs</p>
                <p v-if="search" class="text-sm mt-1">Intenta con otros términos de búsqueda</p>
                <Link 
                    v-else
                    :href="route('admin.bundles.create')"
                    class="btn btn-outline btn-sm mt-4"
                >
                    Crear primer pack
                </Link>
            </div>

            <!-- Paginación -->
            <div 
                v-if="props.bundles.links && props.bundles.links.length > 3"
                class="px-6 py-4 border-t border-border"
            >
                <div class="flex justify-center">
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in props.bundles.links"
                            :key="link.label"
                            :href="link.url"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                            :class="{
                                'bg-primary text-primary-foreground': link.active,
                                'text-muted-foreground hover:text-foreground hover:bg-muted': !link.active && link.url,
                                'text-muted-foreground/50 pointer-events-none': !link.url
                            }"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>