<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Companies Management</h2>
                <button class="btn btn-success" @click="openCreateModal">
                    Add Company
                </button>
            </div>

            <!-- Companies List -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>Company Name</th>
                            <th>Product</th>
                            <th>Contact Person</th>
                            <th>Contact Email</th>
                            <th>Contact Phone</th>
                            <th>Subscription Status</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="company in companies" :key="company.id">
                            <td>{{ company.name }}</td>
                            <td>
                                <span v-if="company.product">{{ company.product.name }}</span>
                                <span v-else class="text-muted">N/A</span>
                            </td>
                            <td>{{ company.contact_person || 'N/A' }}</td>
                            <td>{{ company.contact_email || 'N/A' }}</td>
                            <td>{{ company.contact_phone || 'N/A' }}</td>
                            <td>
                                <span v-if="company.active_subscription"
                                      class="badge"
                                      :class="getSubscriptionClass(company.active_subscription.status)">
                                    {{ company.active_subscription.status }}
                                    (${{ parseFloat(company.active_subscription.price_per_month).toFixed(2) }}/mo)
                                </span>
                                <span v-else class="badge bg-secondary">No Subscription</span>
                            </td>
                            <td>
                                <span class="badge" :class="company.is_active ? 'bg-success' : 'bg-secondary'">
                                    {{ company.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary me-1" @click="editCompany(company)">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-info me-1" @click="viewTransactions(company)">
                                    Transactions
                                </button>
                                <button class="btn btn-sm btn-danger" @click="deleteCompany(company.id)">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="companies.length === 0">
                            <td colspan="8" class="text-center text-muted">No companies found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Company Modal -->
            <div v-if="showModal" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Edit Company' : 'Add Company' }}</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Company Name *</label>
                                    <input type="text" class="form-control" v-model="currentCompany.name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Product *</label>
                                    <select class="form-select" v-model="currentCompany.product_id" required>
                                        <option value="">Select Product</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">
                                            {{ product.name }} - ${{ parseFloat(product.monthly_price).toFixed(2) }}/mo
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Person</label>
                                    <input type="text" class="form-control" v-model="currentCompany.contact_person">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" v-model="currentCompany.contact_email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Phone</label>
                                    <input type="text" class="form-control" v-model="currentCompany.contact_phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" v-model="currentCompany.is_active">
                                        <option :value="true">Active</option>
                                        <option :value="false">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeModal">Cancel</button>
                            <button class="btn btn-primary" @click="saveCompany" :disabled="!canSave">
                                {{ isEditing ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Modal -->
            <div v-if="showTransactionsModal" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Transactions for {{ selectedCompany?.name }}</h5>
                            <button type="button" class="btn-close" @click="closeTransactionsModal"></button>
                        </div>
                        <div class="modal-body">
                            <div v-if="loadingTransactions" class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div v-else>
                                <p class="text-muted mb-3">
                                    Total Transactions: {{ companyTransactions.length }}
                                </p>
                                <div v-if="companyTransactions.length === 0" class="alert alert-info">
                                    No transactions found for this company.
                                </div>
                                <div class="table-responsive" v-else>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Project</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="transaction in companyTransactions" :key="transaction.id">
                                                <td>{{ formatDate(transaction.transaction_date) }}</td>
                                                <td>{{ transaction.project?.name || 'N/A' }}</td>
                                                <td>${{ parseFloat(transaction.amount).toFixed(2) }}</td>
                                                <td>
                                                    <span class="badge" :class="getPaymentStatusClass(transaction.payment_status)">
                                                        {{ transaction.payment_status }}
                                                    </span>
                                                </td>
                                                <td>{{ transaction.transaction_type }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeTransactionsModal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            companies: [],
            products: [],
            showModal: false,
            isEditing: false,
            currentCompany: this.getEmptyCompany(),
            showTransactionsModal: false,
            selectedCompany: null,
            companyTransactions: [],
            loadingTransactions: false
        };
    },
    computed: {
        canSave() {
            return this.currentCompany.name && this.currentCompany.product_id;
        }
    },
    mounted() {
        this.fetchCompanies();
        this.fetchProducts();
    },
    methods: {
        getEmptyCompany() {
            return {
                name: '',
                product_id: '',
                contact_person: '',
                contact_email: '',
                contact_phone: '',
                is_active: true
            };
        },
        fetchCompanies() {
            axios.get('/api/companies')
                .then(res => {
                    this.companies = res.data.success ? res.data.data : res.data;
                })
                .catch(err => {
                    console.error('Error fetching companies:', err);
                    alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                });
        },
        fetchProducts() {
            axios.get('/api/products')
                .then(res => {
                    this.products = res.data.success ? res.data.data : res.data;
                })
                .catch(err => {
                    console.error('Error fetching products:', err);
                });
        },
        openCreateModal() {
            this.isEditing = false;
            this.currentCompany = this.getEmptyCompany();
            this.showModal = true;
        },
        editCompany(company) {
            this.isEditing = true;
            this.currentCompany = { ...company };
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.isEditing = false;
            this.currentCompany = this.getEmptyCompany();
        },
        saveCompany() {
            if (!this.canSave) {
                alert('Please fill in all required fields');
                return;
            }

            const url = this.isEditing
                ? `/api/companies/${this.currentCompany.id}`
                : '/api/companies';

            const method = this.isEditing ? 'put' : 'post';

            axios[method](url, this.currentCompany)
                .then(() => {
                    this.fetchCompanies();
                    this.closeModal();
                })
                .catch(err => {
                    alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                });
        },
        deleteCompany(id) {
            if (confirm('Are you sure you want to delete this company?')) {
                axios.delete(`/api/companies/${id}`)
                    .then(() => {
                        this.fetchCompanies();
                    })
                    .catch(err => {
                        alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                    });
            }
        },
        viewTransactions(company) {
            this.selectedCompany = company;
            this.showTransactionsModal = true;
            this.loadingTransactions = true;
            this.companyTransactions = [];

            // Fetch all transactions for this company
            axios.get(`/api/companies/${company.id}`)
                .then(res => {
                    const companyData = res.data.success ? res.data.data : res.data;
                    this.companyTransactions = companyData.transactions || [];
                    this.loadingTransactions = false;
                })
                .catch(err => {
                    console.error('Error fetching transactions:', err);
                    alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                    this.loadingTransactions = false;
                });
        },
        closeTransactionsModal() {
            this.showTransactionsModal = false;
            this.selectedCompany = null;
            this.companyTransactions = [];
        },
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString();
        },
        getSubscriptionClass(status) {
            const classes = {
                'active': 'bg-success',
                'stopped': 'bg-warning',
                'cancelled': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        },
        getPaymentStatusClass(status) {
            const classes = {
                'paid': 'bg-success',
                'unpaid': 'bg-warning',
                'late': 'bg-danger',
                'overdue': 'bg-danger',
                'notified': 'bg-info'
            };
            return classes[status] || 'bg-secondary';
        }
    }
};
</script>
