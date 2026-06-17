<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  transferId: { type: String, required: true }
});

const transferData = ref(null);
const form = reactive({ items: [] });
const errorMessage = ref('');
const successMessage = ref('');

onMounted(async () => {
  try {
    const response = await axios.post(route('admin.transfers.reception', { id: props.transferId }), form);
    transferData.value = response.data;
    form.items = response.data.items.map(item => ({
      sku_id: item.sku_id,
      name: item.sku_name || item.sku_id,
      qty_sent: item.qty_sent,
      qty_received: item.qty_sent 
    }));
  } catch (error) {
    errorMessage.value = 'No se pudo cargar la información de la transferencia.';
  }
});

const submitForm = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const response = await axios.post(route('admin.transfers.reception', { id: props.transferId }), form);
    successMessage.value = 'Transferencia recibida y procesada en el balance.';
    transferData.value.status = 'completed';
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Error al procesar la recepción.';
  }
};
</script>

<template>
  <div v-if="transferData" class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-2">Recepción de Transferencia Inter-Sucursal</h2>
    <p class="text-sm text-gray-600 mb-4">Código único de control: <strong>{{ transferData.code }}</strong></p>

    <div v-if="errorMessage" class="p-3 mb-4 bg-red-100 text-red-700 rounded">{{ errorMessage }}</div>
    <div v-if="successMessage" class="p-3 mb-4 bg-green-100 text-green-700 rounded">{{ successMessage }}</div>

    <form v-if="transferData.status === 'in_transit'" @submit.prevent="submitForm">
      <table class="w-full text-left border-collapse mb-4">
        <thead>
          <tr class="border-b bg-gray-50 text-sm">
            <th class="p-2">Producto (SKU)</th>
            <th class="p-2 text-center">Cantidad Enviada</th>
            <th class="p-2 text-center">Cantidad Recibida</th>
            <th class="p-2 text-center">Discrepancia (Scrap)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in form.items" :key="index" class="border-b">
            <td class="p-2 font-medium">{{ item.name }}</td>
            <td class="p-2 text-center bg-gray-50">{{ item.qty_sent }}</td>
            <td class="p-2 text-center">
              <input type="number" step="0.001" :max="item.qty_sent" min="0" v-model.number="item.qty_received" class="w-24 border text-center rounded p-1" required />
            </td>
            <td class="p-2 text-center font-semibold" :class="item.qty_sent - item.qty_received > 0 ? 'text-red-600' : 'text-gray-500'">
              {{ (item.qty_sent - item.qty_received).toFixed(3) }}
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4 p-3 bg-yellow-50 text-yellow-800 rounded text-sm mb-4">
        * Nota: Cualquier discrepancia numérica detectada entre la cantidad enviada y recibida será absorbida automáticamente como una baja definitiva (OUT_SCRAP) por pérdida en el trayecto en la sucursal de origen.
      </div>

      <div class="text-right">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded font-semibold">Cerrar y Confirmar Recepción</button>
      </div>
    </form>

    <div v-else class="p-4 bg-blue-50 text-blue-800 rounded">
      Esta transferencia ya fue procesada y se encuentra en estado: <strong>{{ transferData.status.toUpperCase() }}</strong>.
    </div>
  </div>
</template>