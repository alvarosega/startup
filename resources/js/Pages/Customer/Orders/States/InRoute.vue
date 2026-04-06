<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ArrowLeft, Truck, Phone, ShieldCheck, MapPin } from 'lucide-vue-next';

const props = defineProps({ order: Object });

const driver = props.order.driver;
</script>

<template>
    <ShopLayout>
        <Head :title="'Pedido en Camino ' + order.code" />
        
        <div class="max-w-xl mx-auto p-6 pb-24">
            <Link :href="route('customer.orders.index')" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-foreground mb-8">
                <ArrowLeft :size="14" /> Historial
            </Link>

            <div class="space-y-6">
                <div class="bg-card border border-border rounded-[2.5rem] p-8 shadow-xl text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-primary"></div>
                    <h1 class="text-3xl font-black italic uppercase tracking-tighter mb-1">#{{ order.code }}</h1>
                    <p class="text-[10px] font-black uppercase tracking-widest text-primary animate-pulse">
                        {{ order.status === 'dispatched' ? 'Unidad en tránsito' : 'Unidad en destino' }}
                    </p>
                </div>

                <div class="bg-primary rounded-[2.5rem] p-8 text-center text-white shadow-2xl shadow-primary/20">
                    <ShieldCheck class="mx-auto mb-4 opacity-50" :size="32" />
                    <h2 class="text-[10px] font-black uppercase tracking-[0.3em] mb-4">Código de Recepción</h2>
                    <div class="bg-white text-black py-6 rounded-3xl inline-block px-12 shadow-inner border-4 border-black/5">
                        <span class="text-5xl font-black font-mono tracking-[0.2em]">{{ order.delivery_otp }}</span>
                    </div>
                    <p class="text-[9px] font-bold uppercase mt-6 opacity-80 leading-relaxed">
                        Dicta este PIN al conductor para validar la entrega física de tus productos.
                    </p>
                </div>

                <div class="bg-muted/40 border border-border rounded-[2.5rem] p-8">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-6 flex items-center gap-2">
                        <Truck :size="14" /> Logística de Asignación
                    </h3>
                    
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center text-primary text-2xl font-black uppercase border border-primary/20">
                            {{ driver.first_name.charAt(0) }}
                        </div>
                        <div class="flex-1">
                            <p class="text-lg font-black text-foreground leading-none">{{ driver.first_name }} {{ driver.last_name }}</p>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase mt-1">
                                {{ driver.vehicle }} • {{ driver.plate }}
                            </p>
                        </div>
                        <a :href="'tel:' + driver.phone" class="w-12 h-12 bg-foreground text-background rounded-xl flex items-center justify-center shadow-lg active:scale-90 transition-transform">
                            <Phone :size="18" />
                        </a>
                    </div>

                    <div class="pt-6 border-t border-border/50">
                        <div class="flex items-start gap-3">
                            <MapPin class="text-primary mt-1" :size="16" />
                            <div>
                                <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest">Punto de Entrega</p>
                                <p class="text-xs font-bold text-foreground mt-1 uppercase">{{ order.delivery_data.address }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>