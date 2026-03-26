<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeft, ShoppingCart, Zap, CheckCircle2 } from 'lucide-vue-next';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import HeroCarousel from '@/Components/Shop/HeroCarousel.vue';

const props = defineProps({
    bundle: { type: Object, required: true },
    bundleBanners: { type: Object, default: () => ({ data: [] }) }
});

const goBack = () => window.history.back();

const handleAddAtomic = () => {
    router.post(route('customer.cart.upsert'), {
        target_id: props.bundle.data.id,
        target_type: 'bundle',
        quantity: 1
    }, { preserveScroll: true });
};
</script>

<template>
    <ShopLayout>
        <Head :title="bundle.data.name" />
        <div class="w-full min-h-screen bg-background pb-32">
            <div class="px-6 py-8 flex items-center gap-4">
                <button @click="goBack" class="p-3 bg-card border border-border/50 rounded-2xl hover:bg-muted transition-colors">
                    <ChevronLeft :size="24" stroke-width="3" />
                </button>
                <h1 class="text-3xl font-black uppercase italic tracking-tighter">Detalles de la Promo</h1>
            </div>

            <HeroCarousel v-if="bundleBanners.data.length > 0" :banners="bundleBanners.data" />

            <div class="px-6 md:px-12 grid grid-cols-1 lg:grid-cols-2 gap-16 mt-12">
                <div class="relative group">
                    <div class="aspect-square rounded-[3.5rem] overflow-hidden bg-muted/20 border border-border/50 flex items-center justify-center p-12">
                        <img :src="bundle.data.image_url" class="w-full h-full object-contain transition-transform group-hover:scale-105" />
                    </div>
                    <div class="absolute -top-4 -right-4 bg-primary text-white px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-[0.3em] shadow-xl rotate-3">
                        Pack Cerrado
                    </div>
                </div>

                <div class="flex flex-col justify-center">
                    <div class="flex items-center gap-2 text-primary mb-6">
                        <Zap :size="20" class="fill-current" />
                        <span class="text-xs font-black uppercase tracking-[0.4em]">Oferta de Valor Directo</span>
                    </div>
                    <h2 class="text-6xl font-black uppercase italic tracking-tighter leading-[0.85] mb-8">{{ bundle.data.name }}</h2>
                    <p class="text-muted-foreground text-lg mb-12 leading-relaxed max-w-xl">{{ bundle.data.description }}</p>
                    <div class="space-y-4 mb-16">
                        <div v-for="item in bundle.data.items" :key="item.id" class="flex items-center gap-5 p-4 bg-card border border-border/50 rounded-3xl">
                            <div class="w-14 h-14 bg-muted/30 rounded-2xl p-2 flex-shrink-0">
                                <img :src="item.image" class="w-full h-full object-contain" />
                            </div>
                            <div class="flex-1">
                                <span class="block font-black text-sm uppercase tracking-tight">{{ item.name }}</span>
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">{{ item.quantity }} unidades incluidas</span>
                            </div>
                            <CheckCircle2 :size="18" class="text-primary/40" />
                        </div>
                    </div>
                    <div class="bg-card border-2 border-primary/20 rounded-[3rem] p-10 flex items-center justify-between shadow-2xl">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-1">Precio Final</span>
                            <span class="text-5xl font-black tracking-tighter italic">Bs. {{ bundle.data.fixed_price }}</span>
                        </div>
                        <button @click="handleAddAtomic" class="h-20 px-12 bg-primary text-white rounded-3xl font-black uppercase tracking-widest text-xs flex items-center gap-5 active:scale-95 transition-all">
                            <ShoppingCart :size="22" stroke-width="3" /> Añadir al Carrito
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>