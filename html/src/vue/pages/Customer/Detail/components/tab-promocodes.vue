<template>
    <div>
        <div class="mt-3 d-flex justify-content-between">
            <a :href="createPromocodeUrl()" class="btn btn-success">Создать промокод</a>
            <div v-if="!massEmpty(massSelectionType)">
                <button @click="setStatus(promoCodeStatus.active)" class="btn btn-warning">Активировать</button>
                <button @click="setStatus(promoCodeStatus.paused)" class="btn btn-danger">Деактивировать</button>
            </div>
            <div v-else>
                <button class="btn" :class="classBtnActive()" @click="toggleArhive">Действующие</button>
                <button class="btn" :class="classBtnArchive()" @click="toggleArhive">Архив</button>
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
                <template v-if="promocodes.length > 0">
                    <tr v-for="promoCode in promocodes">
                        <td>
                            <input type="checkbox"
                                   :checked="massHas({type: massSelectionType, id: promoCode.id})"
                                   @change="e => massCheckbox(e, massSelectionType, promoCode.id)">
                        </td>
                        <td>{{ promoCode.code }}</td>
                        <td>{{ promoCode.name }}</td>
                        <td>{{ promoCode.created_at }}</td>
                        <td>{{ promoCode.validityPeriod }}</td>
                        <td>
                            {{ typeName(promoCode.type) }}
                            <span v-if="promoCode.discount_id">
                            (<a :href="getRoute('discount.detail', {id: promoCode.discount_id})" target="_blank">
                                {{ promoCode.discount_id }}
                            </a>)
                        </span>
                        </td>
                        <td>{{ statusName(promoCode.status) }}</td>
                    </tr>
                </template>
                <template v-else>
                    <tr>
                        <td class="text-center" colspan="8">
                            <div class="bg-light p-5">
                                Список промокодов пуст.
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script>
    import massSelectionMixin from '../../../../mixins/mass-selection';
    import Services from "../../../../../scripts/services/services";
    import qs from "qs";
    export default {
        mixins: [massSelectionMixin],
        props: {
            id: Number,
            options: {}
        },
        data() {
            return {
                massSelectionType: 'promocodes',
                promocodes: [],
                mode: 'active'
            };
        },
        methods: {
            load() {
                Services.showLoader();
                Services.net().get(this.getRoute('customers.detail.promocodes', {id: this.id}), {mode: this.mode})
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
            },
            createPromocodeUrl() {
                return this.getRoute('promo-code.create') + '?' + qs.stringify({
                    returnUrl: this.getRoute('customers.detail', {id: this.id}) + '?tab=referralPromocodes',
                    referral: this.id,
                });
            },
            toggleArhive() {
                this.mode = this.mode === 'archive' ? 'active' : 'archive';
            },
            classBtnActive() {
                return this.mode === 'archive' ? 'btn-outline-dark' : 'btn-dark';
            },
            classBtnArchive() {
                return this.mode === 'archive' ? 'btn-dark' : 'btn-outline-dark';
            }
        },
        watch: {
            mode() {
                this.load();
            }
        },
        created() {
            this.load();
        }
    }
</script>

<style scoped>

</style>