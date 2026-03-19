<script setup>
import { ChevronRight, Ban } from 'lucide-vue-next';

const props = defineProps({ zones: Array });
const emit = defineEmits(['select-item', 'select-zone']);

const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};

/**
 * Inyección de Variables para el Gradiente Radial
 */
const getZoneStyle = (hex) => {
    if (!hex) return {};
    const color = hex.startsWith('#') ? hex : '#' + hex;
    return {
        '--zone-color': color,
        '--zone-color-10': `${color}1A`, // 10% opacidad
        '--zone-color-40': `${color}66`, // 40% opacidad
    };
};
</script>

<template>
    <div class="w-full pb-20 pt-4 space-y-12 relative z-10">
        <div v-for="zone in zones" :key="zone.id" class="flex flex-col relative w-full" :style="getZoneStyle(zone.color)">
            
            <div class="px-6 mb-4 flex items-end justify-between cursor-pointer group"
                 @click="emit('select-zone', zone)">
                <div class="flex flex-col gap-0.5">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-[-0.02em] transition-all group-hover:translate-x-1">
                        {{ zone.name }}
                    </h2>
                    <div class="h-1 w-8 rounded-full bg-primary transition-all duration-500 group-hover:w-16"
                         :style="{ backgroundColor: 'var(--zone-color)' }"></div>
                </div>
                
                <button class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider transition-all hover:gap-3"
                        :style="{ color: 'var(--zone-color)' }">
                    Explorar <ChevronRight :size="14" stroke-width="2.5" />
                </button>
            </div>

            <div v-if="zone.aisles && zone.aisles.length > 0" 
                 class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 gap-4 pb-4">
                 
                <div v-for="aisle in zone.aisles" :key="aisle.id"
                     @click="emit('select-item', { item: aisle, zone: zone })"
                     class="w-[42vw] md:w-[180px] aspect-[4/5] shrink-0 snap-start rounded-xl flex flex-col relative cursor-pointer overflow-hidden transition-all duration-500 group border border-gray-100 dark:border-[#262626] active:scale-95 shadow-sm">
                     
                     <div class="absolute inset-0 z-0 aisle-radial-background"></div>
                     
                     <div class="flex-1 flex items-center justify-center p-4 z-10 transition-transform duration-700 group-hover:scale-110">
                        <img :src="getImageUrl(aisle.image_path)" :alt="aisle.name" 
                             class="w-full h-full object-contain drop-shadow-xl dark:drop-shadow-none">
                     </div>

                     <div class="p-3 bg-gradient-to-t from-white dark:from-[#050505] via-transparent to-transparent z-20">
                        <h3 class="text-[11px] font-bold uppercase text-gray-800 dark:text-gray-200 text-center tracking-tight leading-tight transition-colors group-hover:text-primary"
                            :style="{ color: 'inherit', '--hover-color': 'var(--zone-color)' }">
                            {{ aisle.name }}
                        </h3>
                     </div>
                </div>

                <div class="w-4 shrink-0"></div>
            </div>

            <div v-else class="w-full px-6">
                <div class="py-10 border border-dashed border-gray-200 dark:border-[#262626] bg-gray-50/50 dark:bg-white/5 rounded-xl flex flex-col items-center justify-center gap-2">
                    <Ban :size="20" class="text-gray-300 dark:text-gray-700" />
                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-widest">Zona en mantenimiento</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/**
 * Implementación del Gradiente Radial Aurora
 * Basado en la estructura de Uiverse.io
 */
.aisle-radial-background {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    /* MODO CLARO: Blanco #FFF -> Zone Color Soft */
    background: #FFFFFF;
    background: radial-gradient(125% 125% at 50% 10%, #FFFFFF 40%, var(--zone-color-40) 100%);
    transition: background 0.5s ease;
}

.dark .aisle-radial-background {
    /* MODO OSCURO: Negro Pitch #050505 -> Zone Color Soft */
    background: #050505;
    background: radial-gradient(125% 125% at 50% 10%, #050505 40%, var(--zone-color-40) 100%);
}

/* Efecto hover sobre el texto h3 usando la variable de zona */
.group:hover h3 {
    color: var(--zone-color) !important;
}

/* Animación de respiración sutil para la aurora en móvil */
@keyframes aurora-subtle-pulse {
    0%, 100% { opacity: 0.8; }
    50% { opacity: 1; }
}

@media (max-width: 768px) {
    .aisle-radial-background {
        animation: aurora-subtle-pulse 4s infinite ease-in-out;
    }
}
</style>