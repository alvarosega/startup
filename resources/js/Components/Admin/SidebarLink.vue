<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    active: { type: Boolean, default: false },
    title: { type: String, default: '' },
    icon: { type: String, required: true }
});

const classes = computed(() => {
    const base = 'group relative flex items-center justify-center w-10 h-10 mx-auto transition-all duration-150 ease-out select-none shrink-0 mb-1 rounded-xl';
    const state = props.active
        ? 'bg-primary/12 text-primary border border-primary/25 shadow-sm font-bold scale-[1.04]'
        : 'text-muted-foreground/80 hover:text-foreground hover:bg-neutral-500/[0.08] dark:hover:bg-white/[0.08]';

    return `${base} ${state}`;
});
</script>

<template>
    <Link :href="href" :class="classes">
        <!-- Indicador de luz sutil en el lateral Apple Dock -->
        <div 
            v-if="active" 
            class="absolute -left-1.5 top-2.5 bottom-2.5 w-1 bg-primary rounded-full shadow-sm"
        ></div>
        
        <span 
            class="material-symbols-rounded text-[21px] shrink-0 pointer-events-none transition-transform duration-150 group-active:scale-95"
            :style="{ fontVariationSettings: active ? `'FILL' 1` : `'FILL' 0` }"
        >
            {{ icon }}
        </span>
    </Link>
</template>
