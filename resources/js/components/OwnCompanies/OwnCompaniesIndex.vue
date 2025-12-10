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
            <table class="table table-striped table-bordered" :style="{ borderColor: companyType === 'local' ? '#0d6efd' : '#212529', borderWidth: '2px' }">
                <thead :class="companyType === 'local' ? 'table-primary' : 'table-dark'">
                <tr>
                    <th>Անուն</th>
                    <th>ՀՎՀՀ</th>
                    <th>Հասցե</th>
                    <th>Հեռախոս</th>
                    <th>Էլ․ Փոստ</th>
                    <th>Ռեկվիզիտներ</th>
                    <th>Ֆայլեր</th>
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
                        <div v-if="company.requisites"
                             @click="copyToClipboard(company.requisites)"
                             style="cursor: pointer; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                             :title="company.requisites"
                             class="text-primary">
                            <i class="fa-solid fa-copy me-1"></i>
                            {{ company.requisites }}
                        </div>
                        <span v-else class="text-muted">-</span>
                    </td>
                    <td>
                        <div v-if="company.files && company.files.length > 0">
                            <span class="badge bg-primary">{{ company.files.length }} ֆայլ</span>
                            <button class="btn btn-sm btn-link" @click="showFiles(company)" title="Դիտել ֆայլերը">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <span v-else class="text-muted">-</span>
                    </td>
                    <td>
                        {{company.is_active}}
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
                    <td colspan="10" class="text-center">No own companies found</td>
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
                                <label class="form-label">Ռեկվիզիտներ</label>
                                <textarea class="form-control" :class="{'is-invalid': errors.requisites}" v-model="currentCompany.requisites" rows="4" placeholder="Մուտքագրեք ընկերության ռեկվիզիտները..."></textarea>
                                <div class="invalid-feedback" v-if="errors.requisites">
                                    {{ errors.requisites[0] }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ֆայլեր</label>
                                <input type="file" class="form-control" @change="handleFileSelect" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx">
                                <small class="text-muted">Կարող եք ընտրել մի քանի ֆայլեր (մաքս. 10MB յուրաքանչյուր)</small>
                                <div v-if="selectedFiles.length > 0" class="mt-2">
                                    <div v-for="(file, index) in selectedFiles" :key="index" class="badge bg-secondary me-1 mb-1">
                                        {{ file.name }} ({{ formatFileSize(file.size) }})
                                        <button type="button" class="btn-close btn-close-white ms-1" style="font-size: 10px;" @click="removeSelectedFile(index)"></button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Կարգավիճակ</label>
                                {{currentCompany.is_active}}
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
            <!-- Files Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showFilesModal }" style="background: rgba(0,0,0,0.5);" v-if="showFilesModal" @click.self="closeFilesModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ֆայլեր - {{ selectedCompany?.name }}</h5>
                            <button type="button" class="btn-close" @click="closeFilesModal"></button>
                        </div>
                        <div class="modal-body">
                            <div v-if="selectedCompany && selectedCompany.files && selectedCompany.files.length > 0">
                                <ul class="list-group">
                                    <li v-for="file in selectedCompany.files" :key="file.id" class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa-solid fa-file me-2"></i>
                                            <a :href="`/storage/${file.file_path}`" target="_blank" class="text-decoration-none">
                                                {{ file.file_name }}
                                            </a>
                                            <small class="text-muted ms-2">({{ formatFileSize(file.file_size) }})</small>
                                        </div>
                                        <button class="btn btn-sm btn-danger" @click="deleteFile(file.id)" title="Ջնջել">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div v-else class="text-center text-muted">
                                Ֆայլեր չկան
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeFilesModal">Փակել</button>
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
import boolean from "vue-good-table/src/components/types/boolean";

