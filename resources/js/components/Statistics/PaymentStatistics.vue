<template>
    <div>
        <app-header></app-header>
        <div class="container-fluid mt-4 px-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Վճարումների Վիճակագրություն</h3>
                <div class="d-flex gap-2">
                    <span class="badge bg-danger me-2">Bad: {{ badCount }}</span>
                    <span class="badge bg-success">Good: {{ goodCount }}</span>
                </div>
            </div>

            <!-- Statistics Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Գործընկեր</th>
                            <th>Կոնտակտ</th>
                            <th>Կատեգորիա</th>
                            <th>Ընդհանուր վճարումներ</th>
                            <th>Ժամանակին</th>
                            <th>Հաշիվից հետո</th>
                            <th>Ուշացում</th>
                            <th>Ժամանակին %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="stat in statistics" :key="stat.partner_id" :class="getRowClass(stat.category)">
                            <td>
                                <strong>{{ stat.partner_name }}</strong>
                            </td>
                            <td>
                                <div class="small">
                                    <div v-if="stat.contact_person">
                                        <i class="fa-solid fa-user"></i> {{ stat.contact_person }}
                                    </div>
                                    <div v-if="stat.contact_email">
                                        <i class="fa-solid fa-envelope"></i> {{ stat.contact_email }}
                                    </div>
                                    <div v-if="stat.contact_phone">
                                        <i class="fa-solid fa-phone"></i> {{ stat.contact_phone }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" :class="getCategoryBadge(stat.category)">
                                    {{ stat.category === 'good' ? 'GOOD' : 'BAD' }}
                                </span>
                            </td>
                            <td class="text-center">{{ stat.total_payments }}</td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ stat.on_time }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ stat.after_invoice }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ stat.overdue_count }}</span>
                            </td>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div
                                        class="progress-bar bg-success"
                                        :style="{width: stat.on_time_percentage + '%'}"
                                        v-if="stat.on_time_percentage > 0"
                                    >
                                        {{ stat.on_time_percentage }}%
                                    </div>
                                    <div
                                        class="progress-bar bg-warning"
                                        :style="{width: stat.after_invoice_percentage + '%'}"
                                        v-if="stat.after_invoice_percentage > 0"
                                    >
                                        {{ stat.after_invoice_percentage }}%
                                    </div>
                                    <div
                                        class="progress-bar bg-danger"
                                        :style="{width: stat.overdue_percentage + '%'}"
                                        v-if="stat.overdue_percentage > 0"
                                    >
                                        {{ stat.overdue_percentage }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="statistics.length === 0">
                            <td colspan="8" class="text-center">Տվյալներ չեն գտնվել</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Legend -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Ավտոմատացիա</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="alert alert-success mb-0">
                                <strong>Ժամանակին</strong> - վճարված մինչև invoice_date
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning mb-0">
                                <strong>Հաշիվից հետո</strong> - վճարված invoice_date-ից հետո, բայց մինչև due_date
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-danger mb-0">
                                <strong>Ուշացում</strong> - վճարված due_date-ից հետո
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Կատեգորիա:</strong> BAD - 2 կամ ավելի ուշացումներ | GOOD - մնացած բոլորը
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
            statistics: []
        };
    },
    computed: {
        badCount() {
            return this.statistics.filter(s => s.category === 'bad').length;
        },
        goodCount() {
            return this.statistics.filter(s => s.category === 'good').length;
        }
    },
    mounted() {
        this.fetchStatistics();
    },
    methods: {
        async fetchStatistics() {
            try {
                const response = await axios.get('/api/payment-statistics');
                this.statistics = response.data.data;
            } catch (error) {
                console.error('Error fetching statistics:', error);
                alert('Չհաջողվեց բեռնել վիճակագրությունը');
            }
        },
        getRowClass(category) {
            return category === 'bad' ? 'table-danger' : '';
        },
        getCategoryBadge(category) {
            return category === 'bad' ? 'bg-danger' : 'bg-success';
        }
    }
};
</script>

<style scoped>
.progress {
    font-size: 0.75rem;
    font-weight: bold;
}

.small {
    font-size: 0.85rem;
}

.small div {
    margin-bottom: 2px;
}

.gap-2 {
    gap: 0.5rem;
}
</style>
