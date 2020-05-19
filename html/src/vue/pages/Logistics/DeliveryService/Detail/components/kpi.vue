<template>
    <b-card>
        <b-row>
            <b-col>
                <p class="font-weight-bold">KPIs</p>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <p class="font-weight-bold">Количество отправлений</p>
            </b-col>
            <b-col>
                <div class="float-right">{{ shipmentsInfo.allCount }}</div>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <p class="font-weight-bold">Сумма отправлений</p>
            </b-col>
            <b-col>
                <div class="float-right">{{ shipmentsInfo.allPrice }}</div>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        name: "kpi",
        props: [
            'shipmentsInfo'
        ]
    }
</script>