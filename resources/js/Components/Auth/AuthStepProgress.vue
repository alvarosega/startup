<!-- resources/js/Components/Auth/AuthStepProgress.vue -->
<script setup>
import { User, Truck } from 'lucide-vue-next';

const props = defineProps({
    currentStep: {
        type: Number,
        default: 1
    },
    totalSteps: {
        type: Number,
        default: 3
    }
});

const progressPercentage = computed(() => {
    return ((props.currentStep - 1) / (props.totalSteps - 1)) * 100;
});
</script>

<template>
    <div class="relative px-4 mb-6">
        <!-- Progress Bar Background -->
        <div class="absolute top-5 left-0 w-full h-1 bg-muted -z-10 rounded-full"></div>
        <!-- Progress Bar Fill -->
        <div class="absolute top-5 left-0 h-1 bg-primary -z-10 rounded-full transition-all duration-500 ease-smooth"
             :style="{ width: progressPercentage + '%' }"></div>

        <!-- Steps -->
        <div class="flex justify-between">
            <div v-for="step in totalSteps" :key="step" 
                 class="flex flex-col items-center gap-2">
                
                <!-- Step Circle -->
                <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 bg-card"
                     :class="[
                        currentStep === step ? 'border-primary text-primary shadow-md scale-110' : 
                        currentStep > step ? 'border-success bg-success text-success-foreground' : 
                        'border-input text-muted-foreground'
                     ]">
                    <component :is="step === 1 ? User : Truck" :size="18" />
                </div>
                
                <!-- Step Label -->
                <span class="text-[10px] font-medium uppercase tracking-wider bg-background px-1"
                      :class="currentStep >= step ? 'text-foreground' : 'text-muted-foreground'">
                    {{ step === 1 ? 'Cuenta' : step === 2 ? 'Vehículo' : 'Perfil' }}
                </span>
            </div>
        </div>
    </div>
</template>