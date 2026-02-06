<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    active: { type: Boolean, default: false },
    collapsed: { type: Boolean, default: false },
    title: { type: String, default: '' } // Nueva prop para accesibilidad
});

const classes = computed(() => {
    // Clases base: Flexbox, bordes redondeados suaves, transición de colores
    const base = 'group relative flex items-center my-1 text-sm font-medium rounded-xl transition-all duration-300 ease-[cubic-bezier(0.25,0.8,0.25,1)] overflow-hidden border border-transparent';
    
    // Layout: Si está colapsado es un cuadrado centrado, si no, tiene padding lateral
    const layout = props.collapsed 
        ? 'justify-center w-10 h-10 mx-auto px-0' 
        : 'gap-3 px-3.5 py-2.5 mx-2';
    
    // Estados Visuales (Semánticos)
    const state = props.active
        // Activo: Color primario, texto contraste, sombra suave
        ? 'bg-primary text-primary-foreground shadow-md shadow-primary/25 font-bold' 
        // Inactivo: Texto mutado, hover sutil
        : 'text-muted-foreground hover:bg-muted hover:text-foreground active:scale-95 hover:border-border/50';

    return `${base} ${layout} ${state}`;
});
</script>

<template>
    <Link :href="href" :class="classes" :title="collapsed ? title : ''">
        
        <span class="shrink-0 transition-transform duration-300" 
              :class="[
                  collapsed ? 'scale-110' : 'scale-100',
                  active && !collapsed ? 'translate-x-0.5' : '' 
              ]">
            <slot name="icon" />
        </span>
        
        <span v-if="!collapsed" 
              class="whitespace-nowrap overflow-hidden transition-all duration-300 origin-left animate-in fade-in slide-in-from-left-2">
            <slot />
        </span>

        <div v-if="collapsed && active" 
             class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary-foreground/30 rounded-r-full">
        </div>
    </Link>
</template>