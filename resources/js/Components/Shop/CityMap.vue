<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3'; // Importamos el router

const props = defineProps({
    zones: Object 
});

// --- CONFIGURACIÓN VISUAL ---
const SCREEN_W = 390; 
const CENTER_X = SCREEN_W / 2;

// GEOMETRÍA RECTANGULAR (Tu configuración original)
const BLOCK_W = 110;     
const BLOCK_SKEW = 25;   
const SLICE_H = 30;      
const GAP = 10;          
const GROUP_MARGIN = 40; 

const dynamicZones = computed(() => props.zones ? Object.values(props.zones) : []);

const mapItems = computed(() => {
    let currentY = 60;

    return dynamicZones.value.map((zone, index) => {
        const side = index === 0 ? 'center' : (index % 2 !== 0 ? 'left' : 'right');
        const dir = side === 'left' ? 1 : -1;

        const aisles = zone.aisles && zone.aisles.length > 0 
            ? zone.aisles 
            : [{ id: `gen-${zone.id}`, name: 'General', image_url: null }];
            
        const totalHeight = aisles.length * (SLICE_H + GAP);

        let x = CENTER_X;
        if (side === 'left') x = CENTER_X - 140; 
        if (side === 'right') x = CENTER_X + 140;

        const y = currentY;
        currentY += totalHeight + GROUP_MARGIN; 

        return {
            ...zone,
            x, y, side, dir, aisles, totalHeight,
            baseColor: zone.color || '#64748b'
        };
    });
});

const svgHeight = computed(() => {
    if (!mapItems.value.length) return 800;
    const last = mapItems.value[mapItems.value.length - 1];
    return last.y + last.totalHeight + 150;
});

// --- ACCIÓN DE NAVEGACIÓN ---
const goToZone = (zone) => {
    // Navegamos a la vista de productos de esa zona
    // Asumiendo que tienes una ruta llamada 'shop.zone'
    router.get(route('shop.zone', zone.slug));
};
</script>

<template>
    <div class="w-full h-full bg-slate-50 overflow-y-auto overflow-x-hidden touch-pan-y scroll-smooth pb-24">
        
        <div class="pt-6 px-6 text-center">
            <h2 class="text-[10px] font-black tracking-[0.3em] text-slate-400 uppercase">TIENDA</h2>
        </div>

        <svg :viewBox="`0 0 ${SCREEN_W} ${svgHeight}`" class="w-full h-auto block select-none">
            
            <defs>
                <filter id="wideShadow">
                    <feGaussianBlur in="SourceAlpha" stdDeviation="4"/>
                    <feOffset dx="0" dy="4" result="offsetblur"/>
                    <feComponentTransfer><feFuncA type="linear" slope="0.3"/></feComponentTransfer>
                    <feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge>
                </filter>
            </defs>

            <path :d="`M${CENTER_X} 40 V${svgHeight - 40}`" stroke="#E2E8F0" stroke-width="2" stroke-dasharray="6, 6" />

            <g v-for="item in mapItems" :key="item.slug" 
               @click="goToZone(item)" 
               class="cursor-pointer active:opacity-80 transition-opacity"
            >
                <ellipse :cx="item.x + (BLOCK_W/2 * item.dir)" :cy="item.y + item.totalHeight + 10" :rx="BLOCK_W * 0.8" ry="25" fill="black" opacity="0.1" class="blur-sm"/>

                <g :transform="`translate(${item.x + (BLOCK_W/2 * item.dir)}, ${item.y - 25})`">
                    <text text-anchor="middle" font-size="10" font-weight="900" fill="#1E293B" class="uppercase tracking-widest">{{ item.name }}</text>
                    <line x1="-30" y1="5" x2="30" y2="5" :stroke="item.baseColor" stroke-width="2" />
                </g>

                <g v-for="(aisle, i) in [...item.aisles].reverse()" :key="aisle.id">
                    <g :transform="`translate(${item.x}, ${item.y + item.totalHeight - ((i + 1) * (SLICE_H + GAP))})`">
                        
                        <g filter="url(#wideShadow)">
                            <path :d="`M0 ${SLICE_H} L${BLOCK_W * item.dir} ${SLICE_H - BLOCK_SKEW} V-${BLOCK_SKEW} L0 0 Z`" 
                                  :fill="item.baseColor" />

                            <foreignObject 
                                v-if="aisle.image_url"
                                :x="item.dir === 1 ? 0 : -BLOCK_W" 
                                :y="-BLOCK_SKEW" 
                                :width="BLOCK_W" 
                                :height="SLICE_H + BLOCK_SKEW"
                            >
                                <div xmlns="http://www.w3.org/1999/xhtml" class="w-full h-full relative">
                                    <img 
                                        :src="aisle.image_url" 
                                        class="w-full h-full object-cover block"
                                        :style="{
                                            clipPath: item.dir === 1 
                                                ? 'polygon(0% 45%, 100% 0%, 100% 55%, 0% 100%)' 
                                                : 'polygon(0% 0%, 100% 45%, 100% 100%, 0% 55%)',
                                            transform: 'scale(1.02)'
                                        }" 
                                    />
                                    <div class="absolute inset-0 bg-white/10 pointer-events-none" 
                                         :style="{
                                            clipPath: item.dir === 1 
                                                ? 'polygon(0% 45%, 100% 0%, 100% 55%, 0% 100%)' 
                                                : 'polygon(0% 0%, 100% 45%, 100% 100%, 0% 55%)'
                                         }"></div>
                                </div>
                            </foreignObject>
                            
                            <path v-else :d="`M0 ${SLICE_H} L${BLOCK_W * item.dir} ${SLICE_H - BLOCK_SKEW} V-${BLOCK_SKEW} L0 0 Z`" fill="white" fill-opacity="0.1" />

                            <path :d="`M0 ${SLICE_H} L${(BLOCK_W * 0.4) * (item.dir * -1)} ${SLICE_H - BLOCK_SKEW} V-${BLOCK_SKEW} L0 0 Z`" :fill="item.baseColor" />
                            <path :d="`M0 ${SLICE_H} L${(BLOCK_W * 0.4) * (item.dir * -1)} ${SLICE_H - BLOCK_SKEW} V-${BLOCK_SKEW} L0 0 Z`" fill="black" fill-opacity="0.3" />
                            <path :d="`M0 0 L${BLOCK_W * item.dir} -${BLOCK_SKEW} L${(BLOCK_W * 0.6) * item.dir} -${BLOCK_SKEW * 2} L${(BLOCK_W * 0.4) * (item.dir * -1)} -${BLOCK_SKEW} Z`" 
                                  :fill="item.baseColor" class="brightness-125" stroke="white" stroke-width="0.5" stroke-opacity="0.4" />
                        </g>

                        <g v-if="item.dir === 1" :transform="`translate(${BLOCK_W + 10}, 0)`">
                            <line x1="-10" y1="0" x2="0" y2="0" stroke="#94A3B8" stroke-width="1" />
                            <text x="5" y="3" text-anchor="start" font-size="9" font-weight="700" fill="#475569" class="uppercase tracking-tight">{{ aisle.name.substring(0, 18) }}</text>
                        </g>
                        <g v-else :transform="`translate(-${BLOCK_W + 10}, 0)`">
                            <line x1="0" y1="0" x2="10" y2="0" stroke="#94A3B8" stroke-width="1" />
                            <text x="-5" y="3" text-anchor="end" font-size="9" font-weight="700" fill="#475569" class="uppercase tracking-tight">{{ aisle.name.substring(0, 18) }}</text>
                        </g>

                    </g>
                </g>
            </g>
        </svg>
    </div>
</template>