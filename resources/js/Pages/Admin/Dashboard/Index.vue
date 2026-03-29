<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Cpu, Activity, LayoutDashboard, ShieldCheck } from 'lucide-vue-next';

const props = defineProps({
    system_status: String,
    last_sync: String
});
</script>

<template>
    <Head title="Panel de Control" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <LayoutDashboard :size="24" class="text-primary" />
                <h1 class="text-2xl font-black tracking-tighter uppercase italic">Dashboard</h1>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <div class="bg-card border border-border p-6 rounded-3xl shadow-sm relative overflow-hidden group">
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Estado_Sistema</p>
                                <h3 class="text-2xl font-bold text-emerald-500 flex items-center gap-2">
                                    {{ system_status }}
                                    <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                                </h3>
                            </div>
                            <Cpu :size="32" class="text-zinc-200 group-hover:text-primary transition-colors" />
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-[2px] bg-emerald-500/20"></div>
                    </div>

                    <div class="bg-card border border-border p-6 rounded-3xl shadow-sm relative overflow-hidden group">
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Sincronización</p>
                                <p class="text-lg font-bold text-foreground font-mono">{{ last_sync }}</p>
                            </div>
                            <Activity :size="32" class="text-zinc-200 group-hover:text-primary transition-colors" />
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-[2px] bg-zinc-500/20"></div>
                    </div>

                    <div class="bg-card border border-border p-6 rounded-3xl shadow-sm relative overflow-hidden group">
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Capa_Seguridad</p>
                                <p class="text-lg font-bold text-foreground italic uppercase">Protocolo Zero-Trust</p>
                            </div>
                            <ShieldCheck :size="32" class="text-zinc-200 group-hover:text-primary transition-colors" />
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-[2px] bg-blue-500/20"></div>
                    </div>
                </div>

                <div class="bg-zinc-900 rounded-[2.5rem] p-12 text-white relative overflow-hidden shadow-2xl">
                    <div class="relative z-10 max-w-2xl">
                        <h2 class="text-4xl font-black tracking-tighter uppercase italic mb-4 leading-none">Consola de Mando Activa</h2>
                        <p class="text-zinc-400 font-medium text-lg leading-relaxed">
                            Bienvenido al sistema de gestión operativa de Electric Luxury. Desde aquí tienes control total sobre la red logística, inventarios y clientes.
                        </p>
                    </div>
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <LayoutDashboard :size="300" />
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>