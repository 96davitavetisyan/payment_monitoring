<template>
    <div>
        <app-header></app-header>
        <div class="container-fluid mt-4 px-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Պայմանագրեր</h3>
                <button class="btn btn-success btn-sm" @click="showCreateModal = true">
                    + Ավելացնել
                </button>
            </div>

            <!-- Contracts Table -->
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered">
                    <thead class="table-dark">
                    <tr>
                        <th style="width: 120px;">Համար</th>
                        <th style="width: 150px;">Գործընկեր</th>
                        <th style="width: 150px;">Մեր ընկերություն</th>
                        <th style="width: 120px;">Ապրանք</th>
                        <th style="width: 90px;">Պայմանագրի սկիզբ</th>
                        <th style="width: 90px;">Պայմանագրի ավարտ</th>
                        <th style="width: 90px;">Վճարման օր</th>
                        <th style="width: 90px;">Վճարման վերջնական օր</th>
                        <th style="width: 90px;">Հաշվեհամար</th>
                        <th style="width: 80px;">Վճարման տեսակ</th>
                        <th style="width: 120px;">Գումար</th>
                        <th style="width: 90px;">Կարգավիճակ</th>
                        <th style="width: 150px;">Գործողություններ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="contract in contracts" :key="contract.id">
                        <td class="small">{{ contract.contract_number || '-' }}</td>
                        <td class="small">{{ contract.partner_company?.name || '-' }}</td>
                        <td class="small">{{ contract.own_company?.name || '-' }}</td>
                        <td class="small">{{ contract.product?.name || '-' }}</td>
                        <td class="small">{{ formatDate(contract.contract_start_date) }}</td>
                        <td class="small">{{ formatDate(contract.contract_end_date) || '-' }}</td>
                        <td class="small">{{ formatPaymentDate(contract.payment_date) || '-' }}</td>
                        <td class="small">{{ formatPaymentDate(contract.payment_finish_date) || '-' }}</td>
                        <td class="small">{{ contract.account_number || '-' }}</td>
                        <td>
                            {{ contract.payment_type === 'monthly' ? 'Ամենամյա' : (contract.payment_type === 'one_time' ? 'Միանվագ' : 'Տարեկան') }}
                        </td>
                        <td class="small">{{ formatAmount(contract.payment_amount) }}</td>
                        <td>
                            <span class="badge" :class="getStatusClass(contract.status)">
                                {{ getStatusLabel(contract.status) }}
                            </span>
                        </td>
                        <td>
                            <div class="" role="group">
                                <!-- Edit -->
                                <button class="btn btn-outline-primary" @click="editContract(contract)" title="Խմբագրել">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <!-- Transactions -->
                                <button class="btn btn-outline-info" @click="viewTransactions(contract.id)" title="Վճարումներ">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </button>

                                <!-- Delete -->
                                <button class="btn btn-outline-danger" @click="deleteContract(contract.id)" title="Ջնջել">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="contracts.length === 0">
                        <td colspan="10" class="text-center">Տվյալներ չկան</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Create/Edit Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showCreateModal }" style="background: rgba(0,0,0,0.5);" v-if="showCreateModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Պայմանագիր</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Գործընկեր *</label>
                                    <select class="form-select form-select-sm" :class="{'is-invalid': errors.partner_company_id}" v-model="currentContract.partner_company_id" required>
                                        <option value="">Ընտրել</option>
                                        <option v-for="partner in partnerCompanies" :key="partner.id" :value="partner.id">
                                            {{ partner.name }}
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="errors.partner_company_id">
                                        {{ errors.partner_company_id[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Մեր ընկերություն *</label>
                                    <select class="form-select form-select-sm" :class="{'is-invalid': errors.own_company_id}" v-model="currentContract.own_company_id" required>
                                        <option value="">Ընտրել</option>
                                        <option v-for="own in ownCompanies" :key="own.id" :value="own.id">
                                            {{ own.name }}
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="errors.own_company_id">
                                        {{ errors.own_company_id[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ապրանք *</label>
                                    <select class="form-select form-select-sm" :class="{'is-invalid': errors.product_id}" v-model="currentContract.product_id" required>
                                        <option value="">Ընտրել</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">
                                            {{ product.name }}
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="errors.product_id">
                                        {{ errors.product_id[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Պայմանագրի համար</label>
                                    <input type="text" class="form-control form-control-sm" :class="{'is-invalid': errors.contract_number}" v-model="currentContract.contract_number">
                                    <div class="invalid-feedback" v-if="errors.contract_number">
                                        {{ errors.contract_number[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Պայմանագրի սկիզբ *</label>
                                    <DatePicker class="w-100" :class="{'is-invalid': errors.contract_start_date}" v-model="currentContract.contract_start_date" format="DD-MM-YYYY" placeholder="Ընտրել" value-type="format" required/>
                                    <div class="invalid-feedback" v-if="errors.contract_start_date">
                                        {{ errors.contract_start_date[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Պայմանագրի ավարտ</label>
                                    <DatePicker class="w-100" :class="{'is-invalid': errors.contract_end_date}" v-model="currentContract.contract_end_date" format="DD-MM-YYYY" placeholder="Ընտրել" value-type="format" required/>
                                    <div class="invalid-feedback" v-if="errors.contract_end_date">
                                        {{ errors.contract_end_date[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Վճարման տեսակ *</label>
                                    <select class="form-select form-select-sm" :class="{'is-invalid': errors.payment_type}" v-model="currentContract.payment_type" required>
                                        <option value="one_time">Միանվագ</option>
                                        <option value="monthly">Ամենամյա</option>
                                        <option value="yearly">Տարեկան</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="errors.payment_type">
                                        {{ errors.payment_type[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3" v-if="isOneTimePayment">
                                    <label class="form-label">Վճարման օր</label>
                                    <input type="number" min="1" max="31" class="form-control form-control-sm" :class="{'is-invalid': errors.payment_date}" v-model="currentContract.payment_date">
                                    <div class="invalid-feedback" v-if="errors.payment_date">
                                        {{ errors.payment_date[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3" v-if="isOneTimePayment">
                                    <label class="form-label">Վճարման վերջնական օր</label>
                                    <input type="number" min="1" max="31" class="form-control form-control-sm" :class="{'is-invalid': errors.payment_finish_date}" v-model="currentContract.payment_finish_date">
                                    <div class="invalid-feedback" v-if="errors.payment_finish_date">
                                        {{ errors.payment_finish_date[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Գումար *</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" :class="{'is-invalid': errors.payment_amount}" v-model="currentContract.payment_amount" required>
                                    <div class="invalid-feedback" v-if="errors.payment_amount">
                                        {{ errors.payment_amount[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Հաշվեհամար *</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" :class="{'is-invalid': errors.account_number}" v-model="currentContract.account_number" required>
                                    <div class="invalid-feedback" v-if="errors.account_number">
                                        {{ errors.account_number[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Կարգավիճակ *</label>
                                    <select class="form-select form-select-sm" :class="{'is-invalid': errors.status}" v-model="currentContract.status" required>
                                        <option value="active">Ակտիվ</option>
                                        <option value="completed">Ավարտված</option>
                                        <option value="cancelled">Չեղարկված</option>
                                        <option value="suspended">Կասեցված</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="errors.status">
                                        {{ errors.status[0] }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Պայմանագիր</label>
                                    <input type="file" class="form-control form-control-sm" :class="{'is-invalid': errors.contract_file}" @change="handleFileChange" ref="contractFile">
                                    <small v-if="currentContract.contract_file" class="text-muted">
                                        {{ getFileName(currentContract.contract_file) }}
                                    </small>
                                    <div class="invalid-feedback" v-if="errors.contract_file">
                                        {{ errors.contract_file[0] }}
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Նշումներ</label>
                                    <textarea class="form-control form-control-sm" rows="2" v-model="currentContract.notes"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary btn-sm" @click="closeModal">Չեղարկել</button>
                            <button class="btn btn-success btn-sm" @click="saveContract">{{ isEditing ? 'Թարմացնել' : 'Ստեղծել' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import Swal from 'sweetalert2';
import dateMixin from '../../mixins/dateMixin';
import DatePicker from 'vue2-datepicker';

export default {
    mixins: [dateMixin],
    components: { DatePicker },
    data() {
        return {
            contracts: [],
            partnerCompanies: [],
            ownCompanies: [],
            products: [],
            showCreateModal: false,
            isEditing: false,
            selectedFile: null,
            errors: {},
            currentContract: {
                partner_company_id: '',
                own_company_id: '',
                product_id: '',
                contract_number: '',
                contract_start_date: '',
                contract_end_date: '',
                payment_date : '',
                payment_finish_date: '',
                payment_type: 'monthly',
                payment_amount: '',
                account_number: '',
                status: 'active',
                notes: ''
            }
        };
    },
    mounted() {
        this.fetchContracts();
        this.fetchPartnerCompanies();
        this.fetchOwnCompanies();
        this.fetchProducts();
    },
    computed: {
        isOneTimePayment() {
            return this.currentContract.payment_type !== 'one_time';
        }
    },
    methods: {
        async fetchContracts() {
            try {
                const response = await axios.get('/api/contracts');
                this.contracts = response.data.data;
            } catch (error) {
                console.error('Error fetching contracts:', error);
                alert('Failed to load contracts');
            }
        },
        async fetchPartnerCompanies() {
            try {
                const response = await axios.get('/api/partner-companies');
                this.partnerCompanies = response.data.data;
            } catch (error) {
                console.error('Error fetching partner companies:', error);
            }
        },
        async fetchOwnCompanies() {
            try {
                const response = await axios.get('/api/own-companies');
                this.ownCompanies = response.data.data;
            } catch (error) {
                console.error('Error fetching own companies:', error);
            }
        },
        async fetchProducts() {
            try {
                const response = await axios.get('/api/products');
                this.products = response.data.data;
            } catch (error) {
                console.error('Error fetching products:', error);
            }
        },
        handleFileChange(event) {
            this.selectedFile = event.target.files[0];
        },
        editContract(contract) {
            this.currentContract = {
                id: contract.id,
                partner_company_id: contract.partner_company_id,
                own_company_id: contract.own_company_id,
                product_id: contract.product_id,
                contract_number: contract.contract_number,
                contract_start_date: this.formatDate(contract.contract_start_date),
                contract_end_date: this.formatDate(contract.contract_end_date),
                payment_date : contract.payment_date ,
                payment_finish_date: contract.payment_finish_date,
                payment_type: contract.payment_type,
                payment_amount: contract.payment_amount,
                account_number: contract.account_number,
                status: contract.status,
                notes: contract.notes,
                contract_file: contract.contract_file
            };
            this.isEditing = true;
            this.showCreateModal = true;
        },
        async saveContract() {
            console.log(this.isEditing,"ewfd")
            try {
                const formData = new FormData();

                Object.keys(this.currentContract).forEach(key => {
                    if (this.currentContract[key] !== null && this.currentContract[key] !== '' && key !== 'contract_file') {
                        formData.append(key, this.currentContract[key]);
                    }
                });

                if (this.selectedFile) {
                    formData.append('contract_file', this.selectedFile);
                }

                if (this.isEditing) {
                    formData.append('_method', 'PUT');
                    await axios.post(`/api/contracts/${this.currentContract.id}`, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                } else {
                    await axios.post('/api/contracts', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                }

                this.fetchContracts();
                this.closeModal();
                Swal.fire({
                    icon: 'success',
                    title: this.isEditing ? 'Պայմանագիրը թարմացվեց' : 'Պայմանագիր ստեղծվեց',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
                this.isEditing = false;

            } catch (error) {
                console.error('Error saving contract:', error);

                // Handle validation errors
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};

                    Swal.fire({
                        icon: 'error',
                        title: 'Սխալ',
                        text: 'Խնդրում ենք ստուգել լրացված դաշտերը',
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Սխալ',
                        text: 'Չհաջողվեց պահպանել պայմանագիրը։ ' + (error.response?.data?.message || error.message),
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            }
        },
        async deleteContract(id) {
            const result = await Swal.fire({
                title: 'Ջնջե՞լ պայմանագիրը',
                text: 'Վստա՞հ եք, որ ցանկանում եք ջնջել այս պայմանագիրը։',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Այո, ջնջել',
                cancelButtonText: 'Չեղարկել'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/api/contracts/${id}`);
                    this.fetchContracts();

                    Swal.fire({
                        icon: 'success',
                        title: 'Ջնջված է',
                        text: 'Պայմանագիրը հաջողությամբ ջնջվեց։',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });

                } catch (error) {
                    console.error('Error deleting contract:', error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Սխալ',
                        text: 'Չհաջողվեց ջնջել պայմանագիրը։',
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            }
        },

        viewTransactions(contractId) {
            this.$router.push({ name: 'contract-transactions', params: { contractId } });
        },
        closeModal() {
            this.showCreateModal = false;
            this.selectedFile = null;
            this.errors = {};
            this.currentContract = {
                partner_company_id: '',
                own_company_id: '',
                product_id: '',
                contract_number: '',
                contract_start_date: '',
                contract_end_date: '',
                payment_date : '',
                payment_finish_date: '',
                payment_type: 'monthly',
                payment_amount: '',
                account_number: '',
                status: 'active',
                notes: ''
            };
            if (this.$refs.contractFile) {
                this.$refs.contractFile.value = '';
            }
        },
        formatAmount(amount) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount) + ' ֏';
        },
        formatPaymentDate(day) {
            if(day){
                const today = new Date();

                const year = today.getFullYear();
                const month = today.getMonth();

                let date = new Date(year, month, day);

                if (date < today) {
                    date = new Date(year, month + 1, day);
                }

                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');

                return `${dd}-${mm}-${yyyy}`;
            }

            return `-`;
        },
        getStatusClass(status) {
            const classes = {
                'active': 'bg-success',
                'completed': 'bg-primary',
                'cancelled': 'bg-danger',
                'suspended': 'bg-warning text-dark'
            };
            return classes[status] || 'bg-secondary';
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
        getFileName(path) {
            return path ? path.split('/').pop() : '';
        }
    }
};
</script>

<style scoped>
.table-sm th, .table-sm td {
    padding: 0.4rem;
    font-size: 0.875rem;
}
.btn-group-sm > .btn {
    padding: 0.15rem 0.4rem;
    font-size: 0.75rem;
}
</style>
