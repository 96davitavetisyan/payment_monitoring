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
                            <strong>Պրոդուկտ:</strong> {{ contract.product?.name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Վճարում:</strong> {{ contract.payment_type === 'monthly' ? 'Ամենամյա' : (contract.payment_type === 'one_time' ? 'Միանվագ' : 'Տարեկան') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered">
                    <thead class="table-dark">
                    <tr>
                        <th style="width: 140px;">Հաշվեհամար</th>
                        <th style="width: 100px;">Վճարման ամսաթիվ</th>
                        <th style="width: 110px;">Վերջնաժամկետ</th>
                        <th style="width: 120px;">Գումար</th>
                        <th style="width: 90px;">Կարգավիճակ</th>
                        <th style="width: 90px;">Երբ է ծանուցվել</th>
                        <th style="width: 100px;">Երբ է վճարվել</th>
                        <th style="width: 100px;">Ֆայլեր</th>
                        <th style="width: 180px;">Գործողություններ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="transaction in transactions" :key="transaction.id">
                        <td class="small">{{ transaction.invoice_number }}</td>
                        <td class="small">{{ formatDate(transaction.invoice_date) }}</td>
                        <td class="small">{{ formatDate(transaction.due_date) }}</td>
                        <td class="small">{{ formatAmount(transaction.amount) }}</td>
                        <td>
                            <span class="badge" :class="getStatusClass(transaction.payment_status)" style="font-size: 10px;">
                                {{ getStatusLabel(transaction.payment_status) }}
                            </span>
                        </td>
                        <td class="small">{{ formatDate(transaction.notified_at) || '-' }}</td>
                        <td class="small">{{ formatDate(transaction.paid_date) || '-' }}</td>
                        <td>
                            <div v-if="transaction.files.length">
                                <button v-for="file in transaction.files" :key="file.id"
                                        class="btn btn-outline-success btn-sm mb-1"
                                        @click="downloadFile(file.id)">
                                    <i class="fa-solid fa-paperclip"></i> {{ file.file_name }}
                                </button>
                            </div>
                            <span v-else>-</span>
                        </td>
                        <td>
                            <div class="" role="group">
                                <button class="btn btn-outline-primary" @click="editTransaction(transaction)" title="Խմբագրել">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <button class="btn btn-outline-success" @click="uploadFiles(transaction)" title="Ֆայլեր">
                                    <i class="fa-solid fa-paperclip"></i>
                                </button>

                                <button class="btn btn-outline-warning" @click="sendNotification(transaction.id)" title="Ծանուցում">
                                    <i class="fa-solid fa-envelope"></i>
                                </button>

                                <button class="btn btn-outline-danger" @click="deleteTransaction(transaction.id)" title="Ջնջել">
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                                <button class="btn btn-outline-info" @click="openPaidFileModal(transaction)" title="Վճարման ամսաթիվ և ֆայլ">
                                    <i class="fa-solid fa-calendar-plus"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="transactions.length === 0">
                        <td colspan="9" class="text-center">Տվյալներ չկան</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Create/Edit Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showModal }" style="background: rgba(0,0,0,0.5);" v-if="showModal" @click.self="closeModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Գործարք</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Հաշվեհամար</label>
                                <input type="text" class="form-control" v-model="currentTransaction.invoice_number" required>
                            </div>



                            <div class="mb-3">
                                <label class="form-label">Գումար</label>
                                <input type="number" step="0.01" class="form-control" v-model="currentTransaction.amount" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Կարգավիճակ</label>
                                <select class="form-select" v-model="currentTransaction.payment_status" required>
                                    <option value="pending">Սպասող</option>
                                    <option value="paid">Վճարված</option>
                                    <option value="late">Ուշացած</option>
                                    <option value="overdue">Ժամկետանց</option>
                                    <option value="cancelled">Չեղարկված է</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Վճարման ամսաթիվ</label>
<!--                                <date-picker v-model="currentTransaction.invoice_date" valueType="format" type="date"></date-picker>-->
                                <DatePicker class="w-100" v-model="currentTransaction.invoice_date" format="DD-MM-YYYY" placeholder="Ընտրել" value-type="format"/>
<!--                                <input type="date" class="form-control" v-model="currentTransaction.invoice_date" required>-->
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Վերջնաժամկետ</label>
<!--                                <input type="date" class="form-control" v-model="currentTransaction.paid_date" required>-->
                                <DatePicker class="w-100" v-model="currentTransaction.due_date" format="DD-MM-YYYY" placeholder="Ընտրել" value-type="format"/>
                            </div>
                            <div class="mb-3" v-if = "isEditing">
                                <label class="form-label">Երբ է վճարվել</label>
                                <DatePicker class="w-100" v-model="currentTransaction.paid_date" format="DD-MM-YYYY" placeholder="Ընտրել" value-type="format"/>
<!--                                <input type="date" class="form-control" v-model="currentTransaction.paid_date">-->
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
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showFileModal }" style="background: rgba(0,0,0,0.5);" v-if="showFileModal" @click.self="closeFileModal">
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

            <div class="modal" tabindex="-1" :class="{ 'show d-block': showPaidFileModal }" style="background: rgba(0,0,0,0.5);" v-if="showPaidFileModal" @click.self="closePaidFileModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Վճարման ամսաթիվ և ֆայլ</h5>
                            <button type="button" class="btn-close" @click="closePaidFileModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Երբ է վճարվել</label>
<!--                                <input type="date" class="form-control" v-model="paidFileTransactionDate" required>-->

                                <DatePicker class="w-100" v-model="paidFileTransaction.paid_date" format="DD-MM-YYYY" placeholder="Ընտրել" value-type="format" required/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ընտրել ֆայլ</label>
                                <input type="file" class="form-control" @change="handlePaidFileSelection" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closePaidFileModal">Չեղարկել</button>
                            <button class="btn btn-success" @click="savePaidFileTransaction">Պահպանել</button>
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
                notified_at: '',
                notes: '',
            },

            showPaidFileModal: false,
            paidFileTransaction: null,
            selectedPaidFile: null,
        };
    },
    mounted() {
        this.contractId = this.$route.params.contractId;
        this.fetchContract();
        this.fetchTransactions();
    },
    computed: {

        transactionPaidDate: {
            get() {
                return this.toInputDate(this.currentTransaction.paid_date);
            },
            set(value) {
                this.currentTransaction.paid_date = value;
            }
        },
        paidFileTransactionDate: {
            get() {
                return this.toInputDate(this.paidFileTransaction?.paid_date);
            },
            set(value) {
                if (this.paidFileTransaction) {
                    this.paidFileTransaction.paid_date = value;
                }
            }
        }
    },
    methods: {
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
                invoice_number: this.currentTransaction?.invoice_number !== '' ? this.currentTransaction?.invoice_number : this.contract.account_number,
                invoice_date: this.currentTransaction?.invoice_date !== '' ? `25-03-2025` : this.formatPaymentDate(this.contract.payment_date),
                due_date: this.currentTransaction?.paid_date !== '' ? this.currentTransaction?.paid_date : this.formatPaymentDate(this.contract.payment_finish_date),
                amount: this.currentTransaction?.amount !== '' ? this.currentTransaction?.amount : this.contract.payment_amount,
                payment_status: 'pending',
                paid_date: '',
                notes: ''
            };
            this.isEditing = false;
            this.showModal = true;
        },
        editTransaction(transaction) {
            let transactionCopy = { ...transaction };

            if (transactionCopy.invoice_date) {
                transactionCopy.invoice_date = this.formatDate(transactionCopy.invoice_date);
            }
            if (transactionCopy.due_date) {
                transactionCopy.due_date = this.formatDate(transactionCopy.due_date);
            }
            if (transactionCopy.paid_date) {
                transactionCopy.paid_date = this.formatDate(transactionCopy.paid_date);
            }
            this.currentTransaction = transactionCopy;
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

                Swal.fire({
                    icon: 'success',
                    title: this.isEditing ? 'Գործարքը թարմացվեց' : 'Գործարքը ստեղծվեց',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });

                this.isEditing = false;
            } catch (error) {
                console.error('Error saving transaction:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Խնդիր է առաջացել',
                    text: error.response?.data?.message || error.message
                });
            }
        },
        async deleteTransaction(id) {
            const result = await Swal.fire({
                title: 'Հաստատե՞լ ջնջումը',
                text: 'Ցանկանում եք ջնջել այս գործարքը։',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ջնջել',
                cancelButtonText: 'Չեղարկել',
                confirmButtonColor: '#d33'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/api/transactions/${id}`);
                    this.fetchTransactions();
                    Swal.fire({
                        icon: 'success',
                        title: 'Ջնջված է',
                        text: 'Գործարքը հաջողությամբ ջնջվել է',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                } catch (error) {
                    console.error('Error deleting transaction:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Խնդիր է առաջացել',
                        text: 'Գործարքը ջնջել չի հաջողվել',
                    });
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
                Swal.fire({
                    icon: 'warning',
                    title: 'Ուշադրություն',
                    text: 'Խնդրում ենք ընտրել առնվազն մեկ ֆայլ',
                });
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

                Swal.fire({
                    icon: 'success',
                    title: 'Ֆայլերը հաջողությամբ վերբեռնվել են',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });

                this.closeFileModal();
            } catch (error) {
                console.error('Error uploading files:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Խնդիր է առաջացել',
                    text: error.response?.data?.message || error.message,
                });
            }
        },
        async deleteFile(fileId) {
            const result = await Swal.fire({
                title: 'Ջնջել ֆայլը՞',
                text: "Դուք չեք կարող վերադարձնել ֆայլը հետագայում:",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ջնջել',
                cancelButtonText: 'Չեղարկել',
                confirmButtonColor: '#d33'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/api/transactions/${this.currentTransactionForFiles.id}/files/${fileId}`);

                    const response = await axios.get(`/api/transactions/${this.currentTransactionForFiles.id}`);
                    this.currentTransactionFiles = response.data.data.files || [];

                    Swal.fire(
                        'Ջնջված է!',
                        'Ֆայլը հաջողությամբ ջնջվեց',
                        'success'
                    );
                } catch (error) {
                    console.error('Error deleting file:', error);
                    Swal.fire(
                        'Խնդիր է առաջացել',
                        'Ֆայլը չհաջողվեց ջնջել: ' + (error.response?.data?.message || error.message),
                        'error'
                    );
                }
            }
        },
        async sendNotification(transactionId) {
            const { value: file } = await Swal.fire({
                title: 'Ուղարկել ծանուցումը՞',
                text: "Ընտրեք ֆայլը, որը կուղարկվի:",
                input: 'file',
                inputAttributes: {
                    'accept': '.pdf,.doc,.docx',
                    'aria-label': 'Attach file'
                },
                showCancelButton: true,
                confirmButtonText: 'Ուղարկել',
                cancelButtonText: 'Չեղարկել',
                inputValidator: (file) => {
                    if (!file) {
                        return 'Դուք պետք է ընտրեք ֆայլ';
                    }
                }
            });

            if (file) {
                const formData = new FormData();
                formData.append('file', file);

                try {
                    await axios.post(`/api/transactions/${transactionId}/send-notification`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    this.fetchTransactions();

                    Swal.fire(
                        'Ուղարկված է!',
                        'Ծանուցումը հաջողությամբ ուղարկվեց',
                        'success'
                    );
                } catch (error) {
                    console.error('Error sending notification:', error);
                    Swal.fire(
                        'Խնդիր է առաջացել',
                        error.response?.data?.message || error.message,
                        'error'
                    );
                }
            }
        },
        closeModal() {
            this.showModal = false;
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
                'pending': 'Սպասող',
                'paid': 'Վճարված',
                'late': 'Ուշացած',
                'overdue': 'Ժամկետանց',
                'cancelled': 'Չեղարկված է',
                'notified': 'Ծանուցված',
            };
            return labels[status] || status;
        },
        openPaidFileModal(transaction) {
            this.paidFileTransaction = { ...transaction }; // копия транзакции
            this.selectedPaidFile = null;
            this.showPaidFileModal = true;
        },
        closePaidFileModal() {
            this.showPaidFileModal = false;
            this.paidFileTransaction = null;
            this.selectedPaidFile = null;
        },
        handlePaidFileSelection(event) {
            this.selectedPaidFile = event.target.files[0];
        },
        async savePaidFileTransaction() {
            if (!this.paidFileTransaction.paid_date || !this.selectedPaidFile) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ուշադրություն',
                    text: 'Ընտրեք ամսաթիվ և ֆայլ',
                });
                return;
            }

            const formData = new FormData();
            formData.append('paid_date', this.paidFileTransaction.paid_date);
            formData.append('file', this.selectedPaidFile);

            try {
                await axios.post(`/api/transactions/${this.paidFileTransaction.id}/paid-file`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Պահպանվել է',
                    text: 'Վճարման ամսաթիվը և ֆայլը հաջողությամբ պահպանվել են',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });

                this.fetchTransactions(); // обновляем таблицу
                this.closePaidFileModal();
            } catch (error) {
                console.error('Error saving paid file transaction:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Խնդիր է առաջացել',
                    text: error.response?.data?.message || error.message,
                });
            }
        },
        downloadFile(fileId) {
            window.open(`/api/transaction-files/download/${fileId}`, '_blank');
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
