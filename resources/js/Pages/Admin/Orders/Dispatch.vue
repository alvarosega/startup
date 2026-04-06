<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue'; 
import { ArrowLeft, Truck } from 'lucide-vue-next';

const props = defineProps({ order: Object });
</script>

<template>
    <AdminLayout>
        <Head :title="'Despachar ' + order.code" />
        <div class="p-6 max-w-3xl mx-auto">
            <Link :href="route('admin.orders.index')" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-foreground mb-6">
                <ArrowLeft :size="16" /> Monitor Logístico
            </Link>

            <div class="bg-indigo-600 rounded-[40px] p-12 text-center text-white shadow-2xl relative overflow-hidden">
                <div class="absolute top-4 right-4 opacity-20"><Truck :size="150" /></div>
                
                <h1 class="text-3xl font-black uppercase italic tracking-tighter relative z-10 mb-2">
                    Orden <span class="text-indigo-300">#{{ order.code }}</span>
                </h1>
                <p class="text-xs font-bold uppercase tracking-widest opacity-80 relative z-10 mb-12">
                    {{ order.delivery_type === 'pickup' ? 'Esperando al Cliente' : 'Esperando al Conductor' }}
                </p>

                <p class="text-[10px] font-black uppercase tracking-[0.3em] mb-4 relative z-10">PIN Operativo de Seguridad</p>
                <div class="inline-block bg-white text-indigo-900 px-16 py-8 rounded-3xl shadow-inner relative z-10 mb-8">
                    <span class="text-8xl font-mono font-black tracking-[0.2em] ml-6">{{ order.pickup_otp }}</span>
                </div>
                
                <p class="text-sm font-bold opacity-80 uppercase tracking-widest relative z-10 max-w-sm mx-auto">
                    El transportista debe ingresar este código en su terminal para liberar la carga física.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>