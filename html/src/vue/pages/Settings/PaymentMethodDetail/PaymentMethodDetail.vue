<template>
    <layout-main back>
        <form @submit.prevent="save">
            <div class="row">

            </div>
            <v-input id="payment-method-name"
                     v-model="$v.paymentMethod.name.$model"
                     :error="errorNameField"
            >Название
            </v-input>

            <v-input id="payment-method-code"
                     :value="paymentMethod.code"
                     disabled
            >Символьный код
            </v-input>

            <v-input tag="textarea"
                     id="payment-method-button-text"
                     v-model="$v.paymentMethod.button_text.$model"
                     rows="14"
            >Контент на кнопке
            </v-input>

            <v-input id="payment-method-min-available-price"
                     v-model="$v.paymentMethod.min_available_price.$model"
                     type="number"
            >Доступен при сумме от
            </v-input>

            <v-input id="payment-method-max-available-price"
                     v-model="$v.paymentMethod.max_available_price.$model"
                     type="number"
            >Доступен при сумме до
            </v-input>

            <div class="form-group">
                <span class="custom-control custom-switch">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="payment-method-active"
                           v-model="$v.paymentMethod.active.$model"
                    >
                    <label class="custom-control-label" for="payment-method-active"></label>
                    <label for="payment-method-active">Активен</label>
                </span>
            </div>

            <div class="form-group">
                <span class="custom-control custom-switch">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="payment-method-is-apply-discounts"
                           v-model="$v.paymentMethod.is_apply_discounts.$model"
                    >
                    <label class="custom-control-label" for="payment-method-is-apply-discounts"></label>
                    <label for="payment-method-is-apply-discounts">Доступны ли скидки и списание бонусов</label>
                </span>
            </div>

            <div class="form-group">
                <span class="custom-control custom-switch">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="payment-method-is-available-for-mc"
                           v-model="$v.paymentMethod.is_available_for_mc.$model"
                    >
                    <label class="custom-control-label" for="payment-method-is-available-for-mc"></label>
                    <label for="payment-method-is-available-for-mc">Доступен для оплаты МК</label>
                </span>
            </div>

            <div class="form-group" v-if="hasSetting('is_fixed_discount')">
                <span class="custom-control custom-switch">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="payment-method-is-fixed-discount"
                           v-model="$v.paymentMethod.settings.is_fixed_discount.$model">
                    <label class="custom-control-label" for="payment-method-is-fixed-discount"></label>
                    <label for="payment-method-is-fixed-discount">Фиксированный размер скидки</label>
                </span>
            </div>

            <div class="form-group" v-if="hasSetting('discount') && isFixedDiscount">
                <v-input id="payment-method-setting-discount"
                         v-model="$v.paymentMethod.settings.discount.$model">
                    Размер скидки
                </v-input>
            </div>

            <div class="form-group" v-if="hasSetting('is_displayed_in_catalog')">
                <span class="custom-control custom-switch">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="payment-method-is-displayed-in-catalog"
                           v-model="$v.paymentMethod.settings.is_displayed_in_catalog.$model">
                    <label class="custom-control-label" for="payment-method-is-displayed-in-catalog"></label>
                    <label
                        for="payment-method-is-displayed-in-catalog">Выводить в каталоге товаров и на странице товара</label>
                </span>
            </div>

            <div class="form-group" v-if="hasSetting('is_displayed_in_public_events')">
                <span class="custom-control custom-switch">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="payment-method-is-displayed-in-mk"
                           v-model="$v.paymentMethod.settings.is_displayed_in_public_events.$model">
                    <label class="custom-control-label" for="payment-method-is-displayed-in-mk"></label>
                    <label for="payment-method-is-displayed-in-mk">Выводить в каталоге МК и на странице МК</label>
                </span>
            </div>

            <div class="form-group" v-if="hasSetting('installment_period') && (isDisplayedInCatalog || isDisplayedInMk)">
                <v-input id="payment-method-setting-installment-period"
                         v-model="$v.paymentMethod.settings.installment_period.$model"
                         readonly="readonly">
                    Период рассрочки
                </v-input>
            </div>

            <div class="form-group" v-if="hasSetting('signingKD')">
                <v-input id="payment-method-setting-signingKD"
                         v-model="$v.paymentMethod.settings.signingKD.$model">
                    Подписание КД
                </v-input>
            </div>

            <b-button v-if="canUpdate(blocks.settings)" type="submit" variant="dark">
                Обновить
            </b-button>
        </form>
    </layout-main>
</template>

<script>
import Services from "../../../../scripts/services/services";
import VSelect from '../../../components/controls/VSelect/VSelect.vue';
import VInput from '../../../components/controls/VInput/VInput.vue';
import VDate from '../../../components/controls/VDate/VDate.vue';
import {validationMixin} from 'vuelidate';
import {required} from 'vuelidate/lib/validators';
import {mapActions} from 'vuex';

export default {
    components: {
        VSelect,
        VInput,
        VDate
    },
    mixins: [validationMixin],
    props: {
        iPaymentMethod: Object,
    },
    data() {
        return {
            paymentMethod: this.iPaymentMethod || {},
        }
    },
    validations() {
        return {
            paymentMethod: {
                name: {required},
                active: {required},
                is_apply_discounts: {required},
                is_available_for_mc: {required},
                button_text: {},
                min_available_price: {},
                max_available_price: {},
                settings: {
                    is_fixed_discount: {},
                    discount: {},
                    is_displayed_in_catalog: {},
                    is_displayed_in_public_events: {},
                    installment_period: {},
                    signingKD: {},
                },
            },
        }
    },
    methods: {
        ...mapActions({
            showMessageBox: 'modal/showMessageBox',
        }),

        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            let data = {
                name: this.paymentMethod.name,
                code: this.paymentMethod.code,
                active: this.paymentMethod.active,
                is_postpaid: this.paymentMethod.is_postpaid,
                is_apply_discounts: this.paymentMethod.is_apply_discounts,
                is_available_for_mc: this.paymentMethod.is_available_for_mc,
                button_text: this.paymentMethod.button_text,
                min_available_price: this.paymentMethod.min_available_price,
                max_available_price: this.paymentMethod.max_available_price,
                settings: this.paymentMethod.settings,
            };

            Services.showLoader();
            Services.net().put(
                this.getRoute('settings.paymentMethods.update', {id: this.paymentMethod.id}), {}, data
            )
                .then(() => {
                    this.showMessageBox({title: 'Изменения сохранены'});
                    window.location.href = this.route('settings.paymentMethods');
                }, () => {
                    this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                }).finally(() => {
                Services.hideLoader();
            });
        },
        hasSetting(settingKey) {
            return this.paymentMethod.settings && Object.keys(this.paymentMethod.settings).includes(settingKey);
        },
    },
    computed: {
        errorNameField() {
            if (this.$v.paymentMethod.name.$dirty
                && this.$v.paymentMethod.name.$invalid) {
                return "Введите корректное название";
            }
        },
        isFixedDiscount() {
            return this.paymentMethod.settings.is_fixed_discount;
        },
        isDisplayedInCatalog() {
            return this.paymentMethod.settings.is_displayed_in_catalog;
        },
        isDisplayedInMk() {
            return this.paymentMethod.settings.is_displayed_in_public_events;
        },
    }
}
</script>
