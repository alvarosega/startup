<script setup>
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    brands: { type: Array, default: () => [] },
    activeId: { type: [String, Number], default: null },
    loading: { type: Boolean, default: false }
});

const page = usePage();

const navigateToBrand = (slug) => {
    if (props.loading) return;
    
    const isBrandPage = page.component === 'Customer/Brand/Show';
    
    router.visit(route('customer.brand.show', { slug: slug }), { 
        preserveScroll: true,
        preserveState: true,
        // RECTIFICACIÓN: Incluimos brandHero para que el banner cambie al navegar
        ...(isBrandPage ? { only: ['currentBrand', 'brandHero', 'products', 'filters'] } : {})
    });
};

// --- CÁLCULO DE RESPLANDOR (GLOW SYSTEM) ---
const getBrandStyle = (hex) => {
    // Si la marca no tiene color definido, usamos el púrpura Cyber por defecto
    const cleanHex = hex ? hex.replace('#', '') : 'a855f7';
    const baseColor = `#${cleanHex}`;
    
    return {
        '--brand-glow-core': `${baseColor}CC`, // 80% opacidad
        '--brand-glow-outer': `${baseColor}33`, // 20% opacidad
    };
};
</script>

<template>
    <section class="w-full py-4 bg-transparent relative z-10 overflow-visible">
        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 gap-8 pb-4">
            
            <template v-if="!loading && brands.length > 0">
                <div v-for="brand in brands" :key="brand.id"
                     @click="navigateToBrand(brand.slug)"
                     :style="getBrandStyle(brand.bg_color)"
                     class="group flex flex-col items-center gap-5 snap-start shrink-0 cursor-pointer w-[90px] transition-all duration-500">
                     
                    <div class="relative w-20 h-20 flex items-center justify-center">
                        
                        <div class="absolute inset-0 rounded-full transition-all duration-700 blur-[35px] pointer-events-none"
                            :class="[
                                (!activeId || String(activeId) === String(brand.id)) 
                                ? 'opacity-100 scale-[2.5] lg:scale-[3]' 
                                : 'opacity-0 group-hover:opacity-80 group-hover:scale-[2]'
                            ]"
                            :style="{ 
                                background: 'radial-gradient(circle, var(--brand-glow-core) 0%, var(--brand-glow-outer) 40%, transparent 75%)' 
                            }">
                        </div>

                        <img :src="brand.logo_url" 
                             class="relative z-10 w-16 h-16 object-contain transition-all duration-500 group-hover:-translate-y-3 filter drop-shadow-[0_0_15px_rgba(0,0,0,0.2)]"
                             :alt="brand.name"
                             @error="(e) => e.target.src = '/assets/img/brand_placeholder.png'">
                    </div>

                    <span class="text-[10px] font-black tracking-widest uppercase text-center leading-tight line-clamp-2 w-full px-1 transition-all duration-300"
                          :class="String(activeId) === String(brand.id) ? 'text-primary scale-110' : 'text-foreground/70 group-hover:text-foreground'">
                        {{ brand.name }}
                    </span>
                </div>
            </template>

            <template v-else>
                <div v-for="n in 8" :key="n" class="flex flex-col items-center gap-5 shrink-0 w-[90px] animate-pulse">
                    <div class="w-16 h-16 rounded-full bg-foreground/10"></div>
                    <div class="h-2 w-16 bg-foreground/10 rounded-full"></div>
                </div>
            </template>

            <div class="w-12 shrink-0"></div> 
        </div>
    </section>
</template>

<style scoped>
/* Utilidades de Scroll Invisible */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* OPTIMIZACIÓN DE RENDERIZADO: Evita el flickering del blur */
.blur-\[35px\] {
    will-change: transform, opacity;
    transform: translateZ(0); 
}
</style>