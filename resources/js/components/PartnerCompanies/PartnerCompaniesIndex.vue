<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Գործընկեր ընկերություններ (Partner Companies)</h1>
                <button class="btn btn-success" @click="showCreateModal = true">
                    + Ավելացնել
                </button>
            </div>

            <!-- Partner Companies Table -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Անուն (Name)</th>
                    <th>Կապ (Contact Person)</th>
                    <th>Էլ․ Փոստ (Email)</th>
                    <th>Հեռախոս (Phone)</th>
                    <th>Կարգավիճակ (Status)</th>
                    <th>Գործողություններ (Actions)</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="company in companies" :key="company.id">
                    <td>{{ company.name }}</td>
                    <td>{{ company.contact_person || 'N/A' }}</td>
                    <td>{{ company.contact_email || 'N/A' }}</td>
                    <td>{{ company.contact_phone || 'N/A' }}</td>
                    <td>
                        <span class="badge" :class="company.is_active ? 'bg-success' : 'bg-secondary'">
                            {{ company.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary me-2" @click="editCompany(company)">
                            Խմբագրել
                        </button>
                        <button class="btn btn-sm btn-danger" @click="deleteCompany(company.id)">
                            Ջնջել
                        </button>
                    </td>
                </tr>
                <tr v-if="companies.length === 0">
                    <td colspan="6" class="text-center">No partner companies found</td>
                </tr>
                </tbody>
            </table>

            <!-- Create/Edit Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showCreateModal }" style="background: rgba(0,0,0,0.5);" v-if="showCreateModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Գործընկեր ընկերություն</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Անուն (Name) *</label>
                                <input type="text" class="form-control" v-model="currentCompany.name" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Կապ (Contact Person)</label>
                                <input type="text" class="form-control" v-model="currentCompany.contact_person">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Էլ․ Փոստ (Email)</label>
                                <input type="email" class="form-control" v-model="currentCompany.contact_email">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Հեռախոս (Phone)</label>
                                <input type="text" class="form-control" v-model="currentCompany.contact_phone">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Կարգավիճակ (Status)</label>
                                <select class="form-select" v-model="currentCompany.is_active">
                                    <option :value="true">Active</option>
                                    <option :value="false">Inactive</option>
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
                contact_person: '',
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
                const response = await axios.get('/api/partner-companies');
                this.companies = response.data.data;
            } catch (error) {
                console.error('Error fetching partner companies:', error);
                alert('Failed to load partner companies');
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
                alert(this.isEditing ? 'Partner company updated successfully' : 'Partner company created successfully');
            } catch (error) {
                console.error('Error saving partner company:', error);
                alert('Failed to save partner company: ' + (error.response?.data?.message || error.message));
            }
        },
        async deleteCompany(id) {
            if (confirm('Are you sure you want to delete this partner company?')) {
                try {
                    await axios.delete(`/api/partner-companies/${id}`);
                    this.fetchCompanies();
                    alert('Partner company deleted successfully');
                } catch (error) {
                    console.error('Error deleting partner company:', error);
                    alert('Failed to delete partner company');
                }
            }
        },
        closeModal() {
            this.showCreateModal = false;
            this.isEditing = false;
            this.currentCompany = {
                name: '',
                contact_person: '',
                contact_email: '',
                contact_phone: '',
                is_active: true
            };
        }
    }
};
</script>
