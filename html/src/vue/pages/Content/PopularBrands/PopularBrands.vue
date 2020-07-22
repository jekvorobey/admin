<template>
    <layout-main>
        <div class="row mb-3">
            <div class="col-6" style="text-align: left">
                <button class="btn btn-success mr-1"
                    @click="onShowModalEdit(null)">
                    Добавить бренд
                </button>
                <button class="btn btn-danger"
                        :disabled="massEmpty(massPopularBrandType)"
                        @click="onShowModalDelete()">
                    Удалить бренд
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

        <table class="table table-striped">
            <thead>
            <tr>
                <td></td>
                <th>Логотип</th>
                <th>Название</th>
                <th>Показывать логотип</th>
                <th>Действия</th>
            </tr>
            </thead>
                <draggable v-model="popularBrands"
                           v-bind="dragOptions"
                           style="cursor: move"
                           tag="tbody"
                >
                    <tr v-for="popularBrand in popularBrands">
                        <td class="d-flex flex-column align-items-center">
                            <input type="checkbox"
                                   :checked="massHas({type: massPopularBrandType, id: popularBrand.id})"
                                   @change="e => massCheckbox(e, massPopularBrandType, popularBrand.id)"/>
                        </td>
                        <td><img :src="fileUrl(popularBrand.file_id)" class="preview"></td>
                        <td>{{ popularBrand.name }}</td>
                        <td>
                            {{ popularBrand.show_logo ? "Да" : "Нет" }}
                        </td>
                        <td>
                            <button class="btn btn-info btn-md"
                                    @click="onShowModalEdit(popularBrand)">
                                <fa-icon icon="pencil-alt"/>
                            </button>
                        </td>
                    </tr>
                </draggable>
        </table>

        <b-modal id="modal-edit" :title="(editPopularBrand) ? 'Редактирование популярного бренда' : 'Создание популярного бренда'" hide-footer>
            <div class="card">
                <div class="card-body">
                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="popular-brand-brand">Бренд</label>
                        </b-col>
                        <b-col cols="9">
                            <v-select2 id="popular-brand-brand"
                                       v-model="editPopularBrand.brand_id"
                                       class="form-control form-control-sm"
                                       :error="errBrandId"
                            >
                                <option v-for="brand in brands" :value="brand.id">{{ brand.name }}</option>
                            </v-select2>
                        </b-col>
                    </b-row>
                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="popular-brand-show-logo">Показывать логотип</label>
                        </b-col>
                        <b-col cols="9">
                            <input id="popular-brand-show-logo"
                                     type="checkbox"
                                     v-model="editPopularBrand.show_logo"/>
                        </b-col>
                    </b-row>
                    <button class="btn btn-success"
                            @click="savePopularBrand">
                        Сохранить
                    </button>
                    <button class="btn btn-outline-danger"
                            @click="onCloseModalEdit">
                        Отмена
                    </button>
                </div>
            </div>
        </b-modal>
        <b-modal id="modal-delete" title="Удаление популярного бренда" hide-footer>
            <div class="card">
                <div class="card-body">
                    <div>
                        Вы уверены, что хотите удалить следующие популярные бренды: <br>
                        {{massPopularBrandNames()}}
                    </div>
                    <button class="btn btn-outline-danger"
                            @click="deletePopularBrands">
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
    import VSelect2 from '../../../components/controls/VSelect2/v-select2.vue';

    import mediaMixin from '../../../mixins/media';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';
    import massSelectionMixin from '../../../mixins/mass-selection';

    import Services from "../../../../scripts/services/services";

    export default {
        components: {
            draggable,
            VSelect2,
            VInput,
        },
        mixins: [
            mediaMixin,
            validationMixin,
            massSelectionMixin,
        ],
        props: {
            iPopularBrands: Array,
            iBrands: Array,
            dragOptions: {
                animation: 200,
                sort: true,
            },
        },
        data() {
            return {
                popularBrands: this.iPopularBrands,
                editPopularBrand: {
                    brand_id: null,
                    show_logo: false,
                },
                firstEditPopularBrandId: null,
                lastEditPopularBrand: {
                    brand_id: null,
                    show_logo: false,
                },
                brands: this.iBrands,
                massActionType: null,
                massStatus: null,
                massPopularBrandType: 'popularBrands',
                itemsOrder: [],
                isReordering: false,
            };
        },
        validations: {
            editPopularBrand: {
                brand_id: {required},
            },
        },
        methods: {
            onShowModalEdit(popularBrand) {
                if (popularBrand) {
                    this.editPopularBrand = Object.assign({}, popularBrand);
                }
                // При обновлении бренда //
                if (this.editPopularBrand.id) {
                    this.firstEditPopularBrandId = this.editPopularBrand.brand_id;
                    this.brands.push({
                        id: this.editPopularBrand.brand_id,
                        file_id: this.editPopularBrand.file_id,
                        name: this.editPopularBrand.name,
                    });
                }
                this.$bvModal.show('modal-edit');
            },
            onCloseModalEdit() {
                if (this.editPopularBrand.id) {
                    this.brands = this.brands.filter(brand => {
                        return brand.id !== parseInt(this.firstEditPopularBrandId);
                    });
                }
                this.editPopularBrand = {
                    brand_id: null,
                    show_logo: false,
                };
                this.$v.$reset();
                this.$bvModal.hide('modal-edit');
            },
            /**
             * Добавить новый популярный бренд или обновить старый
             */
            savePopularBrand() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                // При обновлении бренда //
                if (this.editPopularBrand.id) {
                    this.lastEditPopularBrand = {
                        id: this.editPopularBrand.id,
                        brand_id: this.editPopularBrand.brand_id,
                        show_logo: this.editPopularBrand.show_logo,
                    };
                    Services.showLoader();
                    Services.net().put(
                        this.getRoute('popularBrands.update'),
                        {},
                        this.editPopularBrand
                    ).then(
                        () => {
                            let foundIndex = this.popularBrands.findIndex(
                                item => item.id === this.lastEditPopularBrand.id
                            );

                            if (this.firstEditPopularBrandId !== this.lastEditPopularBrand.brand_id) {
                                let brand = this.brands.filter(brandI => {
                                    return brandI.id === parseInt(this.lastEditPopularBrand.brand_id);
                                })[0];

                                this.brands = this.brands.filter(brandI => {
                                    return brandI.id !== parseInt(this.lastEditPopularBrand.brand_id);
                                });
                                this.brands.push({
                                    id: this.popularBrands[foundIndex].brand_id,
                                    file_id: this.popularBrands[foundIndex].file_id,
                                    name: this.popularBrands[foundIndex].name,
                                });

                                this.popularBrands[foundIndex].brand_id = this.lastEditPopularBrand.brand_id;
                                this.popularBrands[foundIndex].name = brand.name;
                                this.popularBrands[foundIndex].file_id = brand.file_id;
                            }
                            this.popularBrands[foundIndex].show_logo = this.lastEditPopularBrand.show_logo;

                            Services.msg("Изменения сохранены");
                        },
                        () => {
                            Services.msg("Не удалось сохранить изменения",'danger');
                        }
                    ).finally(() => {
                        Services.hideLoader();
                    })
                }
                // При создании нового бренда //
                else {
                    Services.showLoader();
                    Services.net().post(
                        this.getRoute('popularBrands.create'),
                        {},
                        this.editPopularBrand
                    ).then(
                        data => {
                            let brand = this.brands.filter((brandI) => {
                                return brandI.id === parseInt(data.popular_brand.brand_id);
                            })[0];
                            this.popularBrands.push({
                                id: data.popular_brand.id,
                                brand_id: data.popular_brand.brand_id,
                                file_id: brand.file_id,
                                name: brand.name,
                                order_num: data.popular_brand.order_num,
                                show_logo: data.popular_brand.show_logo,
                            });
                            this.brands = this.brands.filter((brandI) => {
                                return brandI.id !== parseInt(data.popular_brand.brand_id);
                            })
                            Services.msg("Изменения сохранены");
                        },
                        () => {
                            Services.msg("Не удалось сохранить изменения",'danger');
                        }
                    ).finally(() => {
                        Services.hideLoader();
                    })
                }
                this.onCloseModalEdit();
            },
            onShowModalDelete() {
                this.$bvModal.show('modal-delete');
            },
            onCloseModalDelete() {
                this.$bvModal.hide('modal-delete');
            },
            massPopularBrandNames() {
                return this.popularBrands.filter((popularBrand) => {
                    return this.massAll(this.massPopularBrandType).includes(popularBrand.id);
                }).map((popularBrand) => {
                    return popularBrand.name;
                }).join(',');
            },
            /**
             * Удалить популярный бренд
             */
            deletePopularBrands() {
                Services.showLoader();
                Services.net().delete(
                    this.getRoute('popularBrands.delete'),
                    {
                        ids: this.massAll(this.massPopularBrandType),
                    }
                ).then(
                    () => {
                        this.deletedPopularBrands = this.popularBrands.filter((popularBrand) => {
                            return this.massAll(this.massPopularBrandType).includes(popularBrand.id);
                        });
                        this.deletedPopularBrands.forEach((popularBrand) => {
                            this.brands.push({
                                id: popularBrand.brand_id,
                                file_id: popularBrand.file_id,
                                name: popularBrand.name,
                            });
                        });
                        this.popularBrands = this.popularBrands.filter((popularBrand) => {
                            return !this.massAll(this.massPopularBrandType).includes(popularBrand.id);
                        });
                        this.massClear(this.massPopularBrandType);
                        Services.msg("Популярные бренды успешно удален");
                    },
                    () => {
                        Services.msg("Не удалось удалить популярные бренды",'danger');
                    }
                ).finally(() => {
                    Services.hideLoader();
                })
                this.onCloseModalDelete();
            },
            // Изменить порядок популярных брендов и сохранить на сервере //
            reorderItems() {
                this.isReordering = true;
                Services.net().put(
                    this.getRoute('popularBrands.reorder'),
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
            errBrandId() {
                if (this.$v.editPopularBrand.brand_id.$dirty) {
                    if (!this.$v.editPopularBrand.brand_id.required) {
                        return "Обязательное поле!";
                    }
                }
            },
        },
        watch: {
            // Пересортировка популярных брендов //
            'popularBrands': {
                handler() {
                    this.itemsOrder = Object.values(this.popularBrands)
                        .map((item, index) => ({
                                id: item.id,
                                order_num: index + 1,
                            })
                        );
                }
            },
        },
    }
</script>

<style scoped>

</style>