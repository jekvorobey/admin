<template>
    <layout-main>
        <form v-on:submit.prevent.stop="update">
            <h3 class="mb-3">Лимиты на вывод Реферальных Отчислений</h3>
            <div class="mb-3 row">
                <v-input v-model="$v.form.min_withdrawal_amount.$model" class="col-6"
                         :error="errorMinWithdrawalLimit"
                         :help="'Минимальный лимит Яндекс.Кассы - ' + this.limits.YANDEX_MIN_LIMIT + ' руб.'"
                         type="number"
                         step="any"
                         :min="this.limits.YANDEX_MIN_LIMIT"
                         @change="() => {updateInput('min_withdrawal_amount')}">
                    Минимальная сумма вывода за один раз</v-input>

                <v-input v-model="$v.form.max_withdrawal_amount.$model" class="col-6"
                         :error="errorMaxWithdrawalLimit"
                         :help="'Максимальный лимит Яндекс.Кассы - ' + this.limits.YANDEX_MAX_LIMIT + ' руб.'"
                         type="number"
                         step="any"
                         :max="this.limits.YANDEX_MAX_LIMIT"
                         @change="() => {updateInput('max_withdrawal_amount')}">
                    Максимальная сумма вывода за один раз</v-input>
            </div>

            <h3 class="mb-3">Бонусы</h3>
            <div class="mb-3 row">
                <v-input v-model="$v.form.bonus_per_rubles.$model" class="col-6"
                         :error="errorBonusPerRubles"
                         type="number"
                         step="any"
                         min="0"
                         @change="() => {updateInput('bonus_per_rubles')}"
                >Стоимость одного бонуса (в рублях)</v-input>

                <f-multi-select v-model="form.roles_available_for_bonuses"
                                :options="roles"
                                :name="'type'"
                                @change="() => {updateInput('roles_available_for_bonuses')}"
                                class="col-6">
                    Каким ролям можно начислять бонусы
                </f-multi-select>
            </div>
            <div class="mb-3 row">
                <v-input v-model="$v.form.order_activation_bonus_delay.$model" class="col-6"
                         :error="errorOrderActivationBonusDelay"
                         help="Количество дней"
                         type="number"
                         min="0"
                         @change="() => {updateInput('order_activation_bonus_delay')}"
                >Начисление бонуса производится по факту статуса заказа ДОСТАВЛЕН и ОПЛАЧЕН + N дней</v-input>
            </div>

            <h4 class="mb-3 mt-3">Правила списания бонусов</h4>
            <div class="mb-3 row">
                <v-input v-model="$v.form.max_debit_percentage_for_product.$model" class="col-6"
                         :error="errorMaxDebitPercentageForProduct"
                         type="number"
                         min="0"
                         max="100"
                         @change="() => {updateInput('max_debit_percentage_for_product')}"
                >Максимальный процент от единицы товара, который можно оплатить бонусами</v-input>

                <v-input v-model="$v.form.max_debit_percentage_for_discount_product.$model" class="col-6"
                         :error="errorMaxDebitPercentageForDiscountProduct"
                         type="number"
                         min="0"
                         max="100"
                         @change="() => {updateInput('max_debit_percentage_for_discount_product')}"
                >Максимальный процент от единицы товара со скидкой, который можно оплатить бонусами</v-input>

            </div>

            <div class="mb-3 row" style="display: none;">
                <v-input v-model="$v.form.max_debit_percentage_for_order.$model" class="col-6"
                         :error="errorMaxDebitPercentageForOrder"
                         type="number"
                         min="0"
                         max="100"
                         @change="() => {updateInput('max_debit_percentage_for_order')}"
                >Максимальный процент от заказа, который можно оплатить бонусами</v-input>
            </div>

            <h4 class="mb-3 mt-3">Бонусы за активацию клиента</h4>
            <div class="">
                <span class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="bonus-activation-btn" key="bonusActivationBtn" v-model="bonusActivationBtn">
                    <label class="custom-control-label" for="bonus-activation-btn"></label>
                    <label for="bonus-activation-btn">Автоматически начислять бонусы при активации клиента</label>
                </span>
            </div>

            <div class="mb-3 row" v-if="bonusActivationBtn">
                <v-input v-model="$v.form.activation_bonus_name.$model"
                    :error="errorActivationBonusName"
                    class="col-6"
                    placeholder="Бонус за активацию"
                    @change="() => {updateInput('activation_bonus')}"
                >Название события</v-input>
                <v-input v-model="$v.form.activation_bonus_value.$model"
                     :error="errorActivationBonusValue"
                     class="col-6"
                     type="number"
                     min="1"
                     @change="() => {updateInput('activation_bonus')}"
                >Сумма</v-input>
                <div class="col-12">
                    <span class="custom-control custom-switch">
                        <input type="checkbox"
                            class="custom-control-input"
                            id="bonus-unlimited-period-btn"
                            key="bonusUnlimitedPeriodBtn"
                            v-model="bonusUnlimitedPeriodBtn"
                        >
                        <label class="custom-control-label" for="bonus-unlimited-period-btn"></label>
                        <label for="bonus-unlimited-period-btn">Неограниченный срок действия бонусов</label>
                    </span>
                </div>
                <v-input v-model="$v.form.activation_bonus_valid_period.$model"
                         v-if="!bonusUnlimitedPeriodBtn"
                         :error="errorActivationBonusValidPeriod"
                         class="col-3"
                         type="number"
                         min="1"
                         @change="() => {updateInput('activation_bonus')}"
                >Срок действия (дней)</v-input>

            </div>

            <h4 class="mb-3 mt-3">Нотификационные сообщения</h4>
            <div class="mb-3 row">
                <v-input v-model="$v.form.bonus_expire_days_notify.$model" class="col-6"
                         :error="errorBonusExpireDaysNotify"
                         type="number"
                         min="1"
                         @change="() => {updateInput('bonus_expire_days_notify')}"
                         :disabled="bonusExpireDaysNotifyBtn"
                >За сколько дней до окончания срока действия бонуса информировать клиента</v-input>

                <div class="col-12">
                    <span class="custom-control custom-switch">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="bonus-expire-days-notify-btn"
                               key="bonusExpireDaysNotifyBtn"
                               v-model="bonusExpireDaysNotifyBtn"
                        >
                        <label class="custom-control-label" for="bonus-expire-days-notify-btn"></label>
                        <label for="bonus-expire-days-notify-btn">Не информировать клиента</label>
                    </span>
                </div>
            </div>

            <div class="form-group mt-3">
                <button type="submit"
                        :disabled="anyDirty"
                        class="btn btn-primary">
                    Сохранить
                </button>
            </div>
        </form>
    </layout-main>
