<template>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <span class="navbar-brand">Welcome, {{ user.name }}</span>
            <button class="btn btn-outline-danger" @click="logout">Logout</button>
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
            axios.get('/api/user', {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`
                }
            }).then(response => {
                this.user = response.data;
            }).catch(() => {
                // если нет токена, редирект на login
                this.$router.push('/login');
            });
        },
        logout() {
            axios.post('/api/logout', {}, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`
                }
            }).then(() => {
                localStorage.removeItem('token');
                this.$router.push('/login');
            });
        }
    }
}
</script>

