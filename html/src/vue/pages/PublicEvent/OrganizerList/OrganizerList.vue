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
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Описание</th>
                    <th>WhatsApp</th>
                    <th>Viber</th>
                    <th>Telegram</th>
                    <th>Сайт</th>
                    <th>Соц. сеть</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="organizer in organizers" :key="organizer.id">
                    <td>
                        <input type="checkbox"
                                :checked="massHas({type: massSelectionType, id: organizer.id})"
                                @change="e => massCheckbox(e, massSelectionType, organizer.id)">
                    </td>
                    <td>{{organizer.id}}</td>
                    <td>{{organizer.name}}</td>
                    <td>{{organizer.phone}}</td>
                    <td>{{organizer.email}}</td>
                    <td>{{organizer.description}}</td>
                    <td>{{organizer.whatsapp}}</td>
                    <td>{{organizer.viber}}</td>
                    <td>{{organizer.telegram}}</td>
                    <td>{{organizer.site}}</td>
                    <td>{{organizer.social_link}}</td>
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
                        <v-input v-model="$v.form.phone.$model" :placeholder="telPlaceholder" :error="errorPhone" v-mask="telMask" autocomplete="off">Телефон*</v-input>
                        <v-input v-model="$v.form.email.$model" :placeholder="emailPlaceholder" :error="errorEmail" autocomplete="off" >Email*</v-input>
                        <v-input v-model="$v.form.description.$model" :error="errorDescription" tag="textarea">Описание*</v-input>
                        <v-input v-model="$v.form.whatsapp.$model" >WhatsApp</v-input>
                        <v-input v-model="$v.form.viber.$model" >Viber</v-input>
                        <v-input v-model="$v.form.telegram.$model" >Telegram</v-input>
                        <v-input v-model="$v.form.site.$model" >Ссылка на сайт</v-input>
                        <v-input v-model="$v.form.social_link.$model" >Соц. сеть</v-input>
                        <b-form-checkbox v-model="$v.form.global.$model" >Глобальный организатор</b-form-checkbox>
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
    import {required, email} from 'vuelidate/lib/validators';
    import {telMask} from '../../../../scripts/mask';
    import {emailPlaceholder, telPlaceholder} from '../../../../scripts/placeholder';

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
                    social_link: null,
                    whatsapp: null,
                    viber: null,
                    telegram: null,
                    social_link: null
                },
                massSelectionType: 'organizers',
            };
        },
        validations: {
            form: {
                name: {required},
                description: {required},
                phone: {required},
                email: {required},
                site: {required},
                social_link: {required},
                whatsapp: {required},
                viber: {required},
                telegram: {required},
                social_link: {required},
                global: {required}
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

            createOrganizer() {
                this.$v.form.$reset();
                this.editOrganizerId = null;
                this.form.name = null;
                this.form.description = null;
                this.form.phone = null;
                this.form.email = null;
                this.form.site = null;
                this.form.whatsapp = null;
                this.form.viber = null;
                this.form.telegram = null;
                this.form.social_link = null;
                this.form.global = true;
                this.openModal('OrganizerFormModal');
            },

            editOrganizers(organizer) {
                this.$v.form.$reset();
                this.editOrganizerId = organizer.id;
                this.form.owner_id = organizer.owner_id;
                this.form.name = organizer.name;
                this.form.code = organizer.code;
                this.form.description = organizer.description;
                this.form.phone = organizer.phone;
                this.form.email = organizer.email;
                this.form.site = organizer.site;
                this.form.whatsapp = organizer.whatsapp;
                this.form.viber = organizer.viber;
                this.form.telegram = organizer.telegram;
                this.form.social_link = organizer.social_link;
                this.form.global = organizer.global ? true : false;
                this.openModal('OrganizerFormModal');
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this[ACT_SAVE_ORGANIZER]({
                    id: this.editOrganizerId,
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
            telMask() {
                return telMask;
            },
            telPlaceholder() {
                return telPlaceholder;
            },
            emailPlaceholder() {
                return emailPlaceholder;
            },
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
                    if (!this.$v.form.email.email) return "Введите валидный e-mail!";
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
