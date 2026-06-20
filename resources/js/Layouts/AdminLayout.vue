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
    <div class="min-h-screen bg-background text-foreground font-sans antialiased selection:bg-primary/20 selection:text-primary">
        
        <Loader />
        
        <Sidebar />

        <main class="relative min-h-screen md:ml-[72px] pb-40 md:pb-12 flex flex-col data-layout-fluid">
            
            <header class="w-full px-4 py-2.5 md:px-6 flex justify-between items-center sticky top-0 z-40 bg-background border-b border-border">
                
                <div class="flex items-center text-xs text-muted-foreground font-medium">
                    <span>Panel de Control</span>
                </div>

                <div class="flex items-center gap-3">
                    
                    <div v-if="user" class="hidden md:flex items-center border border-border bg-card p-1 pr-3 cursor-default rounded-md transition-colors duration-100 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        <div class="w-8 h-8 bg-primary/10 text-primary flex items-center justify-center font-bold text-base rounded-md border border-primary/20">
                            <span>
                                {{ user?.first_name?.[0]?.toUpperCase() || 'U' }}
                            </span>
                        </div>
                        
                        <div class="flex flex-col border-l border-border pl-2.5 ml-1.5 intense-text-container">
                            <span class="text-xs font-semibold leading-none text-foreground">
                                {{ user?.first_name }}
                            </span>
                            <span class="text-[9px] font-bold uppercase tracking-wider text-primary mt-1 leading-none">
                                {{ user?.roles?.[0]?.replace('_', ' ') || 'Staff' }}
                            </span>
                        </div>
                    </div>

                    <div class="border border-border bg-card rounded-md transition-colors duration-100 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        <ThemeToggler class="p-1.5 text-foreground/80 hover:text-primary transition-colors duration-100" />
                    </div>
                </div>
            </header>

            <div class="flex-1 p-4 md:p-6 w-full mx-auto mt-2 md:mt-0">
                
                <div v-if="$slots.header" class="mb-5 relative border-b border-border/40 pb-3">
                    <h1 class="text-xl md:text-2xl font-bold tracking-tight text-foreground">
                        <slot name="header" />
                    </h1>
                </div>

                <slot />
            </div>

            <div v-if="$slots.footer" class="mt-auto border-t border-border py-3 px-6 text-left text-xs font-medium text-muted-foreground bg-card">
                <slot name="footer" />
            </div>
        </main>

        <div v-if="isDevelopment" class="fixed bottom-36 md:bottom-4 left-4 z-50">
            <span class="inline-block bg-neutral-900 text-white dark:bg-neutral-50 dark:text-neutral-900 text-[10px] px-2 py-0.5 font-bold tracking-wider rounded border border-border">
                DEV
            </span>
        </div>
    </div>
</template>