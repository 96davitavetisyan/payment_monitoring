<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Գործընկեր կազմակերպություններ</h1>
                <button class="btn btn-success" @click="showCreateModal = true">
                    + Ավելացնել
                </button>
            </div>

            <!-- Partner Companies Table -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Կազմակերպության անվանումը</th>
                    <th>ՀՎՀՀ</th>
                    <th>Կոնտակտային անձ</th>
                    <th>Կոնտակտային անձի պաշտոնը</th>
                    <th>Էլ․ Փոստ</th>
                    <th>Հեռախոս</th>
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
                    <td>{{ company.tax_id }}</td>
                    <td>{{ company.contact_person || 'N/A' }}</td>
                    <td>{{ company.contact_person_position || 'N/A' }}</td>
                    <td>{{ company.contact_email || 'N/A' }}</td>
                    <td>{{ company.contact_phone || 'N/A' }}</td>
                    <td>
                        <span class="badge" :class="company.is_active ? 'bg-success' : 'bg-secondary'">
                            {{ company.is_active ? 'Ակտիվ' : 'Անգործուն' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-outline-primary" @click="editCompany(company)">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-outline-danger" @click="deleteCompany(company.id)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr v-if="companies.length === 0">
                    <td colspan="6" class="text-center">Գործընկեր ընկերություններ չեն գտնվել</td>
                </tr>
                </tbody>
            </table>

            <!-- Create/Edit Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showCreateModal }" style="background: rgba(0,0,0,0.5);" v-if="showCreateModal" @click.self="closeModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Գործընկեր կազմակերպություններ</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Կազմակերպության անվանումը</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.name}" v-model="currentCompany.name" required>
                                <div class="invalid-feedback" v-if="errors.name">
                                    {{ errors.name[0] }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ՀՎՀՀ</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.tax_id}" v-model="currentCompany.tax_id" required>
                                <div class="invalid-feedback" v-if="errors.tax_id">
                                    {{ errors.tax_id[0] }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Կոնտակտային անձ</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.contact_person}" v-model="currentCompany.contact_person">
                                <div class="invalid-feedback" v-if="errors.contact_person">
                                    {{ errors.contact_person[0] }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Կոնտակտային անձի պաշտոնը</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.contact_person_position}" v-model="currentCompany.contact_person_position">
                                <div class="invalid-feedback" v-if="errors.contact_person_position">
                                    {{ errors.contact_person_position[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Էլ․ Փոստ </label>
                                <input type="email" class="form-control" :class="{'is-invalid': errors.contact_email}" v-model="currentCompany.contact_email">
                                <div class="invalid-feedback" v-if="errors.contact_email">
                                    {{ errors.contact_email[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Հեռախոս</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.contact_phone}" v-model="currentCompany.contact_phone">
                                <div class="invalid-feedback" v-if="errors.contact_phone">
                                    {{ errors.contact_phone[0] }}
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
                contact_person: '',
                contact_person_position: '',
                contact_email: '',
                contact_phone: '',
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
                const response = await axios.get('/api/partner-companies?with_contracts=1');
                this.companies = response.data.data;
            } catch (error) {
                console.error('Error fetching partner companies:', error);

                Swal.fire({
                    icon: 'error',
                    title: 'Սխալ',
                    text: 'Չհաջողվեց բեռնել գործընկեր կազմակերպությունների ցանկը։',
                    toast: true,
                    position: 'top-end',
                    timer: 2500,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
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
                    await axios.put(`/api/partner-companies/${this.currentCompany.id}`, this.currentCompany);
                } else {
                    await axios.post('/api/partner-companies', this.currentCompany);
                }

                this.fetchCompanies();
                this.closeModal();

                Swal.fire({
                    icon: 'success',
                    title: this.isEditing ? 'Թարմացված է' : ' Ստեղծված է',
                    text: this.isEditing
                        ? 'Գործընկեր կազմակերպությունը հաջողությամբ թարմացվեց։'
                        : 'Գործընկեր կազմակերպությունը հաջողությամբ ստեղծվեց։',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
                this.isEditing = false;


            } catch (error) {
                console.error('Error saving partner company:', error);

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
                        text: error.response?.data?.message
                            ? `Չհաջողվեց պահպանել․ ${error.response.data.message}`
                            : 'Չհաջողվեց պահպանել գործընկեր կազմակերպությունը։',
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            }
        },
        async deleteCompany(id) {
            const result = await Swal.fire({
                title: 'Ջնջե՞լ ընկերությունը',
                text: 'Վստա՞հ եք, որ ցանկանում եք ջնջել այս գործընկեր կազմակերպությանը։',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Այո, ջնջել',
                cancelButtonText: 'Չեղարկել',
                confirmButtonColor: '#d33',
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/api/partner-companies/${id}`);
                    this.fetchCompanies();

                    Swal.fire({
                        icon: 'success',
                        title: 'Ջնջված է',
                        text: 'Ընկերությունը հաջողությամբ ջնջվեց։',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });

                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Սխալ',
                        text: 'Չհաջողվեց ջնջել ընկերությունը։',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            }
        },

        closeModal() {
            this.showCreateModal = false;
            this.errors = {};
            this.currentCompany = {
                name: '',
                tax_id: '',
                contact_person: '',
                contact_person_position: '',
                contact_email: '',
                contact_phone: '',
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
