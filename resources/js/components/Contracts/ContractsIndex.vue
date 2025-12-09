<template>
    <div>
        <app-header></app-header>
        <div class="container-fluid mt-4 px-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Պայմանագրեր</h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" @click="showColumnSelector = !showColumnSelector">
                        <i class="fa-solid fa-columns"></i> Սյուներ
                    </button>
                    <button class="btn btn-outline-primary btn-sm" @click="exportToExcel">
                        <i class="fa-solid fa-file-excel"></i> Արտահանել Excel
                    </button>
                    <button class="btn btn-success btn-sm" @click="showCreateModal = true">
                        + Ավելացնել
                    </button>
                </div>
            </div>

            <!-- Column Selector Dropdown -->
            <div v-if="showColumnSelector" class="card mb-3 shadow-sm">
                <div class="card-body p-3">
                    <h6 class="mb-3">Ընտրել ցուցադրվող սյուները</h6>
                    <div class="row">
                        <div class="col-md-3 mb-2" v-for="col in allColumns" :key="col.field">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    :id="'col-' + col.field"
                                    v-model="col.visible"
                                    @change="saveColumnSettings"
                                >
                                <label class="form-check-label" :for="'col-' + col.field">
                                    {{ col.label }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts Table -->
            <div class="table-responsive" :style="{ border: contractType === 'merchant' ? '2px solid #0d6efd' : '2px solid #212529' }"><vue-good-table
                :columns="visibleColumns"
                :rows="contracts"
                :search-options="{
                    enabled: true,
                    placeholder: 'Որոնել...'
                }"
                :pagination-options="{
                    enabled: true,
                    mode: 'records',
                    perPage: 20,
                    perPageDropdown: [10, 20, 50, 100],
                    dropdownAllowAll: false
                }"
                :row-style-class="getRowClass"
                :styleClass="'vgt-table striped bordered ' + (contractType === 'merchant' ? 'merchant-table' : 'international-table')"
            >
                <template slot="table-row" slot-scope="props">
        <span v-if="props.column.field === 'actions'">
            <button class="btn btn-outline-primary" @click="editContract(props.row)">
                <i class="fa-solid fa-pen"></i>
            </button>

            <button class="btn btn-outline-info" @click="viewTransactions(props.row.id)">
                <i class="fa-solid fa-file-invoice"></i>
            </button>

            <button class="btn btn-outline-danger" @click="deleteContract(props.row.id)">
                <i class="fa-solid fa-trash"></i>
            </button>
        </span>

        <span v-else-if="props.column.field === 'contract_start_date'">
            {{ formatDate(props.row.contract_start_date) }}
        </span>

        <span v-else-if="props.column.field === 'contract_end_date'">
            {{ formatDate(props.row.contract_end_date) }}
        </span>

        <span v-else-if="props.column.field === 'payment_date'">
            {{ formatPaymentDate(props.row.payment_date) }}
        </span>

        <span v-else-if="props.column.field === 'payment_finish_date'">
            {{ formatPaymentDate(props.row.payment_finish_date) }}
        </span>

        <span v-else-if="props.column.field === 'payment_amount'">
            {{ formatAmount(props.row.payment_amount) }}
        </span>

        <span v-else-if="props.column.field === 'payment_type'">
            {{ props.row.payment_type === 'monthly' ? 'Ամենամյա' : (props.row.payment_type === 'one_time' ? 'Միանվագ' : 'Տարեկան') }}
        </span>

        <span v-else-if="props.column.field === 'status'">
            <span class="badge" :class="getStatusClass(props.row.status)">
                {{ getStatusLabel(props.row.status) }}
            </span>
        </span>

                    <span v-else>
            {{ props.formattedRow[props.column.field] }}
        </span>
                </template>
            </vue-good-table>
            </div>

            <!-- Create/Edit Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showCreateModal }" style="background: rgba(0,0,0,0.5);" v-if="showCreateModal" @click.self="closeModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Պայմանագիր</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label mb-0">Գործընկեր *</label>
                                        <button type="button" class="btn btn-outline-success btn-sm py-0 px-2" @click="goToPartnerCompanies" title="Ավելացնել գործընկեր">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
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
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label mb-0">Մեր ընկերություն *</label>
                                        <button type="button" class="btn btn-outline-success btn-sm py-0 px-2" @click="goToOwnCompanies" title="Ավելացնել մեր ընկերություն">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
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
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label mb-0">Պրոդուկտ *</label>
                                        <button type="button" class="btn btn-outline-success btn-sm py-0 px-2" @click="goToProducts" title="Ավելացնել պրոդուկտ">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
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
                                    <label class="form-label">Պայմանագրի կնքման ամսաթիվ *</label>
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
                                    <input type="file" class="form-control form-control-sm" :class="{'is-invalid': errors.contract_file}" @change="handleFileChange" ref="contractFile" accept=".pdf,application/pdf">
                                    <small class="text-muted d-block">Միայն PDF ֆայլեր</small>
                                    <small v-if="currentContract.contract_file" class="text-muted">
                                        Ընթացիկ ֆայլ: {{ getFileName(currentContract.contract_file) }}
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
            showColumnSelector: false,
            allColumns: [
                { label: 'Համար', field: 'contract_number', sortable: true, filterable: true, visible: true },
                { label: 'Գործընկեր', field: 'partner_company.name', sortable: true, filterable: true, visible: true, filterOptions: { enabled: true, filterDropdownItems: [] } },
                { label: 'Մեր ընկերություն', field: 'own_company.name', sortable: true, filterable: true, visible: true, filterOptions: { enabled: true, filterDropdownItems: [] } },
                { label: 'Պրոդուկտ', field: 'product.name', sortable: true, filterable: true, visible: true, filterOptions: { enabled: true, filterDropdownItems: [] } },
                { label: 'Սկիզբ', field: 'contract_start_date', sortable: true, visible: true },
                { label: 'Ավարտ', field: 'contract_end_date', sortable: true, visible: true },
                { label: 'Վճարման օր', field: 'payment_date', sortable: true, visible: false },
                { label: 'Վճարման վերջնական օր', field: 'payment_finish_date', sortable: true, visible: false },
                { label: 'Հաշվեհամար', field: 'account_number', sortable: true, filterable: true, visible: true },
                { label: 'Վճարման տեսակ', field: 'payment_type', sortable: true, filterable: true, visible: true, filterOptions: { enabled: true, filterDropdownItems: [
                    { value: 'one_time', text: 'Միանվագ' },
                    { value: 'monthly', text: 'Ամենամյա' },
                    { value: 'yearly', text: 'Տարեկան' }
                ] } },
                { label: 'Գումար', field: 'payment_amount', sortable: true, visible: true },
                { label: 'Կարգավիճակ', field: 'status', sortable: true, filterable: true, visible: true, filterOptions: { enabled: true, filterDropdownItems: [
                    { value: 'active', text: 'Ակտիվ' },
                    { value: 'completed', text: 'Ավարտված' },
                    { value: 'cancelled', text: 'Չեղարկված' },
                    { value: 'suspended', text: 'Կասեցված' }
                ] } },
                { label: 'Գործողություններ', field: 'actions', sortable: false, visible: true }
            ],
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
        this.loadColumnSettings();
        this.fetchContracts();
        this.fetchPartnerCompanies();
        this.fetchOwnCompanies();
        this.fetchProducts();
    },
    computed: {
        isOneTimePayment() {
            return this.currentContract.payment_type !== 'one_time';
        },
        visibleColumns() {
            return this.allColumns.filter(col => col.visible);
        },
        contractType() {
            return this.$route.path.startsWith('/international') ? 'international' : 'merchant';
        }
    },
    methods: {
        loadColumnSettings() {
            const savedSettings = localStorage.getItem('contractsColumnSettings');
            if (savedSettings) {
                try {
                    const settings = JSON.parse(savedSettings);
                    this.allColumns.forEach(col => {
                        const savedCol = settings.find(s => s.field === col.field);
                        if (savedCol !== undefined) {
                            col.visible = savedCol.visible;
                        }
                    });
                } catch (e) {
                    console.error('Error loading column settings:', e);
                }
            }
        },
        saveColumnSettings() {
            const settings = this.allColumns.map(col => ({
                field: col.field,
                visible: col.visible
            }));
            localStorage.setItem('contractsColumnSettings', JSON.stringify(settings));
        },
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

                // Update filter dropdown for partner companies
                const partnerCol = this.allColumns.find(col => col.field === 'partner_company.name');
                if (partnerCol && partnerCol.filterOptions) {
                    partnerCol.filterOptions.filterDropdownItems = this.partnerCompanies.map(p => ({
                        value: p.name,
                        text: p.name
                    }));
                }
            } catch (error) {
                console.error('Error fetching partner companies:', error);
            }
        },
        async fetchOwnCompanies() {
            try {
                const response = await axios.get('/api/own-companies');
                this.ownCompanies = response.data.data;

                // Update filter dropdown for own companies
                const ownCol = this.allColumns.find(col => col.field === 'own_company.name');
                if (ownCol && ownCol.filterOptions) {
                    ownCol.filterOptions.filterDropdownItems = this.ownCompanies.map(o => ({
                        value: o.name,
                        text: o.name
                    }));
                }
            } catch (error) {
                console.error('Error fetching own companies:', error);
            }
        },
        async fetchProducts() {
            try {
                const response = await axios.get('/api/products');
                this.products = response.data.data;

                // Update filter dropdown for products
                const productCol = this.allColumns.find(col => col.field === 'product.name');
                if (productCol && productCol.filterOptions) {
                    productCol.filterOptions.filterDropdownItems = this.products.map(p => ({
                        value: p.name,
                        text: p.name
                    }));
                }
            } catch (error) {
                console.error('Error fetching products:', error);
            }
        },
        handleFileChange(event) {
            const file = event.target.files[0];

            if (file) {
                const allowedTypes = ['application/pdf'];
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (!allowedTypes.includes(file.type) && fileExtension !== 'pdf') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Սխալ ֆայլի տեսակ',
                        text: 'Խնդրում ենք ընտրել միայն PDF ֆայլ',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });

                    event.target.value = '';
                    this.selectedFile = null;
                    return;
                }

                const maxSize = 10 * 1024 * 1024;
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ֆայլը չափազանց մեծ է',
                        text: 'Ֆայլի մաქսիմալ չափը 10MB է',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });

                    event.target.value = '';
                    this.selectedFile = null;
                    return;
                }

                this.selectedFile = file;
            }
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
                cancelButtonText: 'Չեղարկել',
                confirmButtonColor: '#d33',
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
        },
        goToPartnerCompanies() {
            this.$router.push('/partner-companies');
        },
        goToOwnCompanies() {
            this.$router.push('/own-companies');
        },
        goToProducts() {
            this.$router.push('/products');
        },
        getRowClass(row) {
            // Проверяем транзакции контракта
            if (row.transactions && row.transactions.length > 0) {
                // Проверяем есть ли транзакция со статусом cancelled
                const hasCancelled = row.transactions.some(t => t.payment_status === 'cancelled');
                if (hasCancelled) {
                    return 'table-danger'; // Красный цвет
                }

                // Проверяем есть ли транзакция со статусом notified
                const hasNotified = row.transactions.some(t => t.payment_status === 'notified');
                if (hasNotified) {
                    return 'table-warning'; // Желтый цвет
                }
            }

            return '';
        },
        exportToExcel() {
            try {
                import('xlsx').then(XLSX => {
                    const data = this.contracts.map(contract => {
                        const paymentType = contract.payment_type === 'monthly' ? 'Ամենամյա' :
                                           contract.payment_type === 'yearly' ? 'Տարեկան' :
                                           contract.payment_type === 'one_time' ? 'Միանվագ' : '';

                        const status = contract.status === 'active' ? 'Ակտիվ' :
                                      contract.status === 'completed' ? 'Ավարտված' :
                                      contract.status === 'cancelled' ? 'Չեղարկված' :
                                      contract.status === 'suspended' ? 'Կասեցված' : '';

                        return {
                            'ID': contract.id,
                            'Համար': contract.contract_number || '',
                            'Գործընկեր': contract.partner_company?.name || '',
                            'Մեր ընկերություն': contract.own_company?.name || '',
                            'Պրոդուկտ': contract.product?.name || '',
                            'Սկիզբ': this.formatDate(contract.contract_start_date) || '',
                            'Ավարտ': this.formatDate(contract.contract_end_date) || '',
                            'Վճարման օր': this.formatPaymentDate(contract.payment_date || ''),
                            'Վճարման վերջնական օր': this.formatPaymentDate(contract.payment_finish_date || ''),
                            'Հաշվեհամար': contract.account_number || '',
                            'Վճարման տեսակ': paymentType,
                            'Գումար': contract.payment_amount || '',
                            'Կարգավիճակ': status,
                        };
                    });

                    const ws = XLSX.utils.json_to_sheet(data);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Պայմանագրեր');

                    const maxWidth = data.reduce((w, r) => Math.max(w, r['Գործընկեր']?.length || 0), 10);
                    ws['!cols'] = [
                        { wch: 5 },  // ID
                        { wch: 15 }, // Համար
                        { wch: maxWidth }, // Գործընկեր
                        { wch: maxWidth }, // Մեր ընկերություն
                        { wch: 20 }, // Պրոդուկտ
                        { wch: 12 }, // Սկիզբ
                        { wch: 12 }, // Ավարտ
                        { wch: 12 }, // Վճարման օր
                        { wch: 20 }, // Վճարման վերջնական օր
                        { wch: 15 }, // Հաշվեհամար
                        { wch: 15 }, // Վճարման տեսակ
                        { wch: 12 }, // Գումար
                        { wch: 12 }, // Կարգավիճակ
                    ];

                    const fileName = `contracts_${new Date().toISOString().slice(0, 10)}.xlsx`;
                    XLSX.writeFile(wb, fileName);

                    Swal.fire({
                        icon: 'success',
                        title: 'Հաջողվեց',
                        text: 'Պայմանագրերը արտահանվեցին',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                });
            } catch (error) {
                console.error('Export error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Սխալ',
                    text: 'Չհաջողվեց արտահանել պայմանագրերը',
                    toast: true,
                    position: 'top-end',
                    timer: 2500,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }
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
    .gap-2 {
        gap: 0.5rem;
    }
    .btn-outline-success.btn-sm i {
        font-size: 0.75rem;
    }
</style>

<style>
    .vgt-table {
        font-size: 0.875rem;
    }

    .vgt-table thead th {
        font-weight: 600;
        padding: 0.75rem !important;
    }

    .merchant-table thead th {
        background-color: #0d6efd !important;
        color: white !important;
    }

    .international-table thead th {
        background-color: #343a40 !important;
        color: white !important;
    }

    .vgt-table tbody td {
        padding: 0.5rem !important;
    }

    .vgt-table.striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }

    .vgt-table.bordered td, .vgt-table.bordered th {
        border: 1px solid #dee2e6;
    }

    .vgt-wrap__footer {
        background-color: #f8f9fa;
        padding: 0.75rem;
        border-top: 2px solid #dee2e6;
    }

    .vgt-global-search {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
    }

    .vgt-global-search__input {
        border: none !important;
    }

    .vgt-select {
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        padding: 0.25rem 0.5rem !important;
    }

    .vgt-pull-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Filter dropdowns */
    .vgt-filter-row select {
        font-size: 0.875rem;
        padding: 0.25rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }

    /* Row highlighting based on transaction status */
    .vgt-table tr.table-warning {
        background-color: #fff3cd !important;
    }

    .vgt-table tr.table-warning:hover {
        background-color: #ffe69c !important;
    }

    .vgt-table tr.table-danger {
        background-color: #f8d7da !important;
    }

    .vgt-table tr.table-danger:hover {
        background-color: #f5c2c7 !important;
    }
</style>
