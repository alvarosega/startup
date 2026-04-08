<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue'; 
import { ArrowLeft, Truck, ShieldCheck, Info } from 'lucide-vue-next';

const props = defineProps({ order: Object });
</script>

<template>
    <AdminLayout>
        <Head :title="'Despachar ' + order.code" />
        <div class="p-6 max-w-3xl mx-auto">
            <Link :href="route('admin.orders.index')" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-foreground mb-6 transition-colors">
                <ArrowLeft :size="16" /> Monitor Logístico
            </Link>

            <div class="bg-indigo-600 rounded-[40px] p-12 text-center text-white shadow-2xl relative overflow-hidden border-4 border-white/10">
                <div class="absolute -top-10 -right-10 opacity-10 rotate-12"><Truck :size="280" /></div>
                
                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md rounded-full mb-6 border border-white/20">
                        <ShieldCheck :size="14" class="text-indigo-200" />
                        <span class="text-[10px] font-black uppercase tracking-widest text-indigo-100">Protocolo de Salida Seguro</span>
                    </div>

                    <h1 class="text-4xl font-black uppercase italic tracking-tighter mb-2">
                        Orden <span class="text-indigo-300">#{{ order.code }}</span>
                    </h1>
                    <p class="text-xs font-bold uppercase tracking-widest opacity-70 mb-12">
                        {{ order.delivery_type === 'pickup' ? 'Esperando al Cliente Final' : 'Esperando al Conductor Asignado' }}
                    </p>

                    <div class="space-y-4 mb-10">
                        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-indigo-200">Dictar PIN de Recogida</p>
                        <div class="bg-white text-indigo-900 px-10 py-8 rounded-[32px] shadow-2xl inline-block border-8 border-indigo-500/30">
                            <span class="text-8xl font-mono font-black tracking-[0.2em] leading-none">{{ order.pickup_otp }}</span>
                        </div>
                    </div>
                    
                    <div class="bg-indigo-700/50 backdrop-blur-sm p-6 rounded-3xl border border-white/10 max-w-md mx-auto">
                        <div class="flex items-start gap-4 text-left">
                            <Info class="text-indigo-300 shrink-0" :size="20" />
                            <p class="text-[11px] font-bold text-indigo-100 leading-relaxed uppercase">
                                El transportista debe ingresar estos 5 dígitos en su terminal móvil. Una vez validado, el pedido se vinculará automáticamente a su perfil y cambiará a estado <span class="text-white underline">EN CAMINO</span>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 p-6 bg-card border-2 border-border rounded-3xl flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="bg-muted p-3 rounded-2xl text-muted-foreground"><Truck :size="24" /></div>
                    <div>
                        <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Destinatario</p>
                        <p class="text-sm font-black text-foreground uppercase italic">{{ order.customer.name }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Monto de Operación</p>
                    <p class="text-xl font-black text-foreground font-mono">Bs {{ order.total_amount.toFixed(2) }}</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>