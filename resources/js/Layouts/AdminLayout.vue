<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Admin/Sidebar.vue';
import Loader from '@/Components/Admin/Loader/Loader.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div data-context="admin" class="min-h-screen bg-background text-foreground font-sans antialiased selection:bg-primary/20 selection:text-primary relative overflow-x-hidden">
        
        <!-- Canvas Ambiental de Refracción Apple Glass (Iluminación difusa para efecto de vidrio) -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10 select-none">
            <div class="absolute -top-[120px] -right-[80px] w-[650px] h-[650px] rounded-full bg-primary/[0.07] dark:bg-primary/[0.12] blur-[150px]"></div>
            <div class="absolute top-[35%] -left-[100px] w-[550px] h-[550px] rounded-full bg-info/[0.05] dark:bg-info/[0.08] blur-[160px]"></div>
            <div class="absolute -bottom-[100px] right-[20%] w-[600px] h-[600px] rounded-full bg-purple-500/[0.04] dark:bg-purple-500/[0.07] blur-[170px]"></div>
        </div>

        <Loader />
        
        <Sidebar />

        <main class="relative min-h-screen md:ml-[74px] pb-[92px] md:pb-8 flex flex-col w-full md:w-[calc(100%-74px)] transition-all duration-200">
            
            <!-- Barra Superior Flotante Glass Header -->
            <header v-if="$slots.header" class="glass-header sticky top-0 z-30 px-5 md:px-8 py-3.5 flex items-center justify-between transition-all duration-200">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-5 bg-primary rounded-full shadow-sm"></div>
                    <h1 class="text-base md:text-lg font-bold tracking-tight text-foreground flex items-center gap-2">
                        <slot name="header" />
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <slot name="header-actions" />

                    <div v-if="user?.branch" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-muted/60 text-muted-foreground border border-border/60 backdrop-blur-md">
                        <span class="material-symbols-rounded text-[14px] text-primary">store</span>
                        <span>{{ user.branch.name }}</span>
                    </div>
                </div>
            </header>

            <!-- Contenedor Principal Operativo -->
            <div class="flex-1 p-4 md:p-6 lg:p-8 w-full max-w-[1720px] mx-auto space-y-5">
                <slot />
            </div>

            <footer v-if="$slots.footer" class="mt-auto border-t border-border/30 py-3.5 px-6 text-left text-xs font-medium text-muted-foreground/70 glass-header">
                <slot name="footer" />
            </footer>
        </main>
    </div>
</template>
