<template>
    <b-modal id="offer-stocks-edit-modal" hide-footer ref="modal" size="lg" @show="prepare()">
        <div slot="modal-title">
            <strong>Редактировать остатки оффера</strong>
        </div>
        <div>
            <table class="table table-bordered">
                <tbody>
                <tr>
                    <td class="prop-name">
                        Склад
                        <fa-icon icon="question-circle" v-b-popover.hover="stocksTooltip"></fa-icon>
                    </td>
                    <td>
                        <div class="d-flex">
                            <v-select
                                    v-model="selectedStoreId"
                                    class="w-100"
                                    :options="availableStores"
                                    :disabled="availableStores.length < 1 || loading"
                            ></v-select>
                            <button @click="addField()"
                                    class="btn btn-outline-info float-right h-100 ml-3"
                                    :disabled="!selectedStoreId || availableStores.length < 1">
                                <fa-icon icon="plus"></fa-icon>
                            </button>
                        </div>
                        <hr v-if="newStocks.length > 0">
                        <div v-for="(stock, index) in $v.newStocks.$each.$iter" class="d-flex flex-row mb-2">
                            <div class="col-5 mt-2">
                                {{ stock.$model.name }}
                            </div>
                            <div class="input-group col-5">
                                <v-input v-model="stock.qty.$model"
                                         type="number" min="0"
                                         class="w-75 mb-2"
                                         :error="errorQtyField(index)"
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
                </tbody>
            </table>
        </div>
        <button @click="save()" class="btn btn-success mt-3" :disabled="!$v.$anyDirty || $v.$invalid">Сохранить</button>
    </b-modal>
</template>


<script>
    import Services from "../../../../../scripts/services/services";
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDate from '../../../../components/controls/VDate/VDate.vue';
    import { validationMixin } from 'vuelidate';
    import { required, integer, minValue } from 'vuelidate/lib/validators';

    export default {
        components: {
            VSelect,
            VInput,
            VDate
        },
        mixins: [validationMixin],
        props: {
            offer: Object,
            stocks: Array,
        },
        data() {
            return {
                selectedStoreId: null,
                newStocks: [],
                deletedStocks: [],
                currentStocks: [],
                loading: false,
            }
        },
        validations() {
            return {
                newStocks: {
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
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('offers.edit', {id: this.offer.id}), {}, {
                    'product_id': this.offer.product_id,
                    'stocks': this.getFilteredData(),
                }, {}, true).then((data) => {
                    Services.msg("Информация сохранена");
                    this.$bvModal.hide('offer-stocks-edit-modal');
                    this.$emit('onSave');
                }, () => {
                    Services.msg("Не удалось сохранить информацию", 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            errorQtyField(index) {
                if (this.$v.newStocks.$each.$iter[index].$invalid) {
                    return 'Введите неотрицательное целое число';
                }
            },
            addField() {
                this.deletedStocks = this.deletedStocks.filter((stock) => {
                    return stock.store_id !== this.selectedStoreId;
                });
                this.newStocks.push({
                    store_id: this.selectedStoreId,
                    name: this.currentStocks[this.selectedStoreId].name,
                    qty: this.currentStocks[this.selectedStoreId].qty
                });
                this.$v.newStocks.$touch();
                this.selectedStoreId = null;
            },
            deleteField(index) {
                this.deletedStocks.push({
                    store_id: this.newStocks[index].store_id,
                    qty: 0
                });
                this.newStocks.splice(index, 1);
                this.$v.newStocks.$touch();
            },
            getFilteredData() {
                let editedStocks = this.newStocks.filter((stock) => {
                    return stock.qty !== this.currentStocks[stock.store_id].qty;
                }).map((stock) => {
                    return {
                        store_id: stock.store_id,
                        qty: parseInt(stock.qty),
                    }
                });
                return editedStocks.concat(this.deletedStocks)
            },
            prepare() {
                this.selectedStoreId = null;
                this.deletedStocks = [];
                this.newStocks = this.stocks.map((item) => {
                    return {
                        'store_id': item.store_id,
                        'name': item.name,
                        'qty': item.qty,
                    };
                });
                this.$v.$reset();

                Services.showLoader();
                this.loading = true;
                Services.net().get(this.getRoute('offers.storeAndQty'), {
                    'merchant_id': this.offer.merchant_id,
                    'offer_id': this.offer.id
                }, {}, true).then((data) => {
                    this.currentStocks = data;
                    Services.hideLoader();
                }, () => {
                    Services.hideLoader();
                    Services.msg("Не удалось загрузить необходимую информацию", 'danger');
                }).finally(() => {
                    this.loading = false;
                });
            }
        },
        computed: {
            availableStores() {
                let displayedStores = (this.newStocks || []).map((value) => {
                    return value.store_id;
                });
                return Object.values(this.currentStocks).filter((item) => {
                    return !displayedStores.includes(item.id);
                }).map((item) => {
                    return {
                        value: item.id,
                        text: item.name,
                    };
                });
            },
            stocksTooltip() {
                return 'Добавьте нужный склад и введите количество имеющегося на нём товара'
            }
        },
    }
</script>

<style scoped>
    .prop-name {
        width: 20%;
    }
</style>