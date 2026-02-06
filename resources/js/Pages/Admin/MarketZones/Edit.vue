<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Save, ArrowLeft, CheckCircle, AlertCircle, 
    Palette, Hash, Type, Search, Map as MapIcon, Layers,
    MoveRight, Trash2
} from 'lucide-vue-next';

const props = defineProps({
    zone: Object,
    available_categories: Array // [{id, name, market_zone_id, market_zone_name}, ...]
});

// --- ESTADO ---
const searchCategories = ref('');

// --- FORMULARIO ---
const form = useForm({
    name: props.zone.name,
    hex_color: props.zone.hex_color,
    svg_id: props.zone.svg_id,
    description: props.zone.description,
    categories: props.zone.categories.map(c => c.id) // IDs iniciales
});

// --- LÓGICA ---
const filteredCategories = computed(() => {
    if (!searchCategories.value) return props.available_categories;
    const term = searchCategories.value.toLowerCase();
    return props.available_categories.filter(cat => 
        cat.name.toLowerCase().includes(term)
    );
});

const toggleCategory = (id) => {
    const index = form.categories.indexOf(id);
    if (index === -1) {
        form.categories.push(id);
    } else {
        form.categories.splice(index, 1);
    }
};

const submit = () => form.put(route('admin.market-zones.update', props.zone.id));

