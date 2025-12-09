<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <h1 class="mb-4">Օգտատերեր և Դերեր</h1>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link" :class="{'active': activeTab === 'users'}" @click="activeTab = 'users'" type="button">
                        <i class="fa-solid fa-users me-2"></i>Օգտատերեր
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" :class="{'active': activeTab === 'roles'}" @click="activeTab = 'roles'" type="button">
                        <i class="fa-solid fa-user-shield me-2"></i>Դերեր և Իրավունքներ
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Users Tab -->
                <div v-show="activeTab === 'users'">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Օգտատերերի ցուցակ</h3>
                        <button class="btn btn-success" @click="openCreateModal">
                            + Ավելացնել օգտատեր
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
                            <button class="btn btn-outline-info btn-sm me-1" @click="openContractsModal(user)" title="Պայմանագրեր">
                                <i class="fa-solid fa-file-contract"></i>
                            </button>
                            <button
                                class="btn btn-outline-danger btn-sm"
                                @click="deleteUser(user.id)"
                                title="Ջնջել"
                                v-if="isSuperAdmin && user.id !== loggedInUser.id">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="7" class="text-center">Օգտատերեր չկան</td>
                    </tr>
                </tbody>
            </table>
                </div>
                <!-- End Users Tab -->

                <!-- Roles Tab -->
                <div v-show="activeTab === 'roles'">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Դերերի ցուցակ</h3>
                        <button class="btn btn-success" @click="openCreateRoleModal">
                            + Ավելացնել դեր
                        </button>
                    </div>

                    <!-- Roles Table -->
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Անվանում</th>
                            <th>Իրավունքներ</th>
                            <th>Օգտատերեր</th>
                            <th>Ստեղծվել է</th>
                            <th>Գործողություններ</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="role in roles" :key="role.id">
                                <td>{{ role.id }}</td>
                                <td>
                                    <strong>{{ role.name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-success me-1" v-for="permission in role.permissions" :key="permission.id">
                                        {{ permission.name }}
                                    </span>
                                    <span v-if="!role.permissions || role.permissions.length === 0" class="text-muted">
                                        Իրավունքներ չկան
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ role.users_count || 0 }}
                                    </span>
                                </td>
                                <td>{{ formatDate(role.created_at) }}</td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm me-1" @click="editRole(role)" title="Խմբագրել">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm me-1" @click="openPermissionsModal(role)" title="Կառավարել իրավունքները">
                                        <i class="fa-solid fa-key"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" @click="deleteRole(role.id)" title="Ջնջել">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="roles.length === 0">
                                <td colspan="6" class="text-center">Դերեր չկան</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- End Roles Tab -->
            </div>
            <!-- End Tab Content -->

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
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeModal">Չեղարկել</button>
                            <button class="btn btn-success" @click="saveUser">{{ isEditing ? 'Թարմացնել' : 'Ստեղծել' }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts Management Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showContractsModal }" style="background: rgba(0,0,0,0.5);" v-if="showContractsModal" @click.self="closeContractsModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Պայմանագրեր - {{ selectedUser?.name }}</h5>
                            <button type="button" class="btn-close" @click="closeContractsModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                <div v-if="contracts.length === 0" class="text-muted text-center">
                                    Պայմանագրեր չկան
                                </div>
                                <div v-for="contract in contracts" :key="contract.id" class="form-check mb-3 p-2 border-bottom">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        :value="contract.id"
                                        v-model="selectedUserContracts"
                                        :id="'contract-modal-' + contract.id">
                                    <label class="form-check-label w-100" :for="'contract-modal-' + contract.id">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ contract.contract_number }}</strong>
                                                <span v-if="contract.partner_company" class="text-muted">
                                                    - {{ contract.partner_company.name }}
                                                </span>
                                            </div>
                                            <span class="badge bg-secondary" v-if="contract.status">
                                                {{ contract.status }}
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeContractsModal">Չեղարկել</button>
                            <button class="btn btn-success" @click="saveUserContracts">Պահպանել</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Management Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showPermissionsModal }" style="background: rgba(0,0,0,0.5);" v-if="showPermissionsModal" @click.self="closePermissionsModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Իրավունքներ - {{ selectedRole?.name }}</h5>
                            <button type="button" class="btn-close" @click="closePermissionsModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="border rounded p-3" style="max-height: 500px; overflow-y: auto;">
                                <div v-if="permissions.length === 0" class="text-muted text-center">
                                    Իրավունքներ չկան
                                </div>
                                <div v-for="permission in permissions" :key="permission.id" class="form-check mb-3 p-2 border-bottom">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        :value="permission.id"
                                        v-model="selectedRolePermissions"
                                        :id="'permission-' + permission.id">
                                    <label class="form-check-label w-100" :for="'permission-' + permission.id">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ permission.name }}</strong>
                                                <p class="text-muted mb-0 small" v-if="permission.description">
                                                    {{ permission.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closePermissionsModal">Չեղարկել</button>
                            <button class="btn btn-success" @click="saveRolePermissions">Պահպանել</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create/Edit Role Modal -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showRoleModal }" style="background: rgba(0,0,0,0.5);" v-if="showRoleModal" @click.self="closeRoleModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditingRole ? 'Խմբագրել' : 'Ավելացնել' }} Դեր</h5>
                            <button type="button" class="btn-close" @click="closeRoleModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Անվանում *</label>
                                <input type="text" class="form-control" :class="{'is-invalid': roleErrors.name}" v-model="currentRole.name" required>
                                <small class="form-text text-muted">
                                    Օրինակ: admin, manager, user
                                </small>
                                <div class="invalid-feedback" v-if="roleErrors.name">
                                    {{ roleErrors.name[0] }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Նկարագրություն</label>
                                <textarea class="form-control" :class="{'is-invalid': roleErrors.description}" v-model="currentRole.description" rows="3"></textarea>
                                <div class="invalid-feedback" v-if="roleErrors.description">
                                    {{ roleErrors.description[0] }}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeRoleModal">Չեղարկել</button>
                            <button class="btn btn-success" @click="saveRole">{{ isEditingRole ? 'Թարմացնել' : 'Ստեղծել' }}</button>
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
            permissions: [],
            showModal: false,
            showContractsModal: false,
            showPermissionsModal: false,
            showRoleModal: false,
            isEditing: false,
            isEditingRole: false,
            errors: {},
            roleErrors: {},
            currentUser: {
                name: '',
                email: '',
                password: '',
                role: ''
            },
            currentRole: {
                name: '',
                description: ''
            },
            loggedInUser: null,
            selectedUser: null,
            selectedUserContracts: [],
            selectedRole: null,
            selectedRolePermissions: [],
            activeTab: 'users'
        };
    },
    computed: {
        isSuperAdmin() {
            return this.loggedInUser && this.loggedInUser.roles &&
                   this.loggedInUser.roles.some(role => role.name === 'super-admin');
        }
    },
    mounted() {
        this.fetchCurrentUser();
        this.fetchUsers();
        this.fetchContracts();
        this.fetchRoles();
        this.fetchPermissions();
    },
    methods: {
        async fetchCurrentUser() {
            try {
                const response = await axios.get('/api/user');
                this.loggedInUser = response.data;
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
        async fetchPermissions() {
            try {
                const response = await axios.get('/api/permissions');
                this.permissions = response.data.data || response.data;
            } catch (error) {
                console.error('Error fetching permissions:', error);
            }
        },
        openCreateModal() {
            this.isEditing = false;
            this.currentUser = {
                name: '',
                email: '',
                password: '',
                role: ''
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
                role: user.roles && user.roles.length > 0 ? user.roles[0].name : ''
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
        },
        openContractsModal(user) {
            this.selectedUser = user;
            this.selectedUserContracts = user.contracts ? user.contracts.map(c => c.id) : [];
            this.showContractsModal = true;
        },
        async saveUserContracts() {
            try {
                await axios.put(`/api/user-management/${this.selectedUser.id}`, {
                    contracts: this.selectedUserContracts
                });

                this.fetchUsers();
                this.closeContractsModal();

                Swal.fire({
                    icon: 'success',
                    title: 'Պայմանագրերը հաջողությամբ թարմացվեցին',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });

            } catch (error) {
                console.error('Error saving user contracts:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Չհաջողվեց պահպանել պայմանագրերը',
                    text: error.response?.data?.message || error.message,
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }
        },
        closeContractsModal() {
            this.showContractsModal = false;
            this.selectedUser = null;
            this.selectedUserContracts = [];
        },
        openPermissionsModal(role) {
            this.selectedRole = role;
            this.selectedRolePermissions = role.permissions ? role.permissions.map(p => p.id) : [];
            this.showPermissionsModal = true;
        },
        async saveRolePermissions() {
            try {
                await axios.put(`/api/roles/${this.selectedRole.id}/permissions`, {
                    permissions: this.selectedRolePermissions
                });

                this.fetchRoles();
                this.closePermissionsModal();

                Swal.fire({
                    icon: 'success',
                    title: 'Իրավունքները հաջողությամբ թարմացվեցին',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });

            } catch (error) {
                console.error('Error saving role permissions:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Չհաջողվեց պահպանել իրավունքները',
                    text: error.response?.data?.message || error.message,
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }
        },
        closePermissionsModal() {
            this.showPermissionsModal = false;
            this.selectedRole = null;
            this.selectedRolePermissions = [];
        },
        openCreateRoleModal() {
            this.isEditingRole = false;
            this.currentRole = {
                name: '',
                description: ''
            };
            this.roleErrors = {};
            this.showRoleModal = true;
        },
        editRole(role) {
            this.currentRole = {
                id: role.id,
                name: role.name,
                description: role.description || ''
            };
            this.isEditingRole = true;
            this.roleErrors = {};
            this.showRoleModal = true;
        },
        async saveRole() {
            try {
                this.roleErrors = {};

                if (this.isEditingRole) {
                    await axios.put(`/api/roles/${this.currentRole.id}`, this.currentRole);
                } else {
                    await axios.post('/api/roles', this.currentRole);
                }

                this.fetchRoles();
                this.closeRoleModal();

                Swal.fire({
                    icon: 'success',
                    title: this.isEditingRole ? 'Դերը հաջողությամբ թարմացվեց' : 'Դերը հաջողությամբ ստեղծվեց',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });

            } catch (error) {
                console.error('Error saving role:', error);

                if (error.response && error.response.status === 422) {
                    this.roleErrors = error.response.data.errors || {};
                    Swal.fire({
                        icon: 'warning',
                        title: 'Խնդրում ենք ստուգել լրացված դաշտերը',
                        text: Object.values(this.roleErrors).flat().join('\n'),
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Չհաջողվեց պահպանել դերը',
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
        async deleteRole(id) {
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
                    await axios.delete(`/api/roles/${id}`);
                    this.fetchRoles();

                    Swal.fire({
                        icon: 'success',
                        title: 'Դերը հաջողությամբ ջնջվեց',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            } catch (error) {
                console.error('Error deleting role:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Չհաջողվեց ջնջել դերը',
                    text: error.response?.data?.message || error.message,
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }
        },
        closeRoleModal() {
            this.showRoleModal = false;
            this.roleErrors = {};
            this.isEditingRole = false;
        }
    }
};
</script>

<style scoped>
.modal.show {
    display: block;
}
</style>
