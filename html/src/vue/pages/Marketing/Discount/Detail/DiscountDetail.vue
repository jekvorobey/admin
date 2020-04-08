<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col>
                <b-card>
                    <infopanel
                        :i-discount="iDiscount"
                    ></infopanel>
                </b-card>
            </b-col>
            <b-col>
                <b-card>
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th colspan="4">KPIs</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Сумма заказов с использованием скидки (в рублях)</th>
                            <td>–</td>
                        </tr>
                        <tr>
                            <th>Сумма, которое сэкономили покупатели (в рублях)</th>
                            <td>–</td>
                        </tr>
                        <tr>
                            <th>Количество пользователей, которые воспользовались скидкой</th>
                            <td>–</td>
                        </tr>
                        <tr>
                            <th>Количество заказов со скидкой</th>
                            <td>–</td>
                        </tr>
                        </tbody>
                    </table>
                </b-card>
            </b-col>
        </b-row>

        <b-card no-body>
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <template>
                        Заглушка
                    </template>
                </b-tab>
            </b-tabs>
        </b-card>
    </layout-main>
</template>

<script>
    import Infopanel from './components/infopanel.vue';
    import tabsMixin from '../../../../mixins/tabs.js';

    export default {
        name: 'page-discount-detail',
        components: {
            Infopanel
        },
        mixins: [tabsMixin],
        props: {
            iDiscount: Object,
        },
        data() {
            return {

            };
        },
        methods: {

        },
        computed: {
            tabs() {
                let tabs = {};
                let i = 0;

                tabs.main = {i: i++, title: 'Информация'};
                tabs.orders = {i: i++, title: 'Заказы'};
                tabs.reports = {i: i++, title: 'Отчеты'};
                tabs.logs = {i: i++, title: 'Логи'};

                return tabs;
            },
        },
    };
</script>
