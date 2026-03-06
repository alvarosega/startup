<script setup>
import { ref, onMounted, computed } from 'vue';
import Sidebar from '@/Components/Admin/Sidebar.vue';
import Loader from '@/Components/Admin/Loader/Loader.vue';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const isDevelopment = ref(false);
onMounted(() => {
    isDevelopment.value = ['localhost', '127.0.0.1', 'test', 'dev'].some(host => 
        window.location.hostname.includes(host)
    );
});
</script>

<template>
    <div class="min-h-screen bg-background text-foreground font-sans antialiased selection:bg-primary/20 selection:text-primary relative overflow-hidden">
        
        <!-- Loader component (se mejora después) -->
        <Loader />
        
        <!-- Sidebar -->
        <Sidebar />

        <!-- Main content -->
        <main class="relative min-h-screen transition-all duration-300 md:ml-[72px] pb-40 md:pb-12 flex flex-col z-10">
            
            <!-- Header -->
            <header class="w-full px-4 py-4 md:px-8 flex justify-end items-center sticky top-0 z-40 bg-background/90 backdrop-blur-sm border-b border-border transition-all duration-300 hover:border-primary/50">
                <div class="flex items-center gap-4">
                    
                    <!-- User info -->
                    <div v-if="user" class="hidden md:flex items-center border border-border bg-card p-1 pr-4 cursor-default transition-all duration-300 hover:border-primary hover:shadow-neon relative group">
                        <!-- Esquinas decorativas -->
                        <div class="absolute top-[-2px] left-[-2px] w-1 h-1 bg-primary opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute bottom-[-2px] right-[-2px] w-1 h-1 bg-primary opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        
                        <!-- Avatar -->
                        <div class="w-10 h-10 bg-primary/10 text-primary flex items-center justify-center font-mono font-black text-xl border border-primary/50 relative overflow-hidden group/avatar">
                            <span class="relative z-10 transition-transform duration-300 group-hover/avatar:scale-110">{{ user?.first_name?.[0]?.toUpperCase() || 'U' }}</span>
                            <!-- Efecto scan en avatar -->
                            <div class="absolute inset-0 bg-primary/20 translate-y-full group-hover/avatar:translate-y-0 transition-transform duration-500"></div>
                        </div>
                        
                        <!-- User details -->
                        <div class="flex flex-col border-l border-border pl-3 ml-1">
                            <span class="font-mono text-sm font-bold leading-none text-foreground uppercase tracking-tight transition-colors duration-300 group-hover:text-primary">
                                {{ user?.first_name }}
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary mt-1 relative">
                                [{{ user?.roles?.[0]?.replace('_', ' ') || 'STAFF_UNIT' }}]
                                <span class="absolute bottom-0 left-0 w-0 h-[1px] bg-primary group-hover:w-full transition-all duration-300"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Theme toggler -->
                    <div class="border border-border bg-card transition-all duration-300 hover:border-primary hover:shadow-neon relative group">
                        <ThemeToggler class="p-2 transition-all duration-300 group-hover:text-primary group-hover:scale-110" />
                        
                        <!-- Esquinas decorativas -->
                        <div class="absolute top-[-2px] left-[-2px] w-1 h-1 bg-primary opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute bottom-[-2px] right-[-2px] w-1 h-1 bg-primary opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                </div>
            </header>

            <!-- Content area -->
            <div class="flex-1 p-4 md:p-6 lg:p-8 max-w-[1920px] w-full mx-auto relative z-0 mt-2 md:mt-0">
                <div class="hud-panel w-full h-full p-6 md:p-8 relative bg-card/60 backdrop-blur-sm transition-all duration-500 hover:shadow-neon-strong group/panel">
                    
                    <!-- Panel decorations -->
                    <div class="absolute top-0 left-0 w-20 h-2 border-t-2 border-l-2 border-primary transition-all duration-300 group-hover/panel:w-24 group-hover/panel:h-3"></div>
                    <div class="absolute top-1 left-3 text-[10px] font-mono text-primary uppercase font-bold tracking-widest animate-pulse z-10 transition-opacity duration-300 group-hover/panel:opacity-80">UNIT_01_SYS</div>
                    
                    <!-- Header slot -->
                    <div v-if="$slots.header" class="mb-6 md:mb-8 pr-12 md:pr-0 font-display font-black uppercase text-4xl tracking-widest text-foreground relative z-10 group/header"> 
                        <span class="absolute left-0 bottom-0 w-12 h-[2px] bg-primary transition-all duration-500 group-hover/header:w-full"></span>
                        <slot name="header" />
                    </div>

                    <!-- Main slot -->
                    <div class="relative z-10 w-full h-full text-foreground/90 font-mono text-sm">
                        <slot />
                    </div>
                </div>
            </div>

            <!-- Footer slot -->
            <div v-if="$slots.footer" class="mt-12 border-t border-border py-4 px-8 text-left text-[10px] font-mono font-bold uppercase text-muted-foreground tracking-widest bg-background transition-all duration-300 hover:border-primary/50">
                SYS_LOG_FOOTER // <slot name="footer" />
            </div>
        </main>

        <!-- Development mode indicator -->
        <div v-if="isDevelopment" class="fixed bottom-32 md:bottom-6 left-4 z-50 pointer-events-none transition-all duration-300 hover:scale-105">
            <span class="inline-block border border-primary bg-background/90 text-primary font-mono text-[10px] px-3 py-1 font-bold uppercase animate-pulse relative group">
                <span class="absolute top-0 left-0 w-1 h-1 bg-primary transition-all duration-300 group-hover:w-full group-hover:h-full group-hover:opacity-10"></span>
                SYS_ENV: // DEPLOYMENT_ACTIVE_DEV_MODE
            </span>
        </div>
    </div>
</template>

<style scoped>
/* Efectos locales que no necesitan estar en el global */
.hud-panel {
    border: 1px solid hsl(var(--border));
    box-shadow: 0 0 8px hsl(var(--primary) / 0.2);
    transition: all 0.3s ease;
}

.hud-panel:hover {
    border-color: hsl(var(--primary));
    box-shadow: 0 0 20px hsl(var(--primary) / 0.4);
}

/* Efecto de neón para sombras personalizadas */
.shadow-neon {
    box-shadow: 0 0 10px hsl(var(--primary) / 0.3);
}

.shadow-neon-strong {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.6);
}

/* Animación de glitch simplificada */
@keyframes glitch-simple {
    0%, 100% { transform: skew(0deg, 0deg); }
    95% { transform: skew(2deg, 1deg); filter: hue-rotate(10deg); }
    97% { transform: skew(-2deg, -1deg); filter: hue-rotate(-10deg); }
}

.glitch-text {
    animation: glitch-simple 8s infinite;
}

/* Efecto de brillo para íconos */
.icon-glow {
    filter: drop-shadow(0 0 4px currentColor);
    transition: filter 0.3s ease;
}

.icon-glow:hover {
    filter: drop-shadow(0 0 8px currentColor);
}
</style>