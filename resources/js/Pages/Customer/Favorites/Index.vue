<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Heart, ArrowLeft, HeartOff, LayoutGrid, Terminal } from 'lucide-vue-next';

// Layouts & Componentes
import ShopLayout from '@/Layouts/ShopLayout.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';

const props = defineProps({
    favorites: { type: Object, default: () => ({ data: [] }) }
});

const page = usePage();
const isAuth = computed(() => !!page.props.auth?.customer);

// --- PROTOCOLO DE LECTURA HÍBRIDA ---
const guestFavs = ref([]);

const loadGuestFavorites = () => {
    if (!isAuth.value) {
        guestFavs.value = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
    }
};

onMounted(() => {
    loadGuestFavorites();
    window.addEventListener('local-favorites-updated', loadGuestFavorites);
});

onUnmounted(() => {
    window.removeEventListener('local-favorites-updated', loadGuestFavorites);
});

// Selector dinámico: Si es Auth usa DB, si es Guest usa LocalStorage
const displayList = computed(() => {
    return isAuth.value ? (props.favorites?.data || []) : guestFavs.value;
});

const totalCount = computed(() => displayList.value.length);
</script>