<template>
    <form id="form" novalidate v-on:submit.prevent.stop="send">
        <div class="row">
            <v-input v-model="promoCode.name" class="col-12">Название</v-input>
        </div>
        <div class="form-row">
            <v-input v-model="promoCode.code" class="col-3" maxlength="32" autocomplete="off">Код</v-input>
            <div class="col-auto">
                <label>Сгенерировать случайный промокод</label>
                <button type="button" class="btn btn-info btn-block" @click="generate()">Сгенерировать</button>
            </div>
        </div>
        <div class="row">
            <v-select v-model="promoCode.type" :options="types" class="col-3">Тип промокода</v-select>
        </div>
        <div class="row">
            <div class="col-6" v-if="promoCode.type === PROMO_CODE_TYPE_DISCOUNT">
                <label>Выберите скидку</label>
                <v-select2 v-model="promoCode.discount_id" class="form-control" width="100%" :options="discounts">
                    <option v-for="discount in discounts" :value="discount.value" :key="discount.value">
                        {{ discount.text }}
                    </option>
                </v-select2>
            </div>
            <div class="col-6" v-if="promoCode.type === PROMO_CODE_TYPE_GIFT">
                <v-select v-model="promoCode.gift_id" :options="gifts" :selectSize="5">Выберите подарок</v-select>
            </div>
            <div class="col-6" v-if="promoCode.type === PROMO_CODE_TYPE_BONUS">
                <v-select v-model="promoCode.bonus_id" :options="bonuses" :selectSize="5">Выберите бонус</v-select>
            </div>
        </div>
        <div class="row">
            <div class="col-3 mb-3 mt-3">
                <label for="start_date">Дата старта</label>
                <b-form-input id="start_date" v-model="promoCode.start_date" type="date"></b-form-input>
            </div>
            <div class="col-3 mt-3">
                <label for="end_date">Дата окончания</label>
                <b-form-input id="end_date" v-model="promoCode.end_date" type="date"></b-form-input>
            </div>
        </div>
        <div class="row">
            <v-select v-model="promoCode.status" :options="iStatuses" class="col-3">Статус</v-select>
        </div>

        <div class="row">
            <div class="col-12 mt-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="merchantBtn" key="merchantBtn" v-model="merchantBtn">
                    <label class="custom-control-label" for="merchantBtn">Спонсор</label>
                </div>
            </div>

            <v-select v-model="promoCode.merchant_id" :options="merchants" class="col-3 mt-1" v-if="merchantBtn">Выберите мерчанта</v-select>
        </div>

        <div class="row">
            <div class="col-12 mt-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="ownerBtn" key="ownerBtn" v-model="ownerBtn">
                    <label class="custom-control-label" for="ownerBtn">Привязать промокод к РП</label>
                </div>
            </div>

            <v-input v-if="ownerBtn" v-model="promoCode.owner_id" min="1" name="owner_id" type="number" help="ID реферального партнёра" class="col-3 mt-1"></v-input>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <b>Ограничения на применение промокода</b>
            </div>

            <div class="col-12 mt-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="limitedBtn" key="limitedBtn" v-model="limitedBtn">
                    <label class="custom-control-label" for="limitedBtn">Максимальное количество применений</label>
                </div>
            </div>

            <div class="col-2" v-if="limitedBtn">
                <v-input v-model="promoCode.counter" min="1" step="1" name="counter" type="number" :help="'Не более N раз'"></v-input>
            </div>

            <div class="col-12 mt-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="customersBtn" key="customersBtn" v-model="customersBtn">
                    <label class="custom-control-label" for="customersBtn">Покупатели</label>
                </div>
            </div>

            <div class="col-6 mt-1" v-if="customersBtn">
                <v-input v-model="customers" help="ID пользователей (через запятую)"></v-input>
            </div>

            <div class="col-12 mt-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="roleBtn" key="roleBtn" v-model="roleBtn">
                    <label class="custom-control-label" for="roleBtn">Роли</label>
                </div>
            </div>

            <v-select v-model="roleId" v-if="roleBtn" class="col-4" :options="iRoles"></v-select>

            <div class="col-12 mt-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="segmentsBtn" key="segmentsBtn" v-model="segmentsBtn">
                    <label class="custom-control-label" for="segmentsBtn">Сегменты</label>
                </div>
            </div>

            <v-select v-model="segments" v-if="segmentsBtn"
                class="col-4"
                :options="iSegments"
                :multiple="true"></v-select>

            <div class="col-12 mt-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="synergyBtn" key="synergyBtn" v-model="synergyBtn">
                    <label class="custom-control-label" for="synergyBtn">Суммируется с промокодами</label>
                </div>
            </div>

            <div class="col-12 mt-3" v-if="synergyBtn">
                <label>Выберите промокоды</label>
                <v-select2 v-model="promoCodes" class="form-control" width="100%" multiple>
                    <option v-for="promoCode in iPromoCodes" :value="promoCode.value">{{ promoCode.text }}</option>
                </v-select2>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-success" :disabled="!valid || processing">{{ submitText }}</button>
            </div>
        </div>
    </form>
</template>

