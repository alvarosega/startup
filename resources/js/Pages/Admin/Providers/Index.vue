<script setup>
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Plus, Search, Phone, Mail, MapPin, 
    Truck, Edit, Trash2, Building2, 
    User, Globe 
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({ 
    providers: Object,
    filters: Object,
    can_manage: Boolean 
});

const search = ref(props.filters.search || '');

watch(search, debounce((val) => {
    router.get(route('admin.providers.index'), { search: val }, { 
        preserveState: true, replace: true, preserveScroll: true
    });
}, 300));

const handleDelete = (provider) => {
    // Usamos provider.name o company_name como fallback
    const name = provider.name || provider.company_name || 'este proveedor';
    if (confirm(`¿Estás seguro de eliminar a "${name}"?`)) {
        router.delete(route('admin.providers.destroy', provider.id));
    }
};

const getInitials = (name) => (name || 'P').substring(0, 2).toUpperCase();

const stats = computed(() => {
    const total = props.providers.total || 0;
    const currentView = props.providers.data.length;
    return [
        { label: 'Total Socios', value: total, icon: Building2, color: 'text-primary', bg: 'bg-primary/10' },
        { label: 'En Pantalla', value: currentView, icon: User, color: 'text-emerald-500', bg: 'bg-emerald-500/10' },
        { label: 'Alcance', value: 'Global', icon: Globe, color: 'text-indigo-500', bg: 'bg-indigo-500/10', isText: true },
    ];
});
</script>

