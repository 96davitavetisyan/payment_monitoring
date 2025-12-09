<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Միջազգային Պրոդուկտներ</h1>
                <button class="btn btn-success" @click="showCreateModal = true">
                    + Ավելացնել
                </button>
            </div>

            <!-- Products Table -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Անուն</th>
                    <th>Ընկերություն</th>
                    <th>Կարգավիճակ</th>
                    <th>Ստեղծվել է</th>
                    <th>Գործողություններ</th>
                </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products" :key="product.id">
                        <td>{{ product.id }}</td>
                        <td>
                            <a href="#" @click.prevent="showProductContracts(product)" class="text-primary" style="cursor: pointer; text-decoration: underline;">
                                {{ product.name }}
                                <span v-if="product.contracts && product.contracts.length > 0" class="badge bg-info ms-1" style="font-size: 10px;">
                                    {{ product.contracts.length }}
                                </span>
                            </a>
                        </td>
                        <td>
                            <span v-if="product.own_company">
                                {{ product.own_company.name }}
                            </span>
                            <span v-else class="text-muted">-</span>
                        </td>
                        <td>
                            <span class="badge" :class="getStatusClass(product.status)">
                                {{ product.status === 'active' ? 'Ակտիվ' : 'Կասեցված' }}
                            </span>
                        </td>
                        <td>{{ formatDate(product.created_at) }}</td>
                        <td>
                            <button class="btn btn-outline-info btn-sm me-1" @click="$router.push(`/products/${product.id}/feedbacks`)" title="Ֆիդբեքներ">
                                <i class="fa-solid fa-comments"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-sm me-1" @click="editProduct(product)" title="Խմբագրել">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" @click="deleteProduct(product.id)" title="Ջնջել">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="products.length === 0">
                        <td colspan="6" class="text-center">No products found</td>
                    </tr>
                </tbody>
            </table>

            <!-- Create/Edit Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showCreateModal }" style="background: rgba(0,0,0,0.5);" v-if="showCreateModal" @click.self="closeModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Պրոդուկտ</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Անվանում *</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.name}" v-model="currentProduct.name" required>
                                <div class="invalid-feedback" v-if="errors.name">
                                    {{ errors.name[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ընկերություն</label>
                                <select class="form-select" :class="{'is-invalid': errors.own_company_id}" v-model="currentProduct.own_company_id">
                                    <option value="">Ընտրել ընկերություն...</option>
                                    <option v-for="company in ownCompanies" :key="company.id" :value="company.id">
                                        {{ company.name }}
                                    </option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.own_company_id">
                                    {{ errors.own_company_id[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Կարգավիճակ *</label>
                                <select class="form-select" :class="{'is-invalid': errors.status}" v-model="currentProduct.status" required>
                                    <option value="active">Ակտիվ</option>
                                    <option value="suspended">Կասեցված</option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.status">
                                    {{ errors.status[0] }}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeModal">Չեղարկել</button>
                            <button class="btn btn-success" @click="saveProduct">{{ isEditing ? 'Թարմացնել' : 'Ստեղծել' }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts Modal -->
            <contracts-modal
                :show="showContractsModal"
                :title="selectedProduct?.name || ''"
                :contracts="productContracts"
                @close="closeContractsModal"
            />
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import ContractsModal from '../Shared/ContractsModal.vue';
import dateMixin from '../../mixins/dateMixin';

import Swal from 'sweetalert2';

export default {
    mixins: [dateMixin],
    components: {
        ContractsModal
    },
    data() {
        return {
            products: [],
            ownCompanies: [],
            showContractsModal: false,
            showCreateModal: false,
            selectedProduct: null,
            productContracts: [],
            isEditing: false,
            errors: {},
            currentProduct: {
                name: '',
                status: 'active',
                own_company_id: '',
                type: 'international'
            }
        };
    },
    mounted() {
        this.fetchProducts();
        this.fetchOwnCompanies();
    },
    methods: {
        async fetchProducts() {
            try {
                const response = await axios.get('/api/products?with_contracts=1&type=international');
                this.products = response.data.success ? response.data.data : response.data.data || response.data;
                console.log('Products loaded:', this.products);
            } catch (error) {
                console.error('Error fetching products:', error);
                alert('Failed to load products: ' + (error.response?.data?.message || error.message));
            }
        },
        async fetchOwnCompanies() {
            try {
                const response = await axios.get('/api/own-companies');
                this.ownCompanies = response.data.data || response.data;
            } catch (error) {
                console.error('Error fetching own companies:', error);
            }
        },
        getStatusClass(status) {
            const classes = {
                'active': 'bg-success',
                'suspended': 'bg-warning',
                'completed': 'bg-primary',
                'cancelled': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        },
        showProductContracts(product) {
            this.selectedProduct = product;
            this.productContracts = product.contracts || [];
            this.showContractsModal = true;
        },
        closeContractsModal() {
            this.showContractsModal = false;
            this.selectedProduct = null;
            this.productContracts = [];
        },
        editProduct(product) {
            this.currentProduct = {
                id: product.id,
                name: product.name,
                status: product.status,
                own_company_id: product.own_company_id || ''
            };
            this.isEditing = true;
            this.showCreateModal = true;
        },
        async saveProduct() {
            try {
                if (this.isEditing) {
                    await axios.put(`/api/products/${this.currentProduct.id}`, this.currentProduct);
                } else {
                    await axios.post('/api/products', this.currentProduct);
                }

                this.fetchProducts();
                this.closeModal();
                Swal.fire({
                    icon: 'success',
                    title: this.isEditing ? 'Պրոդուկտը հաջողությամբ թարմացվեց' : 'Պրոդուկտը հաջողությամբ ստեղծվեց',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });

                this.isEditing = false;

            } catch (error) {
                console.error('Error saving product:', error);

                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                    Swal.fire({
                        icon: 'warning',
                        title: 'Խնդրում ենք ստուգել լրացված դաշտերը',
                        text: Object.values(this.errors).flat().join('\n'),
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Չհաջողվեց պահպանել պրոդուկտը',
                        text: error.response?.data?.message || error.message,
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            }
        },
        async deleteProduct(id) {
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
                    await axios.delete(`/api/products/${id}`);
                    this.fetchProducts();

                    Swal.fire({
                        icon: 'success',
                        title: 'Պրոդուկտը հաջողությամբ ջնջվեց',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            } catch (error) {
                console.error('Error deleting product:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Չհաջողվեց ջնջել պրոդուկտը',
                    text: error.response?.data?.message || error.message,
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }
        },
        closeModal() {
            this.showCreateModal = false;
            this.errors = {};
            this.currentProduct = {
                name: '',
                status: 'active',
                own_company_id: '',
                type: 'international'
            };
        }
    }
};
</script>
