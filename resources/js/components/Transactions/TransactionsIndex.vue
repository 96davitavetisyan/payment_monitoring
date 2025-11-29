<template>
    <div>
        <app-header></app-header>
        <div class="container-fluid mt-4 px-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <button class="btn btn-secondary btn-sm" @click="$router.push('/contracts')">
                        ← Հետ
                    </button>
                    <h3 class="d-inline ms-3">Հաշիվ-ապրանքագրեր</h3>
                </div>
                <button class="btn btn-success btn-sm" @click="openCreateModal">
                    + Ավելացնել
                </button>
            </div>

            <!-- Contract Info -->
            <div v-if="contract" class="card mb-3">
                <div class="card-body py-2">
                    <div class="row small">
                        <div class="col-md-3">
                            <strong>Գործընկեր:</strong> {{ contract.partner_company?.name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Մեր ընկերություն:</strong> {{ contract.own_company?.name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Ապրանք:</strong> {{ contract.product?.name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Վճարում:</strong> {{ contract.payment_type }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered">
                    <thead class="table-dark">
                    <tr>
                        <th style="width: 140px;">Հաշիվ #</th>
                        <th style="width: 100px;">Ամսաթիվ</th>
                        <th style="width: 110px;">Վերջնաժամկետ</th>
                        <th style="width: 120px;">Գումար</th>
                        <th style="width: 90px;">Կարգավիճակ</th>
                        <th style="width: 100px;">Վճարված</th>
                        <th style="width: 180px;">Գործողություններ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="transaction in transactions" :key="transaction.id">
                        <td class="small">{{ transaction.invoice_number }}</td>
                        <td class="small">{{ formatDateShort(transaction.invoice_date) }}</td>
                        <td class="small">{{ formatDateShort(transaction.due_date) }}</td>
                        <td class="small">{{ formatAmount(transaction.amount) }}</td>
                        <td>
                            <span class="badge" :class="getStatusClass(transaction.payment_status)" style="font-size: 10px;">
                                {{ getStatusLabel(transaction.payment_status) }}
                            </span>
                        </td>
                        <td class="small">{{ formatDateShort(transaction.paid_date) || '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" @click="editTransaction(transaction)" title="Խմբագրել">
                                    ✏️
                                </button>
                                <button class="btn btn-outline-success" @click="uploadFiles(transaction)" title="Ֆայլեր">
                                    📎
                                </button>
                                <button class="btn btn-outline-warning" @click="sendNotification(transaction.id)" title="Ծանուցում">
                                    ✉️
                                </button>
                                <button class="btn btn-outline-danger" @click="deleteTransaction(transaction.id)" title="Ջնջել">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="transactions.length === 0">
                        <td colspan="7" class="text-center">Տվյալներ չկան</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Create/Edit Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showModal }" style="background: rgba(0,0,0,0.5);" v-if="showModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Գործարք</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Հաշիվ #</label>
                                <input type="text" class="form-control" v-model="currentTransaction.invoice_number" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ամսաթիվ</label>
                                <input type="date" class="form-control" v-model="currentTransaction.invoice_date" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Վերջնաժամկետ</label>
                                <input type="date" class="form-control" v-model="currentTransaction.due_date" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Գումար</label>
                                <input type="number" step="0.01" class="form-control" v-model="currentTransaction.amount" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Կարգավիճակ</label>
                                <select class="form-select" v-model="currentTransaction.payment_status" required>
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="late">Late</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Վճարման ամսաթիվ</label>
                                <input type="date" class="form-control" v-model="currentTransaction.paid_date">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Նշումներ</label>
                                <textarea class="form-control" rows="2" v-model="currentTransaction.notes"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeModal">Չեղարկել</button>
                            <button class="btn btn-success" @click="saveTransaction">{{ isEditing ? 'Թարմացնել' : 'Ստեղծել' }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Upload Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showFileModal }" style="background: rgba(0,0,0,0.5);" v-if="showFileModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Վերբեռնել ֆայլեր</h5>
                            <button type="button" class="btn-close" @click="closeFileModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Ընտրել ֆայլեր</label>
                                <input type="file" class="form-control" @change="handleFileSelection" multiple ref="fileInput">
                                <small class="text-muted">Կարող եք ընտրել մի քանի ֆայլ</small>
                            </div>

                            <!-- Existing Files -->
                            <div v-if="currentTransactionFiles.length > 0">
                                <h6>Առկա ֆայլեր:</h6>
                                <ul class="list-group list-group-sm">
                                    <li v-for="file in currentTransactionFiles" :key="file.id" class="list-group-item d-flex justify-content-between align-items-center py-1">
                                        <small>{{ file.file_name }}</small>
                                        <button class="btn btn-sm btn-danger" @click="deleteFile(file.id)">Ջնջել</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeFileModal">Փակել</button>
                            <button class="btn btn-success" @click="submitFiles">Վերբեռնել</button>
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
            contractId: null,
            contract: null,
            transactions: [],
            showModal: false,
            showFileModal: false,
            isEditing: false,
            selectedFiles: [],
            currentTransactionForFiles: null,
            currentTransactionFiles: [],
            currentTransaction: {
                contract_id: '',
                invoice_number: '',
                invoice_date: '',
                due_date: '',
                amount: '',
                payment_status: 'pending',
                paid_date: '',
                notes: ''
            }
        };
    },
    mounted() {
        this.contractId = this.$route.params.contractId;
        if (this.contractId) {
            this.fetchContract();
            this.fetchTransactions();
        }
    },
    methods: {
        async fetchContract() {
            try {
                const response = await axios.get(`/api/contracts/${this.contractId}`);
                this.contract = response.data.data;
            } catch (error) {
                console.error('Error fetching contract:', error);
                alert('Failed to load contract');
            }
        },
        async fetchTransactions() {
            try {
                const response = await axios.get('/api/transactions', {
                    params: { contract_id: this.contractId }
                });
                this.transactions = response.data.data;
            } catch (error) {
                console.error('Error fetching transactions:', error);
                alert('Failed to load transactions');
            }
        },
        openCreateModal() {
            this.currentTransaction = {
                contract_id: this.contractId,
                invoice_number: '',
                invoice_date: '',
                due_date: '',
                amount: '',
                payment_status: 'pending',
                paid_date: '',
                notes: ''
            };
            this.isEditing = false;
            this.showModal = true;
        },
        editTransaction(transaction) {
            this.currentTransaction = { ...transaction };
            this.isEditing = true;
            this.showModal = true;
        },
        async saveTransaction() {
            try {
                if (this.isEditing) {
                    await axios.put(`/api/transactions/${this.currentTransaction.id}`, this.currentTransaction);
                } else {
                    await axios.post('/api/transactions', this.currentTransaction);
                }
                this.fetchTransactions();
                this.closeModal();
                alert(this.isEditing ? 'Transaction updated successfully' : 'Transaction created successfully');
            } catch (error) {
                console.error('Error saving transaction:', error);
                alert('Failed to save transaction: ' + (error.response?.data?.message || error.message));
            }
        },
        async deleteTransaction(id) {
            if (confirm('Are you sure you want to delete this transaction?')) {
                try {
                    await axios.delete(`/api/transactions/${id}`);
                    this.fetchTransactions();
                    alert('Transaction deleted successfully');
                } catch (error) {
                    console.error('Error deleting transaction:', error);
                    alert('Failed to delete transaction');
                }
            }
        },
        async uploadFiles(transaction) {
            this.currentTransactionForFiles = transaction;
            this.showFileModal = true;
            try {
                const response = await axios.get(`/api/transactions/${transaction.id}`);
                this.currentTransactionFiles = response.data.data.files || [];
            } catch (error) {
                console.error('Error fetching transaction files:', error);
            }
        },
        handleFileSelection(event) {
            this.selectedFiles = Array.from(event.target.files);
        },
        async submitFiles() {
            if (this.selectedFiles.length === 0) {
                alert('Please select at least one file');
                return;
            }

            const formData = new FormData();
            this.selectedFiles.forEach(file => {
                formData.append('files[]', file);
            });

            try {
                await axios.post(`/api/transactions/${this.currentTransactionForFiles.id}/upload-files`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                alert('Files uploaded successfully');
                this.closeFileModal();
            } catch (error) {
                console.error('Error uploading files:', error);
                alert('Failed to upload files: ' + (error.response?.data?.message || error.message));
            }
        },
        async deleteFile(fileId) {
            if (confirm('Are you sure you want to delete this file?')) {
                try {
                    await axios.delete(`/api/transactions/${this.currentTransactionForFiles.id}/files/${fileId}`);
                    const response = await axios.get(`/api/transactions/${this.currentTransactionForFiles.id}`);
                    this.currentTransactionFiles = response.data.data.files || [];
                    alert('File deleted successfully');
                } catch (error) {
                    console.error('Error deleting file:', error);
                    alert('Failed to delete file');
                }
            }
        },
        async sendNotification(transactionId) {
            if (confirm('Send payment reminder email?')) {
                try {
                    await axios.post(`/api/transactions/${transactionId}/send-notification`);
                    this.fetchTransactions();
                    alert('Notification sent successfully');
                } catch (error) {
                    console.error('Error sending notification:', error);
                    alert('Failed to send notification: ' + (error.response?.data?.message || error.message));
                }
            }
        },
        closeModal() {
            this.showModal = false;
            this.isEditing = false;
            this.currentTransaction = {
                contract_id: this.contractId,
                invoice_number: '',
                invoice_date: '',
                due_date: '',
                amount: '',
                payment_status: 'pending',
                paid_date: '',
                notes: ''
            };
        },
        closeFileModal() {
            this.showFileModal = false;
            this.selectedFiles = [];
            this.currentTransactionForFiles = null;
            this.currentTransactionFiles = [];
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
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
                'pending': 'bg-warning text-dark',
                'paid': 'bg-success',
                'late': 'bg-danger',
                'overdue': 'bg-danger',
                'cancelled': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
        },
        getStatusLabel(status) {
            const labels = {
                'pending': 'Pending',
                'paid': 'Paid',
                'late': 'Late',
                'overdue': 'Overdue',
                'cancelled': 'Cancel'
            };
            return labels[status] || status;
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
