<template>
    <b-modal id="payment-method-edit-modal" hide-footer ref="modal" size="lg" @hidden="$v.$reset()">
        <div slot="modal-title">
            <strong>Редактировать способ оплаты</strong>
        </div>
        <div class="card">
            <div class="card-body">
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label for="payment-method-name">Название</label>
                    </b-col>
                    <b-col cols="8">
                        <v-input id="payment-method-name"
                               v-model="$v.paymentMethod.name.$model"
                               class="mb-2"
                               :error="errorNameField"
                        />
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label for="payment-method-code">
                            Символьный код
                        </label>
                    </b-col>
                    <b-col cols="8">
                        <v-input id="payment-method-code"
                                 :value="paymentMethod.code"
                                 class="mb-2"
                                 disabled
                        />
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label for="payment-method-postpaid">Постоплата</label>
                    </b-col>
                    <b-col cols="8">
                        <input id="payment-method-postpaid"
                               type="checkbox"
                               :value="paymentMethod.is_postpaid"
                               disabled
                        />
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label for="payment-method-active">Активен</label>
                    </b-col>
                    <b-col cols="8">
                        <input id="payment-method-active"
                               type="checkbox"
                               v-model="$v.paymentMethod.active.$model"
                        />
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label for="payment-method-is-apply-discounts">Доступно ли скидки и списание бонусов</label>
                    </b-col>
                    <b-col cols="8">
                        <input id="payment-method-is-apply-discounts"
                               type="checkbox"
                               v-model="$v.paymentMethod.is_apply_discounts.$model"
                        />
                    </b-col>
                </b-row>
                <b-row v-if="hasSetting('is_fixed_discount')" class="mb-2">
                    <b-col cols="4">
                        <label for="payment-method-is-fixed-discount">Фиксированный размер скидки</label>
                    </b-col>
                    <b-col cols="8">
                        <input id="payment-method-is-fixed-discount"
                               type="checkbox"
                               v-model="$v.paymentMethod.settings.is_fixed_discount.$model"
                        />
                    </b-col>
                </b-row>
                <b-row v-if="hasSetting('discount') && isFixedDiscount" class="mb-2">
                    <b-col cols="4">
                        <label for="payment-method-setting-discount">Размер скидки</label>
                    </b-col>
                    <b-col cols="8">
                        <v-input id="payment-method-setting-discount"
                                 v-model="$v.paymentMethod.settings.discount.$model"
                                 class="mb-2"
                        />
                    </b-col>
                </b-row>
                <b-row v-if="hasSetting('signingKD')" class="mb-2">
                    <b-col cols="4">
                        <label for="payment-method-setting-signingKD">Подписание КД</label>
                    </b-col>
                    <b-col cols="8">
                        <v-input id="payment-method-setting-signingKD"
                                 v-model="$v.paymentMethod.settings.signingKD.$model"
                                 class="mb-2"
                        />
                    </b-col>
                </b-row>
                <b-row v-if="hasSetting('button_text')" class="mb-2">
                    <b-col cols="4">
                        <label for="payment-method-button-text">Текст на кнопке</label>
                    </b-col>
                    <b-col cols="8">
                        <v-input id="payment-method-button-text"
                               v-model="$v.paymentMethod.settings.button_text.$model"
                               class="mb-2"
                        />
                    </b-col>
                </b-row>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-success"
                    :disabled="$v.paymentMethod.$invalid"
                    @click="save">
                Сохранить
            </button>
            <button class="btn btn-outline-danger"
                    @click="closeModal">
                Отмена
            </button>
        </div>
    </b-modal>
</template>

<script>
    import Services from "../../../../../scripts/services/services";
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDate from '../../../../components/controls/VDate/VDate.vue';
    import { validationMixin } from 'vuelidate';
    import { required } from 'vuelidate/lib/validators';

    export default {
        components: {
            VSelect,
            VInput,
            VDate
        },
        mixins: [validationMixin],
        props: {
            editingModel: Object,
        },
        data() {
            return {
                paymentMethod: this.editingModel || {},
            }
        },
        validations() {
            return {
                paymentMethod: {
                    name: {required},
                    active: {required},
                    is_apply_discounts: {required},
                    settings: {
                        is_fixed_discount: {},
                        discount: {},
                        signingKD: {},
                        button_text: {},
                    },
                },
            }
        },
        methods: {
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
                    settings: this.paymentMethod.settings,
                };

                Services.showLoader();
                Services.net().put(
                    this.getRoute('settings.paymentMethods.edit', {id: this.paymentMethod.id}), {}, data
                )
                    .then(() => {
                        this.$emit('saved', this.paymentMethod);
                        Services.msg('Параметры способа оплаты успешно сохранены');
                    }, () => {
                        Services.msg('Не удалось сохранить изменения','danger');
                    }).finally(() => {
                        this.$v.$reset();
                        this.closeModal();
                        Services.hideLoader();
                    });
            },
            closeModal() {
                this.$bvModal.hide('payment-method-edit-modal');
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
        },
        watch: {
            editingModel(value) {
                this.paymentMethod = value;
            },
        },
    }
</script>
