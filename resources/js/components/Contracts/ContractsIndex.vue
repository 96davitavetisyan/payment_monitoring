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
                        <th style="width: 90px;">Սկիզբ</th>
                        <th style="width: 90px;">Ավարտ</th>
                        <th style="width: 80px;">Տեսակ</th>
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
                        <td class="small">{{ formatDateShort(contract.contract_start_date) }}</td>
                        <td class="small">{{ formatDateShort(contract.contract_end_date) || '-' }}</td>
                        <td>
                            <span class="badge bg-info" style="font-size: 10px;">
                                {{ contract.payment_type === 'monthly' ? 'Monthly' : 'One-time' }}
                            </span>
                        </td>
                        <td class="small">{{ formatAmount(contract.payment_amount) }}</td>
                        <td>
                            <span class="badge" :class="getStatusClass(contract.status)" style="font-size: 10px;">
                                {{ getStatusLabel(contract.status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" @click="editContract(contract)" title="Խմբագրել">
                                    ✏️
                                </button>
                                <button class="btn btn-outline-info" @click="viewTransactions(contract.id)" title="Վճարումներ">
                                    💰
                                </button>
                                <button class="btn btn-outline-danger" @click="deleteContract(contract.id)" title="Ջնջել">
                                    🗑️
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
                                    <select class="form-select form-select-sm" v-model="currentContract.partner_company_id" required>
                                        <option value="">Ընտրել</option>
                                        <option v-for="partner in partnerCompanies" :key="partner.id" :value="partner.id">
                                            {{ partner.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Մեր ընկերություն *</label>
                                    <select class="form-select form-select-sm" v-model="currentContract.own_company_id" required>
                                        <option value="">Ընտրել</option>
                                        <option v-for="own in ownCompanies" :key="own.id" :value="own.id">
                                            {{ own.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ապրանք *</label>
                                    <select class="form-select form-select-sm" v-model="currentContract.product_id" required>
                                        <option value="">Ընտրել</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">
                                            {{ product.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Համար</label>
                                    <input type="text" class="form-control form-control-sm" v-model="currentContract.contract_number">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Սկիզբ *</label>
                                    <input type="date" class="form-control form-control-sm" v-model="currentContract.contract_start_date" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ավարտ</label>
                                    <input type="date" class="form-control form-control-sm" v-model="currentContract.contract_end_date">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Վճարման տեսակ *</label>
                                    <select class="form-select form-select-sm" v-model="currentContract.payment_type" required>
                                        <option value="one-time">One-time</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Գումար *</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" v-model="currentContract.payment_amount" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Կարգավիճակ *</label>
                                    <select class="form-select form-select-sm" v-model="currentContract.status" required>
                                        <option value="active">Active</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ֆայլ</label>
                                    <input type="file" class="form-control form-control-sm" @change="handleFileChange" ref="contractFile">
                                    <small v-if="currentContract.contract_file" class="text-muted">
                                        {{ getFileName(currentContract.contract_file) }}
                                    </small>
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
export default {
    data() {
        return {
            contracts: [],
            partnerCompanies: [],
            ownCompanies: [],
            products: [],
            showCreateModal: false,
            isEditing: false,
            selectedFile: null,
            currentContract: {
                partner_company_id: '',
                own_company_id: '',
                product_id: '',
                contract_number: '',
                contract_start_date: '',
                contract_end_date: '',
                payment_type: 'monthly',
                payment_amount: '',
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
                contract_start_date: contract.contract_start_date,
                contract_end_date: contract.contract_end_date,
                payment_type: contract.payment_type,
                payment_amount: contract.payment_amount,
                status: contract.status,
                notes: contract.notes,
                contract_file: contract.contract_file
            };
            this.isEditing = true;
            this.showCreateModal = true;
        },
        async saveContract() {
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
                alert(this.isEditing ? 'Contract updated successfully' : 'Contract created successfully');
            } catch (error) {
                console.error('Error saving contract:', error);
                alert('Failed to save contract: ' + (error.response?.data?.message || error.message));
            }
        },
        async deleteContract(id) {
            if (confirm('Are you sure you want to delete this contract?')) {
                try {
                    await axios.delete(`/api/contracts/${id}`);
                    this.fetchContracts();
                    alert('Contract deleted successfully');
                } catch (error) {
                    console.error('Error deleting contract:', error);
                    alert('Failed to delete contract');
                }
            }
        },
        viewTransactions(contractId) {
            this.$router.push({ name: 'contract-transactions', params: { contractId } });
        },
        closeModal() {
            this.showCreateModal = false;
            this.isEditing = false;
            this.selectedFile = null;
            this.currentContract = {
                partner_company_id: '',
                own_company_id: '',
                product_id: '',
                contract_number: '',
                contract_start_date: '',
                contract_end_date: '',
                payment_type: 'monthly',
                payment_amount: '',
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
        formatDateShort(date) {
            if (!date) return null;
            const d = new Date(date);
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${month}/${day}/${d.getFullYear()}`;
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
                'active': 'Active',
                'completed': 'Done',
                'cancelled': 'Cancel',
                'suspended': 'Suspend'
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
