<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Truck, MapPin, Phone, ShieldAlert, ArrowLeft, Package } from 'lucide-vue-next';

const props = defineProps({ order: Object });

const statusMap = {
    preparing: 'Empacando en Almacén',
    ready_for_dispatch: 'Listo para Despacho',
    dispatched: 'En Camino a su Domicilio',
    arrived: 'El Conductor ha llegado'
};
</script>

<template>
    <ShopLayout :isProfileSection="true">
        <Head :title="'Seguimiento #' + order.code" />

        <div class="max-w-xl mx-auto min-h-[70vh] py-8">
            <Link :href="route('customer.order.index')" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-foreground/50 hover:text-primary mb-8 transition-colors">
                <ArrowLeft :size="14" /> Volver al Historial
            </Link>

            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-primary/10 text-primary rounded-3xl mb-4 border border-primary/20">
                    <Truck :size="32" stroke-width="1.5" />
                </div>
                <h1 class="text-2xl font-black uppercase italic tracking-tighter text-foreground">
                    Estado Logístico
                </h1>
                <p class="text-[10px] font-mono text-primary font-black uppercase tracking-[0.2em] mt-2">
                    {{ statusMap[order.status] }}
                </p>
            </div>

            <div v-if="order.delivery_type === 'delivery'" class="bg-amber-500/10 border-2 border-amber-500/30 rounded-3xl p-8 mb-6 text-center shadow-lg shadow-amber-500/5">
                <ShieldAlert class="mx-auto text-amber-500 mb-3" :size="24" />
                <h3 class="text-[10px] font-black uppercase tracking-widest text-amber-600 mb-4">Código de Recepción</h3>
                <div class="bg-surface/50 inline-block px-10 py-4 rounded-2xl border border-amber-500/20">
                    <span class="text-6xl font-mono font-black tracking-[0.2em] text-foreground">{{ order.delivery_otp }}</span>
                </div>
                <p class="text-[10px] font-bold text-amber-600/70 uppercase mt-4 max-w-xs mx-auto">
                    Dicte este código al conductor únicamente cuando tenga el paquete en sus manos.
                </p>
            </div>

            <div class="bg-surface/40 border border-white/10 rounded-3xl p-6">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground/40 mb-4 flex items-center gap-2">
                    <MapPin :size="14"/> Cadena de Custodia
                </h3>

                <div v-if="order.driver" class="flex items-center gap-4 bg-primary/5 border border-primary/20 p-4 rounded-2xl">
                    <div class="w-12 h-12 bg-primary text-background rounded-xl flex items-center justify-center font-black">
                        {{ order.driver.name.charAt(0) }}
                    </div>
                    <div class="flex-1">
                        <p class="text-[9px] font-black uppercase tracking-widest text-primary mb-0.5">Asignado</p>
                        <p class="text-sm font-black text-foreground uppercase">{{ order.driver.name }}</p>
                    </div>
                    <a :href="'tel:' + order.driver.phone" class="w-12 h-12 bg-white/5 border border-white/10 text-foreground hover:bg-primary hover:text-background hover:border-primary rounded-xl flex items-center justify-center transition-all">
                        <Phone :size="18" />
                    </a>
                </div>

                <div v-else class="flex items-center gap-4 bg-white/5 border border-white/10 p-4 rounded-2xl opacity-60">
                    <div class="w-12 h-12 bg-black/20 rounded-xl flex items-center justify-center text-foreground/30">
                        <Package :size="20" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-foreground/50">Buscando Repartidor...</p>
                        <p class="text-[9px] font-bold text-foreground/40 mt-1">El almacén está preparando el paquete.</p>
                    </div>
                </div>
            </div>

        </div>
    </ShopLayout>
</template>