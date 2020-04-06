<template>
    <layout-main>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" @click="createBrand">Создать бренд</button>
            <div v-if="massAll(massSelectionType).length" class="action-bar d-flex justify-content-start">
                <span class="mr-4 align-self-baseline">Выбрано брендов: {{massAll(massSelectionType).length}}</span>
                <v-delete-button @delete="() => deleteBrands(massAll(massSelectionType))"/>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <td></td>
                    <th>ID</th>
                    <th>Логотип</th>
                    <th>Название</th>
                    <th>Код</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="brand in brands">
                    <td>
                        <input type="checkbox"
                                :checked="massHas({type: massSelectionType, id: brand.id})"
                                @change="e => massCheckbox(e, massSelectionType, brand.id)">
                    </td>
                    <td>{{brand.id}}</td>
                    <td><img :src="fileUrl(brand.file_id)" class="preview"></td>
                    <td>{{brand.name}}</td>
                    <td>{{brand.code}}</td>
                    <td>
                        <v-delete-button @delete="() => deleteBrands([brand.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editBrand(brand)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div>
            <b-pagination
                    v-if="numPages !== 1"
                    v-model="page"
                    :total-rows="total"
                    :per-page="pageSize"
                    :hide-goto-end-buttons="numPages < 10"
                    class="mt-3 float-right"
            ></b-pagination>
        </div>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('BrandFormModal')">
                <div slot="header">
                    Бренд
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-input v-model="$v.form.name.$model" :error="errorName">Название*</v-input>
                        <v-input v-model="$v.form.code.$model" :error="errorCode">Код</v-input>
                        <v-input v-model="$v.form.description.$model" :error="errorDescription" tag="textarea">Описание*</v-input>
                        <img v-if="form.file_id" :src="fileUrl(form.file_id)" class="preview">
                        <file-input destination="brand" :error="errorFile" @uploaded="onFileUpload">Логотип*</file-input>
                    </div>
                    <div class="form-group">
                        <button @click="onSave" type="button" class="btn btn-primary">Сохранить</button>
                        <button @click="onCancel" type="button" class="btn btn-secondary">Отмена</button>
                    </div>
                </div>
            </modal>
        </transition>
    </layout-main>
</template>

<script>
    import withQuery from 'with-query';

    import { mapActions, mapGetters } from 'vuex';

    import {
        ACT_DELETE_BRAND,
        ACT_LOAD_PAGE,
        ACT_SAVE_BRAND,
        GET_LIST,
        GET_NUM_PAGES,
        GET_PAGE_NUMBER,
        GET_PAGE_SIZE,
        GET_TOTAL,
        NAMESPACE,
        SET_PAGE,
    } from '../../../store/modules/brands';

    import modalMixin from '../../../mixins/modal';
    import mediaMixin from '../../../mixins/media';
    import massSelectionMixin from '../../../mixins/mass-selection';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Modal from '../../../components/controls/modal/modal.vue';
    import VInput from '../../../components/controls/VInput/VInput.vue';
    import FileInput from '../../../components/controls/FileInput/FileInput.vue';
    import VDeleteButton from '../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../scripts/services/services";

    export default {
        mixins: [
            modalMixin,
            mediaMixin,
            massSelectionMixin,
            validationMixin,
        ],
        components: {
            Modal,
            VInput,
            FileInput,
            VDeleteButton
        },
        props: {
            iBrands: {},
            iTotal: {},
            iCurrentPage: {},
        },
        data() {
            this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
                list: this.iBrands,
                total: this.iTotal,
                page: this.iCurrentPage
            });

            return {
                editBrandId: null,
                form: {
                    name: null,
                    code: null,
                    description: null,
                    file_id: null,
                },
                massSelectionType: 'brands',
            };
        },
        validations: {
            form: {
                name: {required},
                code: {
                    pattern: (value) => /^[a-zA-Z0-9_]*$/.test(value)
                },
                description: {required},
                file_id: {required}
            }
        },
        methods: {
            ...mapActions(NAMESPACE, [
                ACT_LOAD_PAGE,
                ACT_SAVE_BRAND,
                ACT_DELETE_BRAND,
            ]),
            loadPage(page) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: page,
                }));

                return this[ACT_LOAD_PAGE]({page});
            },

            createBrand() {
                this.$v.form.$reset();
                this.editBrandId = null;
                this.form.name = null;
                this.form.code = null;
                this.form.description = null;
                this.form.file_id = null;
                this.openModal('BrandFormModal');
            },

            editBrand(brand) {
                this.$v.form.$reset();
                this.editBrandId = brand.id;
                this.form.name = brand.name;
                this.form.code = brand.code;
                this.form.description = brand.description;
                this.form.file_id = brand.file_id;
                this.openModal('BrandFormModal');
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this[ACT_SAVE_BRAND]({
                    id: this.editBrandId,
                    brand: this.form
                }).then(() => {
                    return this[ACT_LOAD_PAGE]({page: this.page});
                }).finally(() => {
                    this.closeModal();
                    Services.hideLoader();
                });
            },
            onCancel() {
                this.closeModal();
            },
            deleteBrands(ids) {
                Services.showLoader();
                this[ACT_DELETE_BRAND]({ids})
                    .then(() => {
                        return this[ACT_LOAD_PAGE]({page: this.page});
                    })
                    .finally(() => {
                        Services.hideLoader();
                        this.massClear(this.massSelectionType);
                    });
            },
            onFileUpload(file) {
                this.$v.form.file_id.$model = file.id;
            }
        },
        created() {
            window.onpopstate = () => {
                let query = qs.parse(document.location.search.substr(1));
                if (query.page) {
                    this.page = query.page;
                }
            };
        },
        computed: {
            ...mapGetters(NAMESPACE, {
                GET_PAGE_NUMBER,
                total: GET_TOTAL,
                pageSize: GET_PAGE_SIZE,
                numPages: GET_NUM_PAGES,
                brands: GET_LIST,
            }),
            page: {
                get: function () {
                    return this.GET_PAGE_NUMBER;
                },
                set: function (page) {
                    this.loadPage(page);
                }
            },

            errorName() {
                if (this.$v.form.name.$dirty) {
                    if (!this.$v.form.name.required) return "Обязательное поле!";
                }
            },
            errorDescription() {
                if (this.$v.form.description.$dirty) {
                    if (!this.$v.form.description.required) return "Обязательное поле!";
                }
            },
            errorFile() {
                if (this.$v.form.file_id.$dirty) {
                    if (!this.$v.form.file_id.required) return "Обязательное поле!";
                }
            },
            errorCode() {
                if (this.$v.form.code.$dirty) {
                    if (!this.$v.form.code.pattern) return "Только латиница, цифры и подчёркивание!";
                }
            },
        }
    };
</script>

<style scoped>
    th {
        vertical-align: top !important;
    }
    .with-small small{
        display: block;
        color: gray;
        line-height: 1rem;
        overflow: hidden;
    }
    .preview {
        height: 50px;
        border-radius: 5px;
    }
</style>
