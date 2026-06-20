<script setup>
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Save, ArrowLeft, Palette, Hash, Type, 
    Map as MapIcon, Cpu, Terminal, AlertTriangle, Wifi, WifiOff
} from 'lucide-vue-next';

const props = defineProps({
    zone: Object
});

// DESEMPAQUETADO SEGURO
const zoneData = computed(() => props.zone.data || props.zone);

// --- FORMULARIO (Alineado con DB Migration) ---
const form = useForm({
    _method: 'PUT',
    name: zoneData.value.name || '',
    hex_color: zoneData.value.hex_color || '#3b82f6', 
    svg_id: zoneData.value.svg_id || '',
    description: zoneData.value.description || '',
    is_active: Boolean(zoneData.value.is_active)
});

const zoneCode = computed(() => {
    return `ZON_${String(zoneData.value.id).substring(0, 8).toUpperCase()}`;
});

const submit = () => {
    form.post(route('admin.market-zones.update', zoneData.value.id), {
        preserveScroll: true,
        onError: () => {
            const card = document.getElementById('main-card');
            if (card) {
                card.classList.add('shake');
                setTimeout(() => card.classList.remove('shake'), 400);
            }
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Editar Zona Logística" />
        
        <div class="max-w-4xl mx-auto pb-12 px-4 md:px-0">
            
            <div class="mb-8 relative group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10 flex justify-between items-end">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-[8px] font-mono border border-primary/30 bg-primary/5 text-primary px-2 py-1">
                                {{ zoneCode }}
                            </span>
                            <span class="text-[8px] font-mono px-2 py-1 flex items-center gap-1"
                                  :class="form.is_active ? 'text-cyan-500 border border-cyan-500/30 bg-cyan-500/10' : 'text-destructive border border-destructive/30 bg-destructive/10'">
                                <component :is="form.is_active ? Wifi : WifiOff" :size="10" />
                                {{ form.is_active ? 'ONLINE' : 'OFFLINE' }}
                            </span>
                        </div>
                        <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none" data-text="EDITAR ZONA">
                            EDITAR ZONA
                        </h1>
                        <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                            <Cpu :size="12" class="text-primary animate-pulse" /> 
                            <span class="text-primary uppercase">{{ zoneData.name }}</span>
                            <Terminal :size="12" class="text-primary animate-pulse" />
                        </p>
                    </div>
                    
                    <Link :href="route('admin.market-zones.index')" class="px-4 py-2 border border-destructive/50 text-destructive font-mono text-xs hover:bg-destructive hover:text-destructive-foreground transition-all relative group/cancel">
                        CANCELAR
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-destructive opacity-0 group-hover/cancel:opacity-100"></span>
                    </Link>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div id="main-card" class="border border-border/50 bg-background shadow-2xl relative overflow-hidden group/card p-8">
                    
                    <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                    <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                    <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                        
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                    <Type :size="12" /> // NOMBRE IDENTIFICADOR
                                </label>
                                <div class="relative group/input">
                                    <input v-model="form.name" type="text" class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-lg font-bold focus:border-primary focus:shadow-neon-primary outline-none transition-all uppercase" :class="{'border-destructive/50': form.errors.name}" placeholder="EJ: ZONA NORTE" />
                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                </div>
                                <p v-if="form.errors.name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block flex items-center gap-1">
                                    <Hash :size="12" /> // MAP SVG ID (VINCULACIÓN GEOGRÁFICA)
                                </label>
                                <input v-model="form.svg_id" type="text" class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" :class="{'border-destructive/50': form.errors.svg_id}" placeholder="path-north-1" />
                                <p v-if="form.errors.svg_id" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.svg_id }}
                                </p>
                                <p class="text-[8px] font-mono text-muted-foreground mt-1 uppercase">Debe coincidir con el ID del vector en el mapa del frontend.</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block flex items-center gap-1">
                                    <Palette :size="12" /> // VECTOR HEX COLOR
                                </label>
                                <div class="flex gap-3 h-[46px]">
                                    <div class="aspect-square h-full border border-border/50 overflow-hidden relative cursor-pointer hover:border-primary transition-colors">
                                        <input v-model="form.hex_color" type="color" class="absolute inset-0 w-[200%] h-[200%] -top-1/2 -left-1/2 cursor-pointer p-0 border-0" />
                                    </div>
                                    <input v-model="form.hex_color" type="text" class="w-full bg-background border border-border/50 px-4 font-mono uppercase focus:border-primary outline-none transition-all" placeholder="#000000" maxlength="7" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest block flex items-center gap-1">
                                    // NOTAS OPERATIVAS (OPCIONAL)
                                </label>
                                <textarea v-model="form.description" rows="3" class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-xs focus:border-primary outline-none transition-all resize-none" placeholder="DESCRIPCIÓN INTERNA DE LA ZONA..."></textarea>
                            </div>
                            
                            <label class="flex items-center gap-3 p-4 border border-primary/30 bg-primary/5 cursor-pointer group/check">
                                <input v-model="form.is_active" type="checkbox" class="w-4 h-4 text-primary bg-background border-primary focus:ring-primary/50" />
                                <span class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest">ZONA ACTIVA Y VISIBLE</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-background/80 backdrop-blur-sm border border-primary/30 flex justify-end items-center relative">
                    <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/submit overflow-hidden">
                        <span v-if="form.processing" class="flex items-center gap-2 relative z-10"><Cpu :size="14" class="animate-spin" /> PROCESANDO...</span>
                        <span v-else class="flex items-center gap-2 relative z-10"><Save :size="14" /> GUARDAR CAMBIOS</span>
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/submit:translate-y-0 transition-transform duration-500"></span>
                    </button>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // {{ zoneCode }}_EDIT_ZON // {{ new Date().toISOString().slice(0,10) }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes shake { 10%, 90% { transform: translate3d(-1px, 0, 0); } 20%, 80% { transform: translate3d(2px, 0, 0); } 30%, 50%, 70% { transform: translate3d(-4px, 0, 0); } 40%, 60% { transform: translate3d(4px, 0, 0); } }
.shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
.glitch-text { position: relative; animation: glitch-skew 4s infinite linear alternate-reverse; }
.glitch-text::before, .glitch-text::after { content: attr(data-text); position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8; }
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }
@keyframes glitch-skew { 0%, 20%, 22%, 80%, 82%, 100% { transform: skew(0deg); } 21% { transform: skew(2deg); } 81% { transform: skew(-2deg); } }
@keyframes glitch-anim-1 { 0% { clip-path: inset(20% 0 30% 0); } 100% { clip-path: inset(40% 0 20% 0); } }
@keyframes glitch-anim-2 { 0% { clip-path: inset(60% 0 10% 0); } 100% { clip-path: inset(30% 0 40% 0); } }
.shadow-neon-primary { box-shadow: 0 0 20px hsl(var(--primary) / 0.3); }
</style>