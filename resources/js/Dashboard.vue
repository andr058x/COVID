<template>
    <div class="d-flex flex-column justify-content-center p-5">
        <h1 class="text-center">Covid situation Germany</h1>
        <div v-if="metrics">
            <h6 class="text-center">
                New infections in the last 24h
                <span class="badge badge-secondary">{{ metrics['newInfectionsInLastDay'] | format }}</span>
            </h6>
            <h6 class="text-center">
                Total infections
                <span class="badge badge-secondary">{{ metrics['totalInfections'] | format }}</span>
            </h6>
            <h6 class="text-center">
                Increase of infections in the last 24h
                <span class="badge badge-secondary">{{ metrics['infectionsIncreaseInLastDay'] | format }}</span>
            </h6>
            <h6 class="text-center">
                Average increase of the last {{ metrics['numberOfDaysForAverage'] }} days
                <span class="badge badge-secondary">{{ metrics['averageInfectionsIncreaseInLastDays'] | format }}</span>
            </h6>
            <h6 class="text-center">
                Average decrease of the last {{ metrics['numberOfDaysForAverage'] }} days
                <span class="badge badge-secondary">{{ metrics['averageInfectionsDecreaseInLastDays'] | format }}</span>
            </h6>
            <br>
            <h6 class="text-center">
                Incidence value for whole Germany
                <span class="badge badge-secondary">{{ metrics['incidenceValueForWholeGermany'] | format }}</span>
            </h6>
            <h6 class="text-center">
                Target total infection
                <span class="badge badge-secondary">{{ metrics['targetTotalInfection'] | format }}</span>
            </h6>
            <h6 class="text-center">
                Prediction of the days of lockdown still required until a defined incidence is reached
                <span class="badge badge-secondary">{{ metrics['predictionOfDaysOfLockdownStillRequired'] | format }}</span>
            </h6>
        </div>
        <div v-else>
            <h6 class="text-center">Please wait. Metrics are being collected...</h6>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            metrics: null,
        };
    },
    created() {
        this.fetchStatistics();
    },
    filters: {
        // This filter is used in the template to format numbers.
        // For example, 1234567 will be formatted to 1,234,567
        format(value) {
            return value.toLocaleString()
        },
    },
    methods: {
        fetchStatistics() {
            axios.get('/ajax/metrics')
                .then(response => {
                    this.metrics = response.data.data;
                });
        },
    },
};
</script>
