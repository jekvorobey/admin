<template>
    <layout-main>
        <div class="row mb-3">
            <div class="col-6" style="text-align: left">
                <button class="btn btn-success mr-1"
                    @click="onShowModalCreate()">
                    Добавить популярный товар
                </button>
                <button class="btn btn-danger"
                        :disabled="massEmpty(massPopularProductType)"
                        @click="onShowModalDelete()">
                    Удалить популярный товар
                </button>
            </div>
            <div class="col-6" style="text-align: right">
                <button v-if="itemsOrder.length > 0"
                        class="btn btn-dark"
                        @click="reorderItems">
                    <template v-if="!isReordering">
                        <fa-icon icon="save"/> Сохранить порядок
                    </template>
                    <template v-else>
                            <span class="spinner-border"
                                  style="width: 1.5rem; height: 1.5rem;"
                                  role="status"
                                  aria-hidden="true">
                            </span>
                        Порядок сохраняется...
                    </template>
                </button>
                <button v-else
                        class="btn btn-light"
                        disabled>
                    <fa-icon icon="check"/> Порядок сохранён
                </button>
            </div>
        </div>

        <table v-if="popularProducts.length > 0" class="table table-striped">
            <thead>
            <tr>
                <td></td>
                <th>Название</th>
            </tr>
            </thead>
                <draggable v-model="popularProducts"
                           v-bind="dragOptions"
                           style="cursor: move"
                           tag="tbody"
                >
                    <tr v-for="popularProduct in popularProducts">
                        <td class="d-flex flex-column align-items-center">
                            <input type="checkbox"
                                   :checked="massHas({type: massPopularProductType, id: popularProduct.id})"
                                   @change="e => massCheckbox(e, massPopularProductType, popularProduct.id)"/>
                        </td>
                        <td><a :href="getRoute('products.detail', {id: popularProduct.product_id})">
                            {{ popularProduct.name }}
                        </a></td>
                    </tr>
                </draggable>
        </table>
        <div v-else>Популярные товары отсутствуют</div>

        <b-modal id="modal-create" title="Создание популярного товара" hide-footer>
            <div class="card">
                <div class="card-body">
                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="popular-product">ID товара (-ов)</label>
                        </b-col>
                        <b-col cols="9">
                            <v-input id="popular-product"
                                       v-model="newPopularProduct.product_id"
                                       :error="errProductId"
                            >
                            </v-input>
                        </b-col>
                    </b-row>
                    <button class="btn btn-success"
                            @click="savePopularProduct">
                        Сохранить
                    </button>
                    <button class="btn btn-outline-danger"
                            @click="onCloseModalCreate">
                        Отмена
                    </button>
                </div>
            </div>
        </b-modal>
        <b-modal id="modal-delete" title="Удаление популярного товара" hide-footer>
            <div class="card">
                <div class="card-body">
                    <div>
                        Вы уверены, что хотите удалить следующие популярные товары: <br>
                        <p v-html="deletedProductNames">{{deletedProductNames}}</p>
                    </div>
                    <button class="btn btn-outline-danger"
                            @click="deletePopularProducts">
                        Удалить
                    </button>
                    <button class="btn btn-success"
                            @click="onCloseModalDelete">
                        Отмена
                    </button>
                </div>
            </div>
        </b-modal>
    </layout-main>
</template>

