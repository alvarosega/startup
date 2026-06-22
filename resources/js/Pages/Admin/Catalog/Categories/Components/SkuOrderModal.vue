<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    category: Object
});

const emit = defineEmits(['close']);

const skus = ref([]);
const isLoading = ref(false);
const dragIndex = ref(null);
const dragOverIndex = ref(null);
const isSaving = ref(false);

watch(() => props.show, async (isOpen) => {
    if (isOpen && props.category) {
        isLoading.value = true;
        skus.value = [];
        dragOverIndex.value = null;
        try {
            const response = await fetch(route('admin.catalog.categories.skus', props.category.id));
            if (response.ok) {
                skus.value = await response.json();
            }
        } catch (error) {
            console.error("Fallo crítico al resolver el inventario de SKUs:", error);
        } finally {
            isLoading.value = false;
        }
    }
});

// --- MOTOR HTML5 DRAG & DROP DETERMINISTA CON RESOLUCIÓN EN SOLTADO (DROP) ---
const onDragStart = (index) => {
    dragIndex.value = index;
};

const onDragOver = (index) => {
    if (dragIndex.value === null) return;
    dragOverIndex.value = index;
};

const onDrop = (targetIndex) => {
    if (dragIndex.value === null || dragIndex.value === targetIndex) return;

    // Mutación controlada en un único paso tras finalizar el gesto físico
    const element = skus.value[dragIndex.value];
    skus.value.splice(dragIndex.value, 1);
    skus.value.splice(targetIndex, 0, element);

    dragIndex.value = null;
    dragOverIndex.value = null;
};

const onDragEnd = () => {
    dragIndex.value = null;
    dragOverIndex.value = null;
};

const saveOrder = () => {
    isSaving.value = true;
    const orderedIds = skus.value.map(sku => sku.id);

    router.put(route('admin.catalog.categories.update-sku-order', props.category.id), {
        ids: orderedIds
    }, {
        preserveState: true,
        onSuccess: () => {
            isSaving.value = false;
            emit('close');
        },
        onError: () => {
            isSaving.value = false;
        }
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-opacity" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-neutral-950/40 dark:bg-neutral-950/60 transition-opacity" @click="emit('close')"></div>

        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col z-10 overflow-hidden text-neutral-900 dark:text-neutral-50">
            
            <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 flex items-center justify-between select-none">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-rounded text-neutral-900 dark:text-white text-xl">view_cozy</span>
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-neutral-900 dark:text-neutral-50">Distribución de Góndola Digital</h3>
                        <p class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mt-0.5">
                            {{ category?.name }} — Reordenamiento secuencial de SKUs
                        </p>
                    </div>
                </div>
                <button @click="emit('close')" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center border border-neutral-200 dark:border-neutral-800">
                    <span class="material-symbols-rounded text-base">close</span>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-1.5">
                
                <div v-if="isLoading" class="py-16 text-center text-xs text-neutral-400 dark:text-neutral-500 flex flex-col items-center justify-center gap-2 select-none font-mono">
                    <span class="material-symbols-rounded text-2xl text-neutral-900 dark:text-white animate-spin">progress_activity</span>
                    <span class="uppercase tracking-wider">Resolviendo matriz de variantes físicas...</span>
                </div>

                <div v-else-if="skus.length === 0" class="py-16 text-center text-xs text-neutral-400 dark:text-neutral-500 font-mono uppercase tracking-wider select-none">
                    <span class="material-symbols-rounded text-2xl text-neutral-300 dark:text-neutral-700 block mb-1">inventory_2</span>
                    No existen SKUs asignados a los productos de este nodo.
                </div>

                <div v-else class="space-y-1.5 select-none">
                    <div 
                        v-for="(sku, index) in skus" 
                        :key="sku.id"
                        draggable="true"
                        @dragstart="onDragStart(index)"
                        @dragover.prevent="onDragOver(index)"
                        @drop="onDrop(index)"
                        @dragend="onDragEnd"
                        :class="[
                            'flex items-center justify-between p-2.5 bg-white dark:bg-neutral-950 border rounded-md transition-all duration-150',
                            dragIndex === index ? 'opacity-25 bg-neutral-50 dark:bg-neutral-900 border-neutral-200 dark:border-neutral-800' : 'border-neutral-200 dark:border-neutral-800',
                            dragOverIndex === index && dragIndex !== index ? 'border-neutral-900 dark:border-white bg-neutral-50/50 dark:bg-neutral-900/50 scale-[0.99]' : ''
                        ]"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="cursor-grab active:cursor-grabbing p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 rounded transition-colors flex items-center justify-center">
                                <span class="material-symbols-rounded text-lg">drag_indicator</span>
                            </div>
                            
                            <div class="w-9 h-9 bg-neutral-100 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-sm shrink-0 overflow-hidden flex items-center justify-center">
                                <img v-if="sku.image" :src="sku.image" class="w-full h-full object-cover" />
                                <span v-else class="text-[9px] text-neutral-400 dark:text-neutral-500 font-mono uppercase font-bold">N/A</span>
                            </div>
                            
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-neutral-900 dark:text-neutral-50 truncate uppercase tracking-tight">{{ sku.name }}</h4>
                                <p class="text-[10px] text-neutral-400 dark:text-neutral-500 truncate font-mono uppercase mt-0.5">{{ sku.product_name }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 pl-2 font-mono text-[10px]">
                            <span class="bg-neutral-50 dark:bg-neutral-900 px-2 py-0.5 rounded-sm border border-neutral-200 dark:border-neutral-800 text-neutral-500 dark:text-neutral-400 uppercase select-all">
                                {{ sku.code }}
                            </span>
                            <span class="font-bold text-neutral-900 dark:text-white bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded-sm border border-neutral-200 dark:border-neutral-800">
                                POS: {{ (index + 1) * 10 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0 select-none">
                <span class="text-[10px] text-neutral-400 dark:text-neutral-500 font-medium text-center sm:text-left leading-tight max-w-sm">
                    * El orden secuencial superior determina la prioridad de renderizado y posicionamiento dentro de la góndola móvil del cliente.
                </span>
                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto justify-end">
                    <button type="button" @click="emit('close')" class="px-3 py-1.5 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs font-bold uppercase tracking-wider transition-colors">
                        Cerrar
                    </button>
                    <button v-if="skus.length > 0" type="button" @click="saveOrder" :disabled="isSaving" 
                            class="bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 px-4 py-1.5 border border-transparent rounded-md transition-colors text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 disabled:opacity-50">
                        <span class="material-symbols-rounded text-sm">save</span>
                        <span>Persistir Posiciones</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>