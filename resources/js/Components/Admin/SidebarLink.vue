<script setup>
    import { computed } from 'vue';
    import { Link } from '@inertiajs/vue3';
    
    const props = defineProps({
        href: { type: String, required: true },
        active: { type: Boolean, default: false },
        collapsed: { type: Boolean, default: false }, // Nuevo control
    });
    
    const classes = computed(() => {
        // Base suave
        const base = 'flex items-center gap-3 py-2 text-sm font-medium rounded-global transition-all duration-200 group relative';
        
        // Si está colapsado: Centramos (justify-center) y reducimos padding horizontal (px-2)
        // Si está abierto: Alineamos izq (default) y padding normal (px-3)
        const layout = props.collapsed ? 'justify-center px-2' : 'px-3';
        
        const colors = props.active
            ? 'bg-skin-primary/10 text-skin-primary'
            : 'text-skin-muted hover:bg-skin-fill-hover hover:text-skin-base';
    
        return `${base} ${layout} ${colors}`;
    });
    </script>
    
    <template>
        <Link :href="href" :class="classes" :title="collapsed ? $slots.default()[0]?.children : ''">
            
            <span v-if="$slots.icon" class="shrink-0 transition-transform duration-200" :class="collapsed ? 'scale-110' : ''">
                <slot name="icon" />
            </span>
            
            <span v-if="!collapsed" class="whitespace-nowrap overflow-hidden fade-in">
                <slot />
            </span>
    
            <div v-if="collapsed && active" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-skin-primary rounded-r-full"></div>
        </Link>
    </template>