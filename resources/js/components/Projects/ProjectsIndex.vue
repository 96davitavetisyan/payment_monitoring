<template>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Projects</h1>
            <button class="btn btn-success" @click="showCreateModal = true">New Project</button>
        </div>

        <!-- Projects Table -->
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="project in projects" :key="project.id">
                <td>{{ project.name }}</td>
                <td>
            <span class="badge" :class="project.status === 'active' ? 'bg-success' : 'bg-secondary'">
              {{ project.status }}
            </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary me-2" @click="toggleStatus(project.id)">
                        Toggle Status
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Modal for creating new project -->
        <div class="modal" tabindex="-1" :class="{ 'show d-block': showCreateModal }" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New Project</h5>
                        <button type="button" class="btn-close" @click="showCreateModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" class="form-control mb-2" placeholder="Project Name" v-model="newProject.name">

                        <input type="date" class="form-control mb-2" v-model="newProject.start_date">

                        <!-- Select для ответственного -->
                        <select class="form-select mb-2" v-model="newProject.responsible_user_id">
                            <option value="" disabled>Select Responsible User</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>

                        <!-- Статус проекта -->
                        <select class="form-select" v-model="newProject.status">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" @click="showCreateModal = false">Cancel</button>
                        <button class="btn btn-success" @click="createProject">Create</button>
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
            newProject: {
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
        axios.get('/api/users').then(r => this.users = r.data);
    },
    methods: {
        fetchProjects() {
            axios.get('/api/projects', {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            })
                .then(res => this.projects = res.data);
        },
        toggleStatus(id) {
            axios.get(`/api/projects/${id}/toggle-status`, {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            }).then(() => this.fetchProjects());
        },
        createProject() {
            axios.post('/api/projects', this.newProject, {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            }).then(res => {
                console.log(this.projects,"wedkweijdwie")
                this.projects.push(res.data.data);

                console.log(this.projects)
                this.showCreateModal = false;
                this.newProject.name = '';
                this.newProject.start_date = '';
            });
        }
    }
};
</script>
