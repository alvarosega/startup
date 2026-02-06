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

const isDevelopment = ref(false);
onMounted(() => {
    isDevelopment.value = ['localhost', '127.0.0.1', 'test', 'dev'].some(host => 
        window.location.hostname.includes(host)
    );
});
</script>

<template>
    <div class="min-h-screen bg-background text-foreground font-sans antialiased selection:bg-primary/20 selection:text-primary">
        
        <div class="fixed inset-0 z-[-1]">
            <div class="absolute inset-0 bg-gradient-to-br from-background via-background to-muted/50" />
            <div class="absolute inset-0 bg-dots opacity-[0.4] [mask-image:radial-gradient(ellipse_at_center,black_40%,transparent_80%)]" />
        </div>

        <Sidebar @toggle-collapse="handleSidebarToggle" />

        <main 
            class="relative flex-1 min-h-screen transition-[margin] duration-300 ease-smooth will-change-[margin]"
            :class="[
                isSidebarCollapsed ? 'md:ml-[88px]' : 'md:ml-[280px]',
                'pb-24 md:pb-8'
            ]"
        >
            <header class="absolute top-0 right-0 w-full md:sticky md:top-0 z-50 px-4 py-3 md:px-8 flex justify-end items-center pointer-events-none">
                <div class="flex items-center gap-4 pointer-events-auto">
                    
                    <div v-if="user" class="hidden md:flex glass rounded-full pl-1 pr-4 py-1 items-center gap-3 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="avatar avatar-sm bg-gradient-to-br from-primary to-accent text-primary-foreground shadow-inner">
                            {{ user?.first_name?.[0]?.toUpperCase() || 'U' }}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold leading-none text-foreground/90">
                                {{ user?.first_name }}
                            </span>
                            <span class="text-[10px] uppercase tracking-wider text-muted-foreground font-medium">
                                {{ user?.roles?.[0]?.replace('_', ' ') || 'Staff' }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-background/80 backdrop-blur-md border border-border/50 rounded-full p-1 shadow-lg md:shadow-sm md:bg-transparent md:border-none hover:scale-105 transition-transform">
                        <ThemeToggler />
                    </div>
                </div>
            </header>

            <div class="px-4 pt-4 md:pt-0 sm:px-6 lg:px-8 max-w-[1600px] mx-auto animate-in fade-in slide-in-from-bottom-4 duration-500 relative z-0">
                <div v-if="$slots.header" class="mb-4 md:mb-8 pr-12 md:pr-0"> <slot name="header" />
                </div>

                <slot />
            </div>

            <div v-if="$slots.footer" class="mt-20 border-t border-border/40 py-6 px-8 text-center text-sm text-muted-foreground">
                <slot name="footer" />
            </div>
        </main>

        <div v-if="isDevelopment" class="fixed bottom-24 md:bottom-4 left-4 z-50 pointer-events-none">
            <span class="badge badge-warning font-mono text-[10px] shadow-lg animate-pulse opacity-50 hover:opacity-100 transition-opacity">
                DEV
            </span>
        </div>
    </div>
</template>