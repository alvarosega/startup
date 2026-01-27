<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, ShoppingCart, Plus, Minus, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    product: Object
});

const emit = defineEmits(['close']);

const selectedVariant = ref(null);
const quantity = ref(1);
const processing = ref(false);

// Seleccionar variante automáticamente si solo hay una
const init = () => {
    if (props.product?.variants?.length === 1) {
        selectedVariant.value = props.product.variants[0];
    } else {
        selectedVariant.value = null; // Reset para obligar a elegir
    }
    quantity.value = 1;
};

// Exponer init para llamarlo desde el padre al abrir
defineExpose({ init });

const addToCart = () => {
    if (!selectedVariant.value) return;
    
    processing.value = true;
    router.post(route('cart.store'), {
        sku_id: selectedVariant.value.id,
        quantity: quantity.value
    }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            emit('close'); // Cerrar modal al terminar
        }
    });
};

const increment = () => {
    if (selectedVariant.value && quantity.value < selectedVariant.value.stock) {
        quantity.value++;
    }
};

const decrement = () => {
    if (quantity.value > 1) quantity.value--;
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

        <div class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row animate-in fade-in zoom-in-95 duration-200">
            
            <button @click="$emit('close')" class="absolute top-4 right-4 z-10 p-2 bg-white/80 rounded-full hover:bg-gray-100 transition">
                <X :size="20" class="text-gray-500" />
            </button>

            <div class="w-full md:w-1/2 bg-gray-50 p-8 flex items-center justify-center">
                <img :src="product.image_url" :alt="product.name" class="max-h-64 object-contain mix-blend-multiply">
            </div>

            <div class="w-full md:w-1/2 p-6 flex flex-col">
                <div class="mb-auto">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ product.brand }}</p>
                    <h2 class="text-2xl font-black text-gray-900 mb-2">{{ product.name }}</h2>
                    <p class="text-sm text-gray-500 mb-6">{{ product.description || 'Sin descripción disponible.' }}</p>

                    <div class="space-y-3 mb-6">
                        <label class="text-xs font-bold text-gray-700 uppercase">Selecciona una presentación:</label>
                        <div class="grid grid-cols-1 gap-2">
                            <button v-for="variant in product.variants" :key="variant.id"
                                @click="selectedVariant = variant; quantity = 1"
                                :disabled="!variant.has_stock"
                                class="flex items-center justify-between p-3 rounded-xl border text-left transition-all"
                                :class="[
                                    selectedVariant?.id === variant.id 
                                        ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600' 
                                        : 'border-gray-200 hover:border-blue-300',
                                    !variant.has_stock ? 'opacity-50 cursor-not-allowed bg-gray-50' : ''
                                ]">
                                <div>
                                    <span class="font-bold text-sm text-gray-800">{{ variant.name }}</span>
                                    <span v-if="!variant.has_stock" class="ml-2 text-[10px] bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded font-bold">AGOTADO</span>
                                </div>
                                <span class="font-black text-blue-700">Bs {{ variant.price.toFixed(2) }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="selectedVariant" class="border-t border-gray-100 pt-6 animate-in slide-in-from-bottom-2 fade-in">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-medium text-gray-600">Cantidad:</span>
                        <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-1 border border-gray-200">
                            <button @click="decrement" class="p-1 hover:bg-white rounded shadow-sm transition disabled:opacity-50" :disabled="quantity <= 1">
                                <Minus :size="16" />
                            </button>
                            <span class="w-8 text-center font-bold text-gray-800">{{ quantity }}</span>
                            <button @click="increment" class="p-1 hover:bg-white rounded shadow-sm transition disabled:opacity-50" :disabled="quantity >= selectedVariant.stock">
                                <Plus :size="16" />
                            </button>
                        </div>
                    </div>

                    <button @click="addToCart" :disabled="processing"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-xl shadow-blue-200 transition-all active:scale-95 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <ShoppingCart :size="20" />
                        <span v-if="processing">Agregando...</span>
                        <span v-else>Agregar al Carrito - Bs {{ (selectedVariant.price * quantity).toFixed(2) }}</span>
                    </button>
                    
                    <p v-if="quantity >= selectedVariant.stock" class="text-[10px] text-orange-500 text-center mt-2 font-medium">
                        <AlertTriangle :size="10" class="inline" /> Stock máximo disponible alcanzado
                    </p>
                </div>
                
                <div v-else class="border-t border-gray-100 pt-6 text-center text-sm text-gray-400">
                    Elige una opción para continuar
                </div>
            </div>
        </div>
    </div>
</template>