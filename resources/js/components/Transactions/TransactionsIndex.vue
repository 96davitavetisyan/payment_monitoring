<template>
    <div>
        <h2>Transactions for Project {{ project.name }}</h2>
        <ul>
            <li v-for="tx in activeTransactions" :key="tx.id">
                {{ tx.customer_name }} - {{ tx.amount }} - {{ tx.payment_status }}
            </li>
        </ul>
        <h3>History</h3>
        <ul>
            <li v-for="tx in historyTransactions" :key="tx.id">
                {{ tx.customer_name }} - {{ tx.amount }} - {{ tx.payment_status }}
            </li>
        </ul>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    props: ['project'],
    data(){ return { activeTransactions: [], historyTransactions: [] } },
    mounted(){ this.fetchTransactions() },
    methods:{
        fetchTransactions(){
            axios.get(`/api/projects/${this.project.id}/transactions`).then(r=>{
                this.activeTransactions = r.data.active;
                this.historyTransactions = r.data.history;
            });
        }
    }
}
</script>
