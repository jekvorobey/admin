<template>
    <layout-main>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" @click="createOrganizer">Создать организатора</button>
            <div v-if="massAll(massSelectionType).length" class="action-bar d-flex justify-content-start">
                <span class="mr-4 align-self-baseline">Выбрано брендов: {{massAll(massSelectionType).length}}</span>
                <v-delete-button @delete="() => deleteOrganizers(massAll(massSelectionType))"/>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <td></td>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Сайт</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="organizer in organizers">
                    <td>
                        <input type="checkbox"
                                :checked="massHas({type: massSelectionType, id: organizer.id})"
                                @change="e => massCheckbox(e, massSelectionType, organizer.id)">
                    </td>
                    <td>{{organizer.id}}</td>
                    <td>{{organizer.name}}</td>
                    <td>{{organizer.site}}</td>
                    <td>
                        <v-delete-button @delete="() => deleteOrganizers([organizer.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editOrganizers(organizer)">
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
            <modal :close="closeModal" v-if="isModalOpen('OrganizerFormModal')">
                <div slot="header">
                    Организатор
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-input v-model="$v.form.name.$model" :error="errorName">Название*</v-input>
                        <v-input v-model="$v.form.description.$model" :error="errorDescription" tag="textarea">Описание*</v-input>
                        <v-input v-model="$v.form.phone.$model" :error="errorPhone">Телефон</v-input>
                        <v-input v-model="$v.form.email.$model" :error="errorEmail">Email</v-input>
                        <v-input v-model="$v.form.site" >Ссылка на сайт</v-input>
                        <span>Добавить контакты</span>
                        <button  type="button" class="btn btn-light" @click="addRow()"><fa-icon icon="plus"></fa-icon></button>
                        <div v-for="(contact, index) in form.contacts" class="d-flex align-items-center justify-content-between">
                            <v-input type="text" placeholder="Название" class="mr-2" :value="index" v-model="tmpContacts.key"></v-input>
                            <v-input type="text" placeholder="Cсылка" class="mr-2" :value="contact" v-model="tmpContacts.value"></v-input>
                            
                            <button type="button" class="btn btn-danger float-right mr-2" @click="deleteRow(index)"><fa-icon icon="times"></fa-icon></button>
                        </div>
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
        ACT_DELETE_ORGANIZER,
        ACT_LOAD_PAGE,
        ACT_SAVE_ORGANIZER,
        GET_LIST,
        GET_NUM_PAGES,
        GET_PAGE_NUMBER,
        GET_PAGE_SIZE,
        GET_TOTAL,
        NAMESPACE,
        SET_PAGE,
    } from '../../../store/modules/organizers';

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
            iOrganizers: {},
            iTotal: {},
            iCurrentPage: {},
        },
        data() {
            this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
                list: this.iOrganizers,
                total: this.iTotal,
                page: this.iCurrentPage
            });

            return {
                editOrganizerId: null,
                form: {
                    owner_id: null,
                    global: true,
                    name: null,
                    description: null,
                    phone: null,
                    email: null,
                    site: null,
                    contacts: [
                    ]
                },
                tmpContacts: [
                ],
                massSelectionType: 'organizers',
            };
        },
        validations: {
            form: {
                name: {required},
                description: {required},
                phone: {required},
                email: {required}
            }
        },
        methods: {
            ...mapActions(NAMESPACE, [
                ACT_LOAD_PAGE,
                ACT_SAVE_ORGANIZER,
                ACT_DELETE_ORGANIZER,
            ]),
            loadPage(page) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: page,
                }));

                return this[ACT_LOAD_PAGE]({page});
            },

            addRow() {
                this.form.contacts.push({
                    name: '',
                    link: ''
                })
            },

            deleteRow(index) {
                this.form.contacts.splice(index,1)
            },

            createOrganizer() {
                this.$v.form.$reset();
                this.editOrganizersId = null;
                this.form.name = null;
                this.form.description = null;
                this.form.phone = null;
                this.form.email = null;
                this.form.site = null;
                this.form.contacts = []
                this.openModal('OrganizerFormModal');
            },

            editOrganizers(organizer) {
                this.$v.form.$reset();
                this.editOrganizersId = organizer.id;
                this.form.name = organizer.name;
                this.form.code = organizer.code;
                this.form.description = organizer.description;
                this.form.phone = organizer.phone;
                this.form.email = organizer.email;
                this.form.site = organizer.site;
                this.form.contacts = organizer.contacts;
                this.openModal('OrganizerFormModal');
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this[ACT_SAVE_ORGANIZER]({
                    id: this.editOrganizersId,
                    organizer: this.form
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
            deleteOrganizers(ids) {
                Services.showLoader();
                this[ACT_DELETE_ORGANIZER]({ids})
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
                organizers: GET_LIST,
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
