<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    meta: {
        type: Object,
        required: true
    }
});

// LEY: No filtramos. Transformamos para mantener la estructura.
const paginationLinks = computed(() => props.meta?.links || []);
</script>

<template>
    <div v-if="paginationLinks.length > 3" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8 px-2">
        <div class="text-[10px] font-black text-muted-foreground uppercase tracking-widest bg-muted/30 px-3 py-1.5 rounded-lg border border-border/50">
            Mostrando 
            <span class="text-foreground">{{ meta.from ?? 0 }}</span> 
            <span class="mx-1 text-border">/</span> 
            <span class="text-foreground">{{ meta.to ?? 0 }}</span>
            <span class="mx-2 text-muted-foreground/30">—</span>
            Total: <span class="text-primary">{{ meta.total ?? 0 }}</span>
        </div>

        <nav class="flex items-center gap-1 bg-card border border-border/50 rounded-xl p-1 shadow-sm" aria-label="Paginación">
            <template v-for="(link, index) in paginationLinks" :key="index">
                <Link 
                    v-if="link.url"
                    :href="link.url" 
                    class="px-3 py-1.5 rounded-lg text-[11px] font-black transition-all duration-200 select-none"
                    :class="[
                        link.active 
                            ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20 scale-105' 
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                    ]"
                    v-html="link.label"
                    preserve-scroll
                />
                
                <span 
                    v-else
                    class="px-3 py-1.5 rounded-lg text-[11px] font-black text-muted-foreground/30 cursor-not-allowed select-none bg-muted/10"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>