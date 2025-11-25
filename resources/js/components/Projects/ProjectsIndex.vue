<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Projects</h1>
                <button v-if="$auth.can('create_projects')"
                        class="btn btn-success"
                        @click="showCreateModal = true">
                    New Project
                </button>
            </div>

            <!-- Projects Table -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Start Date</th>
                    <th>Responsible</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="project in projects" :key="project.id">
                    <td>{{ project.name }}</td>
                    <td>{{ project.start_date || 'N/A' }}</td>
                    <td>{{ project.responsible_user ? project.responsible_user.name : 'N/A' }}</td>
                    <td>
                        <span class="badge" :class="project.status === 'active' ? 'bg-success' : 'bg-secondary'">
                            {{ project.status }}
                        </span>
                    </td>
                    <td>
                        <button v-if="$auth.canAny(['suspend_projects', 'activate_projects'])"
                                class="btn btn-sm btn-warning me-2"
                                @click="toggleStatus(project.id)">
                            {{ project.status === 'active' ? 'Suspend' : 'Activate' }}
                        </button>
                        <button v-if="$auth.can('edit_projects')"
                                class="btn btn-sm btn-primary me-2"
                                @click="editProject(project)">
                            Edit
                        </button>
                        <button v-if="$auth.can('delete_projects')"
                                class="btn btn-sm btn-danger"
                                @click="deleteProject(project.id)">
                            Delete
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Modal for creating/editing project -->
            <div class="modal" tabindex="-1" :class="{ 'show d-block': showCreateModal }" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Edit Project' : 'New Project' }}</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Project Name</label>
                                <input type="text" class="form-control" placeholder="Project Name" v-model="currentProject.name">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" v-model="currentProject.start_date">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Responsible User</label>
                                <select class="form-select" v-model="currentProject.responsible_user_id">
                                    <option value="" disabled>Select Responsible User</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" v-model="currentProject.status">
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeModal">Cancel</button>
                            <button class="btn btn-success" @click="saveProject">{{ isEditing ? 'Update' : 'Create' }}</button>
                        </div>
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
            projects: [],
            showCreateModal: false,
            isEditing: false,
            currentProject: {
                name: '',
                start_date: '',
                responsible_user_id: '',
                status: 'active'
            },
            users: []
        };
    },
    mounted() {
        this.fetchProjects();
        this.fetchUsers();
    },
    methods: {
        fetchProjects() {
            axios.get('/api/projects')
                .then(res => {
                    this.projects = res.data.success ? res.data.data : res.data;
                })
                .catch(err => {
                    console.error('Error fetching projects:', err);
                });
        },
        fetchUsers() {
            axios.get('/api/users')
                .then(res => {
                    this.users = res.data.success ? res.data.data : res.data;
                })
                .catch(err => {
                    console.error('Error fetching users:', err);
                });
        },
        toggleStatus(id) {
            axios.post(`/api/projects/${id}/toggle-status`)
                .then(() => {
                    this.fetchProjects();
                })
                .catch(err => {
                    alert('Error toggling status: ' + (err.response?.data?.message || 'Unknown error'));
                });
        },
        editProject(project) {
            this.isEditing = true;
            this.currentProject = {
                id: project.id,
                name: project.name,
                start_date: project.start_date,
                responsible_user_id: project.responsible_user_id,
                status: project.status
            };
            this.showCreateModal = true;
        },
        deleteProject(id) {
            if (confirm('Are you sure you want to delete this project?')) {
                axios.delete(`/api/projects/${id}`)
                    .then(() => {
                        this.fetchProjects();
                    })
                    .catch(err => {
                        alert('Error deleting project: ' + (err.response?.data?.message || 'Unknown error'));
                    });
            }
        },
        saveProject() {
            if (this.isEditing) {
                // Update existing project
                axios.put(`/api/projects/${this.currentProject.id}`, this.currentProject)
                    .then(() => {
                        this.fetchProjects();
                        this.closeModal();
                    })
                    .catch(err => {
                        alert('Error updating project: ' + (err.response?.data?.message || 'Unknown error'));
                    });
            } else {
                // Create new project
                axios.post('/api/projects', this.currentProject)
                    .then(() => {
                        this.fetchProjects();
                        this.closeModal();
                    })
                    .catch(err => {
                        alert('Error creating project: ' + (err.response?.data?.message || 'Unknown error'));
                    });
            }
        },
        closeModal() {
            this.showCreateModal = false;
            this.isEditing = false;
            this.currentProject = {
                name: '',
                start_date: '',
                responsible_user_id: '',
                status: 'active'
            };
        }
    }
};
</script>
