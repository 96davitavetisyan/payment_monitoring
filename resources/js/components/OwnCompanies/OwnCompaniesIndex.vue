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
                    <th>Իրավաբանական անվանում</th>
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
                    <td>{{ company.name }}</td>
                    <td>{{ company.legal_name || 'N/A' }}</td>
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
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showCreateModal }" style="background: rgba(0,0,0,0.5);" v-if="showCreateModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Մեր ընկերություն</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Անուն *</label>
                                <input type="text" class="form-control" v-model="currentCompany.name" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Իրավաբանական անվանում</label>
                                <input type="text" class="form-control" v-model="currentCompany.legal_name">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ՀՎՀՀ</label>
                                <input type="text" class="form-control" v-model="currentCompany.tax_id">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Հասցե</label>
                                <input type="text" class="form-control" v-model="currentCompany.address">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Հեռախոս</label>
                                <input type="text" class="form-control" v-model="currentCompany.phone">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Էլ․ Փոստ)</label>
                                <input type="email" class="form-control" v-model="currentCompany.email">
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
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            companies: [],
            showCreateModal: false,
            isEditing: false,
            currentCompany: {
                name: '',
                legal_name: '',
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
                const response = await axios.get('/api/own-companies');
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
                alert(this.isEditing ? 'Own company updated successfully' : 'Own company created successfully');
            } catch (error) {
                console.error('Error saving own company:', error);
                alert('Failed to save own company: ' + (error.response?.data?.message || error.message));
            }
        },
        async deleteCompany(id) {
            if (confirm('Are you sure you want to delete this own company?')) {
                try {
                    await axios.delete(`/api/own-companies/${id}`);
                    this.fetchCompanies();
                    alert('Own company deleted successfully');
                } catch (error) {
                    console.error('Error deleting own company:', error);
                    alert('Failed to delete own company');
                }
            }
        },
        closeModal() {
            this.showCreateModal = false;
            this.isEditing = false;
            this.currentCompany = {
                name: '',
                legal_name: '',
                tax_id: '',
                address: '',
                phone: '',
                email: '',
                is_active: true
            };
        }
    }
};
</script>
