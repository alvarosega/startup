<script setup>
import { ChevronRight, Ban } from 'lucide-vue-next';

const props = defineProps({ zones: Array });
const emit = defineEmits(['select-item', 'select-zone']);

const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};
</script>

<template>
    <div class="w-full pb-20 pt-6 space-y-12 relative">
        
        <div v-for="zone in zones" :key="zone.id" class="flex flex-col relative w-full">
            
            <div class="px-6 mb-5 flex items-end justify-between cursor-pointer group"
                 @click="emit('select-zone', zone)">
                <div class="flex flex-col">
                    <h2 class="text-2xl font-extrabold text-foreground tracking-tight transition-colors group-hover:text-primary">
                        {{ zone.name }}
                    </h2>
                    <div class="h-1 w-8 bg-primary mt-1 rounded-full transform origin-left transition-transform group-hover:scale-x-150"></div>
                </div>
                <button class="flex items-center gap-1 text-[10px] font-bold text-primary uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                    Ver todo <ChevronRight :size="14" />
                </button>
            </div>

            <div v-if="zone.aisles && zone.aisles.length > 0" 
                 class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 gap-4 pb-6">
                 
                <div v-for="aisle in zone.aisles" :key="aisle.id"
                     @click="emit('select-item', { item: aisle, zone: zone })"
                     class="w-[42vw] md:w-[180px] h-[220px] shrink-0 snap-start bg-card rounded-xl flex flex-col relative cursor-pointer overflow-hidden transition-all duration-300 group shadow-apple-soft hover:shadow-lg dark:shadow-none border border-transparent dark:border-card-border hover:-translate-y-1 active:scale-95">
                     
                     <div class="absolute inset-0 bg-gradient-to-b from-transparent to-muted/20 dark:to-primary/5 z-0"></div>
                     
                     <div class="absolute inset-0 flex items-center justify-center p-6 mb-8 z-10 transition-transform duration-500 group-hover:scale-110">
                        <img :src="getImageUrl(aisle.image_url || aisle.image_path)" :alt="aisle.name" 
                             class="w-full h-full object-contain drop-shadow-[0_10px_10px_rgba(0,0,0,0.15)] dark:drop-shadow-[0_10px_20px_rgba(225,6,0,0.2)]">
                     </div>

                     <div class="absolute bottom-0 left-0 right-0 z-20 p-4 pt-10 bg-gradient-to-t from-card via-card/80 to-transparent">
                        <h3 class="text-[11px] font-extrabold uppercase text-foreground text-center tracking-tight leading-tight group-hover:text-primary transition-colors">
                            {{ aisle.name }}
                        </h3>
                     </div>
                </div>

                <div class="w-[5vw] shrink-0"></div>
            </div>

            <div v-else class="w-full px-6">
                <div class="py-10 border border-dashed border-border dark:border-card-border bg-muted/30 rounded-xl flex flex-col items-center justify-center gap-2">
                    <Ban :size="20" class="text-muted-foreground/40" />
                    <span class="text-[10px] font-bold text-muted-foreground/60 uppercase tracking-[0.2em]">Sin pasillos disponibles</span>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Refinamiento de sombras para modo oscuro F1 */
.dark .group:hover {
    border-color: hsl(var(--primary) / 0.4);
    box-shadow: 0 0 20px -5px hsl(var(--primary) / 0.15);
}
</style>