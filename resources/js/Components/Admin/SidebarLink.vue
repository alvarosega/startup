<script setup>
    import { computed } from 'vue';
    import { Link } from '@inertiajs/vue3';
    
    const props = defineProps({
        href: { type: String, required: true },
        active: { type: Boolean, default: false },
        collapsed: { type: Boolean, default: false },
    });
    
    const classes = computed(() => {
        // Base: Transición elástica ("Porsche curve") y espaciado consistente
        const base = 'group relative flex items-center gap-3 py-2.5 my-1 text-sm font-medium rounded-box transition-all duration-instant ease-elastic overflow-hidden';
        
        // Layout: Ajuste dinámico según colapso
        const layout = props.collapsed ? 'justify-center px-0' : 'px-3.5';
        
        // Estado Visual: 
        // - Activo: Fondo Primary Sólido + Sombra Tintada + Texto Blanco (Lujo)
        // - Inactivo: Texto gris medio + Hover suave en superficie
        const colors = props.active
            ? 'bg-primary text-white shadow-md shadow-primary/25 translate-x-1' // Active
            : 'text-content-light hover:bg-surface hover:text-primary active:scale-95'; // Inactive
    
        return `${base} ${layout} ${colors}`;
    });
    </script>
    
    <template>
        <Link :href="href" :class="classes" :title="collapsed ? $slots.default()[0]?.children : ''">
            
            <span class="shrink-0 transition-transform duration-300 ease-elastic" 
                  :class="[
                      collapsed ? 'scale-110' : 'scale-100',
                      active && !collapsed ? 'animate-pulse-once' : '' 
                  ]">
                <slot name="icon" />
            </span>
            
            <span v-if="!collapsed" 
                  class="whitespace-nowrap overflow-hidden transition-opacity duration-200"
                  :class="active ? 'font-semibold' : 'font-medium'">
                <slot />
            </span>
    
            <div v-if="collapsed && active" 
                 class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-3 bg-white/30 rounded-r-full">
            </div>
        </Link>
    </template>