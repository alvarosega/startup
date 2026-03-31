<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import draggable from 'vuedraggable';
import { ChevronLeft, GripVertical, Save, Box, Terminal } from 'lucide-vue-next';

const props = defineProps({
    products: Object // ProductOrderResource collection
});

const list = ref([...props.products.data]);
const isDirty = ref(false);

const onDragEnd = () => {
    isDirty.value = true;
};

const saveOrder = () => {
    router.patch(route('admin.products.reorder.update'), {
        ids: list.value.map(item => item.id)
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isDirty.value = false;
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Góndola Global | Reordenamiento" />

        <div class="max-w-5xl mx-auto px-4 pb-24">
            <div class="flex items-center justify-between mb-8 border-b border-primary/30 pb-6">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.products.index')" class="p-2 hover:bg-primary/10 border border-transparent hover:border-primary/20 transition-colors">
                        <ChevronLeft :size="20" class="text-primary" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-black text-primary uppercase tracking-tighter">GÓNDOLA_PRINCIPAL</h1>
                        <p class="text-[10px] text-muted-foreground uppercase font-mono flex items-center gap-2">
                            <Terminal :size="10" /> PROTOCOLO DE PRIORIZACIÓN GLOBAL (TOP_5_CUSTOMER)
                        </p>
                    </div>
                </div>

                <button 
                    @click="saveOrder"
                    :disabled="!isDirty"
                    class="h-11 px-8 font-black text-xs uppercase tracking-widest transition-all flex items-center gap-2 active:scale-95"
                    :class="isDirty ? 'bg-primary text-background shadow-neon-primary' : 'bg-primary/10 text-primary/40 cursor-not-allowed'"
                >
                    <Save :size="16" />
                    SINCRONIZAR_ORDEN
                </button>
            </div>

            <div class="border border-primary/20 bg-background/40 backdrop-blur-md overflow-hidden shadow-2xl">
                <div class="bg-primary/5 px-4 py-3 border-b border-primary/20 grid grid-cols-12 gap-4">
                    <div class="col-span-1"></div>
                    <div class="col-span-1 text-[9px] font-mono font-black uppercase text-primary/60 tracking-widest">POS</div>
                    <div class="col-span-10 text-[9px] font-mono font-black uppercase text-primary/60 tracking-widest">ACTIVO_MAESTRO</div>
                </div>

                <draggable 
                    v-model="list" 
                    item-key="id" 
                    @end="onDragEnd"
                    handle=".drag-handle"
                    ghost-class="bg-primary/10"
                    class="divide-y divide-primary/10"
                >
                    <template #item="{ element, index }">
                        <div class="grid grid-cols-12 gap-4 items-center px-4 py-4 hover:bg-primary/5 transition-colors group">
                            <div class="col-span-1 flex justify-center border-r border-primary/5">
                                <GripVertical class="drag-handle cursor-grab active:cursor-grabbing text-primary/30 group-hover:text-primary transition-colors" :size="20" />
                            </div>

                            <div class="col-span-1">
                                <span class="text-xs font-mono font-black text-primary/50 group-hover:text-primary">
                                    [{{ String((index + 1) * 10).padStart(3, '0') }}]
                                </span>
                            </div>

                            <div class="col-span-10 flex items-center gap-4">
                                <div class="w-12 h-12 border border-primary/20 bg-black flex items-center justify-center p-1 shrink-0 overflow-hidden">
                                    <img v-if="element.image" :src="element.image" class="w-full h-full object-contain" />
                                    <Box v-else :size="16" class="text-primary/20" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-foreground uppercase truncate group-hover:text-primary transition-colors tracking-tight">
                                        {{ element.name }}
                                    </p>
                                    <p class="text-[8px] font-mono text-muted-foreground uppercase mt-0.5">
                                        UUID_NODE: {{ element.id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </draggable>
            </div>

            <div v-if="list.length === 0" class="py-20 text-center border border-dashed border-primary/20 bg-primary/5 mt-4">
                <Box :size="48" class="mx-auto text-primary/10 mb-4" />
                <p class="text-[10px] font-mono text-primary/40 uppercase tracking-[0.3em]">Cero activos detectados en el sector.</p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }
.drag-handle { touch-action: none; }
</style>