<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Iconos
import { 
    Tag, Factory, CheckCircle, ArrowRight, ArrowLeft, Save, 
    Globe, Search, UploadCloud, ShieldCheck
} from 'lucide-vue-next';

const props = defineProps({
    providers: Array,
    categories: Array
});

const currentStep = ref(1);
const categorySearch = ref('');

const steps = [
    { id: 1, title: 'Identidad', icon: Tag, fields: ['name', 'image', 'tier'] },
    { id: 2, title: 'Logística', icon: Factory, fields: ['provider_id', 'origin_country_code'] },
    { id: 3, title: 'Segmentación', icon: Globe, fields: ['categories'] },
];

const form = useForm({
    name: '',
    provider_id: '',
    manufacturer: '',
    origin_country_code: '',
    tier: 'Standard',
    website: '',
    image: null,
    is_active: true,
    is_featured: false,
    categories: []
});

// Filtrar categorías
const filteredCategories = computed(() => {
    if (!categorySearch.value) return props.categories;
    return props.categories.filter(c => 
        c.name.toLowerCase().includes(categorySearch.value.toLowerCase())
    );
});

const toggleCategory = (id) => {
    const index = form.categories.indexOf(id);
    if (index === -1) form.categories.push(id);
    else form.categories.splice(index, 1);
};

// Navegación
const nextStep = () => {
    if (currentStep.value === 1 && !form.name) {
        form.setError('name', 'Nombre obligatorio');
        return;
    }
    if (currentStep.value === 2 && !form.provider_id) {
        form.setError('provider_id', 'Debes elegir un proveedor');
        return;
    }
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    form.post(route('admin.brands.store'), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            const stepWithError = steps.find(step => 
                step.fields.some(f => errors[f])
            );
            if (stepWithError) currentStep.value = stepWithError.id;
        }
    });
};

// Utilidades
const progressPercentage = computed(() => 
    ((currentStep.value - 1) / (steps.length - 1)) * 100
);