</template>


<script>
    import Services from '../../../../scripts/services/services';
    import {validationMixin} from 'vuelidate';
    import {required, requiredIf, minValue, maxValue, integer} from 'vuelidate/lib/validators';
    import VInput from '../../../components/controls/VInput/VInput.vue';
    import FMultiSelect from '../../../components/filter/f-multi-select.vue';

    export default {
        name: 'page-marketing-settings',
        components: {VInput, FMultiSelect},
        props: {
            min_withdrawal_amount: Number,
            max_withdrawal_amount: Number,
            bonus_per_rubles: Number,
            order_activation_bonus_delay: Number,
            max_debit_percentage_for_product: Number,
            max_debit_percentage_for_discount_product: Number,
            max_debit_percentage_for_order: Number,
            roles_available_for_bonuses: Array,
            activation_bonus: [Object, null],
            bonus_expire_days_notify: [Number, null],
            roles: Array,
        },
        mixins: [validationMixin],
        data() {
            return {
                limits: {
                    YANDEX_MIN_LIMIT: 100,
                    YANDEX_MAX_LIMIT: 60000,
                },
                form: {
                    min_withdrawal_amount: this.min_withdrawal_amount,
                    max_withdrawal_amount: this.max_withdrawal_amount,
                    bonus_per_rubles: this.bonus_per_rubles,
                    roles_available_for_bonuses: this.roles_available_for_bonuses,
                    order_activation_bonus_delay: this.order_activation_bonus_delay,
                    max_debit_percentage_for_product: this.max_debit_percentage_for_product,
                    max_debit_percentage_for_discount_product: this.max_debit_percentage_for_discount_product,
                    max_debit_percentage_for_order: this.max_debit_percentage_for_order,
                    bonus_expire_days_notify: this.bonus_expire_days_notify,

                    activation_bonus_name: this.activation_bonus ? this.activation_bonus.name : null,
                    activation_bonus_value: this.activation_bonus ? this.activation_bonus.value : null,
                    activation_bonus_valid_period: this.activation_bonus ? this.activation_bonus.valid_period : null,
                },
                requestData: {},
                bonusActivationBtn: this.activation_bonus && this.activation_bonus.value > 0,
                bonusUnlimitedPeriodBtn: !this.activation_bonus || this.activation_bonus.valid_period <= 0,
                bonusExpireDaysNotifyBtn: !this.bonus_expire_days_notify || this.bonus_expire_days_notify <= 0,
            }
        },
        validations: {
            form: {
                min_withdrawal_amount: {
                    required,
                    minValue: minValue(100),
                    maxValue: maxValue(60000)
                },
                max_withdrawal_amount: {
                    required,
                    minValue: minValue(100),
                    maxValue: maxValue(60000)
                },
                bonus_per_rubles: {required, minValue: minValue(0)},
                order_activation_bonus_delay: {required, integer, minValue: minValue(0)},
                activation_bonus_name: {
                    required: requiredIf(function () { return this.bonusActivationBtn }),
                },
                activation_bonus_value: {
                    required: requiredIf(function () { return this.bonusActivationBtn }),
                    minValue: minValue(1),
                    integer
                },
                activation_bonus_valid_period: {
                    required: requiredIf(function () { return this.bonusActivationBtn && !this.bonusUnlimitedPeriodBtn }),
                    minValue: minValue(1),
                    integer
                },
                bonus_expire_days_notify: {
                    required: requiredIf(function () { return !this.bonusExpireDaysNotifyBtn }),
                    minValue: minValue(1),
                    integer
                },
                max_debit_percentage_for_product: {required, minValue: minValue(0), maxValue: maxValue(100), integer},
                max_debit_percentage_for_discount_product: {required, minValue: minValue(0), maxValue: maxValue(100), integer},
                max_debit_percentage_for_order: {required, minValue: minValue(0), maxValue: maxValue(100), integer},
            }
        },
        methods: {
            updateInput(type) {
                this.requestData[type] = this.form[type];
                if (type === 'activation_bonus') {
                    this.requestData['activation_bonus'] = this.formatActivationBonus;
                }
            },
            update() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(
                    this.route('settings.marketing.update'),
                    null,
                    this.requestData
                ).then(() => {
                    Services.msg('Данные успешно изменены');
                    location.reload();
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        computed: {
            formatActivationBonus() {
                if (!this.bonusActivationBtn) {
                    return null;
                }

                let name = this.form.activation_bonus_name;
                let value = parseInt(this.form.activation_bonus_value);
                let valid_period = !this.bonusUnlimitedPeriodBtn ? parseInt(this.form.activation_bonus_valid_period) : null;
                return (name && value > 0 && (valid_period > 0 || this.bonusUnlimitedPeriodBtn))
                    ? {name, value,valid_period}
                    : null;
            },
            // ===============================
            errorMinWithdrawalLimit() {
                if (
                    Number(this.form.min_withdrawal_amount) > Number(this.form.max_withdrawal_amount)
                ) {
                    return 'Минимальный порог не может быть выше максимального'
                }
                if (this.$v.form.min_withdrawal_amount.$dirty) {
                    if (!this.$v.form.min_withdrawal_amount.required)
                        return "Обязательное поле!";
                    if (!this.$v.form.min_withdrawal_amount.minValue)
                        return `Минимальная выплата Яндекс.Кассы - ${this.limits.YANDEX_MIN_LIMIT} руб.`;
                    if (!this.$v.form.min_withdrawal_amount.maxValue)
                        return `Максимальная выплата Яндекс.Кассы - ${this.limits.YANDEX_MAX_LIMIT} руб.`;
                }
            },
            errorMaxWithdrawalLimit() {
                if (
                    Number(this.form.min_withdrawal_amount) > Number(this.form.max_withdrawal_amount)
                ) {
                    return 'Максимальный порог не может быть ниже минимального'
                }
                if (this.$v.form.max_withdrawal_amount.$dirty) {
                    if (!this.$v.form.max_withdrawal_amount.required)
                        return "Обязательное поле!";
                    if (!this.$v.form.max_withdrawal_amount.minValue)
                        return `Минимальная выплата Яндекс.Кассы - ${this.limits.YANDEX_MIN_LIMIT} руб.`;
                    if (!this.$v.form.max_withdrawal_amount.maxValue)
                        return `Максимальная выплата Яндекс.Кассы - ${this.limits.YANDEX_MAX_LIMIT} руб.`;
                }
            },
            errorBonusPerRubles() {
                if (this.$v.form.bonus_per_rubles.$dirty) {
                    if (!this.$v.form.bonus_per_rubles.required) return "Обязательное поле!";
                    if (!this.$v.form.bonus_per_rubles.minValue) return "Значение должно быть ≥ 0";
                }
            },
            errorActivationBonusName() {
                if (this.$v.form.activation_bonus_name.$dirty) {
                    if (!this.$v.form.activation_bonus_name.required) return "Обязательное поле!";
                }
            },
            errorOrderActivationBonusDelay() {
                if (this.$v.form.bonus_per_rubles.$dirty) {
                    if (!this.$v.form.order_activation_bonus_delay.required) return "Обязательное поле!";
                    if (!this.$v.form.order_activation_bonus_delay.integer) return "Введите целое число!";
                    if (!this.$v.form.order_activation_bonus_delay.minValue) return "Значение должно быть ≥ 0";
                }
            },
            errorActivationBonusValidPeriod() {
                if (this.$v.form.activation_bonus_valid_period.$dirty) {
                    if (!this.$v.form.activation_bonus_valid_period.required) return "Обязательное поле!";
                    if (!this.$v.form.activation_bonus_valid_period.integer) return "Введите целое число!";
                    if (!this.$v.form.activation_bonus_valid_period.minValue) return "Значение должно быть > 0";
                }
            },
            errorActivationBonusValue() {
                if (this.$v.form.activation_bonus_value.$dirty) {
                    if (!this.$v.form.activation_bonus_value.required) return "Обязательное поле!";
                    if (!this.$v.form.activation_bonus_value.integer) return "Введите целое число!";
                    if (!this.$v.form.activation_bonus_value.minValue) return "Значение должно быть > 0";
                }
            },
            errorMaxDebitPercentageForProduct() {
                if (this.$v.form.max_debit_percentage_for_product.$dirty) {
                    if (!this.$v.form.max_debit_percentage_for_product.required) return "Обязательное поле!";
                    if (!this.$v.form.max_debit_percentage_for_product.integer) return "Введите целое число!";
                    if (!this.$v.form.max_debit_percentage_for_product.minValue) return "Значение должно быть ≥ 0";
                    if (!this.$v.form.max_debit_percentage_for_product.maxValue) return "Значение должно быть ≤ 100";
                }
            },
            errorMaxDebitPercentageForDiscountProduct() {
                if (this.$v.form.max_debit_percentage_for_discount_product.$dirty) {
                    if (!this.$v.form.max_debit_percentage_for_discount_product.required) return "Обязательное поле!";
                    if (!this.$v.form.max_debit_percentage_for_discount_product.integer) return "Введите целое число!";
                    if (!this.$v.form.max_debit_percentage_for_discount_product.minValue) return "Значение должно быть ≥ 0";
                    if (!this.$v.form.max_debit_percentage_for_discount_product.maxValue) return "Значение должно быть ≤ 100";
                }
            },
            errorMaxDebitPercentageForOrder() {
                if (this.$v.form.max_debit_percentage_for_order.$dirty) {
                    if (!this.$v.form.max_debit_percentage_for_order.required) return "Обязательное поле!";
                    if (!this.$v.form.max_debit_percentage_for_order.integer) return "Введите целое число!";
                    if (!this.$v.form.max_debit_percentage_for_order.minValue) return "Значение должно быть ≥ 0";
                    if (!this.$v.form.max_debit_percentage_for_order.maxValue) return "Значение должно быть ≤ 100";
                }
            },
            errorBonusExpireDaysNotify() {
                if (this.$v.form.bonus_expire_days_notify.$dirty) {
                    if (!this.$v.form.bonus_expire_days_notify.required) return "Обязательное поле!";
                    if (!this.$v.form.bonus_expire_days_notify.integer) return "Введите целое число!";
                    if (!this.$v.form.bonus_expire_days_notify.minValue) return "Значение должно быть ≥ 0";
                }
            },
            anyDirty() {
                return this.$v.$invalid
                || Number(this.form.min_withdrawal_amount) > Number(this.form.max_withdrawal_amount)
            },
        },
        watch: {
            bonusActivationBtn() {
                this.updateInput('activation_bonus');
            },
            bonusUnlimitedPeriodBtn() {
                this.updateInput('activation_bonus');
            },
            bonusExpireDaysNotifyBtn() {
                if (this.bonusExpireDaysNotifyBtn) {
                    this.form.bonus_expire_days_notify = null;
                    this.updateInput('bonus_expire_days_notify');
                }
            }
        },
    }
</script>
