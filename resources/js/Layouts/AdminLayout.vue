<script setup>
    import { ref, onMounted } from 'vue';
    import Sidebar from '@/Components/Admin/Sidebar.vue';
    import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
    import { usePage } from '@inertiajs/vue3';
    
    const isSidebarCollapsed = ref(false);
    
    onMounted(() => {
        const savedState = localStorage.getItem('sidebarCollapsed');
        isSidebarCollapsed.value = savedState === 'true' || false;
    });
    
    const handleSidebarToggle = (collapsed) => {
        isSidebarCollapsed.value = collapsed;
        localStorage.setItem('sidebarCollapsed', collapsed.toString());
    };
    
    const page = usePage();
    const user = page.props.auth.user;
    
    // Detectar si estamos en desarrollo usando el sistema de clases
    const isDevelopment = ref(false);
    onMounted(() => {
        isDevelopment.value = window.location.hostname === 'localhost' || 
                             window.location.hostname.includes('127.0.0.1') ||
                             window.location.hostname.includes('dev') ||
                             window.location.hostname.includes('test');
    });
    </script>
    
    <template>
        <div class="min-h-screen bg-background text-foreground font-sans antialiased dark-mode-transition">
            <!-- Background Pattern (usando variables del sistema) -->
            <div class="fixed inset-0 bg-gradient-to-br from-background via-background to-muted/30 -z-10" />
            
            <!-- Grid Pattern (optimizado para el sistema) -->
            <div 
                class="fixed inset-0 -z-10 opacity-50"
                :style="{
                    backgroundImage: `radial-gradient(circle at 50% 50%, hsl(var(--background)/0.95) 0%, transparent 50%), 
                                    linear-gradient(to bottom right, hsl(var(--primary)/0.03) 1px, transparent 1px)`,
                    backgroundSize: '50px 50px'
                }"
            />
    
            <Sidebar @toggle-collapse="handleSidebarToggle" />
    
            <!-- Main Content -->
            <main 
                class="flex-1 min-h-screen transition-all duration-base ease-smooth will-change-transform"
                :class="[
                    isSidebarCollapsed ? 'md:ml-[88px]' : 'md:ml-[280px]',
                    'pb-20 md:pb-0'
                ]"
            >
                <div class="relative">
                    <!-- Floating User Badge (Desktop) - Usando sistema de glass -->
                    <div v-if="user" class="hidden lg:block fixed top-6 left-1/2 transform -translate-x-1/2 z-30 animate-slide-up">
                        <div class="glass flex items-center gap-3 px-4 py-2 rounded-full shadow-lg">
                            <div class="avatar avatar-md bg-gradient-to-br from-primary to-secondary text-primary-foreground shadow-md">
                                {{ user?.first_name?.[0]?.toUpperCase() || 'U' }}
                            </div>
                            <div class="pr-2">
                                <p class="text-sm font-semibold text-foreground leading-none">
                                    {{ user?.first_name || 'Usuario' }}
                                </p>
                                <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-medium">
                                    {{ user?.roles?.[0]?.replace('_', ' ') || 'Staff' }}
                                </p>
                            </div>
                        </div>
                    </div>
    
                    <!-- Theme Toggler -->
                    <div class="fixed top-6 right-6 z-40">
                        <ThemeToggler />
                    </div>
                </div>
    
                <!-- Content Container -->
                <div class="pt-6 md:pt-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    <!-- Page Header Slot -->
                    <div class="mb-8 md:mb-10">
                        <slot name="header" />
                    </div>
    
                    <!-- Main Content Slot -->
                    <div class="animate-in duration-300">
                        <slot />
                    </div>
    
                    <!-- Page Footer Slot -->
                    <div class="mt-12 pt-8 border-t border-border/30">
                        <slot name="footer" />
                    </div>
                </div>
    
                <!-- Development Indicator -->
                <div v-if="isDevelopment" class="fixed bottom-4 left-4 z-50">
                    <div class="badge badge-warning animate-pulse">
                        <span class="text-xs font-bold">DEV</span>
                    </div>
                </div>
    
                <!-- Mobile Bottom Spacer -->
                <div class="h-20 md:h-0" />
            </main>
        </div>
    </template>
    
    <style scoped>
    /* Optimización de rendimiento */
    .will-change-transform {
        will-change: transform, margin;
    }
    
    /* Usamos las utilidades del sistema en lugar de CSS personalizado */
    </style>