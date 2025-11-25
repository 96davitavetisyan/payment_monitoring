<template>
    <div>
        <h3>Feedback for Project {{ project.name }}</h3>
        <ul>
            <li v-for="fb in feedbacks" :key="fb.id">
                {{ fb.content }} (by {{ fb.account_manager_id }})
            </li>
        </ul>
        <textarea v-model="newFeedback"></textarea>
        <button @click="submitFeedback">Add Feedback</button>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    props: ['project'],
    data(){ return { feedbacks: [], newFeedback: '' } },
    mounted(){ this.fetchFeedbacks() },
    methods:{
        fetchFeedbacks(){
            axios.get(`/api/projects/${this.project.id}/feedbacks`).then(r=>this.feedbacks=r.data)
        },
        submitFeedback(){
            axios.post(`/api/projects/${this.project.id}/feedbacks`, { content: this.newFeedback })
                .then(()=>{ this.newFeedback=''; this.fetchFeedbacks(); })
        }
    }
}
</script>
