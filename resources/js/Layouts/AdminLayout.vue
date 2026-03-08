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
        
        <!-- Loader component -->
        <Loader />
        
        <!-- Sidebar -->
        <Sidebar />

        <!-- Main content -->
        <main class="relative min-h-screen transition-all duration-300 md:ml-[72px] pb-40 md:pb-12 flex flex-col">
            
            <!-- Header -->
            <header class="w-full px-4 py-4 md:px-8 flex justify-end items-center sticky top-0 z-40 bg-background/90 backdrop-blur-sm border-b border-border transition-all duration-200">
                <div class="flex items-center gap-4">
                    
                    <!-- User info -->
                    <div v-if="user" class="hidden md:flex items-center border border-border bg-card p-1 pr-4 cursor-default rounded-lg transition-all duration-200 hover:border-primary/30 hover:shadow-sm group">
                        <!-- Avatar -->
                        <div class="w-10 h-10 bg-primary/10 text-primary flex items-center justify-center font-sans font-bold text-xl rounded-lg border border-primary/20 group-hover:bg-primary/20 transition-all duration-200">
                            <span class="transition-transform duration-200 group-hover:scale-105">{{ user?.first_name?.[0]?.toUpperCase() || 'U' }}</span>
                        </div>
                        
                        <!-- User details -->
                        <div class="flex flex-col border-l border-border pl-3 ml-1">
                            <span class="font-sans text-sm font-semibold leading-none text-foreground transition-colors duration-200 group-hover:text-primary">
                                {{ user?.first_name }}
                            </span>
                            <span class="text-[10px] font-medium uppercase tracking-wider text-primary mt-1">
                                {{ user?.roles?.[0]?.replace('_', ' ') || 'Staff' }}
                            </span>
                        </div>
                    </div>

                    <!-- Theme toggler -->
                    <div class="border border-border bg-card rounded-lg transition-all duration-200 hover:border-primary/30 hover:shadow-sm">
                        <ThemeToggler class="p-2 transition-all duration-200 hover:text-primary" />
                    </div>
                </div>
            </header>

            <!-- Content area -->
            <div class="flex-1 p-4 md:p-6 lg:p-8 max-w-[1920px] w-full mx-auto mt-2 md:mt-0">
                <div class="card w-full h-full p-6 md:p-8">
                    
                    <!-- Header slot with decorative line -->
                    <div v-if="$slots.header" class="mb-6 md:mb-8 relative">
                        <h1 class="font-sans font-bold text-3xl md:text-4xl text-foreground">
                            <slot name="header" />
                        </h1>
                        <div class="absolute -bottom-2 left-0 w-12 h-1 bg-primary rounded-full"></div>
                    </div>

                    <!-- Main slot -->
                    <div class="w-full h-full text-foreground/90 text-sm">
                        <slot />
                    </div>
                </div>
            </div>

            <!-- Footer slot -->
            <div v-if="$slots.footer" class="mt-12 border-t border-border py-4 px-8 text-left text-xs font-medium text-muted-foreground bg-background">
                <slot name="footer" />
            </div>
        </main>

        <!-- Development mode indicator -->
        <div v-if="isDevelopment" class="fixed bottom-32 md:bottom-6 left-4 z-50">
            <span class="inline-block bg-primary/10 text-primary text-xs px-3 py-1 font-medium rounded-md border border-primary/20">
                DEV MODE
            </span>
        </div>
    </div>
</template>

<style scoped>
/* Solo estilos específicos del layout que no están en globales */
.card {
    @apply bg-card text-card-foreground rounded-xl border border-border shadow-sm;
}
</style>