export default {
    components: {
        ContractsModal
    },
    data() {
        return {
            companies: [],
            showCreateModal: false,
            showContractsModal: false,
            showFilesModal: false,
            isEditing: false,
            errors: {},
            selectedCompany: null,
            companyContracts: [],
            selectedFiles: [],
            currentCompany: {
                name: '',
                tax_id: '',
                address: '',
                phone: '',
                email: '',
                requisites: '',
                is_active: true
            }
        };
    },
    computed: {
        boolean() {
            return boolean
        },
        companyType() {
            return this.$route.path.startsWith('/international') ? 'international' : 'local';
        }
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
                const formData = new FormData();
                console.log(this.currentCompany)
                Object.keys(this.currentCompany).forEach(key => {
                    if (this.currentCompany[key] !== null && this.currentCompany[key] !== undefined) {
                        if(key === 'is_active'){
                            formData.append(key, this.currentCompany[key] ? 1 : 0)
                        }else{
                            formData.append(key, this.currentCompany[key]);
                        }
                    }
                });

                this.selectedFiles.forEach((file, index) => {
                    formData.append(`files[${index}]`, file);
                });

                if (this.isEditing) {
                    formData.append('_method', 'PUT');
                    await axios.post(`/api/own-companies/${this.currentCompany.id}`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });
                } else {
                    await axios.post('/api/own-companies', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });
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
            this.selectedFiles = [];
            this.currentCompany = {
                name: '',
                tax_id: '',
                address: '',
                phone: '',
                email: '',
                requisites: '',
                is_active: true
            };
            this.isEditing = false;

        },
        copyToClipboard(text) {
            // Try modern clipboard API first
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Պատճենվեց',
                        text: 'Ռեկվիզիտները պատճենվեցին',
                        toast: true,
                        position: 'top-end',
                        timer: 1500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                    this.fallbackCopyToClipboard(text);
                });
            } else {
                // Fallback for older browsers or HTTP
                this.fallbackCopyToClipboard(text);
            }
        },
        fallbackCopyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);

                if (successful) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Պատճենվեց',
                        text: 'Ռեկվիզիտները պատճենվեցին clipboard-ում',
                        toast: true,
                        position: 'top-end',
                        timer: 1500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                } else {
                    throw new Error('Copy command failed');
                }
            } catch (err) {
                document.body.removeChild(textArea);
                console.error('Fallback: Failed to copy text: ', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Սխալ',
                    text: 'Չհաջողվեց պատճենել տեքստը',
                    toast: true,
                    position: 'top-end',
                    timer: 1500,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }
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
        },

        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.selectedFiles = [...this.selectedFiles, ...files];
        },
        removeSelectedFile(index) {
            this.selectedFiles.splice(index, 1);
        },
        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            if (bytes < 1024) {
                return bytes + ' B';
            } else if (bytes < 1048576) {
                return Math.round(bytes / 1024 * 100) / 100 + ' KB';
            } else {
                return Math.round(bytes / 1048576 * 100) / 100 + ' MB';
            }
        },
        showFiles(company) {
            this.selectedCompany = company;
            this.showFilesModal = true;
        },
        closeFilesModal() {
            this.showFilesModal = false;
            this.selectedCompany = null;
        },
        async deleteFile(fileId) {
            try {
                const result = await Swal.fire({
                    title: 'Դուք համոզված ե՞ք',
                    text: "Ցանկանու՞մ եք ջնջել այս ֆայլը:",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Այո, ջնջել',
                    cancelButtonText: 'Չեղարկել',
                    confirmButtonColor: '#d33',
                });

                if (result.isConfirmed) {
                    await axios.delete(`/api/own-companies/${this.selectedCompany.id}/files/${fileId}`);

                    // Refresh companies list
                    await this.fetchCompanies();

                    // Update selected company
                    const updatedCompany = this.companies.find(c => c.id === this.selectedCompany.id);
                    if (updatedCompany) {
                        this.selectedCompany = updatedCompany;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Ֆայլը հաջողությամբ ջնջվեց',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            } catch (error) {
                console.error('Error deleting file:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Չհաջողվեց ջնջել ֆայլը',
                    text: error.response?.data?.message || error.message
                });
            }
        }
    }
};
</script>