// Helpers visuales para estado de categoría
const getCategoryStatus = (cat) => {
    const isSelected = form.categories.includes(cat.id);
    const originalZoneId = cat.market_zone_id;
    const currentZoneId = props.zone.id;

    if (isSelected) {
        if (originalZoneId === currentZoneId) return 'kept'; // Ya estaba aquí
        return 'moved_here'; // Se mueve a esta zona
    } else {
        if (originalZoneId === currentZoneId) return 'removed'; // Se quita de esta zona
        return 'other'; // Está en otra zona o libre
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Editar Zona" />
        
        <div class="max-w-7xl mx-auto pb-32 md:pb-12">
            
            <div class="sticky top-0 z-40 bg-background/95 backdrop-blur-sm md:static border-b md:border-0 border-border px-4 py-3 md:px-0 md:py-0 mb-6 flex justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.market-zones.index')" class="btn btn-ghost btn-sm btn-square -ml-2 text-muted-foreground">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-lg md:text-3xl font-display font-black text-foreground leading-tight">Editar Zona</h1>
                        <p class="text-xs text-muted-foreground hidden md:block">Modificando: {{ props.zone.name }}</p>
                    </div>
                </div>

                <button @click="submit" :disabled="form.processing" class="lg:hidden btn btn-primary btn-sm gap-2 shadow-md">
                    <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
                    <span v-else class="flex items-center gap-1 font-bold">
                        Guardar <Save :size="16" />
                    </span>
                </button>

                <Link :href="route('admin.market-zones.index')" class="hidden lg:flex btn btn-ghost text-muted-foreground btn-sm gap-2">
                    Cancelar
                </Link>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-6 px-4 md:px-0">
                
                <div class="lg:col-span-4 space-y-6">
                    <div class="card overflow-hidden border border-border shadow-sm">
                        <div class="p-4 border-b border-border bg-muted/30 relative overflow-hidden">
                            <div class="absolute left-0 top-0 bottom-0 w-1.5" :style="{ backgroundColor: form.hex_color }"></div>
                            <h3 class="font-bold text-foreground flex items-center gap-2 pl-2">
                                <MapIcon :size="18" class="text-muted-foreground"/> Datos Generales
                            </h3>
                        </div>
                        
                        <div class="p-5 space-y-5">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-foreground uppercase tracking-wider">Nombre</label>
                                <input v-model="form.name" type="text" class="form-input w-full font-bold text-lg" :class="{'border-error': form.errors.name}" />
                                <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-foreground uppercase tracking-wider">Color</label>
                                <div class="flex gap-3 h-12">
                                    <div class="aspect-square h-full rounded-xl border-2 border-border shadow-sm overflow-hidden relative cursor-pointer group">
                                        <input v-model="form.hex_color" type="color" class="absolute inset-0 w-[200%] h-[200%] -top-1/2 -left-1/2 cursor-pointer p-0 border-0" />
                                    </div>
                                    <input v-model="form.hex_color" type="text" class="form-input flex-1 font-mono uppercase" maxlength="7" />
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-foreground uppercase tracking-wider">ID SVG</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-mono">#</span>
                                    <input v-model="form.svg_id" type="text" class="form-input w-full pl-7 font-mono text-sm bg-muted/20" />
                                </div>
                                <p v-if="form.errors.svg_id" class="form-error">{{ form.errors.svg_id }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Notas</label>
                                <textarea v-model="form.description" class="form-input w-full resize-none text-sm" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="hidden lg:block">
                        <button type="submit" class="btn btn-primary w-full py-4 shadow-lg hover:shadow-primary/25 transition-all text-base" :disabled="form.processing">
                            <Save :size="20" class="mr-2"/> Actualizar Zona
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="card h-full flex flex-col border border-border shadow-sm overflow-hidden">
                        
                        <div class="p-4 border-b border-border bg-muted/10 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <div class="bg-primary/10 p-1.5 rounded-lg text-primary">
                                    <Layers :size="18" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-foreground text-sm">Categorías</h3>
                                    <p class="text-xs text-muted-foreground" v-if="form.categories.length > 0">
                                        <span class="text-primary font-bold">{{ form.categories.length }}</span> seleccionadas
                                    </p>
                                </div>
                            </div>
                            <div class="relative w-full sm:w-64">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="16" />
                                <input v-model="searchCategories" type="text" class="form-input w-full pl-9 py-2 h-9 text-sm" placeholder="Buscar..." />
                            </div>
                        </div>

                        <div class="p-4 md:p-6 flex-1 bg-muted/5 overflow-y-auto max-h-[600px] scrollbar-thin">
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                                <div v-for="cat in filteredCategories" :key="cat.id"
                                     @click="toggleCategory(cat.id)"
                                     class="relative p-3 rounded-lg border cursor-pointer transition-all duration-200 select-none group flex flex-col justify-between min-h-[90px]"
                                     :class="form.categories.includes(cat.id) 
                                        ? 'bg-primary/5 border-primary shadow-sm ring-1 ring-primary/20' 
                                        : 'bg-card border-border hover:border-primary/40'"
                                >
                                    <div class="flex justify-between items-start gap-2">
                                        <span class="font-bold text-sm text-foreground leading-tight group-hover:text-primary transition-colors">
                                            {{ cat.name }}
                                        </span>
                                        <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-colors shrink-0"
                                             :class="form.categories.includes(cat.id) ? 'bg-primary border-primary' : 'border-muted-foreground/30 bg-background'">
                                            <CheckCircle v-if="form.categories.includes(cat.id)" :size="12" class="text-white" />
                                        </div>
                                    </div>

                                    <div class="mt-2 pt-2 border-t border-border/50 text-[10px] font-medium">
                                        
                                        <div v-if="getCategoryStatus(cat) === 'kept'" class="text-emerald-600 flex items-center gap-1">
                                            <CheckCircle :size="10"/> Asignada
                                        </div>

                                        <div v-else-if="getCategoryStatus(cat) === 'moved_here'" class="text-indigo-600 flex items-center gap-1 animate-pulse">
                                            <MoveRight :size="10"/> Se moverá aquí
                                        </div>

                                        <div v-else-if="getCategoryStatus(cat) === 'removed'" class="text-red-600 flex items-center gap-1">
                                            <Trash2 :size="10"/> Se quitará
                                        </div>

                                        <div v-else-if="cat.market_zone_id" class="text-amber-600 flex items-center gap-1 opacity-70">
                                            <AlertCircle :size="10"/> En: {{ cat.market_zone_name }}
                                        </div>

                                        <div v-else class="text-muted-foreground opacity-50">
                                            Sin asignar
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: hsl(var(--muted-foreground)/0.2); border-radius: 10px; }
</style>