<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand">Վճարումների մոնիթորինգ</span>
            <div class="navbar-nav me-auto">
                <router-link to="/products" class="nav-link text-white" active-class="fw-bold">
                    Պրոդուկտներ
                </router-link>
                <router-link to="/partner-companies" class="nav-link text-white" active-class="fw-bold">
                    Գործընկերներ
                </router-link>
                <router-link to="/own-companies" class="nav-link text-white" active-class="fw-bold">
                    Մեր ընկերությունները
                </router-link>
                <router-link to="/contracts" class="nav-link text-white" active-class="fw-bold">
                    Պայմանագրեր
                </router-link>
                <router-link to="/payment-statistics" class="nav-link text-white" active-class="fw-bold">
                     Վիճակագրություն
                </router-link>
                <router-link to="/user-manager" class="nav-link text-white" active-class="fw-bold">
                    Օգտատերեր
                </router-link>
            </div>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <strong>{{ user.name }}</strong>
<!--                    <span v-if="user.roles && user.roles.length" class="badge bg-light text-dark ms-2">-->
<!--                        {{ user.roles[0] }}-->
<!--                    </span>-->
                </span>
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
