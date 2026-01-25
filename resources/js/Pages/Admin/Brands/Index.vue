<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

// Iconos
import { 
    Search, Plus, Image as ImageIcon, Factory, 
    Globe, Tag, Edit, Trash2, ShieldCheck 
} from 'lucide-vue-next';

const props = defineProps({ 
    brands: Array,
    can_manage: Boolean
});

const search = ref('');
const filteredBrands = ref(props.brands);

// Lógica de colores para los Tiers usando las clases del sistema
const tierColors = {
    'Luxury': 'bg-purple-500/10 text-purple-500 border-purple-500/20',
    'Premium': 'bg-amber-500/10 text-amber-600 border-amber-500/20',
    'Standard': 'badge-outline',
    'Economy': 'badge-outline',
};

// Búsqueda en cliente
watch(search, debounce((val) => {
    const lower = val.toLowerCase();
    filteredBrands.value = props.brands.filter(b => 
        b.name.toLowerCase().includes(lower) || 
        (b.provider && b.provider.commercial_name.toLowerCase().includes(lower)) ||
        (b.origin_country_code && b.origin_country_code.toLowerCase().includes(lower))
    );
}, 300));

// Actualizar si cambian las props (ej: después de borrar)
watch(() => props.brands, (newVal) => {
    filteredBrands.value = newVal;
});

const deleteItem = (brand) => {
    if(confirm(`¿Eliminar la marca ${brand.name}? Esto desvinculará los productos asociados.`)) {
        router.delete(route('admin.brands.destroy', brand.id));
    }
};
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-black text-foreground tracking-tight">Marcas Comerciales</h1>
                <p class="text-muted-foreground text-sm mt-1">Portafolio de productos y distribución oficial</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Search :size="16" class="text-muted-foreground" />
                    </div>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Buscar marca, país..." 
                        class="pl-10 w-full bg-card border border-input text-foreground text-sm rounded-lg focus:ring-2 focus:ring-ring focus:border-primary placeholder-muted-foreground/50 transition-all shadow-sm"
                    >
                </div>

                <Link 
                    v-if="can_manage" 
                    :href="route('admin.brands.create')" 
                    class="flex items-center justify-center gap-2 btn btn-primary btn-md whitespace-nowrap"
                >
                    <Plus :size="16" />
                    <span>Nueva Marca</span>
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
            <div 
                v-for="brand in filteredBrands" 
                :key="brand.id" 
                class="card group animate-in overflow-hidden relative"
            >
                <div v-if="!brand.is_active" class="absolute top-0 left-0 w-full h-1 bg-error z-10"></div>

                <div class="p-5 flex items-start gap-4">
                    <div class="w-16 h-16 bg-white border border-border rounded-lg flex items-center justify-center p-2 shrink-0 shadow-sm relative overflow-hidden">
                        <img 
                            v-if="brand.image_url" 
                            :src="brand.image_url" 
                            :alt="brand.name" 
                            class="w-full h-full object-contain"
                        >
                        <ImageIcon v-else :size="24" class="text-muted-foreground/30" />
                        
                        <div 
                            v-if="brand.origin_country_code" 
                            class="absolute bottom-0 right-0 bg-foreground text-background text-[8px] font-black px-1.5 py-0.5 rounded-tl"
                        >
                            {{ brand.origin_country_code }}
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <h3 
                                class="font-bold text-lg text-foreground truncate leading-tight" 
                                :title="brand.name"
                            >
                                {{ brand.name }}
                            </h3>
                            
                            <div 
                                v-if="can_manage" 
                                class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity transform translate-x-2 group-hover:translate-x-0"
                            >
                                <Link 
                                    :href="route('admin.brands.edit', brand.id)" 
                                    class="p-1 rounded text-muted-foreground hover:text-primary hover:bg-primary/10 transition"
                                >
                                    <Edit :size="14" />
                                </Link>
                                <button 
                                    @click="deleteItem(brand)" 
                                    class="p-1 rounded text-muted-foreground hover:text-error hover:bg-error/10 transition"
                                >
                                    <Trash2 :size="14" />
                                </button>
                            </div>
                        </div>

                        <span 
                            class="badge inline-flex items-center gap-1 mt-1 text-[10px] font-bold uppercase"
                            :class="tierColors[brand.tier] || tierColors['Standard']"
                        >
                            <ShieldCheck 
                                v-if="brand.tier === 'Premium' || brand.tier === 'Luxury'" 
                                :size="10" 
                            />
                            {{ brand.tier }}
                        </span>
                    </div>
                </div>

                <div class="px-5 pb-4 mt-auto">
                    <div class="pt-3 border-t border-border/50">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-muted-foreground flex items-center gap-1.5">
                                <Factory :size="12" /> Distribuidor
                            </span>
                            <span 
                                v-if="brand.provider" 
                                class="font-medium text-primary truncate max-w-[120px]" 
                                :title="brand.provider.commercial_name"
                            >
                                {{ brand.provider.commercial_name }}
                            </span>
                            <span v-else class="text-error italic text-[10px]">Sin asignar</span>
                        </div>
                        
                        <div class="flex items-center justify-between text-xs mt-2">
                            <span class="text-muted-foreground flex items-center gap-1.5">
                                <Globe :size="12" /> Fabricante
                            </span>
                            <span class="font-medium text-foreground truncate max-w-[120px]">
                                {{ brand.manufacturer || 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-4 -right-4 text-primary/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                    <Tag :size="80" />
                </div>
            </div>

            <div 
                v-if="filteredBrands.length === 0" 
                class="col-span-full py-16 flex flex-col items-center justify-center text-muted-foreground border-2 border-dashed border-border rounded-lg bg-background/30"
            >
                <Search :size="48" class="mb-4 opacity-20" />
                <p class="font-medium">No se encontraron marcas</p>
                <p class="text-sm opacity-70">Prueba con otro nombre o país</p>
            </div>
        </div>
    </AdminLayout>
</template>