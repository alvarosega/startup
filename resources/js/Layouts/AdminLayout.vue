<script setup>
import Sidebar from '@/Components/Admin/Sidebar.vue';
import Loader from '@/Components/Admin/Loader/Loader.vue';
</script>

<template>
    <div class="min-h-screen bg-background text-foreground font-sans antialiased selection:bg-primary/20 selection:text-primary">
        
        <Loader />
        
        <Sidebar />

        <main class="relative min-h-screen md:ml-[72px] pb-[88px] md:pb-6 flex flex-col w-full md:w-[calc(100%-72px)] transition-all duration-150">
            
            <div class="flex-1 p-4 md:p-6 w-full mx-auto">
                
                <div v-if="$slots.header" class="mb-5 relative border-b border-border/40 pb-3">
                    <h1 class="text-xl md:text-2xl font-bold tracking-tight text-foreground uppercase">
                        <slot name="header" />
                    </h1>
                </div>

                <slot />
            </div>

            <div v-if="$slots.footer" class="mt-auto border-t border-border py-3 px-6 text-left text-xs font-medium text-muted-foreground bg-card">
                <slot name="footer" />
            </div>
        </main>
    </div>
</template>