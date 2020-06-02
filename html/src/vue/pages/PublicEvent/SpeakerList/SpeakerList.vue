<template>
    <layout-main>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" @click="createSpeaker">Добавить спикера</button>
            <div v-if="massAll(massSelectionType).length" class="action-bar d-flex justify-content-start">
                <span class="mr-4 align-self-baseline">Выбрано брендов: {{massAll(massSelectionType).length}}</span>
                <v-delete-button @delete="() => deleteSpeackers(massAll(massSelectionType))"/>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <td></td>
                    <th>ID</th>
                    <th>Аватар</th>
                    <th>Имя</th>
                    <th>Фамилия</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="speaker in speakers">
                    <td>
                        <input type="checkbox"
                                :checked="massHas({type: massSelectionType, id: speaker.id})"
                                @change="e => massCheckbox(e, massSelectionType, speaker.id)">
                    </td>
                    <td>{{speaker.id}}</td>
                    <td><img :src="fileUrl(speaker.file_id)" class="preview"></td>
                    <td>{{speaker.first_name }}</td>
                    <td>{{speaker.last_name}}</td>
                    <td>{{speaker.phone}}</td>
                    <td>{{speaker.email}}</td>
                    <td>
                        <v-delete-button @delete="() => deleteSpeakers([speaker.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editSpeaker(speaker)">
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
            <modal :close="closeModal" v-if="isModalOpen('SpeakerFormModal')">
                <div slot="header">
                    Спикер
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-input v-model="$v.form.first_name.$model" :error="errorFirstName">Название*</v-input>
                        <v-input v-model="$v.form.last_name.$model" :error="errorLastName">Фамилия</v-input>
                        <v-input v-model="$v.form.middle_name.$model" :error="errorMiddleName">Отчество</v-input>
                        <v-input v-model="$v.form.phone.$model" :error="errorPhone" >Телефон*</v-input>
                        <v-input v-model="$v.form.email.$model" :error="errorEmail" >Email*</v-input>
                        <b-form-checkbox v-model="$v.form.global.$model" >Глобальный спикер</b-form-checkbox>
                        <img v-if="form.file_id" :src="fileUrl(form.file_id)" class="preview">
                        <file-input destination="speaker" :error="errorFile" @uploaded="onFileUpload">Аватар*</file-input>
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
        ACT_DELETE_SPEAKERS,
        ACT_LOAD_PAGE,
        ACT_SAVE_SPEAKERS,
        GET_LIST,
        GET_NUM_PAGES,
        GET_PAGE_NUMBER,
        GET_PAGE_SIZE,
        GET_TOTAL,
        NAMESPACE,
        SET_PAGE,
    } from '../../../store/modules/speakers';

    import {
        PROF_NAMESPACE,
        SET_PROFESSIONS,
        GET_PROFESSION_LIST,
        ACT_PROFESSION_LOAD,
    } from '../../../store/modules/professions';


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
            iSpeakers: {},
            iTotal: {},
            iCurrentPage: {},
        },
        data() {
            this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
                list: this.iSpeakers,
                total: this.iTotal,
                page: this.iCurrentPage,
            });
            this.$store.commit(`${PROF_NAMESPACE}/${SET_PROFESSIONS}`, {
                list: {
                    name: 'qweqwe'
                }
            });

            return {
                editSpeakerId: null,
                form: {
                    first_name: null,
                    middle_name: null,
                    last_name: null,
                    phone: null,
                    email: null,
                    file_id: null,
                    global: false,
                },
                massSelectionType: 'speakers',
            };
        },
        validations: {
            form: {
                first_name: {required},
                middle_name: {required},
                last_name: {required},
                email: {required},
                phone: {required},
                file_id: {required},
                global: {required}
            }
        },
        methods: {
            ...mapActions(NAMESPACE, [
                ACT_LOAD_PAGE,
                ACT_SAVE_SPEAKERS,
                ACT_DELETE_SPEAKERS,
            ]
            ),
            ...mapActions(PROF_NAMESPACE, [
                ACT_PROFESSION_LOAD
            ]),
            loadPage(page) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: page,
                }));
                return this[ACT_LOAD_PAGE]({page});
            },

            createSpeaker() {
                this.$v.form.$reset();
                this.editSpeakerId = null;
                this.form.first_name = null;
                this.form.last_name = null;
                this.form.middle_name = null;
                this.form.phone = null;
                this.form.email = null;
                this.form.file_id = null;
                this.form.global = null;
                this.openModal('SpeakerFormModal');
            },

            editSpeaker(speaker) {
                this.$v.form.$reset();
                this.editSpeakerId = speaker.id;
                this.form.first_name = speaker.first_name;
                this.form.last_name = speaker.last_name;
                this.form.middle_name = speaker.middle_name;
                this.form.phone = speaker.phone;
                this.form.email = speaker.email;
                this.form.file_id = speaker.file_id;
                this.form.global = speaker.global;
                this.openModal('SpeakerFormModal');
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this[ACT_SAVE_SPEAKERS]({
                    id: this.editSpeakerId,
                    speaker: this.form
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
                this[ACT_DELETE_SPEAKERS]({ids})
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
                speakers: GET_LIST,
            }),
            ...mapGetters(PROF_NAMESPACE, {
                professions: GET_PROFESSION_LIST,
            }),

            page: {
                get: function () {
                    return this.GET_PAGE_NUMBER;
                },
                set: function (page) {
                    this.loadPage(page);
                }
            },

            errorFirstName() {
                if (this.$v.form.first_name.$dirty) {
                    if (!this.$v.form.first_name.required) return "Обязательное поле!";
                }
            },
            errorMiddleName() {
                if (this.$v.form.middle_name.$dirty) {
                    if (!this.$v.form.middle_name.required) return "Обязательное поле!";
                }
            },
            errorLastName() {
                if (this.$v.form.last_name.$dirty) {
                    if (!this.$v.form.last_name.required) return "Обязательное поле!";
                }
            },
            errorPhone() {
                if (this.$v.form.phone.$dirty) {
                    if (!this.$v.form.phone.required) return "Обязательное поле!";
                }
            },
            errorEmail() {
                if (this.$v.form.email.$dirty) {
                    if (!this.$v.form.email.required) return "Обязательное поле!";
                }
            },
            errorFile() {
                if (this.$v.form.file_id.$dirty) {
                    if (!this.$v.form.file_id.required) return "Обязательное поле!";
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
