<template>
    <layout-main>
        <form id="form" novalidate v-on:submit.prevent.stop="send">
            <div class="row">
                <v-input v-model="bonus.name" class="col-12">Название</v-input>
            </div>

            <div class="row">
                <div class="col-4">
                    <label>Бонус на</label>
                    <select class="custom-select" v-model="bonus.type">
                        <option :value="null">–</option>
                        <option v-for="type in types" :value="type.id">{{ type.name }}</option>
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-4">
                    <label>Тип значения</label>
                    <select class="custom-select" v-model="bonus.value_type">
                        <option v-for="valueType in valueTypes" :value="valueType.id">{{ valueType.name }}</option>
                    </select>
                </div>

                <v-input v-model="bonus.value" class="col-4" type="number" min="0">Значение в {{ valueTypeName }}</v-input>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="promoCodeOnlyBtn" key="promoCodeOnlyBtn" v-model="bonus.promo_code_only">
                        <label class="custom-control-label" for="promoCodeOnlyBtn">Бонусы доступны только по промокоду</label>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="validPeriodBtn" key="validPeriodBtn" v-model="validPeriodBtn">
                        <label class="custom-control-label" for="validPeriodBtn">Неограниченный срок действия</label>
                    </div>
                </div>

                <v-input v-model="bonus.valid_period"
                         v-if="!validPeriodBtn"
                         class="col-4 mt-3"
                         type="number"
                         min="0"
                >Срок действия бонусов (в днях)</v-input>
            </div>

            <div class="row">
                <div class="col-4 mb-3 mt-3">
                    <label for="start_date">Дата старта</label>
                    <b-form-input id="start_date" v-model="bonus.start_date" type="date"></b-form-input>
                </div>
                <div class="col-4 mt-3">
                    <label for="end_date">Дата окончания</label>
                    <b-form-input id="end_date" v-model="bonus.end_date" type="date"></b-form-input>
                </div>
            </div>

            <div class="row">
                <div class="col-4">
                    <label>Статус</label>
                    <select class="custom-select" v-model="bonus.status">
                        <option :value="null">–</option>
                        <option v-for="status in statuses" :value="status.id">{{ status.name }}</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mt-5">
                    <button type="submit" class="btn btn-success">Создать правило</button>
                </div>
            </div>
        </form>
    </layout-main>
</template>

<script>
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import Services from "../../../../../scripts/services/services";

    export default {
        components: {
            VInput,
            Services,
        },
        props: {
            types: Object,
            statuses: Object,
        },
        data() {
            return {
                bonus: {
                    name: null,
                    status: null,
                    type: null,
                    value: null,
                    value_type: 1,
                    value_period: null,
                    start_date: null,
                    end_date: null,
                    promo_code_only: null,
                },
                validPeriodBtn: true
            }
        },
        methods: {
            send() {
                Services.showLoader();
                Services.net().post(this.getRoute('bonus.save'), {}, this.bonus).then(() => {
                    location.reload();
                }).finally(() => {
                    Services.hideLoader();
                })
            }
        },
        computed: {
            valueTypes() {
                return [
                    {name: 'Проценты', id: this.bonusValueTypes.percent},
                    {name: 'Рубли', id: this.bonusValueTypes.rub}
                ];
            },
            valueTypeName() {
                return this.bonus.value_type === this.bonusValueTypes.percent ? 'процентах' : 'рублях';
            }
        },
        watch: {
            validPeriodBtn() {
                this.bonus.valid_period = null;
            },
        }
    }
</script>
