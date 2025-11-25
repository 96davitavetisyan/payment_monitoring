<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Products & Companies</h2>
            </div>

            <!-- Products List -->
            <div class="row">
                <div v-for="product in products" :key="product.id" class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ product.name }}</h5>
                            <p class="card-text text-muted">{{ product.description }}</p>
                            <h6 class="text-primary">${{ parseFloat(product.monthly_price).toFixed(2) }}/month</h6>
                            <span class="badge" :class="product.is_active ? 'bg-success' : 'bg-secondary'">
                                {{ product.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-primary w-100" @click="viewCompanies(product)">
                                View Companies ({{ product.companies_count || 0 }})
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Companies Modal -->
            <div v-if="showCompaniesModal" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Companies using {{ selectedProduct?.name }}</h5>
                            <button type="button" class="btn-close" @click="closeCompaniesModal"></button>
                        </div>
                        <div class="modal-body">
                            <div v-if="loadingCompanies" class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div v-else>
                                <p class="text-muted mb-3">
                                    Total Companies: {{ companiesList.length }}
                                </p>
                                <div v-if="companiesList.length === 0" class="alert alert-info">
                                    No companies are using this product yet.
                                </div>
                                <div class="table-responsive" v-else>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Company Name</th>
                                                <th>Contact Email</th>
                                                <th>Subscription Status</th>
                                                <th>Price/Month</th>
                                                <th>Start Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="company in companiesList" :key="company.id">
                                                <td>{{ company.name }}</td>
                                                <td>{{ company.contact_email || 'N/A' }}</td>
                                                <td>
                                                    <span v-if="company.subscription"
                                                          class="badge"
                                                          :class="getSubscriptionClass(company.subscription.status)">
                                                        {{ company.subscription.status }}
                                                    </span>
                                                    <span v-else class="badge bg-secondary">No Subscription</span>
                                                </td>
                                                <td>
                                                    <span v-if="company.subscription">
                                                        ${{ parseFloat(company.subscription.price_per_month).toFixed(2) }}
                                                    </span>
                                                    <span v-else>-</span>
                                                </td>
                                                <td>
                                                    <span v-if="company.subscription">
                                                        {{ formatDate(company.subscription.starts_from) }}
                                                    </span>
                                                    <span v-else>-</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeCompaniesModal">Close</button>
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
            products: [],
            selectedProduct: null,
            companiesList: [],
            showCompaniesModal: false,
            loadingCompanies: false
        };
    },
    mounted() {
        this.fetchProducts();
    },
    methods: {
        fetchProducts() {
            axios.get('/api/products')
                .then(res => {
                    this.products = res.data.success ? res.data.data : res.data;
                    // Fetch companies count for each product
                    this.products.forEach(product => {
                        this.fetchCompaniesCount(product);
                    });
                })
                .catch(err => {
                    console.error('Error fetching products:', err);
                    alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                });
        },
        fetchCompaniesCount(product) {
            axios.get(`/api/products/${product.id}/companies`)
                .then(res => {
                    product.companies_count = res.data.total_companies || 0;
                    this.$forceUpdate();
                })
                .catch(err => {
                    console.error('Error fetching companies count:', err);
                });
        },
        viewCompanies(product) {
            this.selectedProduct = product;
            this.showCompaniesModal = true;
            this.loadingCompanies = true;
            this.companiesList = [];

            axios.get(`/api/products/${product.id}/companies`)
                .then(res => {
                    this.companiesList = res.data.companies || [];
                    this.loadingCompanies = false;
                })
                .catch(err => {
                    console.error('Error fetching companies:', err);
                    alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                    this.loadingCompanies = false;
                });
        },
        closeCompaniesModal() {
            this.showCompaniesModal = false;
            this.selectedProduct = null;
            this.companiesList = [];
        },
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString();
        },
        getSubscriptionClass(status) {
            const classes = {
                'active': 'bg-success',
                'stopped': 'bg-warning',
                'cancelled': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        }
    }
};
</script>
