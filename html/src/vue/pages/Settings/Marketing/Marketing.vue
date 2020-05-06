<template>
    <layout-main>
        <form v-on:submit.prevent.stop="update">
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

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </layout-main>
</template>


<script>
    import Services from '../../../../scripts/services/services';
    import {validationMixin} from 'vuelidate';
    import {required, requiredIf, minValue, integer} from 'vuelidate/lib/validators';
    import VInput from '../../../components/controls/VInput/VInput.vue';
    import FMultiSelect from '../../../components/filter/f-multi-select.vue';

    export default {
        name: 'page-marketing-settings',
        components: {VInput, FMultiSelect},
        props: {
            bonus_per_rubles: Number,
            order_activation_bonus_delay: Number,
            roles_available_for_bonuses: Array,
            activation_bonus: [Object, null],
            roles: Array,
        },
        mixins: [validationMixin],
        data() {
            return {
                form: {
                    bonus_per_rubles: this.bonus_per_rubles,
                    roles_available_for_bonuses: this.roles_available_for_bonuses,
                    order_activation_bonus_delay: this.order_activation_bonus_delay,

                    activation_bonus_name: this.activation_bonus ? this.activation_bonus.name : null,
                    activation_bonus_value: this.activation_bonus ? this.activation_bonus.value : null,
                    activation_bonus_valid_period: this.activation_bonus ? this.activation_bonus.valid_period : null,
                },
                requestData: {},
                bonusActivationBtn: this.activation_bonus && this.activation_bonus.value > 0,
                bonusUnlimitedPeriodBtn: !this.activation_bonus || this.activation_bonus.valid_period <= 0,
            }
        },
        validations: {
            form: {
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
        },
        watch: {
            bonusActivationBtn() {
                this.updateInput('activation_bonus');
            },
            bonusUnlimitedPeriodBtn() {
                this.updateInput('activation_bonus');
            }
        },
    }
</script>
