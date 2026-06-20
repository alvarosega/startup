<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Clock, ShieldCheck, ArrowLeft } from 'lucide-vue-next';
</script>

<template>
    <Head title="Cuenta en Revisión" />
    <ShopLayout>
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="max-w-md w-full bg-card border rounded-[2.5rem] p-10 text-center shadow-2xl space-y-6">
                <div class="w-20 h-20 bg-amber-500/10 rounded-3xl flex items-center justify-center mx-auto text-amber-600">
                    <Clock :size="40" />
                </div>
                <h1 class="text-3xl font-black uppercase italic tracking-tighter">Solicitud <span class="text-amber-500">Enviada</span></h1>
                <p class="text-sm font-bold text-muted-foreground leading-relaxed uppercase tracking-tighter">
                    Tu documentación ha sido inyectada en el sistema. Los oficiales de logística auditarán tu perfil en las próximas 24-48 horas.
                </p>
                <div class="bg-muted p-4 rounded-2xl border flex items-center gap-3 text-left">
                    <ShieldCheck class="text-emerald-500 shrink-0" :size="20" />
                    <p class="text-[10px] font-black uppercase text-foreground/70">Recibirás una notificación vía WhatsApp una vez tu sucursal sea asignada.</p>
                </div>
                <Link :href="route('driver.login')" class="flex items-center justify-center gap-2 text-xs font-black uppercase tracking-widest hover:text-amber-600 transition-colors">
                    <ArrowLeft :size="14" /> Volver al Inicio
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>