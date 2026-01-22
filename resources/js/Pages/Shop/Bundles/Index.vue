<script setup>
    import ShopLayout from '@/Layouts/ShopLayout.vue';
    import { Head, router } from '@inertiajs/vue3';
    import { ShoppingBag, Star, PackagePlus } from 'lucide-vue-next';
    
    defineProps({ bundles: Array });
    
    const addToCart = (id) => {
        router.post(route('shop.bundles.add', id), {}, {
            preserveScroll: true,
            onSuccess: () => { /* El toast global lo maneja */ }
        });
    };
    </script>
    
    <template>
        <ShopLayout>
            <Head title="Packs y Ofertas" />
            
            <div class="bg-blue-600 text-white py-12 px-4 mb-8">
                <div class="max-w-7xl mx-auto text-center">
                    <h1 class="text-3xl md:text-5xl font-black italic tracking-tighter mb-2">PACKS & OFERTAS</h1>
                    <p class="text-blue-100 font-medium">Combinaciones listas para tu evento.</p>
                </div>
            </div>
    
            <div class="max-w-7xl mx-auto px-4 pb-20">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="bundle in bundles" :key="bundle.id" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden group hover:shadow-xl transition-all duration-300">
                        
                        <div class="h-48 bg-gray-100 flex items-center justify-center relative overflow-hidden">
                            <ShoppingBag class="text-gray-300 w-16 h-16 group-hover:scale-110 transition duration-500" />
                            <div class="absolute top-3 right-3 bg-yellow-400 text-blue-900 text-xs font-black px-2 py-1 rounded shadow">
                                POPULAR
                            </div>
                        </div>
    
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-black text-gray-800 leading-tight">{{ bundle.name }}</h3>
                                <div class="flex items-center gap-1 text-yellow-500 text-xs font-bold">
                                    <Star :size="12" fill="currentColor" /> {{ parseFloat(bundle.reviews_avg_rating || 5).toFixed(1) }}
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ bundle.description || 'Sin descripción.' }}</p>
    
                            <div class="mb-6">
                                <span v-if="bundle.fixed_price" class="text-2xl font-black text-blue-600">Bs {{ bundle.fixed_price }}</span>
                                <span v-else class="text-sm text-gray-400 italic">Precio según productos</span>
                            </div>
    
                            <button @click="addToCart(bundle.id)" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-blue-600/30 transition active:scale-95">
                                <PackagePlus :size="20" /> Agregar Pack
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </ShopLayout>
    </template>