<!-- resources/js/Components/StepProgress.vue -->
<script setup>
    import { CheckCircle } from 'lucide-vue-next';
    
    const props = defineProps({
        steps: {
            type: Array,
            required: true
        },
        currentStep: {
            type: Number,
            required: true
        },
        progressPercentage: {
            type: Number,
            required: true
        }
    });
    
    const emit = defineEmits(['step-click']);
    
    const handleStepClick = (stepId) => {
        if (props.currentStep >= stepId) {
            emit('step-click', stepId);
        }
    };
    </script>
    
    <template>
        <div class="relative px-4">
            <!-- Progress Bar Background -->
            <div class="absolute top-5 left-0 w-full h-1 bg-muted -z-10 rounded-full"></div>
            <!-- Progress Bar Fill -->
            <div class="absolute top-5 left-0 h-1 bg-primary -z-10 rounded-full transition-all duration-500 ease-smooth"
                 :style="{ width: progressPercentage + '%' }"></div>
    
            <!-- Steps -->
            <div class="flex justify-between">
                <div v-for="step in steps" :key="step.id" 
                     class="flex flex-col items-center gap-2 cursor-pointer group"
                     @click="handleStepClick(step.id)">
                    
                    <!-- Step Circle -->
                    <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 bg-card"
                         :class="[
                            currentStep === step.id ? 'border-primary text-primary shadow-md scale-110' : 
                            currentStep > step.id ? 'border-success bg-success text-success-foreground' : 
                            'border-input text-muted-foreground'
                         ]">
                        <CheckCircle v-if="currentStep > step.id" :size="20" />
                        <component v-else :is="step.icon" :size="18" />
                    </div>
                    
                    <!-- Step Label -->
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-background px-1"
                          :class="currentStep >= step.id ? 'text-foreground' : 'text-muted-foreground'">
                        {{ step.title }}
                    </span>
                </div>
            </div>
        </div>
    </template>