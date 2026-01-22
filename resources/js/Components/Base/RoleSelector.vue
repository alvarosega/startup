<script setup>
    defineProps({ modelValue: String, error: String });
    defineEmits(['update:modelValue']);
    </script>
    
    <template>
        <div class="mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div 
                    @click="$emit('update:modelValue', 'client')"
                    :class="[
                        'cursor-pointer p-4 rounded-xl border-2 transition-all text-center',
                        modelValue === 'client' ? 'border-blue-600 bg-blue-50' : 'border-gray-100'
                    ]"
                >
                    <div class="text-3xl mb-2">🛒</div>
                    <h4 class="font-bold text-gray-800">Soy Cliente</h4>
                    <p class="text-[10px] text-gray-500 uppercase">Quiero pedir productos</p>
                </div>
    
                <div 
                    @click="$emit('update:modelValue', 'driver')"
                    :class="[
                        'cursor-pointer p-4 rounded-xl border-2 transition-all text-center',
                        modelValue === 'driver' ? 'border-blue-600 bg-blue-50' : 'border-gray-100'
                    ]"
                >
                    <div class="text-3xl mb-2">🛵</div>
                    <h4 class="font-bold text-gray-800">Soy Repartidor</h4>
                    <p class="text-[10px] text-gray-500 uppercase">Quiero entregar pedidos</p>
                </div>
            </div>
            <p v-if="error" class="text-red-500 text-xs mt-2 text-center">{{ error }}</p>
        </div>
    </template>