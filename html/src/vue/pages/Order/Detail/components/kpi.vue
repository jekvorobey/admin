<template>
    <b-card>
        <b-row>
            <b-col>
                <p class="font-weight-bold">KPIs</p>
                <b-row v-for="kpi in kpis" v-if="kpi.status" v-bind:key="kpi.status.id">
                    <b-col>
                        <span class="font-weight-bold">{{kpi.status.name}}:</span>
                        {{kpi.status_at}}
                    </b-col>
                </b-row>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        name: "kpis",
        props: ['kpis']
    }
</script>

<style scoped>

</style>