<template>
    <div>
        <div class="row mt-4">
            <v-input v-model="$v.form.bonus.limit.$model" class="col-6" type="number" :error="errorBonusLimit" :disabled="bonusLimitBtn">
                Максимальный процент от единицы товара, который можно оплатить бонусами
            </v-input>

            <div class="col-5">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="bonusLimitBtn" key="bonusLimitBtn" v-model="bonusLimitBtn">
                    <label class="custom-control-label" for="bonusLimitBtn">Использовать глобальные настройки</label>
                </div>
            </div>
        </div>

        <button @click="save" class="btn btn-dark mt-3">Сохранить</button>
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
                        limit: this.marketing.bonus.limit,
                    },
                },
                bonusLimitBtn: this.marketing.bonus.limit === null,
            }
        },
        validations: {
            form: {
                bonus: {
                    limit: {required, integer, minValue: minValue(0), maxValue: maxValue(100)}
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
                Services.net().put(this.getRoute('bonus.changeProductLimit'), {}, {
                    product_id: this.product.id,
                    value: !this.bonusLimitBtn ? parseInt(this.form.bonus.limit) : null,
                }).then(() => {
                    Services.msg("Значение обновлено!");
                    setTimeout(() => {
                     //   location = location.reload();
                    }, 1000);
                }).catch(() => {
                    Services.msg("Ошибка при обновлении!", "danger");
                }).finally(() => {
                    Services.hideLoader();
                });
            }
        },
        computed: {
            errorBonusLimit() {
                if (this.$v.form.bonus.limit.$dirty) {
                    if (this.$v.form.bonus.limit.required === false) return "Обязательное поле!";
                    if (this.$v.form.bonus.limit.minValue === false) return "От 0 до 100!";
                    if (this.$v.form.bonus.limit.maxValue === false) return "От 0 до 100!";
                    if (this.$v.form.bonus.limit.integer === false) return "Только целые числа!";
                }
            },
        }
    }
</script>
