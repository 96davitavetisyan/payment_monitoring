<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Մեր ընկերություններ</h1>
                <button class="btn btn-success" @click="showCreateModal = true">
                    + Ավելացնել
                </button>
            </div>

            <!-- Own Companies Table -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Անուն</th>
                    <th>ՀՎՀՀ</th>
                    <th>Հասցե</th>
                    <th>Հեռախոս</th>
                    <th>Էլ․ Փոստ</th>
                    <th>Կարգավիճակ</th>
                    <th>Գործողություններ</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="company in companies" :key="company.id">
                    <td>
                        <a href="#" @click.prevent="showCompanyContracts(company)" class="text-primary" style="cursor: pointer; text-decoration: underline;">
                            {{ company.name }}
                            <span v-if="company.contracts && company.contracts.length > 0" class="badge bg-info ms-1" style="font-size: 10px;">
                                {{ company.contracts.length }}
                            </span>
                        </a>
                    </td>
                    <td>{{ company.tax_id || 'N/A' }}</td>
                    <td>{{ company.address || 'N/A' }}</td>
                    <td>{{ company.phone || 'N/A' }}</td>
                    <td>{{ company.email || 'N/A' }}</td>
                    <td>
                        <span class="badge" :class="company.is_active ? 'bg-success' : 'bg-secondary'">
                            {{ company.is_active ? 'Ակտիվ' : 'Անգործուն' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-outline-primary" @click="editCompany(company)" title="Խմբագրել">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-outline-danger" @click="deleteCompany(company.id)" title="Ջնջել">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr v-if="companies.length === 0">
                    <td colspan="8" class="text-center">No own companies found</td>
                </tr>
                </tbody>
            </table>

            <!-- Create/Edit Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showCreateModal }" style="background: rgba(0,0,0,0.5);" v-if="showCreateModal" @click.self="closeModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Մեր ընկերություն</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Անուն *</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.name}" v-model="currentCompany.name" required>
                                <div class="invalid-feedback" v-if="errors.name">
                                    {{ errors.name[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ՀՎՀՀ</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.tax_id}" v-model="currentCompany.tax_id">
                                <div class="invalid-feedback" v-if="errors.tax_id">
                                    {{ errors.tax_id[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Հասցե</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.address}" v-model="currentCompany.address">
                                <div class="invalid-feedback" v-if="errors.address">
                                    {{ errors.address[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Հեռախոս</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.phone}" v-model="currentCompany.phone">
                                <div class="invalid-feedback" v-if="errors.phone">
                                    {{ errors.phone[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Էլ․ Փոստ</label>
                                <input type="email" class="form-control" :class="{'is-invalid': errors.email}" v-model="currentCompany.email">
                                <div class="invalid-feedback" v-if="errors.email">
                                    {{ errors.email[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Կարգավիճակ</label>
                                <select class="form-select" v-model="currentCompany.is_active">
                                    <option :value="true">Ակտիվ</option>
                                    <option :value="false">Անգործուն</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeModal">Չեղարկել</button>
                            <button class="btn btn-success" @click="saveCompany">{{ isEditing ? 'Թարմացնել' : 'Ստեղծել' }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts Modal -->
            <contracts-modal
                :show="showContractsModal"
                :title="selectedCompany?.name || ''"
                :contracts="companyContracts"
                @close="closeContractsModal"
            />
        </div>
    </div>
</template>

<script>

import Swal from 'sweetalert2';
import ContractsModal from '../Shared/ContractsModal.vue';

export default {
    components: {
        ContractsModal
    },
    data() {
        return {
            companies: [],
            showCreateModal: false,
            showContractsModal: false,
            isEditing: false,
            errors: {},
            selectedCompany: null,
            companyContracts: [],
            currentCompany: {
                name: '',
                tax_id: '',
                address: '',
                phone: '',
                email: '',
                is_active: true
            }
        };
    },
    mounted() {
        this.fetchCompanies();
    },
    methods: {
        async fetchCompanies() {
            try {
                const response = await axios.get('/api/own-companies?with_contracts=1');
                this.companies = response.data.data;
            } catch (error) {
                console.error('Error fetching own companies:', error);
                alert('Failed to load own companies');
            }
        },
        editCompany(company) {
            this.currentCompany = { ...company };
            this.isEditing = true;
            this.showCreateModal = true;
        },
        async saveCompany() {
            try {
                if (this.isEditing) {
                    await axios.put(`/api/own-companies/${this.currentCompany.id}`, this.currentCompany);
                } else {
                    await axios.post('/api/own-companies', this.currentCompany);
                }
                this.fetchCompanies();
                this.closeModal();
                Swal.fire({
                    icon: 'success',
                    title: this.isEditing ? 'Մեր ընկերությունը հաջողությամբ թարմացվեց' : 'Մեր ընկերությունը հաջողությամբ ստեղծվեց',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
                this.isEditing = false;

            } catch (error) {
                console.error('Error saving own company:', error);

                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else {
                    alert('Չհաջողվեց պահպանել մեր ընկերությունը: ' + (error.response?.data?.message || error.message));
                }
            }
        },
        async deleteCompany(id) {
            try {
                const result = await Swal.fire({
                    title: 'Դուք համոզված ե՞ք',
                    text: "Այս գործողությունը հնարավոր է հետ վերադարձնել չի լինի!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Այո, ջնջել',
                    cancelButtonText: 'Չեղարկել',
                    confirmButtonColor: '#d33',
                });

                if (result.isConfirmed) {
                    await axios.delete(`/api/own-companies/${id}`);
                    this.fetchCompanies();

                    Swal.fire({
                        icon: 'success',
                        title: 'Մեր ընկերությունը հաջողությամբ ջնջվեց',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            } catch (error) {
                console.error('Error deleting own company:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Չհաջողվեց ջնջել ընկերությունը',
                    text: error.response?.data?.message || error.message
                });
            }
        },
        closeModal() {
            this.showCreateModal = false;
            this.errors = {};
            this.currentCompany = {
                name: '',
                tax_id: '',
                address: '',
                phone: '',
                email: '',
                is_active: true
            };
        },
        showCompanyContracts(company) {
            this.selectedCompany = company;
            this.companyContracts = company.contracts || [];
            this.showContractsModal = true;
        },
        closeContractsModal() {
            this.showContractsModal = false;
            this.selectedCompany = null;
            this.companyContracts = [];
        }
    }
};
</script>
