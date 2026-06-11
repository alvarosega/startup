<script setup>
import { Package, Coins, Trash2 } from 'lucide-vue-next';

defineProps({ skus: Array });
const emit = defineEmits(['manage-prices', 'delete-sku']);
</script>

<template>
    <div class="border border-border/80 rounded-xl bg-card overflow-hidden shadow-inner">
        <div class="px-4 py-2.5 bg-muted/40 border-b border-border flex items-center gap-1.5">
            <Package :size="14" class="text-muted-foreground" />
            <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Desglose de Presentaciones</span>
        </div>
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-muted/10 font-semibold text-muted-foreground border-b border-border/60">
                <tr>
                    <th class="p-3">Descripción</th>
                    <th class="p-3 font-mono w-40">EAN</th>
                    <th class="p-3 text-right w-32">Precio Ref.</th>
                    <th class="p-3 text-right w-24">F. Conv</th>
                    <th class="p-3 text-right w-24">Peso Kg</th>
                    <th class="p-3 text-center w-24">Estado</th>
                    <th class="p-3 text-right w-36">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/60">
                <tr v-for="sku in skus" :key="sku.id" class="hover:bg-muted/30 transition-colors">
                    <td class="p-3 font-medium text-foreground">{{ sku.name }}</td>
                    <td class="p-3 font-mono font-bold text-primary">{{ sku.code }}</td>
                    <td class="p-3 text-right font-mono">{{ Number(sku.base_price).toFixed(2) }}</td>
                    <td class="p-3 text-right font-mono">{{ Number(sku.conversion_factor).toFixed(3) }}</td>
                    <td class="p-3 text-right font-mono">{{ Number(sku.weight).toFixed(3) }}</td>
                    <td class="p-3 text-center">
                        <span :class="sku.is_active ? 'bg-emerald-500/10 text-emerald-600' : 'bg-destructive/10 text-destructive'" class="px-1.5 py-0.5 text-[10px] font-bold rounded">
                            {{ sku.is_active ? 'Activo' : 'Oculto' }}
                        </span>
                    </td>
                    <td class="p-3 text-right whitespace-nowrap space-x-1">
                        <button type="button" @click="emit('manage-prices', sku)" class="inline-flex items-center gap-1 px-2 py-1 bg-primary/10 text-primary hover:bg-primary/20 rounded font-medium">
                            <Coins :size="12" /> Precios
                        </button>
                        <button type="button" @click="emit('delete-sku', sku.id)" class="p-1 text-destructive hover:bg-destructive/10 rounded">
                            <Trash2 :size="13" />
                        </button>
                    </td>
                </tr>
                <tr v-if="!skus || skus.length === 0">
                    <td colspan="7" class="p-4 text-center text-muted-foreground italic">El maestro no posee presentaciones.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>