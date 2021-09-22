<template>
    <b-modal id="offer-status-edit-modal" title="Редактировать статус оффера" @hidden="resetFields()" hide-footer>
        <template v-slot:default="{close}">
            <v-select v-model="$v.newStatus.$model"
                      :options="statusOptions"
                      class="mt-3"
                      :error="errorStatusField()">
                Выберите новый статус
            </v-select>
            <v-date v-if="displayDateSelect"
                    :min="tomorrow"
                    v-model="$v.newSaleDate.$model"
                    :error="errorDateField()">
                Дата начала продажи
            </v-date>
            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="saveStatus()">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import VSelect from "../../../../components/controls/VSelect/VSelect.vue";
    import VDate from "../../../../components/controls/VDate/VDate.vue";
    import { validationMixin } from 'vuelidate';
    import { required, requiredIf, integer, helpers } from 'vuelidate/lib/validators';
    import * as moment from 'moment';
    import Services from "../../../../../scripts/services/services";

    const futureDate = (date) => {
        let today = moment().format('YYYY-MM-DD');
        return !helpers.req(date) || moment(date).isAfter(today);
    };

    export default {
        components: {
            VSelect,
            VDate,
        },
        mixins: [validationMixin],
        props: ['model'],
        data() {
            return {
                tomorrow: moment().add(1,'days').format('YYYY-MM-DD'),
                newStatus: null,
                newSaleDate: null
            }
        },
        validations() {
            return {
                newStatus: {
                    required,
                    integer,
                },
                newSaleDate: {
                    futureDate,
                    required: requiredIf(function () {
                        return this.newStatus
                            && this.offerCountdownSaleStatuses.includes(this.newStatus);
                    }),
                },
            }
        },
        methods: {
            errorStatusField() {
                if (this.$v.newStatus.$dirty
                    && this.$v.newStatus.$invalid) {
                    return "Выберите статус";
                }
            },
            errorDateField() {
                if (this.$v.newSaleDate.$dirty
                    && this.$v.newSaleDate.$invalid) {
                    return "Укажите корректную дату";
                }
            },
            resetFields() {
                this.tomorrow = moment().add(1,'days').format('YYYY-MM-DD');
                this.newStatus = null;
                this.newSaleDate = null;
                this.$v.$reset();
            },
            saveStatus() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('offers.change.saleStatus'), {}, {
                    'offer_ids': [this.offer.id],
                    'sale_status': this.newStatus,
                    'sale_at': this.newSaleDate
                }, {}, true).then((data) => {
                    Services.msg("Статус оффера изменён!");
                    this.offer.status = this.newStatus;
                    this.offer.sale_at = this.newSaleDate;
                    this.$bvModal.hide('offer-status-edit-modal');
                }, () => {
                    Services.msg("Не удалось изменить статус", 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            }
        },
        computed: {
            offer: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
            statusOptions() {
                return Object.values(this.offerAllSaleStatuses).map((val) => {
                    return {value: parseInt(val.id), text: val.name};
                });
            },
            displayDateSelect() {
                this.newSaleDate = null;
                this.$v.newSaleDate.$reset();
                return this.newStatus
                    && this.offerCountdownSaleStatuses.includes(this.newStatus);
            },
        }
    }
</script>
