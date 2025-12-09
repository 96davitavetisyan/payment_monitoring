
require('./bootstrap');
window.Vue = require('vue').default;
import '@fortawesome/fontawesome-free/css/all.css';
import '@fortawesome/fontawesome-free/js/all.js';
import 'vue2-datepicker/index.css';

import VueGoodTablePlugin from 'vue-good-table';
import 'vue-good-table/dist/vue-good-table.css';

Vue.use(VueGoodTablePlugin);


import VueRouter from 'vue-router';
import auth from './auth';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

Vue.use(VueRouter);

Vue.prototype.$auth = auth;

import Header from './components/Layout/Header.vue';
import TransactionsIndex from './components/Transactions/TransactionsIndex.vue';
import FeedbackIndex from './components/Feedbacks/FeedbackIndex.vue';
import ProductsIndex from './components/Products/ProductsIndex.vue';
import InternationalProductsIndex from './components/InternationalProducts/ProductsIndex.vue';
import PartnerCompaniesIndex from './components/PartnerCompanies/PartnerCompaniesIndex.vue';
import OwnCompaniesIndex from './components/OwnCompanies/OwnCompaniesIndex.vue';
import ContractsIndex from './components/Contracts/ContractsIndex.vue';
import PaymentStatistics from './components/Statistics/PaymentStatistics.vue';
import UsersIndex from "./components/Users/UsersIndex.vue";
import Login from './components/Auth/Login.vue';

// Routes configuration
const routes = [
    { path: '/', redirect: '/login' },
    { path: '/login', component: Login, name: 'login' },

    {
        path: '/products',
        component: ProductsIndex,
        meta: { requiresAuth: true },
        name: 'products'
    },
    {
        path: '/partner-companies',
        component: PartnerCompaniesIndex,
        meta: { requiresAuth: true },
        name: 'partner-companies'
    },
    {
        path: '/own-companies',
        component: OwnCompaniesIndex,
        meta: { requiresAuth: true },
        name: 'own-companies'
    },
    {
        path: '/contracts',
        component: ContractsIndex,
        meta: { requiresAuth: true },
        name: 'contracts'
    },
    {
        path: '/contracts/:contractId/transactions',
        component: TransactionsIndex,
        meta: { requiresAuth: true },
        name: 'contract-transactions'
    },
    {
        path: '/products/:productId/feedbacks',
        component: FeedbackIndex,
        meta: { requiresAuth: true },
        name: 'product-feedbacks'
    },
    {
        path: '/payment-statistics',
        component: PaymentStatistics,
        meta: { requiresAuth: true },
        name: 'payment-statistics'
    },

    // International Products
    {
        path: '/international-products',
        component: InternationalProductsIndex,
        meta: { requiresAuth: true },
        name: 'international-products'
    },
    {
        path: '/international-products/:productId/feedbacks',
        component: FeedbackIndex,
        meta: { requiresAuth: true },
        name: 'international-product-feedbacks'
    },
    {
        path: '/international-partner-companies',
        component: PartnerCompaniesIndex,
        meta: { requiresAuth: true },
        name: 'international-partner-companies'
    },
    {
        path: '/international-own-companies',
        component: OwnCompaniesIndex,
        meta: { requiresAuth: true },
        name: 'international-own-companies'
    },
    {
        path: '/international-contracts',
        component: ContractsIndex,
        meta: { requiresAuth: true },
        name: 'international-contracts'
    },
    {
        path: '/international-payment-statistics',
        component: PaymentStatistics,
        meta: { requiresAuth: true },
        name: 'international-payment-statistics'
    },

    // Users
    {
        path: '/user-manager',
        component: UsersIndex,
        meta: { requiresAuth: true },
        name: 'user-manager'
    }
];

// Create router
const router = new VueRouter({
    mode: 'history',
    routes
});

// Route guard for authentication
router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && !auth.isAuthenticated()) {
        next('/login');
    } else if (to.path === '/login' && auth.isAuthenticated()) {
        next('/products');
    } else {
        next();
    }
});

// Register global components
Vue.component('app-header', Header);

// Initialize Vue app
const app = new Vue({
    el: '#app',
    router,
});