<script>
    import draggable from 'vuedraggable';
    import VInput from '../../../components/controls/VInput/VInput.vue';

    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';
    import massSelectionMixin from '../../../mixins/mass-selection';

    import Services from "../../../../scripts/services/services";

    export default {
        components: {
            draggable,
            VInput,
        },
        mixins: [
            validationMixin,
            massSelectionMixin,
        ],
        props: {
            iPopularProducts: Array,
            dragOptions: {
                animation: 200,
                sort: true,
            },
        },
        data() {
            return {
                popularProducts: this.iPopularProducts,
                newPopularProduct: {
                    product_id: null,
                },
                massActionType: null,
                massStatus: null,
                massPopularProductType: 'popularProducts',
                deletedProductNames: '',
                itemsOrder: [],
                isReordering: false,
                keyCreateDelete: false,
            };
        },
        validations: {
            newPopularProduct: {
                product_id: {required},
            },
        },
        methods: {
            onShowModalCreate() {
                this.$bvModal.show('modal-create');
            },
            onCloseModalCreate() {
                this.newPopularProduct = {
                    product_id: null,
                };
                this.$v.$reset();
                this.$bvModal.hide('modal-create');
            },
            /**
             * Добавить новый популярный товар
             */
            savePopularProduct() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().post(
                    this.getRoute('popularProducts.create'),
                    {},
                    this.newPopularProduct
                ).then(
                    data => {
                        for (let index in data) {
                            let product = data[index];
                            if (product.isAdded) {
                                this.popularProducts.push({
                                    id: product.popular_product.id,
                                    product_id: product.popular_product.product_id,
                                    name: product.popular_product.name,
                                    weight: product.popular_product.weight,
                                });
                                this.keyCreateDelete = true;
                                Services.msg("Новый популярный продукт добавлен");
                            } else {
                                Services.msg(product.message, 'danger');
                            }
                        }
                    },
                    () => {
                        Services.msg("Не удалось добавить новый популярный товар",'danger');
                    }
                ).finally(() => {
                    Services.hideLoader();
                });
                this.onCloseModalCreate();
            },
            onShowModalDelete() {
                this.massPopularProductNames();
                this.$bvModal.show('modal-delete');
            },
            onCloseModalDelete() {
                this.deletedProductNames = '';
                this.$bvModal.hide('modal-delete');
            },
            massPopularProductNames() {
                this.deletedProductNames = this.popularProducts.filter((popularProduct) => {
                    return this.massAll(this.massPopularProductType).includes(popularProduct.id);
                }).map((popularProduct) => {
                    return popularProduct.name;
                }).join('<br>');
            },
            /**
             * Удалить популярные товары
             */
            deletePopularProducts() {
                Services.showLoader();
                Services.net().delete(
                    this.getRoute('popularProducts.delete'),
                    {
                        ids: this.massAll(this.massPopularProductType),
                    }
                ).then(
                    () => {
                        this.popularProducts = this.popularProducts.filter((popularProduct) => {
                            return !this.massAll(this.massPopularProductType).includes(popularProduct.id);
                        });
                        this.massClear(this.massPopularProductType);
                        this.keyCreateDelete = true;
                        Services.msg("Популярные товары успешно удалены");
                    },
                    () => {
                        Services.msg("Не удалось удалить популярные товары",'danger');
                    }
                ).finally(() => {
                    Services.hideLoader();
                });
                this.onCloseModalDelete();
            },
            // Изменить порядок популярных товаров и сохранить на сервере //
            reorderItems() {
                this.isReordering = true;
                Services.net().put(
                    this.getRoute('popularProducts.reorder'),
                    {},
                    {
                        items: this.itemsOrder
                    }
                ).then(
                    () => {
                        Services.msg("Новый порядок сохранён");
                        this.itemsOrder = [];
                    },
                    () => {
                        Services.msg("Не удалось сохранить изменения",'danger');
                    }
                ).finally(() => {
                    this.isReordering = false;
                })
            },
        },
        computed: {
            errProductId() {
                if (this.$v.newPopularProduct.product_id.$dirty) {
                    if (!this.$v.newPopularProduct.product_id.required) {
                        return "Обязательное поле!";
                    }
                }
            },
        },
        watch: {
            // Пересортировка популярных товаров //
            'popularProducts': {
                handler() {
                    if (!this.keyCreateDelete) {
                        this.itemsOrder = Object.values(this.popularProducts)
                            .reverse().map((item, index) => ({
                                    id: item.id,
                                    weight: index + 1,
                                })
                            );
                    } else {
                        this.keyCreateDelete = false;
                    }
                }
            },
        },
    }
</script>

<style scoped>

</style>