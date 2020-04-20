<template>
    <div>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Дата создания</th>
                    <th>Название</th>
                    <th>Сортировка</th>
                    <th>Мерчант</th>
                    <th>Цена</th>
                    <th>Остаток</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="offer in offers">
                    <td>{{ offer.offer_id }}</td>
                    <td>{{ offer.created_at }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ offer.merchant_id }}</td>
                    <td>{{ offer.price }}</td>
                    <td>{{ offer.qty }}</td>
                    <td>{{ offer.sale_status }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        component:{
        },
        props: {
            offers: Array,
        },
        methods: {
        },
        mounted() {

        },
        computed: {
            headOffer: function () {
                return this.offers.find(offer => offer.sale_status == 1)
            },
            sortedOffers: function () {
                return [this.offers.find(offer => offer.sale_status == 1), this.offers.sort((a,b) => {
                    return a.offer_id - b.offer_id;
                })]
            },
        }
    }
</script>

<style scoped>

</style>
