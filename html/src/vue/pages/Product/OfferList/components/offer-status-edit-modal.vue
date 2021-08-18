<template>
    <b-modal id="offer-status-edit-modal" hide-footer ref="modal" size="lg" @hidden="resetFields()">
        <div slot="modal-title">
            <strong>Редактировать статус офферов</strong>
        </div>
        <div>
            <table class="table">
                <tbody>
                <tr v-for="offer in offers">
                    <td>#{{ offer.id }}</td>
                    <td>{{ offer.productName }}</td>
                    <td>{{ offer.merchantName }}</td>
                </tr>
                </tbody>
            </table>
            <v-select v-model="newStatus" :options="modalStatuses" class="mt-3">Выберите новый статус</v-select>
            <v-date v-if="displayDateSelect"
                    :min="tomorrow"
                    v-model="$v.sale_at.$model"
                    :error="errorDateField()">
                Дата начала продажи
            </v-date>
            <button class="btn btn-success mt-3" type="button" @click="approveChangeStatus()" :disabled="!newStatus">Сохранить</button>
        </div>
    </b-modal>
</template>

<script>
    import Services from "../../../../../scripts/services/services";
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import VDate from '../../../../components/controls/VDate/VDate.vue';
    import * as moment from 'moment';
    import { validationMixin } from 'vuelidate';
    import { requiredIf, helpers } from 'vuelidate/lib/validators';

    const futureDate = (date) => {
        let today = moment().format('YYYY-MM-DD');
        return !helpers.req(date) || moment(date).isAfter(today);
    };

    export default {
        components: {
            VSelect,
            VDate
        },
        mixins: [validationMixin],
        props: {
            offers: Array,
        },
        data() {
            return {
                newStatus: null,
                tomorrow: moment().add(1,'days').format('YYYY-MM-DD'),
                sale_at: null
            }
        },
        validations() {
            return {
                sale_at: {
                    futureDate,
                    required: requiredIf(function () {
                        return this.newStatus
                            && this.offerCountdownSaleStatuses.includes(this.newStatus);
                    }),
                },
            }
        },
        methods: {
            approveChangeStatus() {
                this.$v.sale_at.$touch();
                if (!this.newStatus || this.$v.sale_at.$invalid) {
                    return;
                }
                Services.showLoader();
                Services.net().put(this.route('offers.change.saleStatus'), {}, {
                    offer_ids: this.offerIds,
                    sale_status: this.newStatus,
                    sale_at: this.getSelectedDate()
                }, {}, true).then(data => {
                    Services.hideLoader();
                    let message = this.offers.length > 1 ? "Статус офферов изменён!" : "Статус оффера изменён!";
                    Services.msg(message);
                    this.$bvModal.hide('offer-status-edit-modal');
                    setTimeout(window.location.reload.bind(window.location), 1000);
                }, () => {
                    Services.hideLoader();
                    Services.msg('Не удалось изменить статус', 'danger');
                });
            },
            getSelectedDate() {
                let date = this.sale_at;
                if (!this.offerCountdownSaleStatuses.includes(this.newStatus)) {
                    date = null;
                }
                return date;
            },
            errorDateField() {
                if (this.$v.sale_at.$dirty
                    && this.$v.sale_at.$invalid) {
                    return "Укажите корректную дату";
                }
            },
            resetFields() {
                this.newStatus = null;
                this.tomorrow = moment().add(1,'days').format('YYYY-MM-DD');
                this.sale_at = null;
            }
        },
        computed: {
            offerIds() {
                return Object.values(this.offers).map((offer) => offer.id);
            },
            modalStatuses() {
                return Object.values(this.offerAllSaleStatuses).map((val) => {
                    return {value: parseInt(val.id), text: val.name};
                });
            },
            displayDateSelect() {
                this.$v.sale_at.$reset();
                return this.newStatus
                    && this.offerCountdownSaleStatuses.includes(this.newStatus);
            },
        }
    }
</script>
