<template>
    <b-modal id="offer-create-modal" hide-footer ref="modal" size="lg" @hidden="resetFields()">
        <div slot="modal-title">
            <strong v-if="mode === 'create'">Добавить новый оффер</strong>
            <strong v-else-if="mode === 'edit'">Редактировать оффер</strong>
        </div>
        <div>
            <table class="table table-bordered">
                <tbody>
                <tr v-if="!offer">
                    <td class="prop-name">
                        ID товара
                    </td>
                    <td>
                        <v-input v-model="$v.newOffer.product_id.$model"
                                 type="number" min="1"
                                 class="mb-2"
                                 @change="resetValidation()"
                                 :error="errorProductIdField()"
                        />
                    </td>
                <tr v-if="!offer">
                    <td class="prop-name">
                        Мерчант
                    </td>
                    <td>
                        <v-select
                                v-model="$v.newOffer.merchant_id.$model"
                                :options="merchants"
                                :error="errorSelectField('merchant_id')"
                        ></v-select>
                    </td>
                </tr>
                <tr>
                    <td class="prop-name">
                        Цена
                    </td>
                    <td>
                        <div class="input-group">
                            <v-input v-model="$v.newOffer.price.$model"
                                     type="number" min="0"
                                     class="mr-2 mb-2"
                                     :error="errorInputField('price')"
                            />
                            <div class="input-group-append">
                                <p class="mt-2">руб.</p>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="prop-name">
                        Остаток
                        <fa-icon icon="question-circle" v-b-popover.hover="stocksTooltip"></fa-icon>
                    </td>
                    <td>
                        <div class="d-flex">
                            <v-select
                                    v-model="selectedStoreId"
                                    :options="availableStores"
                                    class="w-100"
                                    :disabled="availableStores.length < 1"
                            ></v-select>
                            <button @click="addField()"
                                    class="btn btn-outline-info float-right h-100 ml-3"
                                    :disabled="!selectedStoreId || availableStores.length < 1">
                                <fa-icon icon="plus"></fa-icon>
                            </button>
                        </div>
                        <hr v-if="$v.newOffer.stocks.$model.length > 0">
                        <div v-for="(v, index) in $v.newOffer.stocks.$each.$iter" class="d-flex flex-row mb-2">
                            <div class="col-5 mt-2">
                                {{ stocks[v.$model.store_id].name }}
                            </div>
                            <div class="input-group col-5">
                                    <v-input v-model="v.qty.$model"
                                             type="number" min="0"
                                             class="w-75 mb-2"
                                             :error="errorInputField('stocks', index)"
                                    />
                                <div class="input-group-append">
                                    <p class="mt-2 ml-2">шт.</p>
                                </div>
                            </div>
                            <button @click="deleteField(index)"
                                    class="btn btn-outline-secondary float-right h-100 ml-3 mb-2"
                                    type="button">
                                <fa-icon icon="trash-alt"></fa-icon>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="prop-name">
                        Статус
                    </td>
                    <td>
                        <v-select
                                v-model="$v.newOffer.status.$model"
                                :options="modalStatuses"
                                :disabled="countedQty < 1"
                        ></v-select>
                    </td>
                </tr>
                <tr v-if="displayDateSelect">
                    <td>
                        Дата начала продажи
                    </td>
                    <td>
                        <v-date
                                :min="tomorrow"
                                v-model="$v.sale_at.$model"
                                :error="errorDateField()"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <button @click="save()" class="btn btn-success mt-3" :disabled="!$v.$anyDirty">Сохранить</button>
    </b-modal>
</template>


