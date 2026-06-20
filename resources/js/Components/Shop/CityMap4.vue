<script setup>

import { computed, ref } from 'vue';



const props = defineProps({

    zones: Object

});



// ESTADO PARA ANIMACIÓN

const selectedZoneId = ref(null);



// --- TUS VALORES ORIGINALES (INTACTOS) ---

const SCREEN_W = 400;

const CENTER_X = SCREEN_W / 2;

const BLOCK_W = 200;      

const BLOCK_SKEW = 20;    

const SLICE_H = 60;      

const GAP = 12;          

const GROUP_MARGIN = 40;

const SIDE_DEPTH = 0.5;  



const dynamicZones = computed(() => props.zones ? Object.values(props.zones) : []);



const mapItems = computed(() => {

    let currentY = 80;



    return dynamicZones.value.map((zone, index) => {

        const side = index % 2 === 0 ? 'right' : 'left';

        const dir = side === 'left' ? 1 : -1;

       

        const aisles = zone.aisles?.length > 0 ? zone.aisles : [{ id: `gen-${zone.id}`, name: 'General', image_url: null }];

        const totalHeight = aisles.length * (SLICE_H + GAP);



        let x = CENTER_X;

        if (side === 'left') x = CENTER_X - 100;

        if (side === 'right') x = CENTER_X + 100;



        const y = currentY;

        currentY += totalHeight + GROUP_MARGIN;



        // Usamos variables CSS para color por defecto si no viene

        return { ...zone, x, y, side, dir, aisles, totalHeight, baseColor: zone.color || 'var(--primary)' };

    });

});



const svgHeight = computed(() => {

    if (!mapItems.value.length) return 800;

    const last = mapItems.value[mapItems.value.length - 1];

    return last.y + last.totalHeight + 200;

});



const emit = defineEmits(['select-zone']);



const handleZoneClick = (zone) => {

    selectedZoneId.value = zone.id;

    // Feedback rápido

    emit('select-zone', zone);

    setTimeout(() => selectedZoneId.value = null, 800);

};



// Utilidad para convertir color hex a rgba para las sombras

const hexToRgba = (hex, alpha) => {

    // Si es una variable CSS (var(--...)), devolvemos tal cual (limitación de SVG simple) o un fallback

    if (hex && hex.startsWith('var')) return hex;

   

    if (!hex) return `rgba(0,0,0, ${alpha})`;

    let c;

    if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){

        c= hex.substring(1).split('');

        if(c.length== 3){

            c= [c[0], c[0], c[1], c[1], c[2], c[2]];

        }

        c= '0x'+c.join('');

        return 'rgba('+[(c>>16)&255, (c>>8)&255, c&255].join(',')+','+alpha+')';

    }

    return hex;

}

</script>



