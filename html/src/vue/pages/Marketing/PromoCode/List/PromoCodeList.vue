<template>
    <layout-main>
        <div class="row mb-3">
            <div class="col-12 mt-3">
                <a :href="getRoute('promo-code.create')" class="btn btn-success">Создать промокод</a>
            </div>
        </div>
        <div class="row">
            <table class="table table-hover">
                <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Дата создания</th>
                    <th>Название</th>
                    <th>Тип</th>
                    <th>Период действия</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="promoCodes && promoCodes.length < 1">
                    <td colspan="6" class="text-center">Промокоды не найдены!</td>
                </tr>
                <tr v-if="promoCodes" v-for="(promoCode, index) in promoCodes">
                    <td>{{ promoCode.id }}</td>
                    <td>{{ datePrint(promoCode.created_at) }}</td>
                    <td>{{ promoCode.name }}</td>
                    <td>{{ promoCodeTypeName(promoCode.type) }}</td>
                    <td>{{ promoCode.validityPeriod }}</td>
                    <td>{{ promoCodeStatusName(promoCode.status) }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </layout-main>
</template>

<script>
    export default {
        name: 'page-promo-code-list',
        components: {
        },
        props: {
            iPromoCodes: [Array, null],
            iTypes: Object,
            iStatuses: Object,
        },
        data() {
            return {
                total: 0,
                currentPage: this.iCurrentPage,
                promoCodes: [...this.iPromoCodes],
            }
        },
        methods: {
            promoCodeTypeName(type) {
                return (type in this.iTypes) ? this.iTypes[type]['name'] : 'N/A';
            },
            promoCodeStatusName(status) {
                return (status in this.iStatuses) ? this.iStatuses[status]['name'] : 'N/A';
            }
        }
    }
</script>
