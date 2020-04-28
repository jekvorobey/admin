<template>
    <div>
        <div class="mt-3 d-flex justify-content-between">
            <button class="btn btn-success">Создать промокод</button>
            <div v-if="!massEmpty(massSelectionType)">
                <button @click="setStatus(promoCodeStatus.active)" class="btn btn-warning">Активировать</button>
                <button @click="setStatus(promoCodeStatus.paused)" class="btn btn-danger">Деактивировать</button>
            </div>
        </div>
        <table class="table table-sm mt-3">
            <thead>
            <tr>
                <th></th>
                <th>Код</th>
                <th>Название</th>
                <th>Дата создания</th>
                <th>Действует до</th>
                <th>Тип</th>
                <th>Статус</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="promoCode in promocodes">
                <td>
                    <input type="checkbox"
                           :checked="massHas({type: massSelectionType, id: promoCode.id})"
                           @change="e => massCheckbox(e, massSelectionType, promoCode.id)">
                </td>
                <td>{{ promoCode.code }}</td>
                <td>{{ promoCode.name }}</td>
                <td>{{ promoCode.created_at }}</td>
                <td>{{ promoCode.end_date }}</td>
                <td>{{ typeName(promoCode.type) }}</td>
                <td>{{ statusName(promoCode.status) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import massSelectionMixin from '../../../../mixins/mass-selection';
    import Services from "../../../../../scripts/services/services";
    export default {
        mixins: [massSelectionMixin],
        props: {
            id: Number,
            options: {}
        },
        data() {
            return {
                massSelectionType: 'promocodes',
                promocodes: []
            };
        },
        methods: {
            load() {
                Services.showLoader();
                Services.net().get(this.getRoute('customers.detail.promocodes', {id: this.id}))
                    .then(data => {
                        this.$set(this, 'promocodes', data.promocodes);
                    }).finally(() => {
                        Services.hideLoader();
                    })
            },
            typeName(typeId) {
                return this.options.promoCodeTypes[typeId] ? this.options.promoCodeTypes[typeId].name : 'N/A';
            },
            statusName(statusId) {
                return this.options.promoCodeStatuses[statusId] ? this.options.promoCodeStatuses[statusId].name : 'N/A';
            },
            setStatus(status) {
                Services.showLoader();
                Services.net().post(this.getRoute('promo-code.status'), {}, {
                    ids: this.massAll(this.massSelectionType),
                    status: status
                })
                    .then(data => {
                        this.massClear(this.massSelectionType);
                    }).finally(() => {
                        Services.hideLoader();
                        return this.load();
                    });
            }
        },
        created() {
            this.load();
        }
    }
</script>

<style scoped>

</style>