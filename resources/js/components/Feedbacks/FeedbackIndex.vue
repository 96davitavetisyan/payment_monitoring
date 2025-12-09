<template>
    <div>
        <app-header></app-header>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <button class="btn btn-secondary btn-sm" @click="$router.push('/products')">
                        ← Հետ դեպի Պրոդուկտներ
                    </button>
                    <h2 class="d-inline ms-3">Ֆիդբեքներ - {{ productName }}</h2>
                </div>
                <button class="btn btn-success"
                        @click="openCreateModal">
                    + Ավելացնել
                </button>
            </div>

            <!-- Feedback List -->
            <div class="row">
                <div class="col-md-12">
                    <div v-if="feedbacks.length === 0" class="alert alert-info">
                        Ֆիդբեքներ դեռ չեն ավելացվել
                    </div>
                    <div v-for="feedback in feedbacks" :key="feedback.id" class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        <strong>{{ feedback.account_manager ? feedback.account_manager.name : 'Անհայտ' }}</strong>
                                        <span class="ms-2">{{ formatDate(feedback.created_at) }}</span>
                                    </h6>
                                    <p class="card-text" style="white-space: pre-wrap;">{{ feedback.content }}</p>
                                    <div v-if="feedback.file_path" class="mt-2">
                                        <a :href="'/storage/' + feedback.file_path" target="_blank" class="badge bg-secondary text-decoration-none">
                                            <i class="fa-solid fa-paperclip"></i> Ֆայլ: {{ getFileName(feedback.file_path) }}
                                        </a>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <button class="btn btn-sm btn-outline-primary me-1"
                                            @click="editFeedback(feedback)"
                                            title="Խմբագրել">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            @click="deleteFeedback(feedback.id)"
                                            title="Ջնջել">
                                        <i class="fa-solid fa-trash"></i>
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
                            <h5 class="modal-title">{{ isEditing ? 'Խմբագրել' : 'Ավելացնել' }} Ֆիդբեք</h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Ֆիդբեքի տեքստ *</label>
                                <textarea class="form-control"
                                          rows="5"
                                          v-model="currentFeedback.content"
                                          placeholder="Մուտքագրեք ֆիդբեքը, մեկնաբանությունները կամ խնդիրները..."
                                          required></textarea>
                                <small class="form-text text-muted">Նվազագույնը 10 նիշ</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ֆայլ (ըստ ցանկության)</label>
                                <input type="file"
                                       class="form-control"
                                       @change="handleFile"
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls">
                                <small class="form-text text-muted">Մաքս 10MB - PDF, DOC, DOCX, JPG, PNG, XLSX</small>
                                <div v-if="currentFeedback.file_path && !attachmentFile" class="mt-2">
                                    <small class="text-muted">
                                        Առկա ֆայլ: {{ getFileName(currentFeedback.file_path) }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" @click="closeModal">Չեղարկել</button>
                            <button class="btn btn-success"
                                    @click="saveFeedback"
                                    :disabled="!canSave">
                                {{ isEditing ? 'Թարմացնել' : 'Ստեղծել' }}
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
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            productId: null,
            productName: '',
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
        this.productId = this.$route.params.productId;
        this.fetchProduct();
        this.fetchFeedbacks();
    },
    methods: {
        fetchProduct() {
            axios.get(`/api/products/${this.productId}`)
                .then(res => {
                    const product = res.data.success ? res.data.data : res.data;
                    this.productName = product.name;
                })
                .catch(err => console.error('Error fetching product:', err));
        },
        fetchFeedbacks() {
            axios.get(`/api/products/${this.productId}/feedbacks`)
                .then(res => {
                    this.feedbacks = res.data.success ? res.data.data : res.data;
                })
                .catch(err => {
                    console.error('Error fetching feedbacks:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Սխալ',
                        text: 'Չհաջողվեց բեռնել ֆիդբեքները: ' + (err.response?.data?.message || 'Unknown error')
                    });
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
                Swal.fire({
                    icon: 'warning',
                    title: 'Սխալ',
                    text: 'Ֆիդբեքի տեքստը պետք է լինի նվազագույնը 10 նիշ'
                });
                return;
            }

            const formData = new FormData();
            formData.append('content', this.currentFeedback.content);

            if (this.attachmentFile) {
                formData.append('file', this.attachmentFile);
            }

            const url = this.isEditing
                ? `/api/products/${this.productId}/feedbacks/${this.currentFeedback.id}`
                : `/api/products/${this.productId}/feedbacks`;

            const method = this.isEditing ? 'post' : 'post';

            // For PUT request, add _method field
            if (this.isEditing) {
                formData.append('_method', 'PUT');
            }

            axios.post(url, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
                .then(() => {
                    this.fetchFeedbacks();
                    this.closeModal();
                    Swal.fire({
                        icon: 'success',
                        title: this.isEditing ? 'Թարմացվեց' : 'Ստեղծվեց',
                        text: 'Ֆիդբեքը հաջողությամբ ' + (this.isEditing ? 'թարմացվեց' : 'ստեղծվեց'),
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                })
                .catch(err => {
                    console.error('Error saving feedback:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Սխալ',
                        text: 'Չհաջողվեց պահպանել ֆիդբեքը: ' + (err.response?.data?.message || 'Unknown error')
                    });
                });
        },
        async deleteFeedback(id) {
            const result = await Swal.fire({
                title: 'Դուք համոզված ե՞ք',
                text: "Այս գործողությունը հնարավոր է հետ վերադարձնել չի լինի!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Այո, ջնջել',
                cancelButtonText: 'Չեղարկել',
                confirmButtonColor: '#d33',
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/api/products/${this.productId}/feedbacks/${id}`);
                    this.fetchFeedbacks();
                    Swal.fire({
                        icon: 'success',
                        title: 'Ջնջվեց',
                        text: 'Ֆիդբեքը հաջողությամբ ջնջվեց',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                } catch (err) {
                    console.error('Error deleting feedback:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Սխալ',
                        text: 'Չհաջողվեց ջնջել ֆիդբեքը: ' + (err.response?.data?.message || 'Unknown error')
                    });
                }
            }
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
