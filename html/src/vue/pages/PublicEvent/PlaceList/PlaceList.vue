<template>
    <layout-main>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" @click="createPlace">Создать Место</button>
            <div v-if="massAll(massSelectionType).length" class="action-bar d-flex justify-content-start">
                <span class="mr-4 align-self-baseline">Выбрано мест: {{massAll(massSelectionType).length}}</span>
                <v-delete-button @delete="() => deletePlaces(massAll(massSelectionType))"/>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <td></td>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Город</th>
                    <th>Адрес</th>
                    <th>Как пройти</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="place in places" :key="place.id">
                    <td>
                        <input type="checkbox"
                                :checked="massHas({type: massSelectionType, id: place.id})"
                                @change="e => massCheckbox(e, massSelectionType, place.id)">
                    </td>
                    <td>{{place.id}}</td>
                    <td>{{place.name}}</td>
                    <td>{{place.city_name}}</td>
                    <td>{{place.address}}</td>
                    <td>{{place.description}}</td>
                    <td>
                        <v-delete-button @delete="() => deletePlaces([place.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editPlace(place)">
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
            <modal :close="closeModal" v-if="isModalOpen('PlaceFormModal')">
                <div slot="header">
                    Место
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-input v-model="$v.form.name.$model" :error="errorName">Название*</v-input>
                        <v-input v-model="$v.form.description.$model" :error="errorDescription" tag="textarea">Как пройти*</v-input>
                        <v-dadata
                            :value="$v.form.address.$model"
                            :error="errorAddress"
                            @onSelect="onStoreAddressAdd">
                                Адрес*
                        </v-dadata>
                        <b-form-checkbox v-model="$v.form.global.$model" :error="errorGlobal">Глобальное место*</b-form-checkbox>
                        <v-input v-model="$v.form.city_name.$model" disabled>Город</v-input>
                        <v-input v-model="$v.form.city_id.$model" disabled>ID Места</v-input>
                        <v-input v-model="$v.form.latitude.$model" disabled>Широта</v-input>
                        <v-input v-model="$v.form.longitude.$model" disabled>Долгота</v-input>
                        <img v-if="$v.form.file_id.$model" :src="fileUrl($v.form.file_id.$model)" class="preview">
                        <file-input destination="place" @uploaded="onFileUpload">Изображение</file-input>
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
        ACT_DELETE_PLACES,
        ACT_LOAD_PAGE,
        ACT_SAVE_PLACES,
        GET_LIST,
        GET_NUM_PAGES,
        GET_PAGE_NUMBER,
        GET_PAGE_SIZE,
        GET_TOTAL,
        NAMESPACE,
        SET_PAGE,
    } from '../../../store/modules/places';

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
    import VDadata from '../../../components/controls/VDaData/VDaData.vue';

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
            VDeleteButton,
            VDadata
        },
        props: {
            iPlaces: {},
            iTotal: {},
            iCurrentPage: {},
        },
        data() {
            this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
                list: this.iPlaces,
                total: this.iTotal,
                page: this.iCurrentPage
            });

            return {
                editPlaceId: null,
                form: {
                    name: null,
                    city_name: null,
                    description: null,
                    address: null,
                    global: null,
                    latitude: null,
                    longitude: null,
                    file_id: null
                },
                massSelectionType: 'places',
            };
        },
        validations: {
            form: {
                name: {required},
                city_name: {required},
                description: {required},
                address: {required},
                global: {required},
                city_id: {required},
                longitude: {required},
                latitude: {required},
                file_id: {required}
            }
        },
        methods: {
            ...mapActions(NAMESPACE, [
                ACT_LOAD_PAGE,
                ACT_SAVE_PLACES,
                ACT_DELETE_PLACES,
            ]),
            loadPage(page) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: page,
                }));

                return this[ACT_LOAD_PAGE]({page});
            },
            onStoreAddressAdd(suggestion) {
                this.form.address = suggestion.value;

                const address = suggestion.data;

                this.form.city_name = address.city;
                this.form.city_id = address.fias_id;
                this.form.latitude = address.geo_lat;
                this.form.longitude = address.geo_lon;
            },
            createPlace() {
                this.$v.form.$reset();
                this.editPlaceId = null;
                this.form.name = null;
                this.form.city_name = null;
                this.form.city_id = null;
                this.form.address = null;
                this.form.description = null;
                this.form.latitude = null;
                this.form.longitude = null;
                this.form.global = true;
                this.file_id = null;
                this.openModal('PlaceFormModal');
            },

            editPlace(place) {
                this.$v.form.$reset();
                this.owner_id = place.owner_id;
                this.editPlaceId = place.id;
                this.form.name = place.name;
                this.form.city_name = place.city_name;
                this.form.city_id = place.city_id;
                this.form.address = place.address;
                this.form.description = place.description;
                this.form.latitude = place.latitude;
                this.form.longitude = place.longitude;
                this.form.global = place.global ? true : false;
                this.file_id = place.file_id;
                this.openModal('PlaceFormModal');
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this[ACT_SAVE_PLACES]({
                    id: this.editPlaceId,
                    place: this.form
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
            deletePlaces(ids) {
                Services.showLoader();
                this[ACT_DELETE_PLACES]({ids})
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
            },
            
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
                places: GET_LIST,
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
            errorAddress() {
                if (this.$v.form.address.$dirty) {
                    if (!this.$v.form.address.required) return "Обязательное поле!";
                }
            },
            errorCityName() {
                if (this.$v.form.city_name.$dirty) {
                    if (!this.$v.form.address.required) return "Обязательное поле!";
                }
            },
            errorFile() {
                if (this.$v.form.file_id.$dirty) {
                    if (!this.$v.form.file_id.required) return "Обязательное поле!";
                }
            },
            errorGlobal() {
                if (this.$v.form.global.$dirty) {
                    if (!this.$v.form.global.required) return "Обязательное поле!";
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
