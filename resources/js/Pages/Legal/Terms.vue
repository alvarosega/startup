<script setup>
    import ShopLayout from '@/Layouts/ShopLayout.vue';
    import { Head } from '@inertiajs/vue3';
    </script>
    
    <script>
    export default { layout: ShopLayout }
    </script>
    
    <template>
        <Head title="Términos de Servicio" />
        <div class="py-12 bg-white min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-gray-800">
                <h1 class="text-3xl font-black mb-8 border-b pb-4">Términos y Condiciones de Uso</h1>
    
                <div class="space-y-6 text-sm leading-relaxed">
                    <section>
                        <h2 class="text-lg font-bold text-blue-900 mb-2">1. Aceptación</h2>
                        <p>Al registrarse y utilizar BoliviaLogistics, usted declara ser mayor de 18 años. La venta de alcohol a menores está prohibida y sancionada por ley. Nos reservamos el derecho de solicitar identificación oficial al momento de la entrega.</p>
                    </section>
    
                    <section>
                        <h2 class="text-lg font-bold text-blue-900 mb-2">2. Reservas y Pagos</h2>
                        <p>El sistema opera bajo un modelo de "Reserva Temporal". Al iniciar un pago, el stock se bloquea por un máximo de <strong>5 minutos</strong>. Si no se valida el comprobante de pago dentro de este lapso, la orden será cancelada automáticamente y el stock liberado.</p>
                    </section>
    
                    <section>
                        <h2 class="text-lg font-bold text-blue-900 mb-2">3. Política de Reembolsos</h2>
                        <p>Si usted realiza el pago después de expirado el tiempo de reserva y el producto ya no está disponible (Stock Agotado), el monto quedará como saldo a favor o será devuelto, descontando los costos administrativos bancarios aplicables.</p>
                    </section>
    
                    <section>
                        <h2 class="text-lg font-bold text-blue-900 mb-2">4. Limitación de Responsabilidad</h2>
                        <p>BoliviaLogistics actúa como intermediario tecnológico y logístico. No nos hacemos responsables por el consumo irresponsable de los productos adquiridos.</p>
                    </section>
                    
                    <p class="text-xs text-gray-500 mt-8">Última actualización: Enero 2026</p>
                </div>
            </div>
        </div>
    </template>