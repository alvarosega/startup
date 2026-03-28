<script setup>
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    activeId: { type: [String, Number], default: null },
    loading: { type: Boolean, default: false }
});

const page = usePage();

const navigateToCategory = (slug) => {
    if (props.loading) return;
    const isCategoryPage = page.component === 'Customer/Category/Show';
    router.visit(route('customer.shop.category', { category: slug }), { 
        preserveScroll: true,
        preserveState: true,
        ...(isCategoryPage ? { only: ['categoryData', 'products', 'filters'] } : {})
    });
};

const getCategoryStyle = (hex) => {
    const cleanHex = hex ? hex.replace('#', '') : '6366f1';
    const baseColor = `#${cleanHex}`;
    
    return {
        '--cat-glow-core': `${baseColor}CC`, // 80% opacidad (Núcleo denso)
        '--cat-glow-outer': `${baseColor}33`, // 20% opacidad (Aura expansiva)
    };
};
</script>

<template>
    <section class="w-full py-10 bg-transparent relative z-10 overflow-hidden">
        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 gap-8 pb-4">
            
            <template v-if="!loading && categories.length > 0">
                <div v-for="cat in categories" :key="cat.id"
                     @click="navigateToCategory(cat.slug)"
                     :style="getCategoryStyle(cat.bg_color)"
                     class="group flex flex-col items-center gap-5 snap-start shrink-0 cursor-pointer w-[90px] transition-all duration-500">
                     
                    <div class="relative w-20 h-20 flex items-center justify-center">
                        
                        <div class="absolute inset-0 rounded-full transition-all duration-700 blur-[35px] pointer-events-none"
                            :class="[
                                // Si está activa o en Home (sin selección), mostramos el resplandor
                                (!activeId || String(activeId) === String(cat.id)) 
                                ? 'opacity-100 scale-[2.5] lg:scale-[3]' 
                                : 'opacity-0 group-hover:opacity-80 group-hover:scale-[2]'
                            ]"
                            :style="{ 
                                background: 'radial-gradient(circle, var(--cat-glow-core) 0%, var(--cat-glow-outer) 40%, transparent 75%)' 
                            }">
                        </div>

                        <img :src="cat.image_url" 
                             class="relative z-10 w-16 h-16 object-contain transition-all duration-500 group-hover:-translate-y-3 filter drop-shadow-[0_0_15px_rgba(0,0,0,0.2)]"
                             :alt="cat.name"
                             @error="(e) => e.target.src = '/assets/img/category_placeholder.png'">
                    </div>

                    <span class="text-[10px] font-black tracking-widest uppercase text-center leading-tight line-clamp-2 w-full px-1 transition-all duration-300"
                          :class="String(activeId) === String(cat.id) ? 'text-primary scale-110' : 'text-foreground/70 group-hover:text-foreground'">
                        {{ cat.name }}
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
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Inercia de hardware para el efecto de resplandor masivo */
.blur-\[35px\] {
    will-change: transform, opacity;
    transform: translateZ(0); /* Forzar GPU */
}
</style>