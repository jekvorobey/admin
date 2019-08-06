<template>
    <layout-main>
        <h1>Заявка на съёмку</h1>
        <div class="card">
            <div class="card-body d-flex">
                <table>
                    <tr><td class="name">№</td><td class="value">{{ claim.id }}</td></tr>
                    <tr><td class="name">Создана</td><td class="value">{{ claim.created_at }}</td></tr>
                    <tr><td class="name">Автор</td><td class="value">{{ claim.userName }}</td></tr>
                    <tr><td class="name">Статус</td><td class="value">{{ statusName(claim.status) }}</td></tr>
                </table>
                <div v-if="claim.payload.comment" class="comment">
                    <b>Комментарий</b><br>
                    <p>{{ claim.payload.comment }}</p>
                </div>
            </div>
        </div>
        <h2>Товары</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Артикул</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in products">
                    <td>{{ product.id }}</td>
                    <td><a :href="getRoute('product.edit', {id: product.id})" target="_blank">{{ product.name }}</a></td>
                    <td>{{ product.vendor_code }}</td>
                </tr>
            </tbody>
        </table>
    </layout-main>
</template>

<script>

import Services from "../../../../scripts/services/services";
import {mapGetters} from "vuex";

export default {
    name: 'page-buffer',
    props: {
        claim: {},
        statuses: {},
        products: Array
    },
    data() {
        return {

        };
    },
    methods: {
        statusName(statusId) {
            return this.statuses[statusId] || 'N/A';
        },
    },
    computed: {
        ...mapGetters(['getRoute']),
    }
};
</script>

<style scoped>
    .name {
        font-weight: bold;
        text-align: end;
    }
    .value {
        padding-left: 10px;
    }
    .comment {
        margin-left: 50px;
    }
</style>
