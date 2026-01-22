<script setup>
    import { ref, onMounted } from 'vue';
    import Sidebar from '@/Components/Admin/Sidebar.vue';
    import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
    import { usePage } from '@inertiajs/vue3';
    
    // Estado local para ajustar el margen del contenido
    const isSidebarCollapsed = ref(false);
    
    // Inicializar desde localStorage o valor por defecto
    onMounted(() => {
        const savedState = localStorage.getItem('sidebarCollapsed');
        isSidebarCollapsed.value = savedState === 'true' || false;
    });
    
    const handleSidebarToggle = (collapsed) => {
        isSidebarCollapsed.value = collapsed;
    };
    
    // Opcional: Obtener usuario actual para personalización
    const page = usePage();
    const user = page.props.auth.user;
    
    // Detectar si estamos en desarrollo (alternativa a process.env)
    const isDevelopment = ref(false);
    onMounted(() => {
        // Verificar si estamos en desarrollo por la URL o dominio
        isDevelopment.value = window.location.hostname === 'localhost' || 
                             window.location.hostname.includes('127.0.0.1') ||
                             window.location.hostname.includes('dev') ||
                             window.location.hostname.includes('test');
    });
    </script>
    
    <template>
        <div class="min-h-screen bg-background text-foreground font-sans antialiased dark-mode-transition">
            
            <!-- Background Pattern (Opcional) -->
            <div class="fixed inset-0 bg-gradient-to-br from-background via-background to-muted/30 -z-10" />
            
            <!-- Grid Pattern (Sutil) -->
            <div class="fixed inset-0 bg-[radial-gradient(ellipse_at_center,rgba(0,0,0,0)_0%,rgba(0,0,0,0)_70%,rgba(var(--primary),0.03)_100%)] -z-10" />
    
            <Sidebar @toggle-collapse="handleSidebarToggle" />
    
            <!-- Main Content -->
            <main 
                class="flex-1 min-h-screen transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] will-change-margin"
                :class="[
                    // Desktop: Margen dinámico basado en estado del sidebar
                    isSidebarCollapsed ? 'md:ml-[88px]' : 'md:ml-[280px]',
                    // Mobile: Margen inferior para no tapar el BottomNav
                    'pb-20 md:pb-0'
                ]"
            >
                <div class="relative">
                    <!-- Floating User Badge (Desktop) -->
                    <div class="hidden lg:block fixed top-6 left-1/2 transform -translate-x-1/2 z-30 animate-slide-up">
                        <div class="flex items-center gap-3 px-4 py-2 bg-card/80 backdrop-blur-md border border-border/50 rounded-full shadow-lg shadow-black/5">
                            <div class="w-8 h-8 rounded-full gradient-primary flex items-center justify-center font-bold text-sm text-white shadow-md">
                                {{ user?.first_name?.[0] || 'U' }}
                            </div>
                            <div class="pr-2">
                                <p class="text-sm font-semibold text-foreground leading-none">{{ user?.first_name || 'Usuario' }}</p>
                                <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-medium">
                                    {{ user?.roles?.[0]?.replace('_', ' ') || 'Staff' }}
                                </p>
                            </div>
                        </div>
                    </div>
    
                    <!-- Theme Toggler -->
                    <div class="fixed top-4 right-4 z-40 animate-scale-in">
                        <ThemeToggler />
                    </div>
                </div>
    
                <!-- Content Container -->
                <div class="pt-6 md:pt-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    <!-- Page Header Slot (opcional para páginas que lo necesiten) -->
                    <div class="mb-8 md:mb-10">
                        <slot name="header" />
                    </div>
    
                    <!-- Main Content Slot -->
                    <div class="animate-in duration-500">
                        <slot />
                    </div>
    
                    <!-- Page Footer Slot (opcional) -->
                    <div class="mt-12 pt-8 border-t border-border/30">
                        <slot name="footer" />
                    </div>
                </div>
    
                <!-- Mobile Bottom Spacer -->
                <div class="h-20 md:h-0" />
            </main>
    
            <!-- Performance Indicator (Solo desarrollo) -->
            <div v-if="isDevelopment" 
                 class="fixed bottom-2 left-2 z-50 px-2 py-1 bg-black/80 text-white text-xs rounded font-mono">
                {{ isSidebarCollapsed ? '🗂️' : '📂' }}
            </div>
        </div>
    </template>
    
    <style scoped>
    /* Animaciones personalizadas */
    @keyframes slide-up {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes scale-in {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    @keyframes fade-in {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    .animate-slide-up {
        animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    .animate-scale-in {
        animation: scale-in 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    .animate-in {
        animation: fade-in 0.3s ease-out;
    }
    
    .gradient-primary {
        background: linear-gradient(135deg, hsl(var(--primary)) 0%, hsl(var(--secondary)) 100%);
    }
    
    .dark-mode-transition * {
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }
    
    /* Optimización de rendimiento */
    .will-change-margin {
        will-change: margin;
    }
    
    /* Scrollbar personalizado para el main */
    main::-webkit-scrollbar {
        width: 8px;
    }
    
    main::-webkit-scrollbar-track {
        @apply bg-transparent;
    }
    
    main::-webkit-scrollbar-thumb {
        @apply bg-border rounded-full;
    }
    
    main::-webkit-scrollbar-thumb:hover {
        @apply bg-muted-foreground/50;
    }
    
    /* Estilos de selección */
    ::selection {
        @apply bg-primary/20 text-foreground;
    }
    
    ::-moz-selection {
        @apply bg-primary/20 text-foreground;
    }
    </style>