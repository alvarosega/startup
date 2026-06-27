<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    products: {
        type: Array,
        required: true
    }
});

const localProducts = ref([...props.products]);
const form = useForm({
    ids: []
});

/**
 * Modifica secuencialmente el orden local del arreglo de productos.
 */
const moveProduct = (index, direction) => {
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= localProducts.value.length) return;

    const temp = localProducts.value[index];
    localProducts.value[index] = localProducts.value[targetIndex];
    localProducts.value[targetIndex] = temp;
};

/**
 * Compila y despacha el lote ordenado de identificadores maestros.
 */
const saveOrder = () => {
    form.ids = localProducts.value.map(p => p.id);
    form.patch(route('admin.catalog.products.reorder.update'), {
        preserveState: false
    });
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Priorización del Catálogo Global
        </template>

        <div class="max-w-2xl bg-card border border-border rounded-md shadow-flat p-5 space-y-4">
            <div class="border-b border-border pb-2">
                <p class="text-xs text-muted-foreground">
                    Modifique la disposición jerárquica de los productos maestros. El orden configurado impactará directamente la secuencia de visualización en la góndola del cliente final.
                </p>
            </div>

            <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-1 no-scrollbar">
                <div 
                    v-for="(item, index) in localProducts" 
                    :key="item.id"
                    class="flex items-center justify-between p-3 bg-neutral-50 border border-border rounded-md text-xs font-medium"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-card border border-border rounded overflow-hidden flex items-center justify-center shrink-0">
                            <img v-if="item.image_path" :src="`/storage/${item.image_path}`" class="w-full h-full object-contain" alt="" />
                            <span v-else class="material-symbols-rounded text-muted-foreground/30 text-base">image</span>
                        </div>
                        <span class="text-foreground font-bold tracking-tight uppercase">{{ item.name }}</span>
                    </div>
                    
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" @click="moveProduct(index, -1)" :disabled="index === 0" class="p-1 bg-card border border-border rounded hover:bg-neutral-100 disabled:opacity-40">
                            <span class="material-symbols-rounded text-base block">arrow_upward</span>
                        </button>
                        <button type="button" @click="moveProduct(index, 1)" :disabled="index === localProducts.length - 1" class="p-1 bg-card border border-border rounded hover:bg-neutral-100 disabled:opacity-40">
                            <span class="material-symbols-rounded text-base block">arrow_downward</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                <Link :href="route('admin.catalog.products.index')" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                    Cancelar
                </Link>
                <button type="button" @click="saveOrder" :disabled="form.processing" class="admin-btn-primary inline-flex items-center gap-1.5">
                    <span>{{ form.processing ? 'Secuenciando Catálogo...' : 'Confirmar Cambios' }}</span>
                </button>
            </div>
        </div>
    </AdminLayout>
</template>