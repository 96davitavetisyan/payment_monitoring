require('./bootstrap');
window.Vue = require('vue').default; // добавлено .default для Vue 2

import VueRouter from 'vue-router';
import axios from 'axios';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

Vue.use(VueRouter);

import Header from './components/Layout/Header.vue';
import ProjectsIndex from './components/Projects/ProjectsIndex.vue';
import Login from './components/Auth/Login.vue';

// Настройка маршрутов
const routes = [
    { path: '/login', component: Login },
    { path: '/projects', component: ProjectsIndex, meta: { requiresAuth: true } } // meta для проверки auth
];

// Создаём роутер
const router = new VueRouter({
    mode: 'history', // обязательно для SPA с Laravel
    routes
});

// Защита маршрутов
// router.beforeEach((to, from, next) => {
//     const token = localStorage.getItem('token');
//     if (to.meta.requiresAuth && !token) {
//         next('/login');
//     } else {
//         next();
//     }
// });

// Настройка axios
axios.defaults.baseURL = '/';
axios.defaults.headers.common['Authorization'] = `Bearer ${localStorage.getItem('token')}`;
Vue.component('app-header', Header);
// Инициализация Vue
const app = new Vue({
    el: '#app',
    router,
});
