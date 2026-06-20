<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';

const props = defineProps({
    sku: { type: Object, required: true }
});

const page = usePage();
const user = computed(() => page.props.auth?.customer);
const isAuth = computed(() => !!user.value);
const localFavorites = ref([]);

const updateLocalFavs = () => {
    localFavorites.value = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
};

onMounted(() => {
    updateLocalFavs();
    window.addEventListener('local-favorites-updated', updateLocalFavs);
});

onUnmounted(() => {
    window.removeEventListener('local-favorites-updated', updateLocalFavs);
});

const isFavorite = computed(() => {
    if (!props.sku) return false;
    if (isAuth.value) return user.value.favorites_ids?.includes(props.sku.product_id);
    return localFavorites.value.some(fav => fav.product_id === props.sku.product_id);
});

const isProcessing = ref(false);

const toggleFavorite = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (isProcessing.value || !props.sku) return;

    if (isAuth.value) {
        isProcessing.value = true;
        router.post(route('customer.favorites.toggle'), { product_id: props.sku.product_id }, {
            preserveScroll: true, 
            preserveState: true,
            onFinish: () => isProcessing.value = false
        });
    } else {
        let favs = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
        const index = favs.findIndex(f => f.product_id === props.sku.product_id);
        
        if (index > -1) {
            favs.splice(index, 1);
        } else {
            favs.push({ 
                id: props.sku.id,
                product_id: props.sku.product_id,
                name: props.sku.name,
                image: props.sku.image,
                brand_name: props.sku.brand_name,
                bg_color: props.sku.bg_color
            });
        }
        localStorage.setItem('guest_favorites', JSON.stringify(favs));
        window.dispatchEvent(new Event('local-favorites-updated'));
    }
};
</script>

<template>
    <button @click="toggleFavorite"
            class="absolute top-3 right-3 z-30 p-2 bg-[#15151f]/80 backdrop-blur-md border border-[#32323b] rounded-md transition-all duration-150 active:scale-95 focus:outline-none outline-none hover:bg-[#15151f]">
        <Heart :size="16" :stroke-width="isFavorite ? 0 : 1.5"
               :class="isFavorite ? 'text-primary fill-primary' : 'text-white'" />
    </button>
</template>