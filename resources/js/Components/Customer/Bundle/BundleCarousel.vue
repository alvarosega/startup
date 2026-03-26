<script setup>
import { router, Link } from '@inertiajs/vue3';
import { Zap, ArrowRight, Sparkles } from 'lucide-vue-next';

const props = defineProps({
    bundles: { type: Array, default: () => [] }
});

const handleOpenBundle = (slug) => {
    // Alineación total con el parámetro {slug} de web.php
    router.visit(route('customer.shop.bundle', { slug: slug }));
};
</script>

<template>
    <section class="w-full py-12 overflow-hidden">
        <div class="px-6 md:px-12 flex items-end justify-between mb-8">
            <div class="space-y-1">
                <div class="flex items-center gap-2 text-primary">
                    <Zap :size="16" class="fill-current" />
                    <span class="text-[10px] font-black uppercase tracking-[0.3em]">Smart Savings</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-black uppercase italic tracking-tighter leading-none">Packs Especiales</h2>
            </div>
            <Link :href="route('customer.shop.search')" class="group flex items-center gap-2 text-[10px] font-black uppercase tracking-widest hover:text-primary transition-all">
                Ver todo <ArrowRight :size="14" class="group-hover:translate-x-1 transition-transform" />
            </Link>
        </div>

        <div class="flex overflow-x-auto snap-x snap-mandatory gap-6 px-6 md:px-12 pb-8 hide-scrollbar">
            <div v-for="bundle in bundles" :key="bundle.id" @click="handleOpenBundle(bundle.slug)"
                 class="flex-none w-[85vw] md:w-[450px] h-[320px] snap-center group relative rounded-[2.5rem] overflow-hidden bg-muted border border-border/50 shadow-sm transition-all hover:shadow-2xl cursor-pointer">
                <img :src="bundle.image_path" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-70" />
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent p-8 flex flex-col justify-end">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-3 py-1 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-[9px] font-black text-white uppercase tracking-widest">
                            {{ bundle.type === 'atomic' ? 'Combo Fijo' : 'Pack Editable' }}
                        </span>
                        <div v-if="bundle.type === 'template'" class="text-accent animate-pulse">
                            <Sparkles :size="14" fill="currentColor" />
                        </div>
                    </div>
                    <h3 class="text-3xl font-black text-white uppercase italic leading-none mb-2 tracking-tighter">{{ bundle.name }}</h3>
                    <p class="text-white/60 text-xs font-medium line-clamp-2 mb-6 max-w-[80%]">{{ bundle.description }}</p>
                    <div class="flex items-center justify-between mt-auto">
                        <span v-if="bundle.fixed_price" class="text-2xl font-black text-white tracking-tighter">Bs. {{ bundle.fixed_price }}</span>
                        <span v-else class="text-[10px] font-black text-white/40 uppercase tracking-widest">Precio según selección</span>
                        <div class="w-12 h-12 bg-primary text-white rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                            <ArrowRight :size="20" stroke-width="3" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>