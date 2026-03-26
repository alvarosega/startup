<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    // Recibe el array procesado desde page.props.categories_menu o prop directa
    categories: { type: Array, default: () => [] },
    activeId: { type: String, default: null } 
});

/**
 * NAVEGACIÓN TÉCNICA (PARTIAL RELOAD)
 * Al cambiar de departamento, solo solicitamos los datos específicos 
 * de la categoría y el listado de productos para maximizar la velocidad.
 */
const navigateToCategory = (slug) => {
    router.visit(route('customer.shop.category', { category: slug }), {
        preserveScroll: true, // Mantenemos la posición del carrusel para UX
        preserveState: true,
        only: ['categoryData', 'products', 'filters'] 
    });
};

const getCategoryStyle = (hex) => {
    // Blindaje de color: Asegura formato HEX válido para CSS Variables
    const color = hex && hex.startsWith('#') ? hex : (hex ? `#${hex}` : '#6366f1');
    return {
        '--cat-color': color,
        '--cat-glow-strong': `${color}99`, 
        '--cat-glow-soft': `${color}33`,   
    };
};
</script>

<template>
    <section v-if="categories.length > 0" class="w-full py-8 bg-transparent relative z-10 overflow-hidden">
        <div class="px-6 mb-6 flex items-center justify-between">
            <h2 class="text-[10px] font-black uppercase tracking-[0.3em] text-muted-foreground/60">
                Explorar Departamentos
            </h2>
        </div>

        <div class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar px-6 gap-8 pb-4">
            <div v-for="cat in categories" :key="cat.id"
                 @click="navigateToCategory(cat.slug)"
                 :style="getCategoryStyle(cat.bg_color)"
                 class="group flex flex-col items-center gap-4 snap-start shrink-0 cursor-pointer w-[100px] transition-all duration-500"
                 :class="{ 
                    'opacity-100 scale-110': activeId === cat.id, 
                    'opacity-50 grayscale hover:grayscale-0 hover:opacity-100': activeId && activeId !== cat.id 
                 }">
                
                <div class="relative w-[100px] h-[100px] flex items-center justify-center overflow-visible">
                    <div class="absolute inset-0 rounded-full transition-all duration-700 blur-[25px] scale-110"
                         :class="activeId === cat.id ? 'opacity-100 scale-125' : 'opacity-0 group-hover:opacity-60 group-hover:scale-125'"
                         :style="{ background: 'radial-gradient(circle at center, var(--cat-glow-strong) 0%, var(--cat-glow-soft) 50%, transparent 85%)' }">
                    </div>

                    <img :src="cat.image_url" 
                         class="relative z-10 w-16 h-16 object-contain transition-all duration-500 drop-shadow-[0_10px_20px_rgba(0,0,0,0.15)] group-hover:scale-110 group-hover:-translate-y-2"
                         :alt="cat.name"
                         @error="(e) => e.target.src = '/assets/img/placeholder-cat.png'">
                </div>

                <span class="text-[10px] font-black tracking-tighter uppercase transition-colors text-center leading-tight line-clamp-2 w-full"
                      :class="activeId === cat.id ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'">
                    {{ cat.name }}
                </span>
            </div>
            <div class="w-6 shrink-0"></div> 
        </div>
    </section>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Optimización de rendimiento para animaciones de transformación */
.group {
    will-change: transform, opacity;
    backface-visibility: hidden;
}
</style>