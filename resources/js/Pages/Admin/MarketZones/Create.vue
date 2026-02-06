<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Save, ArrowLeft, CheckCircle, AlertCircle, 
    Palette, Hash, Type, Search, Map as MapIcon, Layers 
} from 'lucide-vue-next';

const props = defineProps({
    available_categories: Array 
});

// --- ESTADO ---
const searchCategories = ref('');

// --- FORMULARIO ---
const form = useForm({
    name: '',
    hex_color: '#3b82f6', 
    svg_id: 'zone-',
    description: '',
    categories: [] 
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

const submit = () => form.post(route('admin.market-zones.store'));
</script>

<template>
    <AdminLayout>
        <Head title="Nueva Zona" />
        
        <div class="max-w-7xl mx-auto pb-32 md:pb-12">
            
            <div class="sticky top-0 z-40 bg-background/95 backdrop-blur-sm md:static border-b md:border-0 border-border px-4 py-3 md:px-0 md:py-0 mb-6 flex justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.market-zones.index')" class="btn btn-ghost btn-sm btn-square -ml-2 text-muted-foreground">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-lg md:text-3xl font-display font-black text-foreground leading-tight">Nueva Zona</h1>
                        <p class="text-xs text-muted-foreground hidden md:block">Define un área logística en el mapa.</p>
                    </div>
                </div>

                <button 
                    @click="submit" 
                    :disabled="form.processing"
                    class="lg:hidden btn btn-primary btn-sm gap-2 shadow-md"
                >
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
                        <div class="bg-muted/30 p-4 border-b border-border">
                            <h3 class="font-bold text-foreground flex items-center gap-2 text-sm md:text-base">
                                <MapIcon :size="18" class="text-primary"/> Configuración Visual
                            </h3>
                        </div>
                        
                        <div class="p-5 space-y-5">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-foreground uppercase tracking-wider flex items-center gap-2">
                                    <Type :size="14" class="text-muted-foreground"/> Nombre
                                </label>
                                <input v-model="form.name" type="text" 
                                       class="form-input w-full font-bold text-lg" 
                                       placeholder="Ej: Zona Norte" 
                                       :class="{'border-error': form.errors.name}" />
                                <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-foreground uppercase tracking-wider flex items-center gap-2">
                                    <Palette :size="14" class="text-muted-foreground"/> Color
                                </label>
                                <div class="flex gap-3 h-12">
                                    <div class="aspect-square h-full rounded-xl border-2 border-border shadow-sm overflow-hidden relative cursor-pointer group">
                                        <input v-model="form.hex_color" type="color" class="absolute inset-0 w-[200%] h-[200%] -top-1/2 -left-1/2 cursor-pointer p-0 border-0" />
                                    </div>
                                    <input v-model="form.hex_color" type="text" class="form-input flex-1 font-mono uppercase" placeholder="#000000" maxlength="7" />
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-foreground uppercase tracking-wider flex items-center gap-2">
                                    <Hash :size="14" class="text-muted-foreground"/> ID SVG
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-mono">#</span>
                                    <input v-model="form.svg_id" type="text" 
                                           class="form-input w-full pl-7 font-mono text-sm bg-muted/20" 
                                           placeholder="zone-north" />
                                </div>
                                <p v-if="form.errors.svg_id" class="form-error">{{ form.errors.svg_id }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Notas</label>
                                <textarea v-model="form.description" class="form-input w-full resize-none text-sm" rows="2" placeholder="Opcional..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="hidden lg:block">
                        <button type="submit" class="btn btn-primary w-full py-4 shadow-lg hover:shadow-primary/25 transition-all text-base" :disabled="form.processing">
                            <Save :size="20" class="mr-2"/> Guardar Zona
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="card h-full flex flex-col border border-border shadow-sm overflow-hidden">
                        
                        <div class="p-4 border-b border-border bg-muted/10 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="bg-primary/10 p-1.5 rounded-lg text-primary">
                                        <Layers :size="18" />
                                    </div>
                                    <h3 class="font-bold text-foreground text-sm">Categorías</h3>
                                </div>
                                <span class="text-xs font-bold" :class="form.categories.length ? 'text-primary' : 'text-muted-foreground'">
                                    {{ form.categories.length }} selec.
                                </span>
                            </div>

                            <div class="relative w-full">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="16" />
                                <input v-model="searchCategories" type="text" 
                                       class="form-input w-full pl-9 py-2 h-9 text-sm" 
                                       placeholder="Filtrar categorías..." />
                            </div>
                        </div>

                        <div class="p-3 md:p-6 flex-1 bg-muted/5 overflow-y-auto max-h-[500px] lg:max-h-[600px] scrollbar-thin">
                            
                            <div v-if="filteredCategories.length === 0" class="text-center py-8 text-muted-foreground text-sm">
                                No se encontraron resultados.
                            </div>

                            <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2 md:gap-3">
                                <div v-for="cat in filteredCategories" :key="cat.id"
                                     @click="toggleCategory(cat.id)"
                                     class="relative p-3 rounded-lg border cursor-pointer transition-all duration-200 select-none group flex items-center justify-between"
                                     :class="form.categories.includes(cat.id) 
                                        ? 'bg-primary/5 border-primary shadow-sm ring-1 ring-primary/20' 
                                        : 'bg-card border-border hover:border-primary/40'"
                                >
                                    <div class="flex flex-col min-w-0 pr-2">
                                        <span class="font-bold text-xs md:text-sm text-foreground truncate group-hover:text-primary transition-colors">
                                            {{ cat.name }}
                                        </span>
                                        <span v-if="cat.market_zone_id && !form.categories.includes(cat.id)" 
                                              class="text-[9px] text-amber-600 mt-0.5 truncate flex items-center gap-1">
                                            <AlertCircle :size="8"/> En: {{ cat.market_zone_name }}
                                        </span>
                                        <span v-else-if="form.categories.includes(cat.id)" class="text-[9px] text-primary font-bold mt-0.5">
                                            Seleccionada
                                        </span>
                                    </div>

                                    <div class="w-5 h-5 rounded border flex items-center justify-center transition-colors shrink-0"
                                         :class="form.categories.includes(cat.id) ? 'bg-primary border-primary' : 'border-muted-foreground/30 bg-background'">
                                        <CheckCircle v-if="form.categories.includes(cat.id)" :size="12" class="text-white" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="lg:hidden fixed bottom-4 left-4 right-4 z-[1000]">
                <button 
                    @click="submit" 
                    :disabled="form.processing" 
                    class="btn btn-primary w-full shadow-[0_4px_20px_rgba(0,0,0,0.3)] border-2 border-white/20 h-14 text-lg font-black tracking-wide"
                >
                    <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                    <span v-else class="flex items-center gap-2">
                        <Save :size="20"/> GUARDAR ZONA
                    </span>
                </button>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
/* Scrollbar sutil */
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: hsl(var(--muted-foreground)/0.2); border-radius: 10px; }
</style>