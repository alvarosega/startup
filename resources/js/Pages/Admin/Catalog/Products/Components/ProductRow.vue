<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import SkuSubTable from './SkuSubTable.vue';

defineProps({ 
    product: Object, 
    can_manage: Boolean 
});

const emit = defineEmits(['delete-product', 'manage-prices', 'delete-sku']);
const isExpanded = ref(false);
</script>

<template>
    <tr class="border-b border-neutral-200 dark:border-neutral-800 hover:bg-neutral-50/30 dark:hover:bg-neutral-800/20 transition-colors" :class="{'bg-neutral-50/50 dark:bg-neutral-900/10': isExpanded}">
        <td class="p-3 text-center select-none">
            <button @click="isExpanded = !isExpanded" 
                    class="p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors flex items-center justify-center mx-auto border border-neutral-200 dark:border-neutral-700/50">
                <span class="material-symbols-rounded text-base">
                    {{ isExpanded ? 'keyboard_arrow_down' : 'keyboard_arrow_right' }}
                </span>
            </button>
        </td>
        
        <td class="p-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-md border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 flex items-center justify-center overflow-hidden shrink-0 select-none">
                    <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-cover" />
                    <span v-else class="material-symbols-rounded text-neutral-400 dark:text-neutral-500/40 text-lg">image</span>
                </div>
                <div class="min-w-0">
                    <span @click="isExpanded = !isExpanded" 
                          class="font-bold text-neutral-900 dark:text-neutral-50 block cursor-pointer hover:text-neutral-600 dark:hover:text-neutral-300 select-none uppercase tracking-tight truncate text-xs">
                        {{ product.name }}
                    </span>
                    <span v-if="product.is_alcoholic" class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-mono font-black tracking-widest bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800 mt-0.5">
                        CONTROL_+18
                    </span>
                </div>
            </div>
        </td>

        <td class="p-3 text-center select-none">
            <span :class="product.skus_count === 0 
                ? 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800' 
                : 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800'">
                {{ product.skus_count === 0 ? 'MIS_VARIANTES' : 'VALIDADO' }}
            </span>
        </td>

        <td class="p-3 text-neutral-500 dark:text-neutral-400 font-mono text-xs uppercase tracking-tight select-all">
            {{ product.brand_name }}
        </td>
        
        <td class="p-3 text-neutral-500 dark:text-neutral-400 text-xs uppercase select-all">
            {{ product.category_name }}
        </td>
        
        <td class="p-3 text-center font-mono text-xs select-none text-neutral-900 dark:text-neutral-50">
            <span @click="isExpanded = !isExpanded" 
                  :class="product.skus_count === 0 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-neutral-900 dark:text-neutral-50 font-semibold'" 
                  class="cursor-pointer hover:underline">
                {{ String(product.skus_count).padStart(2, '0') }} SKU
            </span>
        </td>
        
        <td class="p-3 text-center select-none">
            <span :class="product.is_active 
                ? 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' 
                : 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800'">
                {{ product.is_active ? 'ACTIVO' : 'OCULTO' }}
            </span>
        </td>
        
        <td class="p-3 text-center select-none">
            <div class="flex items-center justify-center gap-1">
                <Link :href="route('admin.catalog.products.edit', product.id)" 
                      class="p-1.5 text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-50 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center" 
                      title="Workspace">
                    <span class="material-symbols-rounded text-base">edit_note</span>
                </Link>
                <button v-if="can_manage" @click="emit('delete-product', product.id, product.name)" 
                        class="p-1.5 text-neutral-300 hover:text-rose-600 dark:hover:text-rose-400 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors flex items-center justify-center">
                    <span class="material-symbols-rounded text-base">delete</span>
                </button>
            </div>
        </td>
    </tr>

    <tr v-if="isExpanded" class="bg-neutral-50/20 dark:bg-neutral-900/20">
        <td colspan="8" class="p-3 pl-12 border-l-2 border-neutral-900 dark:border-neutral-50">
            <SkuSubTable 
                :skus="product.skus" 
                @manage-prices="emit('manage-prices', $event)" 
                @delete-sku="emit('delete-sku', $event)" 
            />
        </td>
    </tr>
</template>