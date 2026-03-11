<script setup>
const props = defineProps({ zones: Array });
const emit = defineEmits(['select-item', 'select-zone']);

// Prevención de imágenes rotas (Inyecta el placeholder PNG transparente)
const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};
</script>

<template>
    <div class="w-full pb-12 pt-4 space-y-10 relative">
        
        <div v-for="zone in zones" :key="zone.id" class="flex flex-col relative w-full">
            
            <div class="px-5 mb-4 flex items-center justify-between cursor-pointer group"
                 @click="emit('select-zone', zone)">
                <h2 class="text-2xl font-sans font-black uppercase text-foreground tracking-tighter active:scale-95 transition-transform origin-left">
                    {{ zone.name }}
                </h2>
                <span class="text-foreground/30 text-2xl leading-none font-black">
                    &rarr;
                </span>
            </div>

            <div v-if="zone.aisles && zone.aisles.length > 0" 
                 class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-5 gap-3 pb-4">
                 
                <div v-for="aisle in zone.aisles" :key="aisle.id"
                     @click="emit('select-item', { item: aisle, zone: zone })"
                     class="w-[38vw] md:w-[160px] h-[190px] shrink-0 snap-start bg-white/5 dark:bg-black/20 backdrop-blur-xl border border-white/10 dark:border-white/5 rounded-[24px] flex flex-col relative cursor-pointer active:scale-95 transition-transform duration-300 overflow-hidden shadow-[0_15px_35px_-10px_rgba(0,0,0,0.4)]">
                     
                     <div class="absolute inset-0 overflow-hidden rounded-[24px] pointer-events-none z-0">
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] bg-primary/10 opacity-40 blur-[30px] rounded-full"></div>
                    </div>
                     
                    <div class="absolute inset-0 flex items-center justify-center p-4 pb-12 z-10 pointer-events-none">
                        <img :src="getImageUrl(aisle.image_url || aisle.image_path)" :alt="aisle.name" 
                             class="w-full h-full object-contain drop-shadow-[0_15px_15px_rgba(0,0,0,0.5)]">
                    </div>

                    <div class="absolute bottom-0 left-0 right-0 z-20 bg-background/40 backdrop-blur-md border-t border-white/10 dark:border-white/5 p-3 flex items-center justify-center h-[48px]">
                        <h3 class="text-[10px] font-sans font-black uppercase text-foreground text-center line-clamp-2 tracking-tight drop-shadow-md leading-none">
                            {{ aisle.name }}
                        </h3>
                    </div>
                </div>

                <div class="w-[5vw] shrink-0"></div>
            </div>

            <div v-else class="w-full px-5">
                <div class="p-6 border border-white/10 dark:border-white/5 bg-white/5 dark:bg-black/10 backdrop-blur-xl rounded-[24px] flex items-center justify-center shadow-inner">
                    <span class="text-[10px] font-black text-foreground/50 uppercase tracking-widest">Zona inactiva</span>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Ocultar la barra de scroll nativa para un diseño limpio en iOS/Android */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>