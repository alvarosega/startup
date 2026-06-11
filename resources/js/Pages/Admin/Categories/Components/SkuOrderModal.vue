<script setup>
import { watch, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, GripVertical, Save, Layers } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    category: Object
});

const emit = defineEmits(['close']);

const skus = ref([]);
const isLoading = ref(false);
const dragIndex = ref(null);
const isSaving = ref(false);

watch(() => props.show, async (isOpen) => {
    if (isOpen && props.category) {
        isLoading.value = true;
        skus.value = [];
        try {
            // Consumo de Endpoint Atómico (Opción A)
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

// --- MOTOR NATIVO HTML5 DRAG & DROP ---
const onDragStart = (index) => {
    dragIndex.value = index;
};

const onDragOver = (index) => {
    if (dragIndex.value === null || dragIndex.value === index) return;
    
    // Intercambio reactivo en memoria local
    const targetItem = skus.value.splice(dragIndex.value, 1)[0];
    skus.value.splice(index, 0, targetItem);
    dragIndex.value = index;
};

const onDragEnd = () => {
    dragIndex.value = null;
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
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-background/80 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>

        <div class="bg-card border border-border rounded-xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col z-10 overflow-hidden">
            <div class="px-6 py-4 bg-muted/30 border-b border-border flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Layers :size="18" class="text-primary" />
                    <div>
                        <h3 class="text-md font-semibold text-foreground">Distribución de Góndola Digital</h3>
                        <p class="text-xs text-muted-foreground">{{ category?.name }} — Reordenamiento secuencial de SKUs</p>
                    </div>
                </div>
                <button @click="emit('close')" class="text-muted-foreground hover:text-foreground p-1 rounded-lg hover:bg-muted transition-colors">
                    <X :size="18" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <div v-if="isLoading" class="py-12 text-center text-sm text-muted-foreground flex flex-col items-center justify-center gap-2">
                    <div class="w-6 h-6 border-2 border-primary/30 border-t-primary rounded-full animate-spin"></div>
                    <span>Resolviendo matriz de variantes físicas...</span>
                </div>

                <div v-else-if="skus.length === 0" class="py-12 text-center text-sm text-muted-foreground">
                    No existen SKUs asignados a los productos de esta categoría.
                </div>

                <div v-else class="space-y-2 select-none">
                    <div 
                        v-for="(sku, index) in skus" 
                        :key="sku.id"
                        draggable="true"
                        @dragstart="onDragStart(index)"
                        @dragover.prevent="onDragOver(index)"
                        @dragend="onDragEnd"
                        :class="[
                            'flex items-center justify-between p-3 bg-background border rounded-lg transition-all duration-150',
                            dragIndex === index ? 'opacity-40 bg-muted border-primary/40' : 'border-border hover:border-border/100 hover:shadow-sm'
                        ]"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="cursor-grab active:cursor-grabbing p-1 text-muted-foreground/40 hover:text-muted-foreground rounded">
                                <GripVertical :size="18" />
                            </div>
                            <img v-if="sku.image" :src="sku.image" class="w-10 h-10 object-cover rounded-md border border-border bg-muted shrink-0" />
                            <div v-else class="w-10 h-10 bg-muted border border-border rounded-md shrink-0 flex items-center justify-center text-[10px] text-muted-foreground font-mono uppercase">N/A</div>
                            
                            <div class="min-w-0">
                                <h4 class="text-sm font-medium text-foreground truncate">{{ sku.name }}</h4>
                                <p class="text-xs text-muted-foreground truncate font-sans">{{ sku.product_name }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 shrink-0 pl-2">
                            <span class="font-mono text-xs bg-muted px-2 py-0.5 rounded border border-border text-muted-foreground">
                                {{ sku.code }}
                            </span>
                            <span class="font-mono text-xs font-semibold text-primary bg-primary/5 px-2 py-0.5 rounded border border-primary/10">
                                Pos: {{ (index + 1) * 10 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-muted/30 border-t border-border flex items-center justify-between">
                <span class="text-xs text-muted-foreground font-sans">
                    * El orden superior determina la prioridad de renderizado prioritario en la aplicación del cliente.
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="emit('close')" class="px-4 py-2 bg-background border border-border text-sm font-medium rounded-lg hover:bg-muted text-foreground transition-colors">
                        Cerrar
                    </button>
                    <button v-if="skus.length > 0" type="button" @click="saveOrder" :disabled="isSaving" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 shadow-sm transition-colors disabled:opacity-50">
                        <Save :size="16" />
                        Persistir Posiciones
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>