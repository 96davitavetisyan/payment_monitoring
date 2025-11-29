<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Ապրանքներ (Products)</h1>
            </div>

            <!-- Products Table -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Անուն (Name)</th>
                    <th>Սկիզբ (Start Date)</th>
                    <th>Պատասխանատու (Responsible User ID)</th>
                    <th>Կարգավիճակ (Status)</th>
                    <th>Ստեղծվել է (Created)</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="product in products" :key="product.id">
                    <td>{{ product.id }}</td>
                    <td>{{ product.name }}</td>
                    <td>{{ formatDate(product.start_date) }}</td>
                    <td>{{ product.responsible_user_id }}</td>
                    <td>
                        <span class="badge" :class="getStatusClass(product.status)">
                            {{ product.status }}
                        </span>
                    </td>
                    <td>{{ formatDate(product.created_at) }}</td>
                </tr>
                <tr v-if="products.length === 0">
                    <td colspan="6" class="text-center">No products found</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            products: []
        };
    },
    mounted() {
        this.fetchProducts();
    },
    methods: {
        async fetchProducts() {
            try {
                const response = await axios.get('/api/products');
                this.products = response.data.success ? response.data.data : response.data.data || response.data;
                console.log('Products loaded:', this.products);
            } catch (error) {
                console.error('Error fetching products:', error);
                alert('Failed to load products: ' + (error.response?.data?.message || error.message));
            }
        },
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString();
        },
        getStatusClass(status) {
            const classes = {
                'active': 'bg-success',
                'suspended': 'bg-warning',
                'completed': 'bg-primary',
                'cancelled': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        }
    }
};
</script>
