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
                            <button type="submit" class="btn btn-primary w-100">Login</button>
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

export default {
    data() {
        return {
            email: '',
            password: '',
            error: null
        }
    },
    methods: {
        async login() {
            try {
                const res = await axios.post('/api/login', {
                    email: this.email,
                    password: this.password
                });
                localStorage.setItem('token', res.data.token);
                this.$router.push('/projects');
            } catch (err) {
                this.error = err.response.data.message || 'Ошибка входа';
            }
        }
    }
}
</script>
