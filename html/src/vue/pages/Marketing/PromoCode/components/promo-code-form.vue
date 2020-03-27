<template>
    <form id="form" novalidate v-on:submit.prevent.stop="send">
        <div class="row">
            <v-input v-model="promoCode.name" class="col-12">Название</v-input>
        </div>
        <div class="row">
            <v-input v-model="promoCode.code" class="col-3">Код</v-input>
            <v-select v-model="promoCode.type" :options="promoCodeTypes" class="col-3">Промокод на</v-select>
        </div>
        <div class="row">
            <v-input v-model="promoCode.counter" class="col-2" type="number" :disabled="unlimited">Частота использования</v-input>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="promo_code_only" v-model="unlimited">
                <label class="form-check-label" for="promo_code_only">
                    Без ограничений
                </label>
            </div>
        </div>
        <div class="row">
            <div class="col-3 mb-3">
                <label for="start_date">Дата старта</label>
                <b-form-input id="start_date" v-model="promoCode.start_date" type="date"></b-form-input>
            </div>
            <div class="col-3">
                <label for="end_date">Дата окончания</label>
                <b-form-input id="end_date" v-model="promoCode.end_date" type="date"></b-form-input>
            </div>
        </div>
        <div class="row">
            <v-select v-model="promoCode.status" :options="promoCodeStatuses" class="col-3">Статус</v-select>
        </div>

        <div class="row">
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-success" :disabled="!valid || processing">{{ submitText }}</button>
            </div>
        </div>
    </form>
</template>

<script>
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue'

    export default {
        components: {
            VInput,
            VSelect,
        },
        props: {
            promoCodeTypes: Object,
            promoCodeStatuses: Object,
            processing: Boolean,
            submitText: String,
            action: Function,
        },
        data() {
            return {
                promoCode: {
                    owner_id: null,
                    name: null,
                    code: null,
                    counter: null,
                    start_date: null,
                    end_date: null,
                    status: null,
                    type: null,
                    discount_id: null,
                    gift_id: null,
                    bonus_id: null,
                    conditions: null,
                },
                unlimited: false,
            }
        },
        methods: {
            send() {
                this.action(this.promoCode);
            },
        },
        computed: {
            valid() {
                return true;
            },
        }
    }
</script>
