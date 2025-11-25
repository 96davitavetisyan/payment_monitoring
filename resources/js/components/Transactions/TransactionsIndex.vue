<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <button class="btn btn-secondary btn-sm" @click="$router.push('/projects')">
                        ← Back to Projects
                    </button>
                    <h2 class="d-inline ms-3">Transactions - {{ projectName }}</h2>
                </div>
                <button v-if="$auth.can('create_transactions')"
                        class="btn btn-success"
                        @click="openCreateModal">
                    New Transaction
                </button>
            </div>

            <!-- Active Transactions -->
            <h4 class="mt-4">Active Transactions</h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-primary">
                    <tr>
                        <th>Company</th>
                        <th>Contact Person</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Contract Period</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="transaction in activeTransactions" :key="transaction.id">
                        <td>{{ transaction.company_name }}</td>
                        <td>{{ transaction.person_name || 'N/A' }}</td>
                        <td>${{ parseFloat(transaction.amount).toFixed(2) }}</td>
                        <td>
                            <span class="badge" :class="getStatusClass(transaction.payment_status)">
                                {{ transaction.payment_status }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ transaction.transaction_type }}</span>
                        </td>
                        <td>{{ formatDate(transaction.transaction_date) }}</td>
                        <td>
                            {{ formatDate(transaction.contract_start_date) }} -
                            {{ formatDate(transaction.contract_end_date) }}
                        </td>
                        <td>
                            <button v-if="$auth.can('edit_transactions')"
                                    class="btn btn-sm btn-primary me-1"
                                    @click="editTransaction(transaction)">
                                Edit
                            </button>
                            <button v-if="$auth.can('edit_transactions')"
                                    class="btn btn-sm btn-warning me-1"
                                    @click="toggleStatus(transaction)">
                                Archive
                            </button>
                            <button v-if="$auth.can('delete_transactions')"
                                    class="btn btn-sm btn-danger"
                                    @click="deleteTransaction(transaction.id)">
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="activeTransactions.length === 0">
                        <td colspan="8" class="text-center text-muted">No active transactions</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- History Transactions -->
            <h4 class="mt-5">Transaction History</h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-secondary">
                    <tr>
                        <th>Company</th>
                        <th>Contact Person</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="transaction in historyTransactions" :key="transaction.id">
                        <td>{{ transaction.company_name }}</td>
                        <td>{{ transaction.person_name || 'N/A' }}</td>
                        <td>${{ parseFloat(transaction.amount).toFixed(2) }}</td>
                        <td>
                            <span class="badge" :class="getStatusClass(transaction.payment_status)">
                                {{ transaction.payment_status }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ transaction.transaction_type }}</span>
                        </td>
                        <td>{{ formatDate(transaction.transaction_date) }}</td>
                        <td>
                            <button v-if="$auth.can('edit_transactions')"
                                    class="btn btn-sm btn-success"
                                    @click="toggleStatus(transaction)">
                                Restore
                            </button>
                        </td>
                    </tr>
                    <tr v-if="historyTransactions.length === 0">
                        <td colspan="7" class="text-center text-muted">No transaction history</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Transaction Modal -->
            <div v-if="showModal" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Edit Transaction' : 'New Transaction' }}</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Company Name *</label>
                                    <input type="text" class="form-control" v-model="currentTransaction.company_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Person</label>
                                    <input type="text" class="form-control" v-model="currentTransaction.person_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Amount *</label>
                                    <input type="number" step="0.01" class="form-control" v-model="currentTransaction.amount" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Payment Status *</label>
                                    <select class="form-select" v-model="currentTransaction.payment_status" required>
                                        <option value="paid">Paid</option>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="late">Late</option>
                                        <option value="overdue">Overdue</option>
                                        <option value="notified">Notified</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Transaction Type *</label>
                                    <select class="form-select" v-model="currentTransaction.transaction_type" required>
                                        <option value="one-time">One-time</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Transaction Date *</label>
                                    <input type="date" class="form-control" v-model="currentTransaction.transaction_date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Max Overdue Date</label>
                                    <input type="date" class="form-control" v-model="currentTransaction.max_overdue_date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contract Start Date</label>
                                    <input type="date" class="form-control" v-model="currentTransaction.contract_start_date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contract End Date</label>
                                    <input type="date" class="form-control" v-model="currentTransaction.contract_end_date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contract File</label>
                                    <input type="file" class="form-control" @change="handleContractFile" accept=".pdf,.doc,.docx">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Attachment</label>
                                    <input type="file" class="form-control" @change="handleFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeModal">Cancel</button>
                            <button class="btn btn-primary" @click="saveTransaction">
                                {{ isEditing ? 'Update' : 'Create' }}
                            </button>
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
            projectId: null,
            projectName: '',
            activeTransactions: [],
            historyTransactions: [],
            showModal: false,
            isEditing: false,
            currentTransaction: this.getEmptyTransaction(),
            contractFile: null,
            attachmentFile: null
        };
    },
    mounted() {
        this.projectId = this.$route.params.projectId;
        this.fetchProject();
        this.fetchTransactions();
    },
    methods: {
        getEmptyTransaction() {
            return {
                company_name: '',
                person_name: '',
                amount: '',
                payment_status: 'unpaid',
                transaction_type: 'one-time',
                transaction_date: '',
                max_overdue_date: '',
                contract_start_date: '',
                contract_end_date: '',
                is_active: true
            };
        },
        fetchProject() {
            axios.get(`/api/projects/${this.projectId}`)
                .then(res => {
                    const project = res.data.success ? res.data.data : res.data;
                    this.projectName = project.name;
                })
                .catch(err => console.error('Error fetching project:', err));
        },
        fetchTransactions() {
            axios.get(`/api/projects/${this.projectId}/transactions`)
                .then(res => {
                    this.activeTransactions = res.data.active || [];
                    this.historyTransactions = res.data.history || [];
                })
                .catch(err => {
                    console.error('Error fetching transactions:', err);
                    alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                });
        },
        openCreateModal() {
            this.isEditing = false;
            this.currentTransaction = this.getEmptyTransaction();
            this.contractFile = null;
            this.attachmentFile = null;
            this.showModal = true;
        },
        editTransaction(transaction) {
            this.isEditing = true;
            this.currentTransaction = { ...transaction };
            this.contractFile = null;
            this.attachmentFile = null;
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.isEditing = false;
            this.currentTransaction = this.getEmptyTransaction();
            this.contractFile = null;
            this.attachmentFile = null;
        },
        handleContractFile(event) {
            this.contractFile = event.target.files[0];
        },
        handleFile(event) {
            this.attachmentFile = event.target.files[0];
        },
        saveTransaction() {
            const formData = new FormData();

            Object.keys(this.currentTransaction).forEach(key => {
                if (this.currentTransaction[key] !== null && this.currentTransaction[key] !== '') {
                    formData.append(key, this.currentTransaction[key]);
                }
            });

            if (this.contractFile) formData.append('contract_file', this.contractFile);
            if (this.attachmentFile) formData.append('file', this.attachmentFile);

            const url = this.isEditing
                ? `/api/projects/${this.projectId}/transactions/${this.currentTransaction.id}`
                : `/api/projects/${this.projectId}/transactions`;

            const method = this.isEditing ? 'put' : 'post';

            axios[method](url, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
                .then(() => {
                    this.fetchTransactions();
                    this.closeModal();
                })
                .catch(err => {
                    alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                });
        },
        toggleStatus(transaction) {
            axios.post(`/api/projects/${this.projectId}/transactions/${transaction.id}/toggle-status`)
                .then(() => this.fetchTransactions())
                .catch(err => alert('Error: ' + (err.response?.data?.message || 'Unknown error')));
        },
        deleteTransaction(id) {
            if (confirm('Are you sure you want to delete this transaction?')) {
                axios.delete(`/api/projects/${this.projectId}/transactions/${id}`)
                    .then(() => this.fetchTransactions())
                    .catch(err => alert('Error: ' + (err.response?.data?.message || 'Unknown error')));
            }
        },
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString();
        },
        getStatusClass(status) {
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
