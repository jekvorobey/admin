<template>
    <div>
        <div class="row">
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
                <v-select v-model="$v.form.do_consolidation.$model" :options="booleanOptions">
                    Консолидация многоместных отправлений
                </v-select>
            </div>
            <div class="col">
                <v-select v-model="$v.form.do_deconsolidation.$model" :options="booleanOptions">
                    Расконсолидация
                </v-select>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <v-select v-model="$v.form.do_zero_mile.$model" :options="booleanOptions">
                    Нулевая миля
                </v-select>
            </div>
            <div class="col">
                <v-select v-model="$v.form.do_express_delivery.$model" :options="booleanOptions">
                    Экспресс-доставка
                </v-select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <v-select v-model="$v.form.do_return.$model" :options="booleanOptions">
                    Принимает возвраты
                </v-select>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="max_cargo_export_time">
                        Крайнее время для заданий на забор
                    </label>
                    <date-picker
                            id="max_cargo_export_time"
                            v-model="$v.form.max_cargo_export_time.$model"
                            input-class="form-control form-control-sm"
                            type="time"
                            format="HH:mm"
                            value-type="format"
                            :time-picker-options="timePickerOptions"
                    />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <p class="font-weight-bold">Услуги логистического оператора</p>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <v-select v-model="$v.form.add_partial_reject_service.$model" :options="booleanOptions">
                    Частичный отказ
                </v-select>
            </div>
            <div class="col">
                <v-select v-model="$v.form.add_return_service.$model" :options="booleanOptions">
                    Возможность возврата
                </v-select>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <v-select v-model="$v.form.add_fitting_service.$model" :options="booleanOptions">
                    Примерка
                </v-select>
            </div>
            <div class="col">
                <v-select v-model="$v.form.add_open_service.$model" :options="booleanOptions">
                    Вскрытие разрешено
                </v-select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <v-select v-model="$v.form.add_insurance_service.$model" :options="booleanOptions">
                    Страхование груза
                </v-select>
            </div>
        </div>
    </div>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import DatePicker from 'vue2-datepicker';
    import 'vue2-datepicker/index.css';
    import 'vue2-datepicker/locale/ru.js';

    import {helpers, required} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    const timeValidator = helpers.regex('timeValidator', /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/);

export default {
    name: 'tab-settings',
    components: {
        VInput,
        VSelect,
        DatePicker,
    },
    props: [
        'model',
    ],
    mixins: [
        validationMixin,
    ],
    data() {
        return {
            timePickerOptions: {
                start: '00:00',
                step: '00:30',
                end: '23:30'
            },
            form: {
                do_consolidation: this.model.do_consolidation,
                do_deconsolidation: this.model.do_deconsolidation,
                do_zero_mile: this.model.do_zero_mile,
                do_express_delivery: this.model.do_express_delivery,
                do_return: this.model.do_return,
                max_cargo_export_time: this.model.max_cargo_export_time,
                add_partial_reject_service: this.model.add_partial_reject_service,
                add_insurance_service: this.model.add_insurance_service,
                add_fitting_service: this.model.add_fitting_service,
                add_return_service: this.model.add_return_service,
                add_open_service: this.model.add_open_service,
            },
        }
    },
    validations: {
        form: {
            do_consolidation: {required},
            do_deconsolidation: {required},
            do_zero_mile: {required},
            do_express_delivery: {required},
            do_return: {required},
            max_cargo_export_time: {timeValidator},
            add_partial_reject_service: {required},
            add_insurance_service: {required},
            add_fitting_service: {required},
            add_return_service: {required},
            add_open_service: {required},
        }
    },
    methods: {
        save() {
            Services.showLoader();
            Services.net().put(this.getRoute('deliveryService.detail.settings.save', {id: this.deliveryService.id}), {}, this.form).then(() => {
                this.deliveryService.do_consolidation = this.form.do_consolidation;
                this.deliveryService.do_deconsolidation = this.form.do_deconsolidation;
                this.deliveryService.do_zero_mile = this.form.do_zero_mile;
                this.deliveryService.do_express_delivery = this.form.do_express_delivery;
                this.deliveryService.do_return = this.form.do_return;
                this.deliveryService.max_cargo_export_time = this.form.max_cargo_export_time;
                this.deliveryService.add_partial_reject_service = this.form.add_partial_reject_service;
                this.deliveryService.add_insurance_service = this.form.add_insurance_service;
                this.deliveryService.add_fitting_service = this.form.add_fitting_service;
                this.deliveryService.add_return_service = this.form.add_return_service;
                this.deliveryService.add_open_service = this.form.add_open_service;

                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            });
        },
        cancel() {
            this.form.do_consolidation = this.deliveryService.do_consolidation;
            this.form.do_deconsolidation = this.deliveryService.do_deconsolidation;
            this.form.do_zero_mile = this.deliveryService.do_zero_mile;
            this.form.do_express_delivery = this.deliveryService.do_express_delivery;
            this.form.do_return = this.deliveryService.do_return;
            this.form.max_cargo_export_time = this.deliveryService.max_cargo_export_time;
            this.form.add_partial_reject_service = this.deliveryService.add_partial_reject_service;
            this.form.add_insurance_service = this.deliveryService.add_insurance_service;
            this.form.add_fitting_service = this.deliveryService.add_fitting_service;
            this.form.add_return_service = this.deliveryService.add_return_service;
            this.form.add_open_service = this.deliveryService.add_open_service;
        },
    },
    computed: {
        deliveryService: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        booleanOptions() {
            return [{value: 0, text: 'Нет'}, {value: 1, text: 'Да'}];
        },
    },
};
</script>
