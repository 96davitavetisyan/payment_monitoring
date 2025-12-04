<template>
    <div class="modal" tabindex="-1" :class="{ 'show d-block': show }" style="background: rgba(0,0,0,0.5);" v-if="show">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Պայմանագրեր - {{ title }}
                        <span class="badge bg-primary ms-2">{{ contracts.length }}</span>
                    </h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>
                <div class="modal-body">
                    <div v-if="contracts.length === 0" class="alert alert-info">
                        Պայմանագրեր չեն գտնվել
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-sm table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Համար</th>
                                    <th>Գործընկեր</th>
                                    <th>Մեր ընկերություն</th>
                                    <th>Ապրանք</th>
                                    <th>Սկիզբ</th>
                                    <th>Ավարտ</th>
                                    <th>Տեսակ</th>
                                    <th>Գումար</th>
                                    <th>Կարգավիճակ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="contract in contracts" :key="contract.id">
                                    <td>{{ contract.contract_number || '-' }}</td>
                                    <td>{{ contract.partner_company?.name || '-' }}</td>
                                    <td>{{ contract.own_company?.name || '-' }}</td>
                                    <td>{{ contract.product?.name || '-' }}</td>
                                    <td>{{ formatDate(contract.contract_start_date) }}</td>
                                    <td>{{ formatDate(contract.contract_end_date) || '-' }}</td>
                                    <td>
                                        <span class="badge bg-info" style="font-size: 10px;">
                                            {{ getPaymentTypeLabel(contract.payment_type) }}
                                        </span>
                                    </td>
                                    <td>{{ formatAmount(contract.payment_amount) }}</td>
                                    <td>
                                        <span class="badge" :class="getStatusClass(contract.status)" style="font-size: 10px;">
                                            {{ getStatusLabel(contract.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="$emit('close')">Փակել</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import dateMixin from '../../mixins/dateMixin';

export default {
    name: 'ContractsModal',
    mixins: [dateMixin],
    props: {
        show: {
            type: Boolean,
            default: false
        },
        title: {
            type: String,
            default: ''
        },
        contracts: {
            type: Array,
            default: () => []
        }
    },
    methods: {
        formatAmount(amount) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount) + ' ֏';
        },
        getPaymentTypeLabel(type) {
            const labels = {
                'monthly': 'Ամենամյա',
                'yearly': 'Տարեկան',
                'one_time': 'Միանվագ'
            };
            return labels[type] || type;
        },
        getStatusLabel(status) {
            const labels = {
                'active': 'Ակտիվ',
                'completed': 'Ավարտված',
                'cancelled': 'Չեղարկված',
                'suspended': 'Կասեցված'
            };
            return labels[status] || status;
        },
        getStatusClass(status) {
            const classes = {
                'active': 'bg-success',
                'completed': 'bg-primary',
                'cancelled': 'bg-danger',
                'suspended': 'bg-warning text-dark'
            };
            return classes[status] || 'bg-secondary';
        }
    }
};
</script>

<style scoped>
.table-sm th, .table-sm td {
    padding: 0.4rem;
    font-size: 0.875rem;
}
</style>
