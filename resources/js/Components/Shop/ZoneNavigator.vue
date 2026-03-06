<script setup>
const props = defineProps({ zones: Array });
const emit = defineEmits(['select-item', 'select-zone']);
</script>

<template>
    <div class="w-full pb-6 pt-4 px-4 space-y-8">
        
        <div v-for="(zone, idx) in zones" :key="zone.id || idx" class="flex flex-col gap-4 relative">
            
            <div class="flex items-end justify-between border-b-2 border-tech pb-2 sticky top-0 bg-background/90 backdrop-blur-md z-10 pt-2 cursor-pointer group"
                 @click="emit('select-zone', zone)">
                <div>
                    <span class="text-[10px] font-mono font-black text-muted uppercase tracking-widest block mb-0.5">
                        0{{ idx + 1 }} // SISTEMA
                    </span>
                    <h2 class="text-2xl font-sans font-black uppercase text-primary tracking-wide transition-colors group-hover:text-f1-red">
                        {{ zone.name }}
                    </h2>
                </div>
                <button class="text-[10px] font-mono font-bold text-muted uppercase group-hover:text-f1-red transition-colors mb-1">
                    Ingresar ↘
                </button>
            </div>

            <div v-if="zone.aisles && zone.aisles.length > 0" class="grid grid-cols-2 gap-3">
                <div v-for="(aisle, aIdx) in zone.aisles" :key="aIdx"
                     @click="emit('select-item', { item: aisle, zone: zone })"
                     class="bg-surface border border-tech clip-f1-br flex flex-col relative group cursor-pointer hover:border-f1-red transition-colors duration-150 h-48 shadow-sm">
                     
                    <div class="absolute inset-0 opacity-10 bg-gradient-to-t from-black to-transparent pointer-events-none z-0"></div>
                     
                    <div class="flex-1 flex items-center justify-center p-4 relative overflow-hidden z-10">
                        <img :src="aisle.image_url" :alt="aisle.name" 
                             class="w-full h-full object-contain tech-shadow transition-transform duration-300 group-hover:scale-105">
                    </div>

                    <div class="p-3 bg-background border-t border-tech flex flex-col justify-center min-h-[56px] z-10">
                        <span class="text-[8px] font-mono text-muted uppercase mb-0.5">Categoría</span>
                        <h3 class="text-xs font-sans font-bold uppercase text-primary line-clamp-2 leading-tight">
                            {{ aisle.name }}
                        </h3>
                    </div>
                </div>
            </div>

            <div v-else class="w-full p-4 border border-tech bg-surface/50 clip-f1-br flex items-center justify-center">
                <span class="text-xs font-mono text-muted uppercase tracking-widest">Registros no encontrados</span>
            </div>

        </div>
    </div>
</template>