<template>
    <div>
        <div class="row mt-4">
            <v-input v-model="$v.form.bonus.maxPercentagePayment.$model" class="col-6" type="number" :error="errorBonusMaxPercentagePayment" :disabled="bonusMPPBtn">
                Максимальный процент от единицы товара, который можно оплатить бонусами
            </v-input>

            <div class="col-5">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="bonusMPPBtn" key="bonusMPPBtn" v-model="bonusMPPBtn">
                    <label class="custom-control-label" for="bonusMPPBtn">Использовать глобальные настройки</label>
                </div>
            </div>
        </div>

        <button @click="save" class="btn btn-dark mt-3" v-if="canUpdate(blocks.products)">Сохранить</button>
    </div>
</template>

<script>
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import { validationMixin } from 'vuelidate';
    import { required, integer, minValue, maxValue } from 'vuelidate/lib/validators';
    import Services from "../../../../../scripts/services/services";

    export default {
        components: {
            VInput
        },
        mixins: [validationMixin],
        props: {
            product: {},
            marketing: Object
        },
        data: function() {
            return {
                form: {
                    bonus: {
                        maxPercentagePayment: this.marketing.bonus.maxPercentagePayment,
                    },
                },
                bonusMPPBtn: this.marketing.bonus.maxPercentagePayment === null,
            }
        },
        validations: {
            form: {
                bonus: {
                    maxPercentagePayment: {required, integer, minValue: minValue(0), maxValue: maxValue(100)}
                },
            },
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('bonus.changeMPP'), {}, {
                    product_id: this.product.id,
                    value: !this.bonusMPPBtn ? parseInt(this.form.bonus.maxPercentagePayment) : null,
                }).then(() => {
                    Services.msg("Значение обновлено!");
                }).catch(() => {
                    Services.msg("Ошибка при обновлении!", "danger");
                }).finally(() => {
                    Services.hideLoader();
                });
            }
        },
        computed: {
            errorBonusMaxPercentagePayment() {
                if (this.$v.form.bonus.maxPercentagePayment.$dirty) {
                    if (this.$v.form.bonus.maxPercentagePayment.required === false) return "Обязательное поле!";
                    if (this.$v.form.bonus.maxPercentagePayment.minValue === false) return "От 0 до 100!";
                    if (this.$v.form.bonus.maxPercentagePayment.maxValue === false) return "От 0 до 100!";
                    if (this.$v.form.bonus.maxPercentagePayment.integer === false) return "Только целые числа!";
                }
            },
        }
    }
</script>
