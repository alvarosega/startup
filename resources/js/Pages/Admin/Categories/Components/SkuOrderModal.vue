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
const dragOverIndex = ref(null); // Seguidor de colisión activa para feedback B2B
const isSaving = ref(false);

watch(() => props.show, async (isOpen) => {
    if (isOpen && props.category) {
        isLoading.value = true;
        skus.value = [];
        dragOverIndex.value = null;
        try {
            const response = await fetch(route('admin.categories.skus', props.category.id));
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

// --- MOTOR NATIVO HTML5 DRAG & DROP OPTIMIZADO CON FEEDBACK RECEPTOR ---
const onDragStart = (index) => {
    dragIndex.value = index;
};

const onDragOver = (index) => {
    if (dragIndex.value === null) return;
    dragOverIndex.value = index;
    
    if (dragIndex.value === index) return;
    
    // Intercambio reactivo inmediato en la matriz local
    const targetItem = skus.value.splice(dragIndex.value, 1)[0];
    skus.value.splice(index, 0, targetItem);
    dragIndex.value = index;
};

const onDragEnd = () => {
    dragIndex.value = null;
    dragOverIndex.value = null;
};

const saveOrder = () => {
    isSaving.value = true;
    const orderedIds = skus.value.map(sku => sku.id);

    router.put(route('admin.categories.update-sku-order', props.category.id), {
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
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 animate-fade-in" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-neutral-950/40 transition-opacity" @click="emit('close')"></div>

        <div class="bg-card border border-border rounded-md shadow-flat w-full max-w-2xl max-h-[80vh] flex flex-col z-10 overflow-hidden text-foreground">
            
            <div class="px-5 py-4 border-b border-border bg-neutral-50/50 dark:bg-neutral-900/20 flex items-center justify-between select-none">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-rounded text-primary text-xl">view_cozy</span>
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-foreground">Distribución de Góndola Digital</h3>
                        <p class="text-[10px] font-mono text-muted-foreground uppercase tracking-wider mt-0.5">
                            {{ category?.name }} — Reordenamiento secuencial de SKUs
                        </p>
                    </div>
                </div>
                <button @click="emit('close')" class="text-muted-foreground hover:text-foreground p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center border border-border/40">
                    <span class="material-symbols-rounded text-base">close</span>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 no-scrollbar">
                
                <div v-if="isLoading" class="py-16 text-center text-xs text-muted-foreground flex flex-col items-center justify-center gap-2 select-none font-mono">
                    <span class="material-symbols-rounded text-2xl text-primary animate-spin">progress_activity</span>
                    <span class="uppercase tracking-wider">Resolviendo matriz de variantes físicas...</span>
                </div>

                <div v-else-if="skus.length === 0" class="py-16 text-center text-xs text-muted-foreground font-mono uppercase tracking-wider select-none">
                    <span class="material-symbols-rounded text-2xl text-neutral-300 block mb-1">inventory_2</span>
                    No existen SKUs asignados a los productos de este nodo.
                </div>

                <div v-else class="space-y-1.5 select-none">
                    <div 
                        v-for="(sku, index) in skus" 
                        :key="sku.id"
                        draggable="true"
                        @dragstart="onDragStart(index)"
                        @dragover.prevent="onDragOver(index)"
                        @dragend="onDragEnd"
                        :class="[
                            'flex items-center justify-between p-2.5 bg-card border rounded-md transition-all duration-150',
                            dragIndex === index ? 'opacity-30 bg-neutral-100 dark:bg-neutral-800 border-border' : 'border-border',
                            dragOverIndex === index && dragIndex !== index ? 'bg-primary/5 border-primary ring-1 ring-primary' : ''
                        ]"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="cursor-grab active:cursor-grabbing p-1 text-neutral-400 hover:text-foreground rounded transition-colors flex items-center justify-center">
                                <span class="material-symbols-rounded text-lg">drag_indicator</span>
                            </div>
                            
                            <div class="w-9 h-9 bg-neutral-100 dark:bg-neutral-800 border border-border rounded-sm shrink-0 overflow-hidden flex items-center justify-center">
                                <img v-if="sku.image" :src="sku.image" class="w-full h-full object-cover" />
                                <span v-else class="text-[9px] text-muted-foreground font-mono uppercase font-bold">N/A</span>
                            </div>
                            
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-foreground truncate uppercase tracking-tight">{{ sku.name }}</h4>
                                <p class="text-[10px] text-muted-foreground truncate font-mono uppercase mt-0.5">{{ sku.product_name }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 pl-2 font-mono text-[10px]">
                            <span class="bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded-sm border border-border text-muted-foreground uppercase select-all">
                                {{ sku.code }}
                            </span>
                            <span class="font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-sm border border-primary/20">
                                POS: {{ (index + 1) * 10 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-border bg-neutral-50/50 dark:bg-neutral-900/20 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0 select-none">
                <span class="text-[10px] text-muted-foreground font-medium text-center sm:text-left leading-tight max-w-sm">
                    * El orden secuencial superior determina la prioridad de renderizado y posicionamiento dentro de la góndola móvil del cliente.
                </span>
                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto justify-end">
                    <button type="button" @click="emit('close')" class="px-3 py-1.5 border border-border bg-card rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs font-bold uppercase tracking-wider transition-colors">
                        Cerrar
                    </button>
                    <button v-if="skus.length > 0" type="button" @click="saveOrder" :disabled="isSaving" 
                            class="admin-btn-primary px-4 py-1.5 text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-sm">save</span>
                        <span>Persistir Posiciones</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>