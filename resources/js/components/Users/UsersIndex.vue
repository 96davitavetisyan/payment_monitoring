<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Օգտատերեր</h1>
                {{isSuperAdmin}}-----

                <button class="btn btn-success" @click="openCreateModal">
                    + Ավելացնել
                </button>
            </div>

            <!-- Users Table -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Անուն</th>
                    <th>Էլ. հասցե</th>
                    <th>Դեր</th>
                    <th>Պայմանագրեր</th>
                    <th>Ստեղծվել է</th>
                    <th>Գործողություններ</th>
                </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td>{{ user.id }}</td>
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>
                            <span class="badge bg-primary" v-for="role in user.roles" :key="role.id">
                                {{ role.name }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ user.contracts ? user.contracts.length : 0 }}
                            </span>
                        </td>
                        <td>{{ formatDate(user.created_at) }}</td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm me-1" @click="editUser(user)" title="Խմբագրել">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" @click="deleteUser(user.id)" title="Ջնջել">
<!--                                    v-if="isSuperAdmin && user.id !== currentUser.id"-->

                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="7" class="text-center">Օգտատերեր չկան</td>
                    </tr>
                </tbody>
            </table>

            <!-- Create/Edit Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showModal }" style="background: rgba(0,0,0,0.5);" v-if="showModal" @click.self="closeModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Օգտատեր</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Անուն *</label>
                                <input type="text" class="form-control" :class="{'is-invalid': errors.name}" v-model="currentUser.name" required>
                                <div class="invalid-feedback" v-if="errors.name">
                                    {{ errors.name[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Էլ. հասցե *</label>
                                <input type="email" class="form-control" :class="{'is-invalid': errors.email}" v-model="currentUser.email" required>
                                <div class="invalid-feedback" v-if="errors.email">
                                    {{ errors.email[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Գաղտնաբառ {{ isEditing ? '' : '*' }}</label>
                                <input type="password" class="form-control" :class="{'is-invalid': errors.password}" v-model="currentUser.password" :required="!isEditing">
                                <small class="form-text text-muted" v-if="isEditing">
                                    Թողեք դատարկ, եթե չեք ցանկանում փոխել
                                </small>
                                <div class="invalid-feedback" v-if="errors.password">
                                    {{ errors.password[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Դեր *</label>
                                <select class="form-select" :class="{'is-invalid': errors.role}" v-model="currentUser.role" required>
                                    <option value="">Ընտրել դեր...</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.role">
                                    {{ errors.role[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Պայմանագրեր</label>
                                <select multiple class="form-select" :class="{'is-invalid': errors.contracts}" v-model="currentUser.contracts" style="height: 150px;">
                                    <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                                        {{ contract.contract_number }} - {{ contract.partner_company?.name }}
                                    </option>
                                </select>
                                <small class="form-text text-muted">
                                    Սեղմեք Ctrl+Click բազմակի ընտրելու համար
                                </small>
                                <div class="invalid-feedback" v-if="errors.contracts">
                                    {{ errors.contracts[0] }}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeModal">Չեղարկել</button>
                            <button class="btn btn-success" @click="saveUser">{{ isEditing ? 'Թարմացնել' : 'Ստեղծել' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import dateMixin from '../../mixins/dateMixin';
import Swal from 'sweetalert2';

export default {
    mixins: [dateMixin],
    data() {
        return {
            users: [],
            contracts: [],
            roles: [],
            showModal: false,
            isEditing: false,
            errors: {},
            currentUser: {
                name: '',
                email: '',
                password: '',
                role: '',
                contracts: []
            },
            loggedInUser: null
        };
    },
    computed: {
        isSuperAdmin() {
            return this.loggedInUser && this.loggedInUser.roles &&
                   this.loggedInUser.roles.some(role => role.name === 'super-admin');
        }
    },
    mounted() {
        // this.fetchCurrentUser();
        this.fetchUsers();
        this.fetchContracts();
        this.fetchRoles();
    },
    methods: {
        async fetchCurrentUser() {
            try {
                const response = await axios.get('/api/user');
                this.loggedInUser = response.data;
                console.log(this.loggedInUser)
            } catch (error) {
                console.error('Error fetching current user:', error);
            }
        },
        async fetchUsers() {
            try {
                const response = await axios.get('/api/user-management');
                this.users = response.data.data || response.data;
            } catch (error) {
                console.error('Error fetching users:', error);
                if (error.response?.status === 403) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Մուտքի թույլտվություն չկա',
                        text: 'Միայն super-admin-ը կարող է դիտել օգտատերերին',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                    this.$router.push('/products');
                }
            }
        },
        async fetchContracts() {
            try {
                const response = await axios.get('/api/contracts');
                this.contracts = response.data.data || response.data;
            } catch (error) {
                console.error('Error fetching contracts:', error);
            }
        },
        async fetchRoles() {
            try {
                const response = await axios.get('/api/roles');
                this.roles = response.data.data || response.data;
            } catch (error) {
                console.error('Error fetching roles:', error);
            }
        },
        openCreateModal() {
            this.isEditing = false;
            this.currentUser = {
                name: '',
                email: '',
                password: '',
                role: '',
                contracts: []
            };
            this.errors = {};
            this.showModal = true;
        },
        editUser(user) {
            this.currentUser = {
                id: user.id,
                name: user.name,
                email: user.email,
                password: '',
                role: user.roles && user.roles.length > 0 ? user.roles[0].name : '',
                contracts: user.contracts ? user.contracts.map(c => c.id) : []
            };
            this.isEditing = true;
            this.showModal = true;
        },
        async saveUser() {
            try {
                this.errors = {};

                if (this.isEditing) {
                    await axios.put(`/api/user-management/${this.currentUser.id}`, this.currentUser);
                } else {
                    await axios.post('/api/user-management', this.currentUser);
                }

                this.fetchUsers();
                this.closeModal();

                Swal.fire({
                    icon: 'success',
                    title: this.isEditing ? 'Օգտատերը հաջողությամբ թարմացվեց' : 'Օգտատերը հաջողությամբ ստեղծվեց',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });

            } catch (error) {
                console.error('Error saving user:', error);

                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                    Swal.fire({
                        icon: 'warning',
                        title: 'Խնդրում ենք ստուգել լրացված դաշտերը',
                        text: Object.values(this.errors).flat().join('\n'),
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Չհաջողվեց պահպանել օգտատերին',
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
        async deleteUser(id) {
            try {
                const result = await Swal.fire({
                    title: 'Դուք համոզված ե՞ք',
                    text: "Այս գործողությունը հետ վերադարձնել հնարավոր չի լինի!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Այո, ջնջել',
                    cancelButtonText: 'Չեղարկել',
                    confirmButtonColor: '#d33',
                });

                if (result.isConfirmed) {
                    await axios.delete(`/api/user-management/${id}`);
                    this.fetchUsers();

                    Swal.fire({
                        icon: 'success',
                        title: 'Օգտատերը հաջողությամբ ջնջվեց',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Չհաջողվեց ջնջել օգտատերին',
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
            this.showModal = false;
            this.errors = {};
            this.isEditing = false;
        }
    }
};
</script>

<style scoped>
.modal.show {
    display: block;
}
</style>
