<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { 
    MapPin, Plus, Star, Trash2, Edit2, X, Save, 
    ChevronDown, AlertTriangle, Navigation, CheckCircle2 
} from 'lucide-vue-next';
import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
import UserAddressesMap from '@/Components/Maps/UserAddressesMap.vue';

const props = defineProps({
    addresses: { type: Array, default: () => [] },
    activeBranches: { type: Array, default: () => [] }
});

const isOpen = ref(false);
const userMapRef = ref(null);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

watch(isOpen, (newValue) => {
    if (newValue) {
        // CORRECCIÓN: Aumentamos a 400ms (la animación dura 300ms).
        // Ese extra de 100ms asegura que el div ya está 100% abierto y quieto.
        setTimeout(() => {
            if (userMapRef.value) {
                userMapRef.value.refresh();
            }
        }, 400); 
    }
});

const form = useForm({
    alias: '', address: '', details: '',
    latitude: -16.5000, longitude: -68.1500, 
    branch_id: null, is_default: false
});

const openCreate = () => {
    isEditing.value = false;
    form.reset();
    form.latitude = -16.5000; 
    form.longitude = -68.1500;
    showModal.value = true;
};

const openEdit = (addr) => {
    isEditing.value = true;
    editingId.value = addr.id;
    form.alias = addr.alias;
    form.address = addr.address;
    form.details = addr.reference || ''; 
    form.latitude = parseFloat(addr.latitude);
    form.longitude = parseFloat(addr.longitude);
    form.branch_id = addr.branch_id;
    form.is_default = Boolean(addr.is_default);
    showModal.value = true;
};

const submit = () => {
    const routeName = isEditing.value ? 'addresses.update' : 'addresses.store';
    const routeParams = isEditing.value ? editingId.value : undefined;
    
    form[isEditing.value ? 'put' : 'post'](route(routeName, routeParams), {
        onSuccess: () => showModal.value = false,
        preserveScroll: true
    });
};

const deleteAddr = (id) => {
    if(confirm('¿Estás seguro de eliminar esta dirección?')) {
        router.delete(route('addresses.destroy', id), { preserveScroll: true });
    }
};

const makeDefault = (id) => {
    router.patch(route('addresses.set-default', id), {}, { preserveScroll: true });
};
</script>

