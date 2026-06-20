<script setup>
    import ShopLayout from '@/Layouts/ShopLayout.vue';
    import { Head } from '@inertiajs/vue3';
    </script>
    
    <script>
    export default { layout: ShopLayout }
    </script>
    
    <template>
        <Head title="Política de Privacidad" />
        <div class="py-12 bg-white min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-gray-800">
                <h1 class="text-3xl font-black mb-8 border-b pb-4">Política de Privacidad</h1>
    
                <div class="space-y-6 text-sm leading-relaxed">
                    <section>
                        <h2 class="text-lg font-bold text-blue-900 mb-2">1. Datos Recolectados</h2>
                        <p>Recolectamos su número telefónico para la creación de cuenta y autenticación. Recolectamos datos de geolocalización (Latitud/Longitud) únicamente para calcular costos de envío y asignar la sucursal más cercana.</p>
                    </section>
    
                    <section>
                        <h2 class="text-lg font-bold text-blue-900 mb-2">2. Uso de la Información</h2>
                        <p>Sus datos son compartidos estrictamente con el personal operativo (Conductores y Almacén) para cumplir con la entrega del pedido. No vendemos sus datos a terceros.</p>
                    </section>
    
                    <section>
                        <h2 class="text-lg font-bold text-blue-900 mb-2">3. Seguridad</h2>
                        <p>Sus contraseñas son almacenadas mediante algoritmos de hash irreversibles. No almacenamos datos sensibles de tarjetas de crédito o débito en nuestros servidores.</p>
                    </section>
    
                    <p class="text-xs text-gray-500 mt-8">Última actualización: Enero 2026</p>
                </div>
            </div>
        </div>
    </template>