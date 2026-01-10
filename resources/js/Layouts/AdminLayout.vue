<script setup>
    import { ref } from 'vue';
    import Sidebar from '@/Components/Admin/Sidebar.vue';
    import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
    
    // Estado recibido desde el Sidebar
    const sidebarWidth = ref(260); // Valor inicial seguro
    const isResizing = ref(false);
    
    // Funciones para recibir eventos del hijo
    const updateWidth = (width) => {
        sidebarWidth.value = width;
    };
    
    const updateResizingState = (state) => {
        isResizing.value = state;
    };
    </script>
    
    <template>
        <div class="min-h-screen bg-skin-fill text-skin-base flex font-sans">
            
            <Sidebar 
                @width-change="updateWidth" 
                @resizing-state="updateResizingState" 
            />
    
            <main 
                class="flex-1 p-8 overflow-y-auto h-screen bg-skin-fill"
                :class="{ 'transition-all duration-300 ease-out': !isResizing }"
                :style="{ marginLeft: sidebarWidth + 'px' }"
            >
                <slot />
            </main>
    
            <ThemeToggler />
            
        </div>
    </template>