<script>
    import moment from 'moment';
    import Services from '../../../../../scripts/services/services';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import VSelect2 from '../../../../components/controls/VSelect2/v-select2.vue';

    moment.locale('ru');

    export default {
        components: {
            VInput,
            VSelect,
            VSelect2,
            Services,
        },
        props: {
            iTypes: Object,
            iStatuses: Object,
            merchants: Array,
            iTypesForMerchant: Array,
            iDiscounts: Object|Array,
            gifts: Object|Array,
            bonuses: Object|Array,
            iSegments: Array,
            iRoles: Array,
            iPromoCodes: Array,
            processing: Boolean,
            submitText: String,
            action: Function,
            referral: {},
        },
        data() {
            return {
                types: [],
                discounts: [],
                promoCode: {
                    owner_id: this.referral,
                    merchant_id: null,
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
                merchantBtn: false,
                limitedBtn: false,
                ownerBtn: !!this.referral,
                customersBtn: false,
                roleBtn: false,
                segmentsBtn: false,
                synergyBtn: false,

                promoCodes: [],
                customers: [],
                segments: [],
                roleId: null,

                PROMO_CODE_TYPE_DISCOUNT: 1,
                PROMO_CODE_TYPE_DELIVERY: 2,
                PROMO_CODE_TYPE_GIFT: 3,
                PROMO_CODE_TYPE_BONUS: 4,
            }
        },
        methods: {
            send() {
                this.promoCode.conditions = {};
                if (this.customersBtn) {
                    this.promoCode.conditions.customers = this.formatIds(this.customers);
                }
                if (this.roleBtn) {
                    this.promoCode.conditions.roles = [this.roleId];
                }
                if (this.segmentsBtn) {
                    this.promoCode.conditions.segments = this.segments;
                }
                if (this.synergyBtn) {
                    this.promoCode.conditions.synergy = this.promoCodes;
                }
                this.promoCode.owner_id = this.ownerBtn ? this.promoCode.owner_id : null;
                this.promoCode.counter = this.limitedBtn ? this.promoCode.counter : null;
                this.promoCode.merchant_id = this.merchantBtn ? this.promoCode.merchant_id : null;
                this.action(this.promoCode);
            },
            generate() {
                Services.showLoader();
                Services.net().get(this.getRoute('promo-code.generate'), null, {
                    portfolios: this.portfolios
                }).then(code => {
                    this.promoCode.code = code;
                }).finally(data => {
                    Services.hideLoader();
                });
            },
            formatIds(ids) {
                if (!ids || ids.length <= 0) {
                    return [];
                }
                return ids
                    .split(',')
                    .map(id => { return parseInt(id); })
                    .filter(id => { return id > 0 });
            },
            updateMerchant() {
                this.updateDiscounts();
                this.updateTypes();
            },
            updateTypes() {
                if (!this.promoCode.merchant_id) {
                    this.types = this.iTypes;
                    return;
                }

                if (!this.iTypesForMerchant.includes(this.promoCode.type)) {
                    this.promoCode.type = null;
                }

                this.types = Object.fromEntries(this.iTypesForMerchant.map(typeId => [typeId, this.iTypes[typeId]]));
            },
            updateDiscounts() {
                this.discounts = this.promoCode.merchant_id
                    ? Object.values(this.iDiscounts).filter(
                        discount => discount.merchant_id === this.promoCode.merchant_id
                    )
                    : [...this.iDiscounts];

                if (this.promoCode.discount_id) {
                    if (!this.discounts.find(discount => this.promoCode.discount_id === discount.id)) {
                        this.promoCode.discount_id = null;
                    }
                }
            }
        },
        computed: {
            checkType() {
                switch (this.promoCode.type) {
                    case this.PROMO_CODE_TYPE_DISCOUNT:
                        return !!this.promoCode.discount_id;
                    case this.PROMO_CODE_TYPE_DELIVERY:
                        return true;
                    case this.PROMO_CODE_TYPE_GIFT:
                        return !!this.promoCode.gift_id;
                    case this.PROMO_CODE_TYPE_BONUS:
                        return !!this.promoCode.bonus_id;
                }

                return false;
            },
            checkDate() {
                let start = this.promoCode.start_date;
                let end = this.promoCode.end_date;
                return !(start && end) || (moment(start).unix() <= moment(end).unix());
            },
            checkRestricts() {
                let res = true;
                if (this.limitedBtn) {
                    res &= this.promoCode.counter > 0 && this.promoCode.counter === parseInt(this.promoCode.counter).toString();
                }
                if (this.ownerBtn) {
                    res &= this.promoCode.owner_id > 0;
                }
                if (this.customersBtn) {
                    res &= this.formatIds(this.customers).length > 0;
                }
                if (this.roleBtn) {
                    res &= this.roleId > 0;
                }
                if (this.segmentsBtn) {
                    res &= this.segments.length > 0;
                }
                if (this.merchantBtn) {
                    res &= this.promoCode.merchant_id > 0;
                }
                return res;
            },
            valid() {
                let required = this.promoCode.name
                    && this.promoCode.type
                    && this.promoCode.code
                    && this.promoCode.status
                    && !this.processing;

                return required
                    && this.checkType
                    && this.checkRestricts
                    && this.checkDate;
            },
        },
        watch: {
            merchantBtn() {
                this.promoCode.merchant_id = null;
                this.updateMerchant();
            },
            ownerBtn(val) {
                this.promoCode.owner_id = null;
            },
            limitedBtn(val) {
                if (!val) {
                    this.promoCode.counter = null;
                }
            },
            'customers': {
                handler(val, oldVal) {
                    if (val && val !== oldVal) {
                        let format = this.formatIds(this.customers).join(', ');
                        let separator = (val.slice(-1) === ',')
                            ? ','
                            : (val.slice(-2) === ', ' ? ', ' : '');
                        this.customers = format + separator;
                    }
                },
            },
            'promoCode.merchant_id': {
                handler(val, oldVal) {
                    if (val !== oldVal) {
                        this.updateMerchant();
                    }
                }
            }
        },
        mounted() {
            this.updateMerchant();
        }
    }
</script>
