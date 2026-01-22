<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    
    const form = useForm({
        company_name: '',
        commercial_name: '',
        tax_id: '',
        internal_code: '',
        
        // Logística
        lead_time_days: 1,
        min_order_value: 0,
        
        // Finanzas
        credit_days: 0,
        credit_limit: 0,
        
        // Contacto
        contact_name: '',
        email_orders: '', // CORREGIDO: coincide con la DB
        phone: '',
        address: '',
        city: '',
        
        is_active: true,
        notes: ''
    });
    
    const submit = () => form.post(route('admin.providers.store'));
</script>

<template>
    <AdminLayout>
        <div class="max-w-5xl mx-auto">
            <h1 class="text-2xl font-bold text-white mb-6">Registrar Proveedor</h1>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg h-fit">
                    <h2 class="text-lg font-bold text-blue-400 mb-4 pb-2 border-b border-gray-700">Identidad Fiscal</h2>
                    
                    <div class="mb-4">
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Razón Social (Factura) *</label>
                        <input v-model="form.company_name" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-blue-500 outline-none uppercase">
                        <p v-if="form.errors.company_name" class="text-red-500 text-xs mt-1">{{ form.errors.company_name }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nombre Comercial</label>
                        <input v-model="form.commercial_name" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-blue-500 outline-none uppercase" placeholder="Ej: EMBOL">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">NIT / RUC *</label>
                            <input v-model="form.tax_id" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-blue-500 outline-none">
                            <p v-if="form.errors.tax_id" class="text-red-500 text-xs mt-1">{{ form.errors.tax_id }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Código ERP</label>
                            <input v-model="form.internal_code" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-blue-500 outline-none" placeholder="PRV-000">
                             <p v-if="form.errors.internal_code" class="text-red-500 text-xs mt-1">{{ form.errors.internal_code }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                         <label class="flex items-center space-x-2 cursor-pointer p-2 bg-gray-900 rounded border border-gray-700">
                            <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded text-blue-600 bg-gray-800 border-gray-600">
                            <span class="text-gray-300 font-bold text-sm">Proveedor Activo</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                        <h2 class="text-lg font-bold text-green-400 mb-4 pb-2 border-b border-gray-700">Condiciones Comerciales</h2>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Tiempo Entrega (Días)</label>
                                <input v-model="form.lead_time_days" type="number" min="0" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Mínimo de Compra (Bs)</label>
                                <input v-model="form.min_order_value" type="number" step="0.01" min="0" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-green-500 outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Días Crédito</label>
                                <input v-model="form.credit_days" type="number" min="0" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Límite Crédito (Bs)</label>
                                <input v-model="form.credit_limit" type="number" step="0.01" min="0" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-green-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 shadow-lg">
                        <h2 class="text-lg font-bold text-yellow-400 mb-4 pb-2 border-b border-gray-700">Contacto & Pedidos</h2>
                        
                        <div class="mb-4">
                            <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Email para Órdenes *</label>
                            <input v-model="form.email_orders" type="email" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-yellow-500 outline-none" placeholder="pedidos@empresa.com">
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nombre Contacto</label>
                                <input v-model="form.contact_name" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-yellow-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Teléfono</label>
                                <input v-model="form.phone" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-yellow-500 outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Ciudad</label>
                                <input v-model="form.city" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-yellow-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Dirección</label>
                                <input v-model="form.address" type="text" class="w-full bg-gray-900 border border-gray-600 text-white rounded p-2 focus:border-yellow-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 flex justify-end gap-4 mt-4">
                    <Link :href="route('admin.providers.index')" class="text-gray-400 hover:text-white font-bold py-3 px-6">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-10 rounded shadow-lg transition disabled:opacity-50">
                        Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>