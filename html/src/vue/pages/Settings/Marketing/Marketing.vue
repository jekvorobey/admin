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

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </layout-main>
</template>


<script>
    import Services from '../../../../scripts/services/services';
    import {validationMixin} from 'vuelidate';
    import {required, minValue} from 'vuelidate/lib/validators';
    import VInput from '../../../components/controls/VInput/VInput.vue';
    import FMultiSelect from '../../../components/filter/f-multi-select.vue';

    export default {
        name: 'page-marketing-settings',
        components: {VInput, FMultiSelect},
        props: {
            bonus_per_rubles: Number,
            roles_available_for_bonuses: Array,
            roles: Array,
        },
        mixins: [validationMixin],
        data() {
            return {
                form: {
                    bonus_per_rubles: this.bonus_per_rubles,
                    roles_available_for_bonuses: this.roles_available_for_bonuses,
                },
                requestData: {},
            }
        },
        validations: {
            form: {
                bonus_per_rubles: {required, minValue: minValue(0)},
            }
        },
        methods: {
            updateInput(type) {
                this.requestData[type] = this.form[type];
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
            // ===============================
            errorBonusPerRubles() {
                if (this.$v.form.bonus_per_rubles.$dirty) {
                    if (!this.$v.form.bonus_per_rubles.required) return "Обязательное поле!";
                    if (!this.$v.form.bonus_per_rubles.minValue) return "Значение должно быть ≥ 0";
                }
            },
        }
    }
</script>