<template>
    <AdminLayout>
        <Head title="Proveedores" />

        <div class="pb-40 md:pb-12 min-h-screen flex flex-col">
            
            <div class="px-4 md:px-0 mb-6 space-y-6">
                <div class="flex flex-col gap-1">
                    <h1 class="text-3xl font-display font-black text-foreground tracking-tighter flex items-center gap-3">
                        Proveedores
                        <span class="text-xs font-bold text-muted-foreground bg-muted px-2 py-1 rounded-full border border-border/50">
                            {{ providers.total }}
                        </span>
                    </h1>
                    <p class="text-sm text-muted-foreground font-medium">
                        Gestión estratégica de la cadena de suministro.
                    </p>
                </div>

                <div class="flex overflow-x-auto snap-x snap-mandatory gap-3 pb-4 -mx-4 px-4 md:mx-0 md:px-0 no-scrollbar select-none">
                    <div v-for="(stat, index) in stats" :key="index" 
                         class="snap-start shrink-0 w-[140px] card !p-3 flex flex-col justify-between h-24 border border-border/60 shadow-sm bg-card">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-black uppercase tracking-wider text-muted-foreground">
                                {{ stat.label }}
                            </span>
                            <div :class="`p-1.5 rounded-full ${stat.bg} ${stat.color}`">
                                <component :is="stat.icon" :size="14" stroke-width="2.5" />
                            </div>
                        </div>
                        <span class="font-display font-black text-foreground tracking-tight" 
                              :class="stat.isText ? 'text-sm mt-1' : 'text-2xl'">
                            {{ stat.value }}
                        </span>
                    </div>
                </div>

                <div class="sticky top-2 z-30 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <Search class="h-4 w-4 text-muted-foreground group-focus-within:text-primary transition-colors duration-300" />
                    </div>
                    <input 
                        v-model="search"
                        type="text" 
                        class="block w-full pl-11 pr-4 py-3.5 bg-background/80 backdrop-blur-md border border-border rounded-xl text-sm font-medium text-foreground placeholder-muted-foreground shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300" 
                        placeholder="Buscar proveedor..." 
                    />
                </div>
            </div>

            <div class="flex-1 px-4 md:px-0">
                <div v-if="providers.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    
                    <div v-for="provider in providers.data" :key="provider.id" 
                         class="card group border border-border bg-card hover:shadow-lg hover:border-primary/30 transition-all duration-300 overflow-hidden flex flex-col">
                        
                        <div class="p-5 flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/10 to-blue-600/10 border border-primary/10 flex items-center justify-center shrink-0 text-primary font-black text-lg shadow-inner">
                                    {{ getInitials(provider.name || provider.company_name) }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-base text-foreground truncate leading-tight">
                                        {{ provider.name || provider.company_name }}
                                    </h3>
                                    <div class="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground">
                                        <User :size="12" />
                                        <span class="truncate font-medium">
                                            {{ provider.contact_name || 'Sin contacto' }}
                                        </span>
                                    </div>
                                </div>

                                <div v-if="can_manage" class="flex items-center gap-1 -mr-2">
                                    <Link :href="route('admin.providers.edit', provider.id)" 
                                          class="btn btn-ghost btn-sm w-8 h-8 p-0 text-muted-foreground hover:text-primary hover:bg-primary/5 rounded-lg transition-colors">
                                        <Edit :size="16" />
                                    </Link>
                                    <button @click="handleDelete(provider)" 
                                            class="btn btn-ghost btn-sm w-8 h-8 p-0 text-muted-foreground hover:text-error hover:bg-error/5 rounded-lg transition-colors">
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </div>

                            <div class="mt-5 space-y-2.5">
                                <div v-if="provider.email" class="flex items-center gap-2.5 text-sm text-muted-foreground group-hover:text-foreground transition-colors">
                                    <div class="p-1 rounded-md bg-muted/50 text-muted-foreground">
                                        <Mail :size="12" />
                                    </div>
                                    <span class="truncate">{{ provider.email }}</span>
                                </div>
                                <div v-if="provider.address" class="flex items-center gap-2.5 text-xs text-muted-foreground line-clamp-1">
                                    <div class="p-1 rounded-md bg-muted/50 text-muted-foreground">
                                        <MapPin :size="12" />
                                    </div>
                                    <span>{{ provider.address }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 border-t border-border divide-x divide-border bg-muted/5">
                            <a v-if="provider.phone" :href="`tel:${provider.phone}`" 
                               class="flex items-center justify-center gap-2 py-3 text-xs font-bold text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors">
                                <Phone :size="14" /> Llamar
                            </a>
                            <span v-else class="flex items-center justify-center gap-2 py-3 text-xs text-muted-foreground/40 cursor-not-allowed">
                                <Phone :size="14" /> N/A
                            </span>

                            <Link :href="route('admin.providers.edit', provider.id)" 
                                  class="flex items-center justify-center gap-2 py-3 text-xs font-bold text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors">
                                <Truck :size="14" /> Detalles
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-24 text-center animate-in fade-in zoom-in duration-500">
                    <div class="w-24 h-24 bg-card border-2 border-dashed border-border rounded-full flex items-center justify-center mb-6">
                        <Search :size="40" class="text-muted-foreground" stroke-width="1.5"/>
                    </div>
                    <h3 class="text-xl font-display font-black text-foreground">Sin Resultados</h3>
                    <p class="text-sm text-muted-foreground mt-2 max-w-xs mx-auto">
                        No se encontraron proveedores.
                    </p>
                    <button v-if="search" @click="search = ''" class="mt-6 btn btn-outline btn-sm">
                        Limpiar búsqueda
                    </button>
                </div>
            </div>

            <div v-if="providers.links && providers.links.length > 3" class="mt-8 px-4 md:px-0 flex justify-center pb-4">
                <div class="flex gap-1.5 overflow-x-auto max-w-full pb-2 no-scrollbar">
                    <template v-for="(link, k) in providers.links" :key="k">
                        <Link v-if="link.url"
                              :href="link.url"
                              v-html="link.label"
                              class="min-w-[40px] h-10 flex items-center justify-center rounded-lg text-xs font-bold border transition-all active:scale-95"
                              :class="{
                                  'bg-primary text-primary-foreground border-primary shadow-md shadow-primary/20': link.active,
                                  'bg-card text-muted-foreground border-border hover:border-primary/50 hover:text-foreground': !link.active
                              }"
                              :preserve-scroll="true" />
                        <span v-else
                              v-html="link.label"
                              class="min-w-[40px] h-10 flex items-center justify-center rounded-lg text-xs text-muted-foreground/30 border border-transparent cursor-default">
                        </span>
                    </template>
                </div>
            </div>

            <Teleport to="body">
                <Link v-if="can_manage" 
                      :href="route('admin.providers.create')" 
                      class="fixed bottom-24 right-4 md:right-8 z-[9999] group predictive-aura">
                    <div class="w-14 h-14 rounded-full bg-primary text-primary-foreground shadow-[0_8px_30px_rgba(0,240,255,0.4)] flex items-center justify-center transition-transform duration-300 group-hover:scale-110 group-active:scale-95 border-2 border-white/10">
                        <Plus :size="28" stroke-width="3" class="group-hover:rotate-90 transition-transform duration-300"/>
                    </div>
                    <span class="sr-only">Crear Proveedor</span>
                </Link>
            </Teleport>

        </div>
    </AdminLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>