<script>
    import Services from "../../../../../scripts/services/services";
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDate from '../../../../components/controls/VDate/VDate.vue';
    import { validationMixin } from 'vuelidate';
    import { required, requiredIf, integer, decimal, minValue, helpers } from 'vuelidate/lib/validators';
    import * as moment from 'moment';

    const futureDate = (date) => {
        let today = moment().format('YYYY-MM-DD');
        return !helpers.req(date) || moment(date).isAfter(today);
    };

    export default {
        components: {
            VSelect,
            VInput,
            VDate
        },
        mixins: [validationMixin],
        props: {
            offer: Object,
            merchants: Object
        },
        data() {
            return {
                mode: 'create',
                oldOffer: null,
                newOffer: {
                    product_id: '',
                    merchant_id: '',
                    price: '',
                    stocks: [],
                    status: '',
                },
                sale_at: null,
                selectedStoreId: null,
                stocks: {},
                loading: false,
                tomorrow: moment().add(1,'days').format('YYYY-MM-DD'),
                validationResult: {
                    isOk: true,
                    message: ''
                },
            }
        },
        validations() {
            return {
                newOffer: {
                    product_id: {
                        required,
                        integer,
                        minValue: minValue(1)
                    },
                    merchant_id: {
                        required,
                        integer
                    },
                    price: {
                        required,
                        decimal,
                        minValue: minValue(0)
                    },
                    stocks: {
                        $each: {
                            store_id: {
                                required,
                                integer
                            },
                            qty: {
                                required,
                                integer,
                                minValue: minValue(0)
                            }
                        }
                    },
                    status: {
                        integer,
                    },
                },
                sale_at: {
                    futureDate,
                    required: requiredIf(function () {
                        return this.newOffer.status
                            && this.offerCountdownSaleStatuses.includes(this.newOffer.status);
                    }),
                },
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                let offer = this.getFilteredData();
                if (this.mode === 'create') {
                    this.createOffer(offer);
                } else if (this.mode === 'edit') {
                    this.editOffer(offer);
                }
            },
            errorSelectField(propertyName) {
                if (this.$v.newOffer[propertyName].$dirty
                    && this.$v.newOffer[propertyName].$invalid) {
                    return "Выберите один из вариантов";
                }
            },
            errorInputField(propertyName, index = null) {
                if (index) {
                    if (this.$v.newOffer[propertyName].$each.$iter[index].qty.$dirty
                        && this.$v.newOffer[propertyName].$each.$iter[index].qty.$invalid) {
                        return "Введите неотрицательное целое число";
                    }
                } else {
                    if (this.$v.newOffer[propertyName].$dirty
                        && this.$v.newOffer[propertyName].$invalid) {
                        if (propertyName === 'price') return 'Введите неотрицательное число';
                        return "Введите неотрицательное целое число";
                    }
                }
            },
            errorProductIdField() {
                if (this.$v.newOffer['product_id'].$dirty
                    && this.$v.newOffer['product_id'].$invalid) {
                    return "ID товара должен быть положительным целым числом";
                }
                if (!this.validationResult.isOk) return this.validationResult.message;
            },
            errorDateField() {
                if (this.$v.sale_at.$dirty
                    && this.$v.sale_at.$invalid) {
                    return "Укажите корректную дату";
                }
            },
            addField() {
                this.newOffer.stocks.push({
                    store_id: this.selectedStoreId,
                    qty: this.stocks[this.selectedStoreId].qty
                });
                this.$v.newOffer.stocks.$touch();
                this.selectedStoreId = null;
            },
            deleteField(index) {
                this.newOffer.stocks.splice(index, 1);
                this.$v.newOffer.stocks.$touch();
            },
            resetFields() {
                // this.mode = 'create';
                this.newOffer = {
                    product_id: '',
                    merchant_id: '',
                    price: '',
                    stocks: [],
                    status: ''
                };
                this.selectedStoreId = null;
                this.stocks = [];
                this.tomorrow = moment().add(1,'days').format('YYYY-MM-DD');
                this.sale_at = null;
                this.validationResult = {
                    isOk: true,
                    message: ''
                };
                this.$v.$reset();
            },
            getFilteredStocks() {
                let shownStockIds = [];
                let editedStocks = this.newOffer.stocks.filter((value) => {
                    shownStockIds.push(value.store_id);
                    return parseInt(value.qty) !== this.stocks[value.store_id].qty;
                }).map((value) => {
                    return {
                        store_id: parseInt(value.store_id),
                        qty: parseInt(value.qty)
                    }
                });

                let emptiedStocks = Object.values(this.stocks).filter((value) => {
                    return value.qty > 0 && !shownStockIds.includes(value.id);
                }).map((value) => {
                    return {
                        store_id: parseInt(value.id),
                        qty: 0
                    }
                });

                return editedStocks.concat(emptiedStocks);
            },
            getSelectedDate() {
                let date = this.sale_at;
                if (!this.offerCountdownSaleStatuses.includes(this.newOffer.status)) {
                    date = null;
                }
                return date;
            },
            getFilteredData() {
                let offer = {
                    product_id: parseInt(this.newOffer.product_id),
                    stocks: this.getFilteredStocks()
                };
                let price = parseFloat(this.newOffer.price);
                let status = parseInt(this.newOffer.status) || null;
                let saleAt = this.getSelectedDate();

                if (this.mode === 'create') {
                    offer['merchant_id'] = parseInt(this.newOffer.merchant_id);
                    offer['price'] = price;
                    offer['sale_status'] = status;
                    offer['sale_at'] = saleAt;
                    return offer;
                }

                offer['offer_id'] = this.offer.id;
                if (price !== parseFloat(this.oldOffer.price)) offer['price'] = price;
                if (status && (status !== this.oldOffer.status)) offer['sale_status'] = status;
                if (saleAt) offer['sale_at'] = saleAt;
                return offer;
            },
            validateOffer(product_id, merchant_id) {
                return Services.net().get(this.getRoute('offers.validate'), {
                    product_id: product_id,
                    merchant_id: merchant_id
                }, {}, true);
            },
            resetValidation() {
                if (!this.validationResult.isOk) {
                    this.validationResult = {
                        isOk: true,
                        message: ''
                    }
                }
            },
            createOffer(offer) {
                Services.showLoader();
                this.validateOffer(offer.product_id, offer.merchant_id).then((data) => {
                    this.validationResult = data;
                    if (!data['isOk']) {
                        Services.hideLoader();
                        return;
                    }

                    Services.net().post(this.getRoute('offers.create'), {}, offer, {}, true).then((data) => {
                        Services.msg("Оффер сохранён!");
                        this.$bvModal.hide('offer-create-modal');
                        setTimeout(window.location.reload.bind(window.location), 1000);
                    }, () => {
                        Services.msg("Не удалось сохранить оффер", 'danger');
                    }).finally(() => {
                        Services.hideLoader();
                    });
                }, () => {
                    Services.hideLoader();
                    Services.msg("Не удалось загрузить необходимую информацию", 'danger');
                });
            },
            editOffer(offer) {
                Services.showLoader();
                Services.net().put(this.getRoute('offers.edit'), {}, offer, {}, true).then((data) => {
                    Services.msg("Оффер отредактирован!");
                    this.$bvModal.hide('offer-create-modal');
                    setTimeout(window.location.reload.bind(window.location), 1000);
                }, () => {
                    Services.msg("Не удалось отредактировать оффер", 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        computed: {
            availableStores() {
                let chosen = (this.newOffer.stocks || []).map((value) => {
                    return value.store_id;
                });
                return Object.values(this.stocks).filter((value) => {
                    return !chosen.includes(value.id);
                }).map((value) => {
                    return {value: parseInt(value.id), text: value.name};
                });
            },
            countedQty() {
                if (this.loading) return null;

                let total = this.newOffer.stocks.reduce((total, stock) => {
                    return total + (parseInt(stock.qty) || 0);
                }, 0);
                if (!total) {
                    this.newOffer.status = '';
                }
                return total;
            },
            modalStatuses() {
                let saleStatuses;
                switch (this.mode) {
                    case 'create':
                        saleStatuses = this.offerCreateSaleStatuses;
                        break;
                    case 'edit':
                        saleStatuses = this.offerEditSaleStatuses;
                        break;
                }
                return Object.values(saleStatuses).map((val) => {
                    return {value: parseInt(val.id), text: val.name};
                });
            },
            displayDateSelect() {
                this.$v.sale_at.$reset();
                return this.newOffer.status
                    && this.offerCountdownSaleStatuses.includes(this.newOffer.status);
            },
            stocksTooltip() {
                return 'Добавьте нужный склад и введите количество имеющегося на нём товара'
            }
        },
        watch: {
            'offer': {
                handler(value) {
                    this.loading = true;
                    this.mode = value ? 'edit' : 'create';
                    this.oldOffer = value ? {
                        price: value.price,
                        status: value.status,
                        sale_at: value.sale_at
                    } : null;
                    this.newOffer = value ? value : {
                        product_id: '',
                        merchant_id: '',
                        price: '',
                        stocks: [],
                        status: ''
                    };
                    this.sale_at = value? value.sale_at : null;
                }
            },
            'newOffer.merchant_id': {
                handler(value) {
                    if (!value) {
                        return;
                    }

                    let params = { merchant_id: value };
                    if (this.offer) {
                        params['offer_id'] = this.offer.id;
                    }

                    Services.showLoader();
                    Services.net().get(this.getRoute('offers.storeAndQty'), params, {}, true).then((data) => {
                        this.stocks = data;

                        let filledStores = Object.values(data).filter((value) => {
                            return value.qty > 0;
                        }).map((value) => {
                            return {
                                store_id: value.id,
                                qty: value.qty
                            }
                        });
                        this.newOffer.stocks = filledStores;
                        this.$v.newOffer.stocks.$reset();
                        Services.hideLoader();
                    }, () => {
                        Services.hideLoader();
                        Services.msg("Не удалось загрузить необходимую информацию", 'danger');
                    }).finally(() => {
                        this.loading = false;
                    });
                },
            },
        }
    }
</script>

<style scoped>
    .prop-name {
        width: 25%;
    }
</style>