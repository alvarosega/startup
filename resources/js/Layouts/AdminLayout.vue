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
        
        <Loader />
        
        <Sidebar />

        <main class="relative min-h-screen transition-none md:ml-[72px] pb-40 md:pb-12 flex flex-col z-10">
            
            <header class="w-full px-4 py-4 md:px-8 flex justify-end items-center sticky top-0 z-40 bg-background/90 backdrop-blur-sm border-b border-border transition-none">
                <div class="flex items-center gap-4">
                    
                    <div v-if="user" class="hidden md:flex items-center border border-border bg-card p-1 pr-4 cursor-default transition-none relative">
                        <div class="absolute top-[-2px] left-[-2px] w-1 h-1 bg-border z-10"></div>
                        <div class="w-10 h-10 bg-primary/10 text-primary flex items-center justify-center font-mono font-black text-xl border border-primary/50 relative overflow-hidden group">
                            <span class="glitch-hover">{{ user?.first_name?.[0]?.toUpperCase() || 'U' }}</span>
                        </div>
                        <div class="flex flex-col border-l border-border pl-3 ml-1">
                            <span class="font-mono text-sm font-bold leading-none text-foreground uppercase tracking-tight glitch-hover">
                                {{ user?.first_name }}
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary mt-1">
                                [{{ user?.roles?.[0]?.replace('_', ' ') || 'STAFF_UNIT' }}]
                            </span>
                        </div>
                    </div>

                    <div class="border border-border bg-card hover:border-primary group transition-none relative z-0">
                        <ThemeToggler class="p-2 icon-glow group-hover:text-primary transition-none" />
                    </div>
                </div>
            </header>

            <div class="flex-1 p-4 md:p-6 lg:p-8 max-w-[1920px] w-full mx-auto relative z-0 mt-2 md:mt-0">
                <div class="hud-panel w-full h-full p-6 md:p-8 relative transition-none bg-card/60 backdrop-blur-sm">
                    
                    <div class="absolute top-0 left-0 w-20 h-2 border-t-2 border-l-2 border-primary z-10"></div>
                    <div class="absolute top-1 left-3 text-[10px] font-mono text-primary uppercase font-bold tracking-widest animate-pulse z-10">UNIT_01_SYS</div>
                    
                    <div v-if="$slots.header" class="mb-6 md:mb-8 pr-12 md:pr-0 transition-none font-display font-black uppercase text-4xl tracking-widest text-foreground relative z-10"> 
                        <span class="absolute left-0 bottom-0 w-12 h-[2px] bg-primary"></span>
                        <slot name="header" />
                    </div>

                    <div class="relative z-10 w-full h-full text-foreground/90 font-mono text-sm">
                        <slot />
                    </div>
                </div>
            </div>

            <div v-if="$slots.footer" class="mt-12 border-t border-border py-4 px-8 text-left text-[10px] font-mono font-bold uppercase text-muted-foreground tracking-widest bg-background transition-none">
                SYS_LOG_FOOTER // <slot name="footer" />
            </div>
        </main>

        <div v-if="isDevelopment" class="fixed bottom-32 md:bottom-6 left-4 z-50 pointer-events-none transition-none">
            <span class="inline-block border border-primary bg-background/90 text-primary font-mono text-[10px] px-3 py-1 font-bold uppercase animate-pulse relative">
                <span class="absolute top-0 left-0 w-1 h-1 bg-primary"></span>
                SYS_ENV: // DEPLOYMENT_ACTIVE_DEV_MODE
            </span>
        </div>
    </div>
</template>