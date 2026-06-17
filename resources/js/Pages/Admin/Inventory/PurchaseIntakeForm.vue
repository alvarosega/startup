<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
  branches: { type: Array, required: true },
  providers: { type: Array, required: true },
  skus: { type: Array, required: true }
});

const form = reactive({
  branch_id: '',
  provider_id: '',
  document_number: '',
  purchase_date: new Date().toISOString().slice(0, 10),
  payment_type: 'CASH',
  items: []
});

const errorMessage = ref('');
const successMessage = ref('');

const addItem = () => {
  form.items.push({ sku_id: '', quantity: 1.000, lot_code: '', expiration_date: null });
};

const removeItem = (index) => {
  form.items.splice(index, 1);
};

const submitForm = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  if (form.items.length === 0) {
    errorMessage.value = 'Debe añadir al menos un ítem a la compra.';
    return;
  }

  try {
    const response = await axios.post(route('admin.purchases.process'), form);
    successMessage.value = `Compra registrada exitosamente. ID: ${response.data.id}`;
    Object.assign(form, { branch_id: '', provider_id: '', document_number: '', items: [] });
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Error en el procesamiento del ingreso.';
  }
};
</script>

<template>
  <div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Recepción de Compra (Ingreso de Inventario)</h2>

    <div v-if="errorMessage" class="p-3 mb-4 bg-red-100 text-red-700 rounded">{{ errorMessage }}</div>
    <div v-if="successMessage" class="p-3 mb-4 bg-green-100 text-green-700 rounded">{{ successMessage }}</div>

    <form @submit.prevent="submitForm">
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-sm font-medium">Sucursal Destino</label>
          <select v-model="form.branch_id" class="w-full mt-1 border rounded p-2" required>
            <option value="">Seleccione una sucursal</option>
            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">Proveedor</label>
          <select v-model="form.provider_id" class="w-full mt-1 border rounded p-2" required>
            <option value="">Seleccione un proveedor</option>
            <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">Número de Factura / Documento</label>
          <input type="text" v-model="form.document_number" class="w-full mt-1 border rounded p-2" required />
        </div>
        <div>
          <label class="block text-sm font-medium">Tipo de Pago</label>
          <select v-model="form.payment_type" class="w-full mt-1 border rounded p-2">
            <option value="CASH">Efectivo</option>
            <option value="CREDIT">Crédito</option>
          </select>
        </div>
      </div>

      <div class="border-t pt-4">
        <div class="flex justify-between items-center mb-2">
          <h3 class="font-semibold text-lg">Ítems de la Compra</h3>
          <button type="button" @click="addItem" class="bg-blue-600 text-white px-3 py-1 rounded text-sm">+ Añadir Producto</button>
        </div>

        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b bg-gray-50 text-sm">
              <th class="p-2">Producto (SKU)</th>
              <th class="p-2">Cantidad</th>
              <th class="p-2">Código Lote</th>
              <th class="p-2">F. Vencimiento</th>
              <th class="p-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in form.items" :key="index" class="border-b">
              <td class="p-2">
                <select v-model="item.sku_id" class="w-full border rounded p-1" required>
                  <option value="">Seleccione SKU</option>
                  <option v-for="s in skus" :key="s.id" :value="s.id">{{ s.name }} ({{ s.code }})</option>
                </select>
              </td>
              <td class="p-2">
                <input type="number" step="0.001" v-model.number="item.quantity" class="w-24 border rounded p-1" required />
              </td>
              <td class="p-2">
                <input type="text" v-model="item.lot_code" class="w-full border rounded p-1" required />
              </td>
              <td class="p-2">
                <input type="date" v-model="item.expiration_date" class="border rounded p-1" />
              </td>
              <td class="p-2 text-right">
                <button type="button" @click="removeItem(index)" class="text-red-600 font-bold">X</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-6 text-right">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded font-semibold">Procesar Ingreso Físico</button>
      </div>
    </form>
  </div>
</template>