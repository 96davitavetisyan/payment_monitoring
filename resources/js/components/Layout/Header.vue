<template>
    <nav class="navbar navbar-expand-lg navbar-dark" :class="isInternationalRoute ? 'bg-dark' : 'bg-primary'">
        <div class="container-fluid">
            <span class="navbar-brand">Վճարումների մոնիթորինգ</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- Local Products Section -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="localProductsDropdown" role="button" data-bs-toggle="dropdown">
                            Մեր պրոդուկտներ
                        </a>
                        <ul class="dropdown-menu">
                            <li><router-link to="/products" class="dropdown-item">Պրոդուկտներ</router-link></li>
                            <li><router-link to="/partner-companies" class="dropdown-item">Գործընկերներ</router-link></li>
                            <li><router-link to="/own-companies" class="dropdown-item">Մեր ընկերությունները</router-link></li>
                            <li><router-link to="/contracts" class="dropdown-item">Պայմանագրեր</router-link></li>
                            <li><router-link to="/payment-statistics" class="dropdown-item">Վիճակագրություն</router-link></li>
                        </ul>
                    </li>

                    <!-- International Products Section -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="intProductsDropdown" role="button" data-bs-toggle="dropdown">
                            Միջազգային պրոդուկտներ
                        </a>
                        <ul class="dropdown-menu">
                            <li><router-link to="/international-products" class="dropdown-item">Պրոդուկտներ</router-link></li>
                            <li><router-link to="/international-partner-companies" class="dropdown-item">Գործընկերներ</router-link></li>
                            <li><router-link to="/international-own-companies" class="dropdown-item">Մեր ընկերությունները</router-link></li>
                            <li><router-link to="/international-contracts" class="dropdown-item">Պայմանագրեր</router-link></li>
                            <li><router-link to="/international-payment-statistics" class="dropdown-item">Վիճակագրություն</router-link></li>
                        </ul>
                    </li>

                </ul>
            </div>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <strong>{{ user.name }}</strong>
                </span>

                <!-- Settings Dropdown -->
                <div class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-gear"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><router-link to="/user-manager" class="dropdown-item">Օգտատերեր</router-link></li>
                    </ul>
                </div>

                <button class="btn btn-outline-light btn-sm" @click="logout">Ելք</button>
            </div>
        </div>
    </nav>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            user: {}
        }
    },
    computed: {
        isInternationalRoute() {
            return this.$route.path.startsWith('/international');
        }
    },
    created() {
        this.fetchUser();
    },
    methods: {
        fetchUser() {
            // Get user from localStorage first (faster)
            const cachedUser = this.$auth.getUser();
            if (cachedUser) {
                this.user = cachedUser;
            }

            // Also fetch fresh data from API
            axios.get('/api/user')
                .then(response => {
                    this.user = response.data;
                    this.$auth.setUser(response.data);
                })
                .catch(() => {
                    // If error, user will be redirected by axios interceptor
                });
        },
        logout() {
            axios.post('/api/logout')
                .then(() => {
                    this.$auth.logout();
                    this.$router.push('/login');
                })
                .catch(() => {
                    // Even if API call fails, clear local data and redirect
                    this.$auth.logout();
                    this.$router.push('/login');
                });
        }
    }
}
</script>
