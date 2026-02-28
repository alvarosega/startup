<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Save, ArrowLeft, UserPlus, Truck, ShieldCheck } from 'lucide-vue-next';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

const form = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    password: '',
    license_number: '',
    license_plate: '',
    vehicle_type: 'moto',
});

// REGLA ESTRICTA: Solo Bolivia
const telOptions = { 
    mode: 'international', 
    defaultCountry: 'BO', 
    onlyCountries: ['BO'], 
    dropdownOptions: { showSearchBox: false, showFlags: true }, 
    inputOptions: { placeholder: '77712345' } 
};

// Fuerza el formato E.164 en el payload del formulario
const onInput = (phone, obj) => { 
    if(obj?.number) form.phone = obj.number; 
};

const submit = () => {
    form.post(route('admin.drivers.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto py-8 px-4">
            
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.drivers.index')" class="p-2 rounded-full border hover:bg-gray-100 transition-colors">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-semibold flex items-center gap-3">
                            <UserPlus :size="28" class="text-gray-400" />
                            Registrar Conductor
                        </h1>
                        <p class="text-sm text-gray-500">Creación manual de operador. Nace verificado y activo.</p>
                    </div>
                </div>
                <button @click="submit" :disabled="form.processing" class="bg-black text-white px-6 py-3 rounded-lg flex items-center gap-2 hover:bg-gray-800 transition-colors">
                    <Save :size="18" /> Guardar Conductor
                </button>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border p-8 space-y-8">
                <div>
                    <h3 class="font-semibold flex items-center gap-2 mb-4 border-b pb-2">
                        <UserPlus :size="18" class="text-gray-400" /> Información Personal
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Nombres</label>
                            <input v-model="form.first_name" type="text" class="w-full border rounded-lg px-4 py-2 focus:ring-black focus:border-black" />
                            <p v-if="form.errors.first_name" class="text-xs text-red-500">{{ form.errors.first_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Apellidos</label>
                            <input v-model="form.last_name" type="text" class="w-full border rounded-lg px-4 py-2 focus:ring-black focus:border-black" />
                            <p v-if="form.errors.last_name" class="text-xs text-red-500">{{ form.errors.last_name }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold flex items-center gap-2 mb-4 border-b pb-2">
                        <ShieldCheck :size="18" class="text-gray-400" /> Credenciales (Silo Driver)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Teléfono (BO)</label>
                            <vue-tel-input 
                                v-model="form.phone" 
                                @on-input="onInput"
                                v-bind="telOptions"
                                class="w-full border rounded-lg h-[42px] font-mono focus-within:ring-1 focus-within:ring-black focus-within:border-black"
                                :class="{ 'border-red-500': form.errors.phone }"
                            />
                            <p v-if="form.errors.phone" class="text-xs text-red-500">{{ form.errors.phone }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Contraseña Inicial</label>
                            <input v-model="form.password" type="password" class="w-full border rounded-lg px-4 py-2 font-mono focus:ring-black focus:border-black" />
                            <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold flex items-center gap-2 mb-4 border-b pb-2">
                        <Truck :size="18" class="text-gray-400" /> Datos Operativos
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Número de Licencia</label>
                            <input v-model="form.license_number" type="text" class="w-full border rounded-lg px-4 py-2 font-mono focus:ring-black focus:border-black" />
                            <p v-if="form.errors.license_number" class="text-xs text-red-500">{{ form.errors.license_number }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Placa del Vehículo</label>
                            <input v-model="form.license_plate" type="text" class="w-full border rounded-lg px-4 py-2 font-mono focus:ring-black focus:border-black" />
                            <p v-if="form.errors.license_plate" class="text-xs text-red-500">{{ form.errors.license_plate }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Tipo de Vehículo</label>
                            <select v-model="form.vehicle_type" class="w-full border rounded-lg px-4 py-2 focus:ring-black focus:border-black">
                                <option value="moto">Motocicleta</option>
                                <option value="car">Automóvil</option>
                                <option value="truck">Camión de Carga</option>
                            </select>
                            <p v-if="form.errors.vehicle_type" class="text-xs text-red-500">{{ form.errors.vehicle_type }}</p>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
:deep(.vti__dropdown) { @apply bg-transparent px-3 border-r border-gray-200; }
:deep(.vti__input) { @apply bg-transparent; }
</style>