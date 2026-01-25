<!-- resources/js/Components/ProviderCard.vue -->
<script setup>
    import { Building2, Clock, DollarSign, CreditCard, Mail, Phone, MapPin, Edit, Trash2 } from 'lucide-vue-next';
    import { Link } from '@inertiajs/vue3';
    
    const props = defineProps({
        provider: {
            type: Object,
            required: true
        },
        canManage: {
            type: Boolean,
            default: false
        }
    });
    
    const emit = defineEmits(['delete']);
    
    const handleDelete = () => {
        if (confirm(`¿Eliminar a ${props.provider.commercial_name || props.provider.company_name}?`)) {
            emit('delete', props.provider.id);
        }
    };
    </script>
    
    <template>
        <div class="group card hover:shadow-md hover:border-primary/30 transition-all duration-300 flex flex-col relative overflow-hidden">
            
            <!-- Active/Inactive indicator -->
            <div class="absolute top-0 left-0 w-1 h-full" 
                 :class="provider.is_active ? 'bg-success' : 'bg-error'"></div>
    
            <div class="card-header">
                <div class="flex justify-between items-start">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded bg-muted text-primary shrink-0">
                            <Building2 :size="20" />
                        </div>
                        <div>
                            <h3 class="font-bold text-foreground text-lg leading-tight">
                                {{ provider.commercial_name || provider.company_name }}
                            </h3>
                            <p v-if="provider.commercial_name" class="text-xs text-muted-foreground mt-0.5 font-medium">
                                {{ provider.company_name }}
                            </p>
                            <div class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-muted border border-border text-muted-foreground">
                                NIT: {{ provider.tax_id }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="card-content space-y-4">
                
                <!-- Business info -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-muted/50 p-2 rounded border border-border/50">
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground mb-1">
                            <Clock :size="12" /> Tiempo Entrega
                        </div>
                        <div class="font-bold text-foreground text-sm">{{ provider.lead_time_days }} días</div>
                    </div>
                    <div class="bg-muted/50 p-2 rounded border border-border/50">
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground mb-1">
                            <DollarSign :size="12" /> Min. Compra
                        </div>
                        <div class="font-bold text-foreground text-sm">Bs {{ provider.min_order_value }}</div>
                    </div>
                </div>
    
                <!-- Credit info -->
                <div class="flex items-center gap-2 text-xs">
                    <CreditCard :size="14" class="text-muted-foreground" />
                    <span v-if="provider.credit_days > 0" class="text-success font-bold">
                        Crédito: {{ provider.credit_days }} días (Bs {{ provider.credit_limit }})
                    </span>
                    <span v-else class="text-error font-bold">Pago al Contado</span>
                </div>
    
                <!-- Contact info -->
                <div class="pt-3 border-t border-border/50 space-y-2">
                    <div v-if="provider.contact_name" class="text-xs font-bold text-foreground">
                        {{ provider.contact_name }}
                    </div>
                    <div v-if="provider.email_orders" class="flex items-center gap-2 text-xs text-muted-foreground truncate hover:text-primary transition-colors cursor-pointer" title="Copiar correo">
                        <Mail :size="14" /> {{ provider.email_orders }}
                    </div>
                    <div v-if="provider.phone" class="flex items-center gap-2 text-xs text-muted-foreground">
                        <Phone :size="14" /> {{ provider.phone }}
                    </div>
                </div>
            </div>
    
            <!-- Footer -->
            <div class="card-footer bg-muted/30 flex justify-between items-center">
                <div class="text-[10px] font-mono text-muted-foreground">
                    ERP: {{ provider.internal_code || 'N/A' }}
                </div>
                
                <div v-if="canManage" class="flex items-center gap-2">
                    <Link :href="route('admin.providers.edit', provider.id)" 
                          class="p-1.5 rounded text-muted-foreground hover:text-primary hover:bg-primary/10 transition-colors"
                          title="Editar">
                        <Edit :size="16" />
                    </Link>
                    <button @click="handleDelete" 
                            class="p-1.5 rounded text-muted-foreground hover:text-error hover:bg-error/10 transition-colors"
                            title="Eliminar">
                        <Trash2 :size="16" />
                    </button>
                </div>
            </div>
        </div>
    </template>