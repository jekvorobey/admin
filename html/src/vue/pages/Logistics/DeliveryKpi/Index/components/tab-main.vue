<template>
    <div>
        <div class="row" v-if="canUpdate(blocks.logistics)">
            <div class="col">
                <div class="float-right">
                    <button class="btn btn-success btn-sm" @click="save" :disabled="!$v.form.$anyDirty">
                        Сохранить
                    </button>
                    <button @click="cancel" class="btn btn-outline-danger btn-sm mr-1" :disabled="!$v.form.$anyDirty">
                        Отмена
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <v-input type="number" v-model="$v.form.rtg.$model" :error="errorRtg" :help="helpRtg">
                    RTG (мин)
                </v-input>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <v-input type="number" v-model="$v.form.ct.$model" :error="errorCt" :help="helpCt">
                    CT (мин)
                </v-input>
            </div>
            <div class="col">
                <v-input type="number" v-model="$v.form.ppt.$model" :error="errorPpt" :help="helpPpt">
                    PPT (мин)
                </v-input>
            </div>
        </div>
    </div>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';

    import {validationMixin} from 'vuelidate';
    import {integer, minValue, required} from 'vuelidate/lib/validators';

    export default {
        name: 'tab-main',
        components: {
            VInput,
        },
        mixins: [
            validationMixin,
        ],
        data() {
            return {
                deliveryKpi: {},
                form: {
                    rtg: 0,
                    ct: 0,
                    ppt: 0,
                },
            };
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('deliveryKpi.main.get')).then(data => {
                this.deliveryKpi = data.deliveryKpi;
                this.form.rtg = this.deliveryKpi.rtg;
                this.form.ct = this.deliveryKpi.ct;
                this.form.ppt = this.deliveryKpi.ppt;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        validations: {
            form: {
                rtg: {required, integer, minValue: minValue(0)},
                ct: {required, integer, minValue: minValue(0)},
                ppt: {required, integer, minValue: minValue(0)},
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('deliveryKpi.main.set'), {}, this.form).then(() => {
                    this.deliveryKpi.rtg = this.form.rtg;
                    this.deliveryKpi.ct = this.form.ct;
                    this.deliveryKpi.ppt = this.form.ppt;

                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            cancel() {
                this.form.rtg = this.deliveryKpi.rtg;
                this.form.ct = this.deliveryKpi.ct;
                this.form.ppt = this.deliveryKpi.ppt;
            },
        },
        computed: {
            errorRtg() {
                if (this.$v.form.rtg.$dirty) {
                    if (!this.$v.form.rtg.required) {
                        return "Обязательное поле!";
                    }
                    if (!this.$v.form.rtg.integer || !this.$v.form.rtg.minValue) {
                        return "Только целое число больше, либо равно 0";
                    }
                }
            },
            errorCt() {
                if (this.$v.form.ct.$dirty) {
                    if (!this.$v.form.ct.required) {
                        return "Обязательное поле!";
                    }
                    if (!this.$v.form.ct.integer || !this.$v.form.ct.minValue) {
                        return "Только целое число больше, либо равно 0";
                    }
                }
            },
            errorPpt() {
                if (this.$v.form.ppt.$dirty) {
                    if (!this.$v.form.ppt.required) {
                        return "Обязательное поле!";
                    }
                    if (!this.$v.form.rtg.integer || !this.$v.form.ppt.minValue) {
                        return "Только целое число больше, либо равно 0";
                    }
                }
            },
            helpRtg() {
                return 'Ready-To-Go time - время для проверки заказа АОЗ до его передачи в MAS';
            },
            helpCt() {
                return 'Confirmation Time - время перехода Отправления из статуса “Ожидает подтверждения” в статус “На комплектации”';
            },
            helpPpt() {
                return 'Planned Processing Time - плановое время для прохождения Отправлением статусов от “На комплектации” до “Готов к передаче ЛО”';
            },
        },
    };
</script>
