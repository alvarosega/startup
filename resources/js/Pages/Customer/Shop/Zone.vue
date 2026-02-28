<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ArrowLeft, Plus, Minus, PackageX, X, ShoppingCart, ChevronRight, Zap, Layers } from 'lucide-vue-next';

const props = defineProps({
    zone: Object,
    groupedCategories: { type: Array, default: () => [] },
    targetCategory: [String, Number, null]
});


const getGuestUuid = () => {
    let uuid = localStorage.getItem('guest_client_uuid');
    if (!uuid) {
        uuid = crypto.randomUUID(); // Estándar moderno de los navegadores
        localStorage.setItem('guest_client_uuid', uuid);
    }
    return uuid;
};
onMounted(() => {
    if (props.targetCategory) {
        nextTick(() => {
            const element = document.getElementById(`subcategory-${props.targetCategory}`);
            if (element) element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
});

const isModalOpen = ref(false);
const selectedProduct = ref(null);
const currentIndex = ref(0);
const quantity = ref(1);

const activeSku = computed(() => selectedProduct.value);

const addToCart = () => {
    if (!activeSku.value || activeSku.value.available_stock <= 0) return; // Validación extra
    
    router.post(route('customer.cart.add'), { 
        sku_id: activeSku.value.id,
        quantity: quantity.value,
        guest_client_uuid: getGuestUuid()
    }, {
        preserveScroll: true,
        onSuccess: () => closeModal()
    });
};

const openProductModal = (product) => {
    selectedProduct.value = product;
    currentIndex.value = 0;
    quantity.value = 1;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => selectedProduct.value = null, 300);
};

const increaseQty = () => quantity.value++;
const decreaseQty = () => { if (quantity.value > 1) quantity.value--; };
const goBack = () => router.visit(route('customer.shop.index'));
</script>

<template>
    <ShopLayout>
        <Head :title="zone?.name || 'Zona'" />
        <div class="min-h-screen bg-background pb-32 relative">
            <header class="sticky top-0 z-40 bg-background/80 backdrop-blur-xl border-b border-border/10 px-4 py-3">
                <div class="container mx-auto flex items-center gap-4">
                    <button @click="goBack" class="w-10 h-10 flex items-center justify-center rounded-full bg-card border border-border/10">
                        <ArrowLeft class="w-5 h-5" />
                    </button>
                    <h1 class="font-black text-xl uppercase italic">{{ zone?.name }}</h1>
                </div>
            </header>

            <div class="container mx-auto py-6 px-4 space-y-10">
                <div v-for="parent in groupedCategories" :key="parent.id">
                    <h2 class="text-xl font-black uppercase mb-4">{{ parent.name }}</h2>
                    <div v-for="sub in parent.subcategories" :key="sub.id" :id="`subcategory-${sub.id}`" class="mb-8">
                        <h3 class="text-sm font-bold opacity-50 uppercase mb-4">{{ sub.name }}</h3>
                        <div class="flex overflow-x-auto gap-4 pb-4 scrollbar-hide">
                            <div v-for="product in sub.products" :key="product.id" 
                                @click="product.available_stock > 0 ? openProductModal(product) : null"
                                class="shrink-0 w-40 bg-card rounded-2xl border border-border/10 p-4 relative"
                                :class="{ 'opacity-50 grayscale cursor-not-allowed': product.available_stock <= 0 }">
                                
                                <div v-if="product.available_stock <= 0" class="absolute inset-0 z-10 flex items-center justify-center">
                                    <span class="bg-destructive text-destructive-foreground text-[10px] font-black uppercase px-2 py-1 rounded-full">Agotado</span>
                                </div>

                                <img :src="product.image_url" class="w-full aspect-square object-contain mb-3">
                                <h4 class="text-xs font-black uppercase truncate">{{ product.name }}</h4>
                                <div class="mt-2 text-primary font-bold">Bs {{ product.price }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-background/90 backdrop-blur-md">
            <div class="bg-card w-full max-w-sm rounded-[32px] p-6 border border-border/10 relative">
                <button @click="closeModal" class="absolute top-4 right-4"><X /></button>
                <img :src="activeSku?.image_url" class="w-full h-48 object-contain mb-6">
                <h3 class="text-xl font-black text-center uppercase">{{ activeSku?.name }}</h3>
                <div class="text-center text-2xl font-black text-primary my-4">Bs {{ activeSku?.price }}</div>
                
                <div class="flex items-center gap-4 mb-6">
                    <button @click="decreaseQty" class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center"><Minus /></button>
                    <div class="flex-1 text-center font-mono text-xl">{{ quantity }}</div>
                    <button @click="increaseQty" class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center"><Plus /></button>
                </div>
                <button 
                    @click="addToCart" 
                    :disabled="activeSku?.available_stock <= 0"
                    class="w-full py-4 font-black uppercase rounded-xl flex items-center justify-center gap-2 transition-all"
                    :class="activeSku?.available_stock > 0 
                        ? 'bg-primary text-black' 
                        : 'bg-muted text-muted-foreground cursor-not-allowed'"
                >
                    <ShoppingCart v-if="activeSku?.available_stock > 0" />
                    <PackageX v-else />
                    {{ activeSku?.available_stock > 0 ? 'Agregar al Pedido' : 'Sin Existencias' }}
                </button>
            </div>
        </div>
    </ShopLayout>
</template>