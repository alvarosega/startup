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
    const base = 'group relative flex items-center justify-center w-full h-12 transition-colors duration-75 ease-linear select-none';
    const state = props.active
        ? 'bg-neutral-200 text-foreground dark:bg-neutral-800'
        : 'text-muted-foreground hover:bg-neutral-100 hover:text-foreground dark:hover:bg-neutral-900';

    return `${base} ${state}`;
});
</script>

<template>
    <Link :href="href" :class="classes">
        <div 
            v-if="active" 
            class="absolute left-0 top-0 bottom-0 w-[3px] bg-primary"
        ></div>
        
        <span 
            class="material-symbols-rounded text-[20px] shrink-0 pointer-events-none"
            :style="{ fontVariationSettings: active ? `'FILL' 1` : `'FILL' 0` }"
        >
            {{ icon }}
        </span>
        
        <span class="fixed left-[76px] hidden group-hover:block px-2.5 py-1 bg-card border border-border rounded-md text-xs font-medium text-foreground shadow-flat whitespace-nowrap z-50 pointer-events-none">
            {{ title }}
        </span>
    </Link>
</template>