<template>

    <div class="w-full h-full bg-background overflow-y-auto overflow-x-hidden touch-pan-y scroll-smooth pb-32 transition-colors duration-300">

       

        <div class="pt-8 px-6 text-center animate-in fade-in slide-in-from-top-4 sticky top-0 z-10 pointer-events-none">

            <h2 class="text-[10px] font-black tracking-[0.5em] text-foreground/40 uppercase drop-shadow-md">

                ALMACÉN 3D

            </h2>

        </div>



        <svg :viewBox="`0 0 ${SCREEN_W} ${svgHeight}`" class="w-full h-auto block select-none px-2 py-4">

            <defs>

                <filter id="floorShadow" x="-50%" y="-50%" width="200%" height="200%">

                    <feGaussianBlur in="SourceAlpha" stdDeviation="8"/>

                    <feOffset dx="0" dy="15" result="offsetblur"/>

                    <feComponentTransfer><feFuncA type="linear" slope="0.4"/></feComponentTransfer>

                    <feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge>

                </filter>



                <linearGradient id="glassSheen" x1="0%" y1="0%" x2="100%" y2="100%">

                    <stop offset="0%" stop-color="white" stop-opacity="0.3"/>

                    <stop offset="40%" stop-color="white" stop-opacity="0.05"/>

                    <stop offset="100%" stop-color="white" stop-opacity="0"/>

                </linearGradient>



                <linearGradient id="sideShadowGradient" x1="0%" y1="0%" x2="0%" y2="100%">

                    <stop offset="0%" stop-color="black" stop-opacity="0.4"/>

                    <stop offset="100%" stop-color="black" stop-opacity="0.8"/>

                </linearGradient>

            </defs>



            <path :d="`M${CENTER_X} 40 V${svgHeight - 40}`"

                  class="stroke-foreground/10" stroke-width="1" stroke-dasharray="4, 4" />



            <g v-for="(item, idx) in mapItems" :key="item.id || idx"

               @click="handleZoneClick(item)"

               class="cursor-pointer transition-all duration-300 ease-out group"

               :class="{

                   'opacity-100': selectedZoneId === null || selectedZoneId === item.id,

                   'opacity-20 blur-sm': selectedZoneId !== null && selectedZoneId !== item.id

               }"

               style="transform-origin: center;">

               

               <g class="transition-transform duration-500 ease-in-out"

                  :class="selectedZoneId === item.id ? 'translate-y-[-10px] z-50' : ''">



                    <ellipse :cx="item.x + (BLOCK_W/2 * item.dir)"

                             :cy="item.y + item.totalHeight + 20"

                             :rx="BLOCK_W * 0.9" ry="25"

                             fill="black" opacity="0.3"

                             class="blur-lg transition-all group-active:scale-95"/>



                    <g :transform="`translate(${item.x + (BLOCK_W/2 * item.dir)}, ${item.y - 40})`">

                        <text text-anchor="middle" font-size="14" font-weight="900"

                              class="fill-foreground uppercase tracking-widest font-display drop-shadow-lg transition-all group-hover:fill-primary"

                              style="text-shadow: 0 4px 10px rgba(0,0,0,0.1);">

                            {{ item.name }}

                        </text>

                        <circle r="3" fill="currentColor" class="text-primary animate-pulse" cy="15" />

                    </g>



                    <g v-for="(aisle, i) in [...item.aisles].reverse()" :key="aisle.id">

                       

                        <g :transform="`translate(${item.x}, ${item.y + item.totalHeight - ((i + 1) * (SLICE_H + GAP))})`"

                           class="transition-transform duration-300 group-hover:translate-y-[-4px]">

                           

                            <g filter="url(#floorShadow)">

                               

                                <g>

                                    <path :d="`M0 ${SLICE_H} L${(BLOCK_W * SIDE_DEPTH) * (item.dir * -1)} ${SLICE_H - BLOCK_SKEW} V-${BLOCK_SKEW} L0 0 Z`"

                                          :fill="item.baseColor" class="brightness-50 saturate-150" />

                                   

                                    <path :d="`M0 ${SLICE_H} L${(BLOCK_W * SIDE_DEPTH) * (item.dir * -1)} ${SLICE_H - BLOCK_SKEW} V-${BLOCK_SKEW} L0 0 Z`"

                                          fill="url(#sideShadowGradient)" />

                                </g>



                                <g>

                                    <path :d="`M0 ${SLICE_H} L${BLOCK_W * item.dir} ${SLICE_H - BLOCK_SKEW} V-${BLOCK_SKEW} L0 0 Z`"

                                          :fill="item.baseColor" class="transition-all duration-300 group-hover:brightness-110" />



                                    <foreignObject

                                        v-if="aisle.image_url"

                                        :x="item.dir === 1 ? 0 : -BLOCK_W"

                                        :y="-BLOCK_SKEW"

                                        :width="BLOCK_W"

                                        :height="SLICE_H + BLOCK_SKEW"

                                        class="pointer-events-none"

                                    >

                                        <div xmlns="http://www.w3.org/1999/xhtml" class="w-full h-full relative overflow-hidden">

                                            <img :src="aisle.image_url"

                                                 class="w-full h-full object-cover block opacity-100"

                                                 :style="{ clipPath: item.dir === 1

                                                    ? 'polygon(0% 45%, 100% 0%, 100% 55%, 0% 100%)'

                                                    : 'polygon(0% 0%, 100% 45%, 100% 100%, 0% 55%)' }"

                                            />

                                            <div class="absolute inset-0 pointer-events-none opacity-20 border-2 border-white mix-blend-overlay"

                                                 :style="{ clipPath: item.dir === 1

                                                    ? 'polygon(0% 45%, 100% 0%, 100% 55%, 0% 100%)'

                                                    : 'polygon(0% 0%, 100% 45%, 100% 100%, 0% 55%)' }"

                                            ></div>

                                        </div>

                                    </foreignObject>



                                    <path :d="`M0 ${SLICE_H} L${BLOCK_W * item.dir} ${SLICE_H - BLOCK_SKEW} V-${BLOCK_SKEW} L0 0 Z`"

                                          fill="url(#glassSheen)" class="mix-blend-overlay pointer-events-none" />

                                   

                                    <path :d="`M0 0 L${BLOCK_W * item.dir} -${BLOCK_SKEW}`"

                                          stroke="white" stroke-width="1.5" stroke-opacity="0.5" fill="none" />

                                </g>

                               

                                <g>

                                    <path :d="`M0 0 L${BLOCK_W * item.dir} -${BLOCK_SKEW} L${(BLOCK_W * (1 - SIDE_DEPTH)) * item.dir} -${BLOCK_SKEW * 2} L${(BLOCK_W * SIDE_DEPTH) * (item.dir * -1)} -${BLOCK_SKEW} Z`"

                                          :fill="item.baseColor" class="brightness-125 saturate-50" />

                                    <path :d="`M0 0 L${BLOCK_W * item.dir} -${BLOCK_SKEW} L${(BLOCK_W * (1 - SIDE_DEPTH)) * item.dir} -${BLOCK_SKEW * 2} L${(BLOCK_W * SIDE_DEPTH) * (item.dir * -1)} -${BLOCK_SKEW} Z`"

                                          fill="white" fill-opacity="0.15" />

                                </g>



                            </g>



                            <g v-if="item.dir === 1" :transform="`translate(${BLOCK_W + 8}, 5)`">

                                <line x1="-8" y1="0" x2="-2" y2="0" class="stroke-foreground/30" stroke-width="1" />

                                <text x="2" y="3" text-anchor="start" font-size="10" font-weight="700"

                                      class="fill-foreground/90 uppercase tracking-tight font-sans drop-shadow-md">

                                    {{ aisle.name.substring(0, 16) }}

                                </text>

                            </g>

                            <g v-else :transform="`translate(-${BLOCK_W + 8}, 5)`">

                                <line x1="2" y1="0" x2="8" y2="0" class="stroke-foreground/30" stroke-width="1" />

                                <text x="-2" y="3" text-anchor="end" font-size="10" font-weight="700"

                                      class="fill-foreground/90 uppercase tracking-tight font-sans drop-shadow-md">

                                    {{ aisle.name.substring(0, 16) }}

                                </text>

                            </g>



                        </g>

                    </g>

                </g>

            </g>

        </svg>

    </div>

</template>