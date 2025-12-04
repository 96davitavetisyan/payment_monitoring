<template>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Login</h3>
                        <form @submit.prevent="login">
                            <div class="mb-3">
                                <input
                                    type="email"
                                    v-model="email"
                                    class="form-control"
                                    placeholder="Email"
                                    required
                                >
                            </div>
                            <div class="mb-3">
                                <input
                                    type="password"
                                    v-model="password"
                                    class="form-control"
                                    placeholder="Password"
                                    required
                                >
                            </div>
                            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                                {{ loading ? 'Logging in...' : 'Login' }}
                            </button>
                            <div v-if="error" class="alert alert-danger mt-3" role="alert">
                                {{ error }}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import auth from '../../auth';

export default {
    data() {
        return {
            email: '',
            password: '',
            error: null,
            loading: false
        }
    },
    methods: {
        async login() {
            this.loading = true;
            this.error = null;

            try {
                const res = await axios.post('/api/login', {
                    email: this.email,
                    password: this.password
                });

                // Store token and user data
                auth.setToken(res.data.token);
                auth.setUser(res.data.user);

                // Update axios default header
                axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.token}`;

                // Redirect to products
                this.$router.push('/products');
            } catch (err) {
                this.error = err.response?.data?.message || 'Login failed. Please try again.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
