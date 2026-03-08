<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    meta: Object, 
    links: Object // <-- CORRECCIÓN: Laravel envía un Objeto aquí, no un Array.
});

const safeLinks = computed(() => {
    // La botonera real (1, 2, 3, Siguiente, Anterior) siempre viene dentro de meta.links
    const arr = props.meta?.links || [];
    // Filtramos los que tienen URL null (ej: el botón "Anterior" cuando estás en la pág 1)
    return arr.filter(link => link.url !== null);
});
</script>

<template>
    <div v-if="safeLinks.length > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
        <div class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">
            Mostrando <span class="text-foreground">{{ meta?.from || 0 }}</span> - <span class="text-foreground">{{ meta?.to || 0 }}</span> 
            de <span class="text-foreground">{{ meta?.total || 0 }}</span> registros
        </div>

        <nav class="flex items-center gap-1 bg-card border border-border/50 rounded-xl p-1 shadow-sm">
            <template v-for="(link, index) in safeLinks" :key="index">
                <Link 
                    :href="link.url" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                    :class="[
                        link.active 
                            ? 'bg-primary text-primary-foreground shadow-sm shadow-primary/20' 
                            : 'text-muted-foreground hover:bg-muted/80 hover:text-foreground'
                    ]"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>