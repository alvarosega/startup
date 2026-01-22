<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    
    // Iconos
    import { 
        Package, Layers, Tag, Barcode, Scale, DollarSign, 
        Plus, Trash2, ArrowRight, ArrowLeft, Save, CheckCircle, 
        UploadCloud, AlertTriangle, Info, Wine 
    } from 'lucide-vue-next';

    const props = defineProps({
        product: Object, // El producto que viene del Controller
        brands: Array,
        categories: Array
    });

    const currentStep = ref(1);
    const imagePreview = ref(props.product.image_url || null);
    
    const steps = [
        { id: 1, title: 'Concepto', icon: Package, fields: ['name', 'brand_id', 'category_id'] },
        { id: 2, title: 'Detalles', icon: Info, fields: ['description', 'image'] },
        { id: 3, title: 'Presentaciones', icon: Barcode, fields: ['skus'] }, 
    ];

    // --- INICIALIZACIÓN DEL FORMULARIO ---
    const form = useForm({
        _method: 'PUT', // Truco para que Laravel acepte ficheros en Update
        name: props.product.name,
        brand_id: props.product.brand_id,
        category_id: props.product.category_id,
        description: props.product.description || '',
        image: null, // Solo enviamos si hay cambio
        is_active: Boolean(props.product.is_active),
        is_alcoholic: Boolean(props.product.is_alcoholic),
        
        // Mapeo Crítico: Convertimos la estructura de BD a la estructura del Formulario
        skus: props.product.skus.map(sku => ({
            id: sku.id, // Importante para que el Controller sepa cual actualizar
            name: sku.name,
            code: sku.code,
            conversion_factor: parseFloat(sku.conversion_factor),
            weight: parseFloat(sku.weight),
            // Extraemos el PRECIO GLOBAL (el último precio base registrado)
            price: sku.prices && sku.prices.length > 0 
                   ? parseFloat(sku.prices[0].final_price) 
                   : 0
        }))
    });

    // --- LÓGICA DE SKUS ---
    const addSku = () => {
        form.skus.push({ 
            id: null, // Es nuevo
            name: '', 
            code: '', 
            price: 0, 
            conversion_factor: 1, 
            weight: 0 
        });
    };

    const removeSku = (index) => {
        if (form.skus.length > 1) {
            // Si el SKU tiene ID (existe en BD), podrías querer avisar que se borrará
            form.skus.splice(index, 1);
        } else {
            alert("El producto debe tener al menos una presentación.");
        }
    };

    // --- NAVEGACIÓN ---
    const nextStep = () => {
        if (currentStep.value === 1) {
            if (!form.name) { form.setError('name', 'Nombre requerido'); return; }
            if (!form.brand_id) { form.setError('brand_id', 'Marca requerida'); return; }
            if (!form.category_id) { form.setError('category_id', 'Categoría requerida'); return; }
        }
        if (currentStep.value < steps.length) currentStep.value++;
    };

    const prevStep = () => {
        if (currentStep.value > 1) currentStep.value--;
    };

    const submit = () => {
        // Validaciones finales
        const invalidSku = form.skus.find(s => !s.name || s.price < 0); // Permitimos precio 0 si es intencional, pero mejor validar > 0
        
        if (invalidSku) {
            alert('Revisa los SKUs. Todos deben tener nombre y precio válido.');
            return;
        }

        // Usamos POST con _method: PUT debido a la limitación de PHP con multipart/form-data
        form.post(route('admin.products.update', props.product.id), { 
            forceFormData: true,
            preserveScroll: true,
            onError: (errors) => {
                console.error(errors);
                alert("Hay errores en el formulario.");
            }
        });
    };

    // --- UTILS ---
    const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
    
    const handleImage = (e) => {
        const file = e.target.files[0];
        if (file) {
            form.image = file;
            imagePreview.value = URL.createObjectURL(file);
        }
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-5xl mx-auto py-6">
            
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-skin-base tracking-tight">Editar Producto</h1>
                        <p class="text-skin-muted text-sm mt-1">Modificando: <span class="font-bold text-skin-primary">{{ product.name }}</span></p>
                    </div>
                    <Link :href="route('admin.products.index')" class="text-sm font-bold text-skin-muted hover:text-skin-danger transition-colors">Cancelar</Link>
                </div>

                <div class="relative px-4">
                    <div class="absolute top-5 left-0 w-full h-1 bg-skin-border -z-10 rounded-full"></div>
                    <div class="absolute top-5 left-0 h-1 bg-skin-primary -z-10 rounded-full transition-all duration-500 ease-out" :style="{ width: progressPercentage + '%' }"></div>

                    <div class="flex justify-between">
                        <div v-for="step in steps" :key="step.id" 
                             class="flex flex-col items-center gap-2 cursor-pointer group"
                             @click="currentStep = step.id"> <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all bg-skin-fill-card"
                                 :class="[currentStep === step.id ? 'border-skin-primary text-skin-primary scale-110 shadow-lg' : currentStep > step.id ? 'border-skin-success bg-skin-success text-white' : 'border-skin-border text-skin-muted']">
                                <CheckCircle v-if="currentStep > step.id" :size="20" />
                                <component v-else :is="step.icon" :size="18" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-skin-fill px-1" :class="currentStep >= step.id ? 'text-skin-base' : 'text-skin-muted'">{{ step.title }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-skin-fill-card border border-skin-border rounded-global shadow-xl overflow-hidden min-h-[500px] flex flex-col">
                <form class="flex-1 flex flex-col" @submit.prevent>
                    <div class="p-8 flex-1">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="col-span-2">
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Nombre del Producto Maestro *</label>
                                        <input v-model="form.name" type="text" 
                                               class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-4 text-xl font-bold focus:ring-2 focus:ring-skin-primary outline-none">
                                        <p v-if="form.errors.name" class="text-skin-danger text-xs mt-1">{{ form.errors.name }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Marca *</label>
                                        <select v-model="form.brand_id" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-skin-primary">
                                            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Categoría *</label>
                                        <select v-model="form.category_id" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-skin-primary">
                                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="space-y-6">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                    <div class="lg:col-span-1">
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Imagen Principal</label>
                                        <div class="relative w-full aspect-square bg-skin-fill border-2 border-dashed border-skin-border rounded-global flex flex-col items-center justify-center cursor-pointer hover:border-skin-primary hover:bg-skin-primary/5 transition overflow-hidden">
                                            <input type="file" @change="handleImage" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-20">
                                            <img v-if="imagePreview" :src="imagePreview" class="absolute inset-0 w-full h-full object-contain p-2 z-10">
                                            <div v-else class="text-center p-4">
                                                <UploadCloud :size="32" class="mx-auto text-skin-muted mb-2" />
                                                <p class="text-xs text-skin-muted font-bold">Cambiar Foto</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="lg:col-span-2 space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Descripción</label>
                                            <textarea v-model="form.description" rows="4" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-skin-primary resize-none"></textarea>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <label class="flex items-center gap-3 p-4 border border-skin-border rounded-global cursor-pointer hover:border-skin-primary transition bg-skin-fill/50">
                                                <input v-model="form.is_active" type="checkbox" class="w-5 h-5 accent-skin-primary">
                                                <span class="text-sm font-bold text-skin-base">Activo</span>
                                            </label>
                                            <label class="flex items-center gap-3 p-4 border border-skin-border rounded-global cursor-pointer hover:border-skin-warning transition bg-skin-fill/50">
                                                <input v-model="form.is_alcoholic" type="checkbox" class="w-5 h-5 accent-skin-warning">
                                                <span class="text-sm font-bold text-skin-base">Alcohol</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else key="3" class="space-y-4">
                                <div class="flex justify-between items-center mb-2">
                                    <div>
                                        <h3 class="text-lg font-bold text-skin-base">Variantes & Precios Globales</h3>
                                        <p class="text-xs text-skin-muted">Define el precio base nacional. Las excepciones se gestionan en "Precios".</p>
                                    </div>
                                    <button type="button" @click="addSku" class="flex items-center gap-2 px-3 py-1.5 bg-skin-success/10 text-skin-success border border-skin-success/20 rounded-global text-xs font-bold hover:bg-skin-success hover:text-white transition">
                                        <Plus :size="14" /> Agregar Variante
                                    </button>
                                </div>

                                <div class="space-y-3 max-h-[350px] overflow-y-auto custom-scrollbar pr-2">
                                    <div v-for="(sku, index) in form.skus" :key="index" 
                                         class="bg-skin-fill/50 border border-skin-border rounded-global p-4 relative group hover:border-skin-primary transition-colors">
                                        
                                        <button v-if="form.skus.length > 1" type="button" @click="removeSku(index)" 
                                                class="absolute top-2 right-2 text-skin-muted hover:text-skin-danger p-1 transition">
                                            <Trash2 :size="14" />
                                        </button>

                                        <div class="grid grid-cols-12 gap-4 items-end">
                                            <div class="col-span-4">
                                                <label class="block text-[10px] font-bold text-skin-muted uppercase mb-1">Nombre</label>
                                                <input v-model="sku.name" type="text" class="w-full bg-skin-fill-card border border-skin-border text-skin-base rounded-global p-2 text-sm focus:border-skin-primary outline-none">
                                            </div>
                                            <div class="col-span-3">
                                                <label class="block text-[10px] font-bold text-skin-muted uppercase mb-1">EAN</label>
                                                <input v-model="sku.code" type="text" class="w-full bg-skin-fill-card border border-skin-border text-skin-base rounded-global p-2 text-sm font-mono focus:border-skin-primary outline-none">
                                            </div>
                                            <div class="col-span-2">
                                                <label class="block text-[10px] font-bold text-skin-muted uppercase mb-1 text-center">Unid.</label>
                                                <input v-model.number="sku.conversion_factor" type="number" class="w-full bg-skin-fill-card border border-skin-border text-skin-base rounded-global p-2 text-sm text-center focus:border-skin-primary outline-none">
                                            </div>
                                            
                                            <div class="col-span-3">
                                                <label class="block text-[10px] font-bold text-skin-success uppercase mb-1 text-right flex items-center justify-end gap-1">
                                                    <DollarSign :size="10"/> Precio Base
                                                </label>
                                                <input v-model.number="sku.price" type="number" step="0.01" min="0" 
                                                       class="w-full bg-skin-fill-card border border-skin-border text-skin-success font-bold rounded-global p-2 text-sm text-right focus:border-skin-success outline-none"
                                                       title="Este precio aplicará a todas las sucursales sin excepción específica">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </Transition>
                    </div>

                    <div class="px-8 py-4 bg-skin-fill/50 border-t border-skin-border flex justify-between items-center">
                        <button type="button" @click="prevStep" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-skin-muted hover:text-skin-base disabled:opacity-0" :disabled="currentStep === 1">
                            <ArrowLeft :size="16" /> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="flex items-center gap-2 px-6 py-2.5 bg-skin-fill-card border border-skin-border hover:border-skin-primary text-skin-base rounded-global text-sm font-bold shadow-sm active:scale-95 transition-all">
                            Siguiente <ArrowRight :size="16" />
                        </button>

                        <button v-else type="button" @click="submit" :disabled="form.processing" class="flex items-center gap-2 px-8 py-2.5 bg-skin-primary hover:bg-skin-primary-hover text-skin-primary-text rounded-global text-sm font-bold shadow-lg shadow-skin-primary/30 active:scale-95 transition-all disabled:opacity-50">
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else class="flex items-center gap-2"><Save :size="18" /> Actualizar Producto</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>