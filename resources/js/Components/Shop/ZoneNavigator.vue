<script setup>
const props = defineProps({ zones: Array });
const emit = defineEmits(['select-item', 'select-zone']);

// Prevención de imágenes rotas
const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};
</script>

<template>
    <div class="w-full pb-12 pt-2 space-y-8 relative">
        
        <div v-for="zone in zones" :key="zone.id" class="flex flex-col relative w-full">
            
            <div class="sticky top-[160px] z-[40] cyber-glass border-y border-tech px-4 py-3 mb-4 flex items-center justify-between shadow-sm cursor-pointer group transition-all duration-300"
                 @click="emit('select-zone', zone)">
                <h2 class="text-lg font-sans font-black uppercase text-foreground tracking-tight group-hover:text-f1-red transition-colors">
                    {{ zone.name }}
                </h2>
                <span class="text-muted group-hover:text-f1-red transition-colors text-xl leading-none">
                    &rarr;
                </span>
            </div>

            <div v-if="zone.aisles && zone.aisles.length > 0" 
                 class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-4 gap-4 pb-4">
                 
                <div v-for="aisle in zone.aisles" :key="aisle.id"
                     @click="emit('select-item', { item: aisle, zone: zone })"
                     class="w-[70vw] md:w-[280px] h-[340px] shrink-0 snap-start bg-surface border border-tech rounded-[20px] flex flex-col relative group cursor-pointer hover-neon-red transition-all duration-500 overflow-hidden shadow-sm">
                     
                     <div class="absolute inset-0 overflow-hidden rounded-[20px] pointer-events-none z-0">
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[140%] h-[140%] bg-gradient-to-tr from-f1-red/20 via-background to-telemetry-green/15 opacity-80 blur-[40px] group-hover:scale-125 transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"></div>
                    </div>
                     
                    <div class="absolute inset-0 flex items-center justify-center p-6 z-10 pointer-events-none">
                        <img :src="getImageUrl(aisle.image_url || aisle.image_path)" :alt="aisle.name" 
                             class="w-full h-full object-contain drop-shadow-[0_20px_25px_rgba(0,0,0,0.35)] transition-transform duration-500 group-hover:scale-[1.15]">
                    </div>

                    <div class="absolute bottom-0 left-0 right-0 z-20 cyber-glass border-t border-tech p-4 flex items-center justify-center">
                        <h3 class="text-sm font-sans font-black uppercase text-foreground text-center line-clamp-2 tracking-wide drop-shadow-md">
                            {{ aisle.name }}
                        </h3>
                    </div>
                </div>

                <div class="w-[10vw] shrink-0"></div>
            </div>

            <div v-else class="w-full px-4">
                <div class="p-6 border border-tech cyber-glass rounded-[20px] flex items-center justify-center shadow-sm">
                    <span class="text-xs font-mono font-bold text-muted uppercase tracking-widest">Zona sin inventario activo</span>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>