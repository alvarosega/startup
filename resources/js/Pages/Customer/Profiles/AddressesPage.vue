<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProfileNav from './Partials/ProfileNav.vue'; 
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from 'leaflet';
import { 
    MapPin, Plus, Star, Trash2, Edit2, Map as MapIcon 
} from 'lucide-vue-next';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    user: Object, 
    addresses: Array,
    activeBranches: Array
});

const MAX_ADDRESSES = 5;
const canAddMore = computed(() => props.addresses.length < MAX_ADDRESSES);

// Icono personalizado para el mapa (Misma estética que el registro)
const customIcon = L.divIcon({
    className: 'custom-pin',
    html: `<div class="w-8 h-8 bg-zinc-900 rounded-full border-[3px] border-white shadow-lg flex items-center justify-center text-white"><div class="w-2 h-2 bg-white rounded-full"></div></div>`,
    iconSize: [32, 32],
    iconAnchor: [16, 32]
});

// Calcula el centro del mapa basado en la primera dirección (o La Paz por defecto)
const mapCenter = computed(() => {
    if (props.addresses.length > 0) {
        return [parseFloat(props.addresses[0].latitude), parseFloat(props.addresses[0].longitude)];
    }
    return [-16.5000, -68.1500];
});

// Acciones de registro
const makeDefault = (id) => {
    router.patch(route('customer.profile.addresses.set-default', { id }), {}, { preserveScroll: true });
};

const deleteAddr = (id) => {
    if(confirm('¿Eliminar esta ubicación permanentemente?')) {
        router.delete(route('customer.profile.addresses.destroy', { id }), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Mis Direcciones" />
    <ShopLayout :is-profile-section="true">
        <div class="max-w-4xl mx-auto pb-24 px-4 py-8">
            
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-zinc-900 tracking-tight uppercase italic">Mis Direcciones</h1>
                <p class="text-zinc-500 text-sm mt-1">Terminales logísticas de entrega ({{ addresses.length }}/{{ MAX_ADDRESSES }}).</p>
            </div>

            <ProfileNav />

            <div class="flex justify-end mb-6">
                <Link v-if="canAddMore" :href="route('customer.profile.addresses.create')" 
                      class="flex items-center gap-2 bg-zinc-900 text-white px-6 py-3 rounded-2xl font-bold text-sm hover:bg-zinc-800 transition-all active:scale-95 shadow-lg shadow-zinc-200">
                    <Plus :size="18" /> Nueva Ubicación
                </Link>
            </div>

            <div class="mb-8 rounded-[2.5rem] overflow-hidden border border-zinc-200 shadow-sm h-72 relative bg-zinc-100 z-10">
                <l-map :zoom="14" :center="mapCenter" class="h-full w-full grayscale-[0.2]">
                    <l-tile-layer url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png" />
                    <l-marker v-for="addr in addresses" :key="addr.id" 
                              :lat-lng="[parseFloat(addr.latitude), parseFloat(addr.longitude)]" 
                              :icon="customIcon" />
                </l-map>
                
                <div class="absolute top-4 left-4 z-[400] bg-white/90 backdrop-blur px-3 py-1.5 rounded-full border border-zinc-200 text-[10px] font-black uppercase tracking-widest text-zinc-500 flex items-center gap-2 shadow-sm pointer-events-none">
                    <MapIcon :size="12" /> Mapa Táctico
                </div>
            </div>

            <div class="grid gap-4">
                <div v-if="addresses.length === 0" class="text-center py-20 bg-zinc-50 rounded-[2.5rem] border border-dashed border-zinc-300">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 border border-zinc-200 shadow-sm">
                        <MapPin :size="24" class="text-zinc-300" />
                    </div>
                    <p class="text-zinc-500 font-medium">No tienes direcciones guardadas.</p>
                </div>

                <div v-for="addr in addresses" :key="addr.id" 
                     class="bg-white rounded-3xl border p-6 flex justify-between items-center transition-all group hover:shadow-md"
                     :class="addr.is_default ? 'border-zinc-900 bg-zinc-50/50' : 'border-zinc-200'">
                    
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors"
                             :class="addr.is_default ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-400 group-hover:bg-zinc-200'">
                            <MapPin :size="22" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-bold text-zinc-900 text-sm uppercase tracking-tight">{{ addr.alias }}</h4>
                                <span v-if="addr.is_default" class="text-[9px] bg-zinc-900 text-white px-2 py-0.5 rounded-full font-black uppercase tracking-tighter">Predeterminada</span>
                            </div>
                            <p class="text-xs text-zinc-500 line-clamp-1 max-w-sm font-medium">{{ addr.address }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <button @click="makeDefault(addr.id)" v-if="!addr.is_default" title="Fijar como principal" class="p-3 text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 rounded-xl transition-all">
                            <Star :size="18" />
                        </button>
                        <Link :href="route('customer.profile.addresses.edit', { id: addr.id })" class="p-3 text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 rounded-xl transition-all inline-block">
                            <Edit2 :size="18" />
                        </Link>
                        <button @click="deleteAddr(addr.id)" class="p-3 text-zinc-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                            <Trash2 :size="18" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>