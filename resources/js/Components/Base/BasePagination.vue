<script setup>
    import { Link } from '@inertiajs/vue3';
    
    defineProps({
        links: {
            type: Array,
            required: true,
        },
    });
    </script>
    
    <template>
        <div v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1 mt-4">
            <template v-for="(link, k) in links" :key="k">
                <div v-if="link.url === null" 
                     class="px-3 py-1 text-xs text-skin-muted border border-transparent rounded opacity-50" 
                     v-html="link.label" />
                
                <Link v-else 
                      :href="link.url" 
                      class="px-3 py-1 text-xs border rounded transition-colors duration-200"
                      :class="link.active 
                        ? 'bg-skin-primary text-skin-primary-text border-skin-primary font-bold' 
                        : 'bg-skin-fill-card text-skin-base border-skin-border hover:bg-skin-fill-hover'"
                      v-html="link.label" />
            </template>
        </div>
    </template>