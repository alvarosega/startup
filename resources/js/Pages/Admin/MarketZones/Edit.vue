<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save, ArrowLeft, CheckCircle, AlertCircle } from 'lucide-vue-next';

// Props recibidos del controlador
const props = defineProps({
    zone: Object,
    available_categories: Array
});

const form = useForm({
    name: props.zone.name,
    hex_color: props.zone.hex_color,
    svg_id: props.zone.svg_id,
    description: props.zone.description,
    // Mapeamos las categorías que ya tiene la zona para que aparezcan seleccionadas
    categories: props.zone.categories.map(c => c.id)
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
</script>

<template>
    <AdminLayout>
        <Head title="Editar Zona" />
        
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center gap-4 mb-8">
                <Link :href="route('admin.market-zones.index')" class="btn btn-outline btn-sm gap-2">
                    <ArrowLeft :size="16" /> Volver
                </Link>
                <div>
                    <h1 class="text-2xl font-black text-foreground">Editar Zona</h1>
                    <p class="text-sm text-muted-foreground">Modifica la zona {{ props.zone.name }} y sus categorías.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-4 space-y-6">
                    <div class="card p-6 border-l-4" :style="{ borderLeftColor: form.hex_color }">
                        <h3 class="font-bold text-lg mb-4 text-foreground">Configuración Visual</h3>
                        
                        <div class="space-y-4">
                            <div class="form-group">
                                <label class="label">Nombre de la Zona</label>
                                <input v-model="form.name" type="text" class="input w-full font-bold" required />
                                <p v-if="form.errors.name" class="text-error text-xs mt-1">{{ form.errors.name }}</p>
                            </div>

                            <div class="form-group">
                                <label class="label">Color Identificativo</label>
                                <div class="flex gap-2">
                                    <input v-model="form.hex_color" type="color" class="h-10 w-16 border border-border rounded cursor-pointer p-1 bg-card" />
                                    <input v-model="form.hex_color" type="text" class="input w-full uppercase font-mono" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="label">ID del SVG</label>
                                <input v-model="form.svg_id" type="text" class="input w-full font-mono bg-muted/30" required />
                                <p v-if="form.errors.svg_id" class="text-error text-xs mt-1">{{ form.errors.svg_id }}</p>
                            </div>

                            <div class="form-group">
                                <label class="label">Descripción</label>
                                <textarea v-model="form.description" class="textarea w-full" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="hidden lg:block">
                        <button type="submit" class="btn btn-primary w-full py-3 text-base shadow-lg" :disabled="form.processing">
                            <Save :size="20" class="mr-2"/> Actualizar Zona
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="card h-full flex flex-col">
                        <div class="p-6 border-b border-border bg-muted/10 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-lg text-foreground">Asignar Categorías</h3>
                                <p class="text-sm text-muted-foreground">Administra el contenido de esta zona.</p>
                            </div>
                            <div class="badge badge-primary font-bold px-3 py-1">
                                {{ form.categories.length }} seleccionadas
                            </div>
                        </div>

                        <div class="p-6 flex-1 overflow-y-auto max-h-[600px] scrollbar-thin">
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                                <div 
                                    v-for="cat in available_categories" 
                                    :key="cat.id"
                                    @click="toggleCategory(cat.id)"
                                    class="relative p-3 rounded-xl border cursor-pointer transition-all duration-200 select-none group"
                                    :class="form.categories.includes(cat.id) 
                                        ? 'bg-primary/5 border-primary shadow-sm ring-1 ring-primary/20' 
                                        : 'bg-card border-border hover:border-primary/50 hover:bg-muted/50'"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors">
                                            {{ cat.name }}
                                        </span>
                                        <div v-if="form.categories.includes(cat.id)" class="text-primary shrink-0 animate-in zoom-in duration-200">
                                            <CheckCircle :size="18" fill="currentColor" class="text-white" />
                                        </div>
                                    </div>

                                    <div class="mt-2 min-h-[20px]">
                                        <div v-if="cat.market_zone_id && cat.market_zone_id !== props.zone.id && !form.categories.includes(cat.id)" 
                                             class="flex items-center gap-1 text-[10px] text-amber-700 bg-amber-100 px-2 py-0.5 rounded w-fit">
                                            <AlertCircle :size="10"/> En otra zona
                                        </div>
                                        
                                        <div v-else-if="cat.market_zone_id === props.zone.id && !form.categories.includes(cat.id)" 
                                             class="flex items-center gap-1 text-[10px] text-red-600 bg-red-100 px-2 py-0.5 rounded w-fit">
                                            Se quitará de la zona
                                        </div>

                                        <div v-else-if="cat.market_zone_id && cat.market_zone_id !== props.zone.id && form.categories.includes(cat.id)" 
                                             class="flex items-center gap-1 text-[10px] text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded w-fit">
                                            Se moverá aquí
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="lg:hidden mt-6">
                        <button type="submit" class="btn btn-primary w-full py-3 text-base shadow-lg" :disabled="form.processing">
                            <Save :size="20" class="mr-2"/> Actualizar Zona
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>