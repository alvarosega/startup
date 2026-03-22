<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import draggable from 'vuedraggable';
import { ChevronLeft, GripVertical, Save, Package } from 'lucide-vue-next';

const props = defineProps({
    category: Object,
    skus: Array
});

const list = ref([...props.skus]);
const isDirty = ref(false); // Detecta si hubo cambios

const onDragEnd = () => {
    isDirty.value = true;
};

const saveOrder = () => {
    router.patch(route('admin.categories.sku-order.update', props.category.data.id), {
        ids: list.value.map(item => item.id)
    }, {
        preserveScroll: true,
        onSuccess: () => isDirty.value = false
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Ordenar SKUs - ${category.data.name}`" />

        <div class="max-w-5xl mx-auto px-4 pb-24">
            <div class="flex items-center justify-between mb-8 border-b border-border pb-6">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.categories.index')" class="p-2 hover:bg-muted rounded-lg transition-colors">
                        <ChevronLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight">Góndola de Variantes</h1>
                        <p class="text-xs text-muted-foreground uppercase font-mono">Categoría: {{ category.data.name }}</p>
                    </div>
                </div>

                <button 
                    @click="saveOrder"
                    :disabled="!isDirty"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-bold transition-all shadow-lg active:scale-95"
                    :class="isDirty ? 'bg-primary text-primary-foreground hover:shadow-primary/20' : 'bg-muted text-muted-foreground cursor-not-allowed'"
                >
                    <Save :size="16" />
                    Guardar Orden
                </button>
            </div>

            <div class="bg-card border border-border rounded-xl overflow-hidden shadow-sm">
                <div class="bg-muted/50 px-4 py-2 border-b border-border grid grid-cols-12 gap-4">
                    <div class="col-span-1"></div>
                    <div class="col-span-1 text-[10px] font-black uppercase text-muted-foreground">Pos</div>
                    <div class="col-span-6 text-[10px] font-black uppercase text-muted-foreground">Variante / Producto</div>
                    <div class="col-span-4 text-[10px] font-black uppercase text-muted-foreground">Código</div>
                </div>

                <draggable 
                    v-model="list" 
                    item-key="id" 
                    @end="onDragEnd"
                    handle=".drag-handle"
                    ghost-class="bg-primary/5"
                    class="divide-y divide-border"
                >
                    <template #item="{ element, index }">
                        <div class="grid grid-cols-12 gap-4 items-center px-4 py-3 hover:bg-muted/30 transition-colors group">
                            <div class="col-span-1 flex justify-center">
                                <GripVertical class="drag-handle cursor-grab active:cursor-grabbing text-muted-foreground/40 group-hover:text-primary transition-colors" :size="20" />
                            </div>

                            <div class="col-span-1">
                                <span class="text-xs font-mono font-bold text-muted-foreground">#{{ (index + 1) * 10 }}</span>
                            </div>

                            <div class="col-span-6 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg border border-border bg-white flex items-center justify-center p-1 shrink-0 overflow-hidden">
                                    <img v-if="element.image" :src="element.image" class="w-full h-full object-contain" />
                                    <Package v-else :size="16" class="text-muted-foreground/30" />
                                </div>
                                <div class="truncate">
                                    <p class="text-sm font-bold text-foreground truncate">{{ element.name }}</p>
                                    <p class="text-[10px] text-primary font-bold uppercase truncate">{{ element.product_name }}</p>
                                </div>
                            </div>

                            <div class="col-span-4">
                                <span class="px-2 py-1 bg-muted rounded text-[10px] font-mono text-muted-foreground border border-border">
                                    {{ element.code || 'SIN CÓDIGO' }}
                                </span>
                            </div>
                        </div>
                    </template>
                </draggable>
            </div>

            <div v-if="list.length === 0" class="py-20 text-center">
                <Package :size="48" class="mx-auto text-muted-foreground/20 mb-4" />
                <p class="text-muted-foreground italic">No hay variantes en esta categoría.</p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.drag-handle {
    touch-action: none;
}
</style>