<template>
    <section class="bg-card rounded-3xl shadow-sm border border-border overflow-hidden transition-all duration-300">
        
        <button @click="isOpen = !isOpen" class="w-full relative overflow-hidden group text-left transition-colors hover:bg-muted/20">
            <div class="relative p-6 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-orange-500/10 text-orange-500 flex items-center justify-center border border-orange-500/20">
                        <MapPin :size="20" stroke-width="2.5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-black text-foreground text-lg tracking-tight leading-none">Direcciones</h2>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border transition-colors"
                                  :class="addresses.length >= 3 ? 'bg-destructive/10 text-destructive border-destructive/20' : 'bg-muted text-muted-foreground border-border'">
                                {{ addresses.length }}/3
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground font-medium mt-1">Gestión de envíos</p>
                    </div>
                </div>
                
                <div class="w-8 h-8 rounded-full bg-muted/50 flex items-center justify-center text-muted-foreground transition-all duration-300 border border-transparent group-hover:border-border" 
                     :class="{'rotate-180 bg-primary/10 text-primary border-primary/20': isOpen}">
                    <ChevronDown :size="18"/>
                </div>
            </div>
        </button>

        <div v-show="isOpen" class="animate-in slide-in-from-top-2 duration-300 ease-out origin-top">
            <div class="p-6 bg-card border-t border-border/50">
                
                <div v-if="addresses.length > 0" class="mb-6 rounded-2xl overflow-hidden border border-border shadow-inner">
                    <UserAddressesMap 
                        ref="userMapRef" :addresses="addresses" 
                        :activeBranches="activeBranches" 
                    />
                    <div class="bg-muted/30 py-2 px-4 flex justify-center gap-4 text-[10px] font-bold text-muted-foreground uppercase tracking-wider border-t border-border">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-warning"></span> Principal</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-primary"></span> Otras</span>
                    </div>
                </div>

                <div class="mb-6">
                    <button v-if="addresses.length < 3" @click="openCreate" 
                            class="w-full py-4 border-2 border-dashed border-border hover:border-primary/50 hover:bg-primary/5 rounded-2xl flex items-center justify-center gap-2 text-muted-foreground hover:text-primary font-bold transition-all group active:scale-[0.98]">
                        <div class="bg-muted group-hover:bg-primary/20 p-1.5 rounded-full transition-colors">
                            <Plus :size="18" />
                        </div>
                        <span>Agregar Nueva Ubicación</span>
                    </button>
                    
                    <div v-else class="w-full py-3 bg-warning/10 border border-warning/20 rounded-xl flex items-center justify-center gap-2 text-warning text-xs font-bold">
                        <AlertTriangle :size="14" />
                        Límite de direcciones alcanzado (3/3).
                    </div>
                </div>

                <div v-if="addresses.length === 0" class="text-center py-10 opacity-60">
                    <Navigation :size="48" class="mx-auto mb-3 text-muted-foreground/50"/>
                    <p class="text-sm font-medium text-muted-foreground">No tienes direcciones guardadas.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="addr in addresses" :key="addr.id" 
                         class="relative rounded-2xl border transition-all duration-200 p-4 group overflow-hidden"
                         :class="[
                            addr.is_default 
                                ? 'bg-primary/5 border-primary/30 ring-1 ring-primary/20' 
                                : 'bg-background border-border hover:border-muted-foreground/30',
                            !addr.branch_id ? 'opacity-70 grayscale-[0.5]' : ''
                         ]"
                    >
                        <div v-if="addr.is_default" class="absolute top-0 right-0 bg-primary text-primary-foreground text-[9px] font-black px-3 py-1 rounded-bl-xl uppercase tracking-widest shadow-sm">
                            Principal
                        </div>

                        <div class="flex items-start gap-3 mb-3">
                            <div class="p-2.5 rounded-xl shrink-0" 
                                 :class="addr.branch_id ? 'bg-success/10 text-success' : 'bg-muted text-muted-foreground'">
                                <MapPin :size="20" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-black text-foreground text-sm truncate flex items-center gap-2">
                                    {{ addr.alias }}
                                </h4>
                                
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <span v-if="!addr.branch_id" class="inline-flex items-center gap-1 text-[10px] bg-destructive/10 text-destructive px-2 py-0.5 rounded border border-destructive/20 font-bold uppercase">
                                        <AlertTriangle :size="10"/> Sin Cobertura
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 text-[10px] bg-success/10 text-success px-2 py-0.5 rounded border border-success/20 font-bold uppercase">
                                        <CheckCircle2 :size="10"/> Con Cobertura
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 pl-1">
                            <p class="text-sm text-foreground/80 font-medium leading-relaxed line-clamp-2">
                                {{ addr.address }}
                            </p>
                            <p v-if="addr.reference" class="text-xs text-muted-foreground mt-1.5 italic flex items-center gap-1">
                                <span class="w-1 h-4 bg-border rounded-full"></span> {{ addr.reference }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 pt-3 border-t border-border/50">
                            <button v-if="!addr.is_default" @click="makeDefault(addr.id)" title="Hacer Principal"
                                    class="p-2 text-muted-foreground hover:text-warning hover:bg-warning/10 rounded-lg transition">
                                <Star :size="18" />
                            </button>
                            <div v-else class="p-2 text-warning">
                                <Star :size="18" fill="currentColor" />
                            </div>

                            <div class="h-4 w-px bg-border mx-1"></div>

                            <button @click="openEdit(addr)" class="flex-1 py-1.5 text-xs font-bold text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition flex items-center justify-center gap-1.5">
                                <Edit2 :size="14"/> Editar
                            </button>
                            
                            <button @click="deleteAddr(addr.id)" class="flex-1 py-1.5 text-xs font-bold text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition flex items-center justify-center gap-1.5">
                                <Trash2 :size="14"/> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center">
            <div class="absolute inset-0 bg-background/80 backdrop-blur-md transition-opacity" @click="showModal = false"></div>
            
            <div class="relative bg-card w-full h-[100svh] md:h-auto md:max-h-[90vh] md:max-w-2xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-slide-up border border-border">
                
                <div class="px-4 py-3 border-b border-border flex justify-between items-center bg-card/80 backdrop-blur-md absolute top-0 left-0 right-0 z-20 md:relative">
                    <button @click="showModal = false" class="p-2 bg-muted/50 hover:bg-muted rounded-full transition text-foreground">
                        <X :size="20"/>
                    </button>
                    <h3 class="font-black text-foreground text-base uppercase tracking-wider">
                        {{ isEditing ? 'Editar' : 'Nueva' }} Ubicación
                    </h3>
                    <div class="w-9"></div> </div>

                <div class="flex-1 overflow-y-auto relative scrollbar-thin pt-16 md:pt-0">
                    
                    <div class="h-[40vh] w-full sticky top-0 z-0 border-b border-border">
                        <ClientLocationPicker
                            v-model:modelValueLat="form.latitude"
                            v-model:modelValueLng="form.longitude"
                            v-model:modelValueAddress="form.address"
                            v-model:modelValueBranchId="form.branch_id"
                            :activeBranches="activeBranches"
                        />
                        <div class="absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-card to-transparent pointer-events-none"></div>
                    </div>

                    <div class="p-6 space-y-6 bg-card relative z-10 min-h-[50vh]">
                        
                        <div v-if="form.branch_id" class="p-3 bg-success/10 border border-success/20 rounded-xl flex items-center gap-2 text-success text-xs font-bold animate-in fade-in">
                            <CheckCircle2 :size="16" /> Zona con Cobertura Disponible.
                        </div>
                        <div v-else class="p-3 bg-warning/10 border border-warning/20 rounded-xl flex items-center gap-2 text-warning text-xs font-bold animate-in fade-in">
                            <AlertTriangle :size="16" /> Zona fuera de cobertura.
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest ml-1">Alias (Nombre)</label>
                                <input v-model="form.alias" type="text" 
                                       class="w-full bg-background border-border rounded-xl text-sm mt-1 focus:ring-primary focus:border-primary p-3 text-foreground placeholder:text-muted-foreground/50 transition-all" 
                                       placeholder="Ej: Casa, Oficina, Gimnasio...">
                                <p v-if="form.errors.alias" class="text-destructive text-xs mt-1 font-bold">{{ form.errors.alias }}</p>
                            </div>
                            
                            <div>
                                <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest ml-1">Detalles / Referencia</label>
                                <input v-model="form.details" type="text" 
                                       class="w-full bg-background border-border rounded-xl text-sm mt-1 focus:ring-primary focus:border-primary p-3 text-foreground placeholder:text-muted-foreground/50 transition-all" 
                                       placeholder="Ej: Portón negro, timbre roto...">
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest ml-1">Dirección Detectada</label>
                                <div class="w-full bg-muted/30 border border-border rounded-xl text-xs mt-1 p-3 text-muted-foreground flex items-start gap-2">
                                    <MapPin :size="14" class="shrink-0 mt-0.5 text-primary"/>
                                    <span class="line-clamp-2">{{ form.address || 'Mueve el pin en el mapa para detectar...' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 rounded-xl border border-border bg-background hover:border-primary/50 transition cursor-pointer" @click="form.is_default = !form.is_default">
                            <div class="relative flex items-center">
                                <input v-model="form.is_default" type="checkbox" 
                                       class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-muted-foreground/30 bg-card checked:border-primary checked:bg-primary transition-all">
                                <div class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-primary-foreground opacity-0 peer-checked:opacity-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-foreground select-none">Usar como ubicación principal</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-border bg-card shrink-0 shadow-[0_-5px_20px_rgba(0,0,0,0.1)] pb-safe z-20">
                    <button 
                        @click="submit" 
                        :disabled="form.processing || !form.address" 
                        class="w-full bg-primary hover:bg-primary/90 text-primary-foreground py-4 rounded-xl font-bold text-base shadow-lg shadow-primary/20 flex items-center justify-center gap-2 active:scale-[0.98] transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <Save v-if="!form.processing" :size="20"/> 
                        <span v-else class="loading loading-spinner loading-sm"></span>
                        {{ form.processing ? 'Guardando...' : 'Guardar Ubicación' }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes slide-up {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
.animate-slide-up {
    animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Env variable for iPhone Home Indicator */
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 1rem);
}
</style>