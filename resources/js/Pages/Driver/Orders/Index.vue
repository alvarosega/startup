<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { MapPin, PackageCheck, Clock, CheckCircle, Truck } from 'lucide-vue-next';
import DriverLayout from '@/Layouts/DriverLayout.vue';

defineProps({ orders: Array });

const form = useForm({});

const takeOrder = (code) => {
    if (confirm('¿Confirmas tu compromiso para transportar esta carga?')) {
        form.post(route('driver.orders.take', code));
    }
};
</script>

<template>
    <DriverLayout>
        <Head title="Cargas Disponibles" />
        <div class="p-4 space-y-4 pb-24">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                    <Truck :size="20" />
                </div>
                <h1 class="text-2xl font-black uppercase italic tracking-tighter">Bolsa de <span class="text-primary">Cargas</span></h1>
            </div>
            
            <div v-if="orders.length > 0" class="grid gap-4">
                <div v-for="order in orders" :key="order.id" 
                    class="bg-card border-2 border-border rounded-3xl p-5 shadow-sm flex flex-col gap-4">
                    
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-black font-mono text-primary">#{{ order.code }}</span>
                                <span v-if="order.status === 'ready_for_dispatch'" class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 text-[8px] font-black uppercase rounded-md">Lista para Recoger</span>
                                <span v-else class="px-2 py-0.5 bg-amber-500/10 text-amber-500 border border-amber-500/20 text-[8px] font-black uppercase rounded-md flex items-center gap-1"><Clock :size="10"/> Empacando</span>
                            </div>
                            <p class="text-sm font-bold uppercase">{{ order.branch.name }}</p>
                            <div class="flex items-center gap-1 text-[10px] text-muted-foreground font-bold leading-tight">
                                <MapPin :size="12" class="shrink-0" /> {{ order.branch.address }}
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] uppercase font-black tracking-widest text-muted-foreground mb-1">Monto</p>
                            <p class="text-xl font-black font-mono leading-none text-foreground">Bs {{ order.total_amount }}</p>
                        </div>
                    </div>

                    <button @click="takeOrder(order.code)" :disabled="form.processing"
                        class="w-full py-4 bg-foreground text-background rounded-2xl font-black uppercase text-xs tracking-widest flex items-center justify-center gap-2 active:scale-95 transition-all disabled:opacity-50">
                        <CheckCircle :size="16" /> {{ form.processing ? 'Asignando...' : 'Aceptar Carga' }}
                    </button>
                </div>
            </div>
            
            <div v-else class="py-20 text-center space-y-4 opacity-40">
                <PackageCheck :size="64" class="mx-auto" />
                <p class="text-xs font-black uppercase tracking-widest">No hay cargas pendientes en tu sucursal</p>
            </div>
        </div>
    </DriverLayout>
</template>