<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProfileNav from './Partials/ProfileNav.vue';
import ClientLocationPicker from '@/Components/Maps/ClientLocationPicker.vue';
import UserAddressesMap from '@/Components/Maps/UserAddressesMap.vue';
import { MapPin, Plus, Star, Trash2, Edit2, X, Save, AlertTriangle, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    addresses: Array,
    activeBranches: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    alias: '', address: '', details: '',
    latitude: -16.5000, longitude: -68.1500, 
    branch_id: null, is_default: false
});

const openCreate = () => {
    isEditing.value = false;
    form.reset();
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
    const routeName = isEditing.value ? 'customer.profile.addresses.update' : 'customer.profile.addresses.store';
    const params = isEditing.value ? { id: editingId.value } : {};
    
    form[isEditing.value ? 'put' : 'post'](route(routeName, params), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
        preserveScroll: true
    });
};

const makeDefault = (id) => {
    router.patch(route('customer.profile.addresses.set-default', { id }), {}, { preserveScroll: true });
};

const deleteAddr = (id) => {
    if(confirm('¿Eliminar ubicación?')) {
        router.delete(route('customer.profile.addresses.destroy', { id }), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Mis Direcciones - Electric Luxury" />
    <ShopLayout :is-profile-section="true">
        <div class="max-w-4xl mx-auto pb-20 px-4">
            <div class="mb-6">
                <h1 class="text-2xl font-black text-foreground tracking-tight uppercase italic text-navy">Mis Direcciones</h1>
                <p class="text-sm text-muted-foreground uppercase tracking-widest font-bold">Gestión de entrega</p>
            </div>

            <ProfileNav />

            <div class="mb-8 rounded-[2.5rem] overflow-hidden border border-border shadow-xl h-64 relative">
                <UserAddressesMap :addresses="addresses" :activeBranches="activeBranches" />
                <button @click="openCreate" v-if="addresses.length < 3" 
                        class="absolute bottom-4 right-4 bg-primary text-primary-foreground p-4 rounded-2xl shadow-2xl z-[500] hover:scale-110 transition-transform active:scale-95">
                    <Plus :size="24" stroke-width="3" />
                </button>
            </div>

            <div class="grid gap-4">
                <div v-for="addr in addresses" :key="addr.id" 
                     class="bg-card rounded-3xl border p-6 flex justify-between items-center transition-all group"
                     :class="addr.is_default ? 'border-primary/50 bg-primary/5 shadow-sm' : 'border-border'">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-2xl bg-muted/50 text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                            <MapPin :size="24" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-black text-sm uppercase">{{ addr.alias }}</h4>
                                <span v-if="addr.is_default" class="text-[9px] bg-primary/20 text-primary px-2 py-0.5 rounded-full font-black uppercase">Principal</span>
                            </div>
                            <p class="text-xs text-muted-foreground line-clamp-1 max-w-xs">{{ addr.address }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="makeDefault(addr.id)" v-if="!addr.is_default" title="Marcar como principal" class="p-2 text-muted-foreground hover:text-warning transition">
                            <Star :size="20" />
                        </button>
                        <button @click="openEdit(addr)" class="p-2 text-muted-foreground hover:text-primary transition">
                            <Edit2 :size="20" />
                        </button>
                        <button @click="deleteAddr(addr.id)" class="p-2 text-muted-foreground hover:text-destructive transition">
                            <Trash2 :size="20" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-[600] flex items-end md:items-center justify-center p-4">
            <div class="absolute inset-0 bg-navy/80 backdrop-blur-md" @click="showModal = false"></div>
            
            <div class="relative bg-card w-full max-w-2xl rounded-t-[2rem] md:rounded-[2.5rem] shadow-2xl flex flex-col overflow-hidden animate-in slide-in-from-bottom duration-300">
                <div class="p-6 border-b border-border flex justify-between items-center bg-muted/20">
                    <h3 class="font-black text-navy uppercase tracking-tighter">{{ isEditing ? 'Editar' : 'Nueva' }} Ubicación</h3>
                    <button @click="showModal = false" class="p-2 hover:bg-muted rounded-full transition"><X :size="20"/></button>
                </div>

                <div class="flex-1 overflow-y-auto max-h-[70vh]">
                    <div class="h-64 border-b border-border">
                        <ClientLocationPicker 
                            v-model:modelValueLat="form.latitude"
                            v-model:modelValueLng="form.longitude"
                            v-model:modelValueAddress="form.address"
                            v-model:modelValueBranchId="form.branch_id"
                            :activeBranches="activeBranches"
                        />
                    </div>

                    <div class="p-8 space-y-6">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Alias</label>
                                <input v-model="form.alias" class="w-full bg-muted/30 border-border rounded-2xl p-4 font-bold focus:ring-primary" placeholder="Ej: Oficina">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Referencia</label>
                                <input v-model="form.details" class="w-full bg-muted/30 border-border rounded-2xl p-4 font-bold focus:ring-primary" placeholder="Ej: Piso 4">
                            </div>
                        </div>
                        
                        <div class="p-4 bg-muted/10 rounded-2xl border border-dashed border-border">
                            <p class="text-[10px] font-black text-muted-foreground uppercase mb-1">Dirección Detectada</p>
                            <p class="text-sm font-bold text-navy">{{ form.address || 'Selecciona un punto en el mapa' }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-muted/20 border-t border-border">
                    <button @click="submit" :disabled="form.processing || !form.address" 
                            class="w-full bg-primary text-primary-foreground py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-primary/20 transition-all active:scale-95 disabled:opacity-50">
                        <span v-if="form.processing" class="loading loading-spinner"></span>
                        <span v-else class="flex items-center justify-center gap-2">
                             <Save :size="18"/> {{ isEditing ? 'Actualizar' : 'Guardar' }} Ubicación
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>