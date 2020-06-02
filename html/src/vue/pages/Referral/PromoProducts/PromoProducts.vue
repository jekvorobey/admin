<template>
    <layout-main>
        <table class="table">
            <thead>
            <tr class="table-secondary">
                <th colspan="5" style="text-align: right">
                    <button class="btn btn-info btn-bg"
                            @click="openAttachModal"
                            :disabled="selectedItems.length === 0">
                        Назначить выбранные <fa-icon icon="check-circle"/>
                    </button>
                </th>
            </tr>
            <tr>
                <th><!--space--></th>
                <th>Товар</th>
                <th>Описание</th>
                <th>Файлы</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(promoProduct, index) in promoProducts">
                <td width="25px">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox"
                               class="custom-control-input"
                               v-model="selectedItems"
                               :value="{
                                   id: promoProduct.id,
                                   brand: promoProduct.brand.id,
                                   category: promoProduct.category.id
                               }"
                               :id="'checkbox' + index"/>
                        <label class="custom-control-label bigger"
                               :for="'checkbox' + index"/>
                    </div>
                </td>
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
                    <div v-if="promoProduct.segments">
                        <small class="text-muted"><fa-icon icon="link"/> Назначен</small>
                    </div>
                    <div v-else>
                        <small class="text-muted"><fa-icon icon="unlink"/> Не назначен</small>
                    </div>

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
                    <template >
                        <button class="btn btn-success btn-sm"
                                @click="savePromoProduct(promoProduct, 'update')"
                                :disabled="promoProducts[index].description === ''
                                ||promoProducts[index].description.length > 1000">
                            <fa-icon icon="save"/>
                        </button>
                        <button class="btn btn-danger btn-sm"
                                @click="deletePromoProduct(promoProduct.id, index)">
                            <fa-icon icon="trash-alt"/>
                        </button>
                    </template>
                </td>
            </tr>
            <tr>
                <td><!-- Отступ --></td>
                <td>
                    <v-input
                            type="number"
                            v-model="newPromoProduct.product_id"
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

        <ModalAttach :promoProducts.sync="promoProducts"
                     :selectedPromos.sync="selectedItems"
                     :activities="this.activities"
                     :ref_levels="this.ref_levels"/>

    </layout-main>
</template>

<script>

import Services from '../../../../scripts/services/services.js';
import ModalAttach from './components/modal-products-attach.vue';
import VInput from "../../../components/controls/VInput/VInput.vue";
import VDeleteButton from "../../../components/controls/VDeleteButton/VDeleteButton.vue";
import FileInput from "../../../components/controls/FileInput/FileInput.vue";

import {validationMixin} from 'vuelidate';
import {required, integer, maxLength} from 'vuelidate/lib/validators';

export default {
    components: {
        ModalAttach,
        VInput,
        FileInput,
        VDeleteButton
    },
    mixins: [validationMixin],
    props: [
        'iPromoProducts',
        'activities',
        'ref_levels'
    ],
    data() {
        return {
            promoProducts: this.iPromoProducts,
            selectedItems: [],
            newPromoProduct: {
                product_id: '',
                description: '',
                mass: 1,
                active: 1,
                files: [],
            },
        };
    },
    validations: {
        newPromoProduct: {
            product_id: {required, integer},
            description: {required, maxLength: maxLength(1000)},
        },
    },
    methods: {
        openAttachModal: async function() {
            this.$bvModal.show('modal-products-attach');
        },
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
            Services.net().put(this.getRoute('referral.promo-products.edit', {}), promoProduct).then(data => {
                this.promoProducts = data.promoProducts;
                this.newPromoProduct.product_id = '';
                this.newPromoProduct.description = '';
                this.newPromoProduct.files = [];
                Services.msg('Информация сохранена')
            }, () => {
                Services.msg('Не удалось сохранить','danger')
            }).finally(() => {
                Services.hideLoader();
                this.$v.$reset();
            })
        },
        deletePromoProduct(id, index) {
            Services.showLoader();
            Services.net().delete(this.getRoute('referral.promo-products.delete', {}),
                {
                    promo_id: id
            }).then(data => {
                this.$delete(this.promoProducts, index);
                Services.msg('Промо-товар удален из общей подборки')
            }, () => {
                Services.msg('Не удалось удалить промо-товар', 'danger');
            }).finally(() => {
                Services.hideLoader();
                this.$v.$reset();
            })
        }
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
};
</script>

<style>
    .bigger::before {
        width: 1.5rem;
        height: 1.5rem;
    }
    .bigger::after {
        width: 1.5rem;
        height: 1.5rem;
    }
</style>