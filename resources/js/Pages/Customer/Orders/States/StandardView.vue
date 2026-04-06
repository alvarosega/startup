<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ArrowLeft, Package, MapPin, CheckCircle } from 'lucide-vue-next';

const props = defineProps({ order: Object });

// Diccionario de estados estáticos
const statusMeta = {
    confirmed: { title: 'Orden Confirmada', desc: 'Esperando procesamiento de almacén.' },
    preparing: { title: 'En Preparación', desc: 'Consolidando artículos físicos.' },
    delivered: { title: 'Completado', desc: 'Orden entregada satisfactoriamente.' },
    cancelled: { title: 'Anulado', desc: 'La operación fue abortada.' },
    returned:  { title: 'Devolución', desc: 'La mercancía ha retornado a almacén.' }
};
</script>

<template>
    <ShopLayout>
        <Head :title="'Orden ' + order.code" />
        <div class="max-w-3xl mx-auto p-6 min-h-[70vh]">
            <Link :href="route('customer.orders.index')" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-foreground mb-8">
                <ArrowLeft :size="14" /> Historial
            </Link>

            <div class="bg-card border border-border rounded-[2rem] p-8 shadow-xl text-center">
                <h1 class="text-3xl font-black italic uppercase tracking-tighter mb-6">#{{ order.code }}</h1>

                <div v-if="order.status === 'ready_for_dispatch'" class="mb-8">
                    <div v-if="order.delivery_type === 'pickup'" class="bg-primary/10 border border-primary/20 p-6 rounded-2xl animate-in zoom-in-95">
                        <MapPin class="mx-auto text-primary mb-3" :size="32" />
                        <h2 class="text-xl font-black text-primary uppercase italic tracking-tighter">Listo para Recojo</h2>
                        <p class="text-xs font-bold text-muted-foreground mt-2">Preséntate en la sucursal indicada con tu ID de orden.</p>
                        <div class="mt-4 bg-background p-4 rounded-xl border border-border inline-block text-left">
                            <span class="text-[9px] uppercase tracking-widest font-black text-muted-foreground">Sucursal Asignada</span>
                            <p class="text-sm font-black text-foreground">{{ order.branch?.name }}</p>
                            <p class="text-[10px] text-muted-foreground font-bold mt-1">{{ order.branch?.address }}</p>
                        </div>
                    </div>
                    
                    <div v-else class="bg-blue-500/10 border border-blue-500/20 p-6 rounded-2xl">
                        <Package class="mx-auto text-blue-500 mb-3" :size="32" />
                        <h2 class="text-xl font-black text-blue-500 uppercase italic tracking-tighter">Empaquetado</h2>
                        <p class="text-xs font-bold text-muted-foreground mt-2">Esperando asignación de conductor para el despacho.</p>
                    </div>
                </div>

                <div v-else class="py-10">
                    <CheckCircle class="mx-auto text-muted-foreground mb-4" :size="40" />
                    <h2 class="text-2xl font-black text-foreground uppercase italic tracking-tighter">
                        {{ statusMeta[order.status]?.title || order.status }}
                    </h2>
                    <p class="text-xs font-bold text-muted-foreground mt-2">
                        {{ statusMeta[order.status]?.desc }}
                    </p>
                </div>

                <div class="border-t border-border pt-6 mt-6 flex justify-between items-center text-xs">
                    <span class="font-black uppercase tracking-widest text-muted-foreground">Total Liquidado</span>
                    <span class="font-mono font-black text-lg">Bs {{ Number(order.total_amount).toFixed(2) }}</span>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>