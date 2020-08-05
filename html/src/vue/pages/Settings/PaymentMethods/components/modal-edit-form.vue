<template>
    <b-modal id="modal-paymentMethod-edit" size="lg" title="Редактирование способа оплаты" @show="decodeData" hide-footer ref="modal">
        <template v-slot:default="{close}">
            <div slot="body">
                <table class="table table-sm">
                    <tbody>
                    <tr>
                        <th width="50%">Название способа оплаты</th>
                        <td>
                            <v-input v-model="payment_method.name"
                                     @change="$v.$touch()"
                                     :error="errName"
                                     aria-required="true"/>
                        </td>
                    </tr>
                    <tr>
                        <th style="border-top: none"></th>
                        <td style="border-top: none">
                            <div class="custom-control custom-switch">
                                <input v-model="payment_method.active"
                                       type="checkbox"
                                       class="custom-control-input"
                                       id="active">
                                <label class="custom-control-label " for="active">
                                    Способ оплаты
                                    {{ payment_method.active ? 'активен' : 'деактивирован'}}
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Максимальная доля оплаты</th>
                        <td>
                            <label for="covers">Можно оплатить до <b>{{ payment_method.covers }}%</b> покупки</label>
                            <input v-model="payment_method.covers"
                                   type="range"
                                   min="1" max="100"
                                   class="custom-range"
                                   id="covers">
                        </td>
                    </tr>
                    <tr>
                        <th>Максимальная сумма за операцию (руб.)</th>
                        <td>
                            <v-input v-model="payment_method.max_limit"
                                     type="number"
                                     @change="$v.$touch()"
                                     :error="errMaxLimit"
                                     aria-required="true"/>
                        </td>
                    </tr>
                    <tr>
                        <th>Поддержка банковских карт</th>
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input v-model="payment_method.accept_prepaid"
                                       type="checkbox"
                                       class="custom-control-input"
                                       id="prepaidCard">
                                <label class="custom-control-label" for="prepaidCard">
                                    Предоплаченные карты
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input v-model="payment_method.accept_virtual"
                                       type="checkbox"
                                       class="custom-control-input"
                                       id="virtualCard">
                                <label class="custom-control-label" for="virtualCard">
                                    Виртуальные карты
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input v-model="payment_method.accept_real"
                                       type="checkbox"
                                       class="custom-control-input"
                                       id="plasticCard">
                                <label class="custom-control-label" for="plasticCard">
                                    Пластиковые карты
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input v-model="payment_method.accept_postpaid"
                                       type="checkbox"
                                       class="custom-control-input"
                                       id="postpaidCard">
                                <label class="custom-control-label" for="postpaidCard">
                                    Дебетовые и кредитные карты
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Запретить комбинирование с другими способами оплаты:</th>
                        <td>
                            <div v-for="(method, index) in payment_methods"
                                 v-if="method.id !== payment_method.id"
                                 class="custom-control custom-checkbox">
                                <input v-model="payment_method.excluded_payment_methods"
                                       :value="method.id"
                                       type="checkbox"
                                       class="custom-control-input"
                                       :id="'method' + index">
                                <label class="custom-control-label" :for="'method' + index">
                                  {{ method.name }}
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Запретить для офферов со статусами:</th>
                        <td>
                            <div v-for="(status, index) in offerStatuses" class="custom-control custom-checkbox">
                                <input v-model="payment_method.excluded_offer_statuses"
                                       :value="status.id"
                                       type="checkbox"
                                       class="custom-control-input"
                                       :id="'status' + index">
                                <label class="custom-control-label" :for="'status' + index">
                                    {{ status.name }}
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Запретить для регионов:</th>
                        <td>
                            <template v-if="payment_method.excluded_regions.length > 0">
                                <p>Выбранные регионы:</p>
                                <ul>
                                    <li v-for="region in payment_method.excluded_regions">
                                        {{ regions[region].name }}
                                    </li>
                                </ul>
                            </template>
                            <em v-else>Ограничения не установлены</em>
                            <b-button variant="btn-sm btn-link btn-block"
                                      v-b-toggle.regionsList>
                                Отобразить регионы
                            </b-button>
                            <b-collapse id="regionsList">
                                <div v-for="(region, index) in regions" class="custom-control custom-checkbox">
                                    <input v-model="payment_method.excluded_regions"
                                           :key="region.id"
                                           :value="region.id"
                                           type="checkbox"
                                           class="custom-control-input"
                                           :id="'region' + index">
                                    <label class="custom-control-label" :for="'region' + index">
                                        {{ region.name }}
                                    </label>
                                </div>
                                <b-button variant="btn btn-sm btn-link"
                                          v-b-toggle.regionsList>
                                    Скрыть список регионов
                                </b-button>
                            </b-collapse>
                        </td>
                    </tr>
                    <tr>
                        <th>Запретить, если используются службы доставки:</th>
                        <td>
                            <template v-if="payment_method.excluded_delivery_services.length > 0">
                                <p>Выбранные службы:</p>
                                <ul>
                                    <li v-for="service in payment_method.excluded_delivery_services">
                                        {{ delivery_services[service].name }}
                                    </li>
                                </ul>
                            </template>
                            <em v-else>Ограничения не установлены</em>
                            <b-button variant="btn-sm btn-link btn-block"
                                      v-b-toggle.deliveryServices>
                                Отобразить Логистических операторов
                            </b-button>
                            <b-collapse id="deliveryServices">
                                <div v-for="(service, index) in delivery_services" class="custom-control custom-checkbox">
                                    <input v-model="payment_method.excluded_delivery_services"
                                           :value="service.id" type="checkbox"
                                           class="custom-control-input"
                                           :id="'service' + index">
                                    <label class="custom-control-label" :for="'service' + index">
                                        {{ service.name }}
                                    </label>
                                </div>
                                <b-button variant="btn btn-sm btn-link"
                                          v-b-toggle.deliveryServices>
                                    Скрыть список Логистических операторов
                                </b-button>
                            </b-collapse>
                        </td>
                    </tr>
                    <tr>
                        <th>Запретить для пользователей (ID):</th>
                        <td>
                            <v-input v-model="payment_method.excluded_customers"
                                     placeholder="ID через запятую"/>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr align="right">
                        <th colspan="2">
                          <b-button @click="close()" variant="outline-danger">Отмена</b-button>
                          <button :disabled="$v.$invalid" class="btn btn-success" @click="save">Сохранить</button>
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import Helpers from "../../../../../scripts/helpers.js";
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../../mixins/modal.js';
    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import {validationMixin} from 'vuelidate';
    import {required, numeric, minValue} from 'vuelidate/lib/validators';
    import Services from "../../../../../scripts/services/services";

    export default {
        name: "method-edit-form",
        components: {
            VInput,
            modal,
        },
        mixins: [
            modalMixin,
            validationMixin,
        ],
        props: {
            modalName: String,
            payment_methods: Object,
            editingMethod: Object,
            regions: Object,
            delivery_services: Object,
            offerStatuses: Object
        },
        data () {
            return {
                payment_method: {
                    id: Number,
                    name: String,
                    accept_prepaid: Boolean,
                    accept_virtual: Boolean,
                    accept_real: Boolean,
                    accept_postpaid: Boolean,
                    covers: Number,
                    max_limit: Number,
                    excluded_payment_methods: [],
                    excluded_regions: [],
                    excluded_delivery_services: [],
                    excluded_offer_statuses: [],
                    excluded_customers: '',
                    active: Boolean
                }
            };
        },
        validations: {
            payment_method: {
                name: {required},
                max_limit: {required, numeric, minValue: minValue(1)},
            }
        },
        methods: {
            /**
             * Подготовить данные для чтения и редактирования в браузере
             */
            decodeData() {
                // Числовые и строковые значения //
                this.payment_method.id = this.editingMethod.id;
                this.payment_method.name = this.editingMethod.name;
                this.payment_method.active = Boolean(this.editingMethod.active);
                this.payment_method.accept_prepaid = Boolean(this.editingMethod.accept_prepaid);
                this.payment_method.accept_virtual = Boolean(this.editingMethod.accept_virtual);
                this.payment_method.accept_real = Boolean(this.editingMethod.accept_real);
                this.payment_method.accept_postpaid = Boolean(this.editingMethod.accept_postpaid);
                this.payment_method.covers = Helpers.roundValue(this.editingMethod.covers * 100);
                this.payment_method.max_limit = Helpers.roundValue(this.editingMethod.max_limit);
                // Ограничения //
                if (this.editingMethod.excluded_payment_methods) {
                    this.payment_method.excluded_payment_methods = JSON.parse(this.editingMethod.excluded_payment_methods);
                }
                if (this.editingMethod.excluded_regions) {
                    this.payment_method.excluded_regions = JSON.parse(this.editingMethod.excluded_regions);
                }
                if (this.editingMethod.excluded_delivery_services) {
                    this.payment_method.excluded_delivery_services = JSON.parse(this.editingMethod.excluded_delivery_services);
                }
                if (this.editingMethod.excluded_offer_statuses) {
                    this.payment_method.excluded_offer_statuses = JSON.parse(this.editingMethod.excluded_offer_statuses);
                }
                if (this.editingMethod.excluded_customers) {
                    this.payment_method.excluded_customers = JSON.parse(this.editingMethod.excluded_customers).join(', ');
                }
            },
            /**
             * Подготовить данные перед отправкой на сервер
             * @return Object
             */
            encodeData() {
                return {
                    name: this.payment_method.name,
                    accept_prepaid: Number(this.payment_method.accept_prepaid),
                    accept_virtual: Number(this.payment_method.accept_prepaid),
                    accept_real: Number(this.payment_method.accept_real),
                    accept_postpaid: Number(this.payment_method.accept_postpaid),
                    covers: this.payment_method.covers / 100,
                    max_limit: this.payment_method.max_limit,

                    excluded_payment_methods: this.payment_method.excluded_payment_methods.length > 0 ?
                        JSON.stringify(this.payment_method.excluded_payment_methods) : [],
                    excluded_regions: this.payment_method.excluded_regions.length > 0 ?
                        JSON.stringify(this.payment_method.excluded_regions) : [],
                    excluded_delivery_services: this.payment_method.excluded_delivery_services.length > 0 ?
                        JSON.stringify(this.payment_method.excluded_delivery_services) : [],
                    excluded_offer_statuses: this.payment_method.excluded_offer_statuses.length > 0 ?
                        JSON.stringify(this.payment_method.excluded_offer_statuses) : [],
                    excluded_customers: this.payment_method.excluded_customers ?
                        JSON.stringify(this.formatIds(this.payment_method.excluded_customers)) : [],
                    active: Number(this.payment_method.active)
                }
            },
            /**
             * Преобразует строку со списком ID в массив
             * @param ids
             * @returns {*[]|number[]}
             */
            formatIds(ids) {
                if (!ids) {
                    return [];
                }

                return ids
                    .split(',')
                    .map(id => { return parseInt(id); })
                    .filter(id => { return id > 0 });
            },
            save: async function() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                this.encodeData();
                await this.$nextTick();

                Services.showLoader();
                Services.net().put(this.getRoute('settings.paymentMethods.edit', {id: this.payment_method.id}),
                    this.encodeData()
                )
                    .then((data) => {
                        this.$emit('saved', data.payment_method);
                        Services.msg("Параметры способа оплаты успешно сохранены");
                    }, () => {
                        Services.msg("Не удалось сохранить изменения",'danger');
                    }).finally(() => {
                    this.$v.$reset();
                    this.$bvModal.hide("modal-paymentMethod-edit");
                    Services.hideLoader();
                })
            },
        },
        computed: {
            errName() {
                if (this.$v.payment_method.name.$dirty) {
                    if (!this.$v.payment_method.name.required) {
                        return "Обязательное поле!";
                    }
                }
            },
            errMaxLimit() {
                if (this.$v.payment_method.max_limit.$invalid) {
                    return "Укажите сумму не менее 1 рубля";
                }
            }
        },
        watch: {
            'payment_method.excluded_customers': {
                handler(val, oldVal) {
                    if (val && val !== oldVal) {
                        let format = this.formatIds(this.payment_method.excluded_customers).join(', ');
                        let separator = val.slice(-1) === ','
                            ? ','
                            : (val.slice(-2) === ', ' ? ', ' : '');
                        this.payment_method.excluded_customers = format + separator;
                    }
                },
            },
        },
    }
</script>

<style scoped>

</style>