<template>
    <table class="table">
        <thead>
        <tr class="table-secondary">
            <th colspan="1">
                <div class="custom-control custom-switch d-inline-block">
                    <input type="checkbox" class="custom-control-input" id="active" v-model="filter.active">
                    <label v-if="filter.active" class="custom-control-label" for="active">
                        Отображаются активные товары
                    </label>
                    <label v-else="" class="custom-control-label" for="active">
                        Отображаются архивированные товары
                    </label>
                </div>
            </th>
            <th colspan="3" style="text-align: right">
                <a :href="getRoute('customers.detail.promoProduct.export', {id: this.id})" class="btn btn-info btn-bg">
                    Экспорт всех товаров <fa-icon icon="file-excel"/>
                </a>
            </th>
        </tr>
            <tr>
                <th width="500px">
                    Товар
                </th>
                <th>Описание</th>
                <th>Файлы</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(promoProduct, index) in promoProducts" v-if="filter.active === !!promoProduct.active">
                <td>
                    <div>
                        <a :href="getRoute('products.detail', {id: promoProduct.product_id})">
                            {{ promoProduct.product_name }}
                        </a>
                    </div>
                    <div v-if="promoProduct.brand">Бренд: {{ promoProduct.brand.name }}</div>
                    <div v-if="promoProduct.category">Категория: {{ promoProduct.category.name }}</div>
                    <div v-if="promoProduct.price">Цена: {{ promoProduct.price }}</div>
                    <div>Дата создания: {{ promoProduct.created_at }}</div>
                    <div v-if="!promoProduct.active">Дата архивации: {{ promoProduct.updated_at }}</div>
                </td>
                <td>
                    <textarea class="form-control"
                              :class="[
                                  {'is-invalid': promoProducts[index].description === ''},
                                  {'is-invalid': promoProducts[index].description.length > 1000}
                                  ]"
                              v-model="promoProduct.description"
                              rows="6"
                              v-if="!!promoProduct.active"
                              placeholder="Обязательное поле"
                              aria-required="true"
                              maxlength="1000"/>
                    <span v-if="!promoProduct.active">{{ promoProduct.description }}</span>
                </td>
                <td>
                    <div v-for="(file, i) in promoProduct.files" class="mb-1">
                        <img :src="media.file(file)" style="max-width: 150px;"/>
                        <v-delete-button btn-class="btn-danger btn-sm" @delete="$delete(promoProduct.files, i)" v-if="!!promoProduct.active"/>
                    </div>

                    <file-input @uploaded="(data) => $set(promoProduct.files, promoProduct.files.length, data.id)" class="mb-3" v-if="!!promoProduct.active"></file-input>
                </td>
                <td>
                    <template v-if="!!promoProduct.active">
                        <button class="btn btn-success btn-sm"
                                @click="savePromoProduct(promoProduct, 'update')"
                                :disabled="promoProducts[index].description === ''
                                ||promoProducts[index].description.length > 1000">
                            <fa-icon icon="save"/>
                        </button>
                        <button class="btn btn-danger btn-sm"
                                @click="archivePromoProduct(promoProduct)"
                                :disabled="promoProducts[index].description === ''
                                ||promoProducts[index].description.length > 1000">
                            <fa-icon icon="file-archive"/>
                        </button>
                    </template>
                </td>
            </tr>
            <tr v-if="filter.active">
                <td>
                    <v-input v-model="newPromoProduct.product_id"
                             type="number"
                             :error="productIdError"
                             placeholder="ID Товара"
                             aria-required="true"/>
                </td>
                <td>
                    <textarea ref="newDescription"
                              class="form-control"
                              :class="newDescriptionError"
                              v-model="newPromoProduct.description"
                              placeholder="Описание товара"
                              aria-required="true"
                              maxlength="1000"/>
                </td>
                <td>
                    <div v-for="(file, i) in newPromoProduct.files" class="mb-1">
                        <img :src="media.file(file)" style="max-width: 150px;"/>
                        <v-delete-button btn-class="btn-danger btn-sm" @delete="$delete(newPromoProduct.files, i)"/>
                    </div>

                    <file-input @uploaded="(data) => $set(newPromoProduct.files, newPromoProduct.files.length, data.id)" class="mb-3"></file-input>
                </td>
                <td>
                    <button class="btn btn-success btn-sm"
                            :disabled="this.$v.$invalid"
                            @click="savePromoProduct(newPromoProduct, 'create')">
                        <fa-icon icon="plus"/>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import VInput from "../../../../components/controls/VInput/VInput.vue";
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import FileInput from '../../../../components/controls/FileInput/FileInput.vue';

import {validationMixin} from 'vuelidate';
import {required, integer, maxLength} from 'vuelidate/lib/validators';

export default {
    name: 'tab-promo-product',
    components: {VInput, FileInput, VDeleteButton},
    mixins: [validationMixin],
    props: ['id'],
    data() {
        return {
            promoProducts: [],
            newPromoProduct: {
                product_id: '',
                description: '',
                active: 1,
                files: [],
            },
            filter: {
                active: true,
            }
        }
    },
    validations: {
        newPromoProduct: {
            product_id: {required, integer},
            description: {required, maxLength: maxLength(1000)},
        },
    },
    methods: {
        /**
         * Сохранить товар: создать новый или обновить старый
         * @param promoProduct - сохраняемый товар
         * @param mode - обновить старый или создать новый
         * @example mode = update
         * @example mode = create
         */
        savePromoProduct(promoProduct, mode) {
            if (mode === 'create') {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
            }

            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.promoProduct.save', {id: this.id}), promoProduct).then(data => {
                this.promoProducts = data.promoProducts;
                this.newPromoProduct.product_id = '';
                this.newPromoProduct.description = '';
                this.newPromoProduct.files = [];
            }).finally(() => {
                Services.hideLoader();
                this.$v.$reset();
            })
        },
        archivePromoProduct(promoProduct) {
            promoProduct.active = 0;
            this.savePromoProduct(promoProduct, 'update');
        },
    },
    computed: {
        /**
         * Ошибка в ID добавляемого товара
         * @returns {string}
         */
        productIdError() {
            if (this.$v.newPromoProduct.product_id.$dirty) {
                if (!this.$v.newPromoProduct.product_id.required) {
                    return "Введите ID товара!";
                }
                if (!this.$v.newPromoProduct.product_id.integer) {
                    return "Введите ID товара - целое число!";
                }
            }
        },
        /**
         * Ошибка в описании нового (добавляемого) товара
         * @returns {string}
         */
        newDescriptionError() {
            if (this.$v.newPromoProduct.description.$dirty) {
                if (!this.$v.newPromoProduct.description.required) {
                    this.$refs.newDescription.focus();
                    this.$refs.newDescription.placeholder="Введите описание товара";
                    return 'is-invalid'
                }
                if (!this.$v.newPromoProduct.description.maxLength) {
                    this.$refs.newDescription.focus();
                    return 'is-invalid'
                }
            }
        },
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.promoProduct', {id: this.id})).then(data => {
            this.promoProducts = data.promoProducts;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>