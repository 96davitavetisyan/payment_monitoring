<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <button class="btn btn-secondary btn-sm" @click="$router.push('/projects')">
                        ← Back to Projects
                    </button>
                    <h2 class="d-inline ms-3">Client Feedback - {{ projectName }}</h2>
                </div>
                <button v-if="$auth.can('create_feedback')"
                        class="btn btn-success"
                        @click="openCreateModal">
                    Add Feedback
                </button>
            </div>

            <!-- Feedback List -->
            <div class="row">
                <div class="col-md-12">
                    <div v-if="feedbacks.length === 0" class="alert alert-info">
                        No feedback entries yet.
                    </div>
                    <div v-for="feedback in feedbacks" :key="feedback.id" class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        <strong>{{ feedback.account_manager ? feedback.account_manager.name : 'Unknown' }}</strong>
                                        <span class="ms-2">{{ formatDate(feedback.created_at) }}</span>
                                    </h6>
                                    <p class="card-text">{{ feedback.content }}</p>
                                    <div v-if="feedback.file_path" class="mt-2">
                                        <span class="badge bg-secondary">
                                            📎 Attachment: {{ getFileName(feedback.file_path) }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="canEditFeedback(feedback)" class="ms-3">
                                    <button class="btn btn-sm btn-outline-primary me-1"
                                            @click="editFeedback(feedback)">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            @click="deleteFeedback(feedback.id)">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback Modal -->
            <div v-if="showModal" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);" @click.self="closeModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ isEditing ? 'Edit Feedback' : 'Add Feedback' }}</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Feedback Content *</label>
                                <textarea class="form-control"
                                          rows="5"
                                          v-model="currentFeedback.content"
                                          placeholder="Enter client feedback, comments, or issues..."
                                          required></textarea>
                                <small class="form-text text-muted">Minimum 10 characters</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attachment (Optional)</label>
                                <input type="file"
                                       class="form-control"
                                       @change="handleFile"
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <small class="form-text text-muted">Max 10MB - PDF, DOC, DOCX, JPG, PNG</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeModal">Cancel</button>
                            <button class="btn btn-primary"
                                    @click="saveFeedback"
                                    :disabled="!canSave">
                                {{ isEditing ? 'Update' : 'Submit' }}
                            </button>
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
            projectId: null,
            projectName: '',
            feedbacks: [],
            showModal: false,
            isEditing: false,
            currentFeedback: {
                content: ''
            },
            attachmentFile: null
        };
    },
    computed: {
        canSave() {
            return this.currentFeedback.content && this.currentFeedback.content.length >= 10;
        }
    },
    mounted() {
        this.projectId = this.$route.params.projectId;
        this.fetchProject();
        this.fetchFeedbacks();
    },
    methods: {
        fetchProject() {
            axios.get(`/api/projects/${this.projectId}`)
                .then(res => {
                    const project = res.data.success ? res.data.data : res.data;
                    this.projectName = project.name;
                })
                .catch(err => console.error('Error fetching project:', err));
        },
        fetchFeedbacks() {
            axios.get(`/api/projects/${this.projectId}/feedbacks`)
                .then(res => {
                    this.feedbacks = res.data.success ? res.data.data : res.data;
                })
                .catch(err => {
                    console.error('Error fetching feedbacks:', err);
                    alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                });
        },
        openCreateModal() {
            this.isEditing = false;
            this.currentFeedback = { content: '' };
            this.attachmentFile = null;
            this.showModal = true;
        },
        editFeedback(feedback) {
            this.isEditing = true;
            this.currentFeedback = { ...feedback };
            this.attachmentFile = null;
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.isEditing = false;
            this.currentFeedback = { content: '' };
            this.attachmentFile = null;
        },
        handleFile(event) {
            this.attachmentFile = event.target.files[0];
        },
        saveFeedback() {
            if (!this.canSave) {
                alert('Feedback content must be at least 10 characters');
                return;
            }

            const formData = new FormData();
            formData.append('content', this.currentFeedback.content);

            if (this.attachmentFile) {
                formData.append('file', this.attachmentFile);
            }

            const url = this.isEditing
                ? `/api/projects/${this.projectId}/feedbacks/${this.currentFeedback.id}`
                : `/api/projects/${this.projectId}/feedbacks`;

            const method = this.isEditing ? 'put' : 'post';

            axios[method](url, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
                .then(() => {
                    this.fetchFeedbacks();
                    this.closeModal();
                })
                .catch(err => {
                    alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                });
        },
        deleteFeedback(id) {
            if (confirm('Are you sure you want to delete this feedback?')) {
                axios.delete(`/api/projects/${this.projectId}/feedbacks/${id}`)
                    .then(() => {
                        this.fetchFeedbacks();
                    })
                    .catch(err => {
                        alert('Error: ' + (err.response?.data?.message || 'Unknown error'));
                    });
            }
        },
        canEditFeedback(feedback) {
            // User can edit if they have manage_feedback permission OR if they created it
            const user = this.$auth.getUser();
            return this.$auth.can('manage_feedback') || (user && feedback.account_manager_id === user.id);
        },
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleString();
        },
        getFileName(path) {
            if (!path) return '';
            return path.split('/').pop();
        }
    }
};
</script>
