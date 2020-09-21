<template>
    <b-card>
        <b-row>
            <b-col>
                <span class="font-weight-bold">Кол-во характеристик:</span>
                <span>{{model.properties_count}}</span>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <span class="font-weight-bold">Кол-во товаров:</span>
                <span>{{model.products_count}}</span>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        name: "kpis",
        props: ['model']
    }
</script>