const imagePreview = ref(null);
const handleImage = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const tiers = [
    { id: 'Economy', label: 'Económica', color: 'bg-muted text-muted-foreground' },
    { id: 'Standard', label: 'Estándar', color: 'bg-primary/10 text-primary border-primary/20' },
    { id: 'Premium', label: 'Premium', color: 'bg-amber-500/10 text-amber-600 border-amber-500/20' },
    { id: 'Luxury', label: 'Lujo', color: 'bg-purple-500/10 text-purple-500 border-purple-500/20' },
];
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto py-6">
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-foreground tracking-tight">Nueva Marca</h1>
                        <p class="text-muted-foreground text-sm mt-1">Registra una marca en el portafolio</p>
                    </div>
                    <Link 
                        :href="route('admin.brands.index')" 
                        class="text-sm font-bold text-muted-foreground hover:text-error transition-colors"
                    >
                        Cancelar
                    </Link>
                </div>

                <div class="relative px-4">
                    <div class="absolute top-5 left-0 w-full h-1 bg-border -z-10 rounded-full"></div>
                    <div 
                        class="absolute top-5 left-0 h-1 bg-primary -z-10 rounded-full transition-all duration-500 ease-out" 
                        :style="{ width: progressPercentage + '%' }"
                    ></div>

                    <div class="flex justify-between">
                        <div 
                            v-for="step in steps" 
                            :key="step.id" 
                            class="flex flex-col items-center gap-2 cursor-pointer group"
                            @click="currentStep >= step.id ? currentStep = step.id : null"
                        >
                            <div 
                                class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all bg-card"
                                :class="[
                                    currentStep === step.id ? 'border-primary text-primary scale-110 shadow-lg' : 
                                    currentStep > step.id ? 'border-success bg-success text-white' : 
                                    'border-border text-muted-foreground'
                                ]"
                            >
                                <CheckCircle v-if="currentStep > step.id" :size="20" />
                                <component v-else :is="step.icon" :size="18" />
                            </div>
                            <span 
                                class="text-[10px] font-bold uppercase tracking-wider bg-background px-1"
                                :class="currentStep >= step.id ? 'text-foreground' : 'text-muted-foreground'"
                            >
                                {{ step.title }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-xl overflow-hidden min-h-[500px] flex flex-col">
                <form class="flex-1 flex flex-col">
                    <div class="p-8 flex-1">
                        <Transition name="fade" mode="out-in">
                            <!-- Paso 1: Identidad -->
                            <div v-if="currentStep === 1" key="1" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div>
                                        <label class="block text-xs font-bold text-muted-foreground uppercase mb-2">
                                            Logotipo
                                        </label>
                                        <div class="relative w-full aspect-video bg-background border-2 border-dashed border-border rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-primary hover:bg-primary/5 transition group overflow-hidden">
                                            <input 
                                                type="file" 
                                                @change="handleImage" 
                                                accept="image/*" 
                                                class="absolute inset-0 opacity-0 cursor-pointer z-20"
                                            >
                                            <img 
                                                v-if="imagePreview" 
                                                :src="imagePreview" 
                                                class="absolute inset-0 w-full h-full object-contain p-4 z-10"
                                            >
                                            
                                            <div 
                                                class="text-center p-4 z-0 group-hover:scale-110 transition-transform duration-300" 
                                                :class="imagePreview ? 'opacity-0' : ''"
                                            >
                                                <UploadCloud :size="32" class="mx-auto text-muted-foreground mb-2" />
                                                <p class="text-xs text-muted-foreground font-bold">Subir Logo</p>
                                            </div>
                                        </div>
                                        <p v-if="form.errors.image" class="text-error text-xs mt-1">
                                            {{ form.errors.image }}
                                        </p>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-muted-foreground uppercase mb-2">
                                                Nombre Comercial *
                                            </label>
                                            <input 
                                                v-model="form.name" 
                                                type="text" 
                                                class="w-full bg-background border border-input text-foreground rounded-lg p-3 text-lg font-bold focus:ring-2 focus:ring-ring focus:border-primary outline-none" 
                                                placeholder="Ej: Coca-Cola"
                                            >
                                            <p v-if="form.errors.name" class="text-error text-xs mt-1">
                                                {{ form.errors.name }}
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-muted-foreground uppercase mb-2">
                                                Posicionamiento (Tier)
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div 
                                                    v-for="tier in tiers" 
                                                    :key="tier.id" 
                                                    @click="form.tier = tier.id"
                                                    class="cursor-pointer border rounded-lg p-2 text-center text-xs font-bold transition-all"
                                                    :class="form.tier === tier.id ? 'border-primary ring-1 ring-primary ' + tier.color : 'border-border text-muted-foreground hover:border-primary'"
                                                >
                                                    {{ tier.label }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 2: Logística -->
                            <div v-else-if="currentStep === 2" key="2" class="space-y-6">
                                <div class="bg-muted/30 p-6 rounded-lg border border-border">
                                    <h3 class="text-sm font-bold text-foreground mb-4 flex items-center gap-2">
                                        <Factory :size="18" class="text-primary" /> 
                                        Cadena de Suministro
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-xs font-bold text-muted-foreground uppercase mb-2">
                                                Distribuidor Oficial *
                                            </label>
                                            <select 
                                                v-model="form.provider_id" 
                                                class="w-full bg-card border border-input text-foreground rounded-lg p-3 outline-none focus:border-primary"
                                            >
                                                <option value="" disabled>-- Selecciona --</option>
                                                <option 
                                                    v-for="p in providers" 
                                                    :key="p.id" 
                                                    :value="p.id"
                                                >
                                                    {{ p.commercial_name }}
                                                </option>
                                            </select>
                                            <p v-if="form.errors.provider_id" class="text-error text-xs mt-1">
                                                {{ form.errors.provider_id }}
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-muted-foreground uppercase mb-2">
                                                País de Origen (ISO)
                                            </label>
                                            <input 
                                                v-model="form.origin_country_code" 
                                                type="text" 
                                                maxlength="2" 
                                                class="w-full bg-card border border-input text-foreground rounded-lg p-3 uppercase font-mono text-center focus:border-primary outline-none" 
                                                placeholder="BO"
                                            >
                                        </div>

                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-muted-foreground uppercase mb-2">
                                                Fabricante (Opcional)
                                            </label>
                                            <input 
                                                v-model="form.manufacturer" 
                                                type="text" 
                                                class="w-full bg-card border border-input text-foreground rounded-lg p-3 outline-none focus:border-primary" 
                                                placeholder="Ej: The Coca-Cola Company"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 p-3 border border-border rounded-lg w-full cursor-pointer hover:border-primary transition">
                                        <input 
                                            v-model="form.is_active" 
                                            type="checkbox" 
                                            class="w-5 h-5 text-primary rounded focus:ring-primary"
                                        >
                                        <span class="text-sm font-bold text-foreground">Marca Activa</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-3 border border-border rounded-lg w-full cursor-pointer hover:border-primary transition">
                                        <input 
                                            v-model="form.is_featured" 
                                            type="checkbox" 
                                            class="w-5 h-5 text-primary rounded focus:ring-primary"
                                        >
                                        <span class="text-sm font-bold text-foreground">Destacada</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Paso 3: Segmentación -->
                            <div v-else key="3" class="space-y-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-bold text-foreground">Asignar Categorías</h3>
                                        <p class="text-xs text-muted-foreground">
                                            Selecciona dónde aparecerán los productos de esta marca.
                                        </p>
                                    </div>
                                    <div class="relative w-64">
                                        <Search :size="14" class="absolute left-3 top-3 text-muted-foreground" />
                                        <input 
                                            v-model="categorySearch" 
                                            type="text" 
                                            placeholder="Filtrar categorías..." 
                                            class="w-full bg-background border border-input rounded-lg pl-9 p-2 text-sm focus:border-primary outline-none"
                                        >
                                    </div>
                                </div>

                                <div class="bg-muted/20 p-4 rounded-lg border border-border h-80 overflow-y-auto">
                                    <div v-if="filteredCategories.length > 0" class="flex flex-wrap gap-2">
                                        <button 
                                            type="button" 
                                            v-for="cat in filteredCategories" 
                                            :key="cat.id"
                                            @click="toggleCategory(cat.id)"
                                            class="px-3 py-1.5 rounded-full text-xs font-bold border transition-all duration-200 select-none flex items-center gap-2"
                                            :class="form.categories.includes(cat.id) 
                                                ? 'bg-primary text-primary-foreground border-primary shadow-md scale-105' 
                                                : 'bg-card text-foreground border-border hover:border-muted-foreground'"
                                        >
                                            {{ cat.name }}
                                            <CheckCircle v-if="form.categories.includes(cat.id)" :size="12" />
                                        </button>
                                    </div>
                                    <div v-else class="h-full flex flex-col items-center justify-center text-muted-foreground">
                                        <Search :size="32" class="mb-2 opacity-50" />
                                        <p class="text-sm">No se encontraron categorías</p>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-xs text-muted-foreground px-2">
                                    <span>{{ form.categories.length }} categorías seleccionadas</span>
                                    <span 
                                        v-if="form.categories.length === 0" 
                                        class="text-error font-bold flex items-center gap-1"
                                    >
                                        <ShieldCheck :size="12" /> 
                                        Se recomienda asignar al menos una
                                    </span>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <div class="px-8 py-4 bg-muted/50 border-t border-border flex justify-between items-center">
                        <button 
                            type="button" 
                            @click="prevStep" 
                            class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-muted-foreground hover:text-foreground disabled:opacity-0" 
                            :disabled="currentStep === 1"
                        >
                            <ArrowLeft :size="16" /> Atrás
                        </button>

                        <button 
                            v-if="currentStep < steps.length" 
                            type="button" 
                            @click="nextStep" 
                            class="flex items-center gap-2 btn btn-outline btn-md"
                        >
                            Siguiente <ArrowRight :size="16" />
                        </button>

                        <button 
                            v-else 
                            type="button" 
                            @click="submit" 
                            :disabled="form.processing" 
                            class="btn btn-primary btn-md"
                        >
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else class="flex items-center gap-2">
                                <Save :size="18" /> Guardar Marca
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>