<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, Tag, Edit, Trash2 } from 'lucide-vue-next';
import SkuSubTable from './SkuSubTable.vue';

defineProps({ product: Object, can_manage: Boolean });
const emit = defineEmits(['delete-product', 'manage-prices', 'delete-sku']);

const isExpanded = ref(false);
</script>

<template>
    <tr class="hover:bg-muted/10 transition-colors border-b border-border/60">
        <td class="p-4 text-center">
            <button @click="isExpanded = !isExpanded" class="p-1 text-muted-foreground hover:text-foreground hover:bg-muted rounded transition-colors">
                <ChevronDown v-if="isExpanded" :size="16" />
                <ChevronRight v-else :size="16" />
            </button>
        </td>
        <td class="p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-muted rounded border border-border overflow-hidden shrink-0 flex items-center justify-center">
                    <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-cover" />
                    <Tag v-else :size="16" class="text-muted-foreground/40" />
                </div>
                <div>
                    <span @click="isExpanded = !isExpanded" class="font-semibold text-foreground block cursor-pointer hover:text-primary select-none">{{ product.name }}</span>
                    <span v-if="product.is_alcoholic" class="text-[9px] bg-red-500/10 text-red-500 px-1.5 py-0.5 rounded font-bold uppercase border border-red-500/20 inline-block mt-0.5">Control +18</span>
                </div>
            </div>
        </td>
        <td class="p-4 text-muted-foreground text-xs uppercase font-medium">{{ product.brand_name }}</td>
        <td class="p-4 text-muted-foreground text-xs">{{ product.category_name }}</td>
        <td class="p-4 text-center font-mono font-bold">
            <span @click="isExpanded = !isExpanded" :class="product.skus_count === 0 ? 'text-amber-500 bg-amber-500/10 border-amber-500/20' : 'bg-muted text-muted-foreground border-border cursor-pointer'" class="text-xs select-none px-2 py-0.5 rounded border">
                {{ product.skus_count }} SKUs
            </span>
        </td>
        <td class="p-4 text-center">
            <span :class="product.is_active ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-destructive/10 text-destructive border-destructive/20'" class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full border">
                {{ product.is_active ? 'Activo' : 'Oculto' }}
            </span>
        </td>
        <td class="p-4 text-right space-x-1 whitespace-nowrap">
            <Link :href="route('admin.products.edit', product.id)" class="inline-flex items-center p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-md" title="Workspace">
                <Edit :size="15" />
            </Link>
            <button v-if="can_manage" @click="emit('delete-product', product.id, product.name)" class="inline-flex items-center p-1.5 text-destructive hover:bg-destructive/10 rounded-md">
                <Trash2 :size="15" />
            </button>
        </td>
    </tr>
    <tr v-if="isExpanded" class="bg-muted/20">
        <td colspan="7" class="p-4 pl-12 bg-muted/30">
            <SkuSubTable 
                :skus="product.skus" 
                @manage-prices="emit('manage-prices', $event)" 
                @delete-sku="emit('delete-sku', $event)" 
            />
        </td>
    </tr>
</template>