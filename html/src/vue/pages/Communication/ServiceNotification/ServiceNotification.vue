<template>
    <layout-main>
        <div class="d-flex justify-content-between mt-3 mb-3" v-if="canUpdate(blocks.communications)">
            <button class="btn btn-success" @click="createNotification">Создать сервисное уведомление</button>
            <div v-if="massAll(massSelectionType).length" class="action-bar d-flex justify-content-start">
                <span class="mr-4 align-self-baseline">Выбрано сервисных нотификаций: {{massAll(massSelectionType).length}}</span>
                <v-delete-button @delete="() => deleteNotifications(massAll(massSelectionType))"/>
            </div>
        </div>
        <div :class="{'menu-scroll-hidden': !isMobile, 'menu-scroll': isMobile}" ref="menu">
        <table class="table" @mouseenter="showScroll = true" @mouseleave="showScroll = false">
            <scroll-btns @onScroll="onScroll" :showScroll="showScroll"/>
            <thead>
                <tr>
                    <td></td>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Тип</th>
                    <th>Каналы</th>
                    <th>Тема</th>
                    <th>Отправить от</th>
                    <th>Активно</th>
                    <th class="text-right" v-if="canUpdate(blocks.communications)">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="notification in notifications" :key="notification.id">
                    <td>
                        <input type="checkbox"
                                :checked="massHas({type: massSelectionType, id: notification.id})"
                                @change="e => massCheckbox(e, massSelectionType, notification.id)">
                    </td>
                    <td>{{notification.id}}</td>
                    <td class="with-small">
                        <a :href="getRoute('communications.service-notification.template.listNotification', {id: notification.id})">{{ notification.name }}</a>
                    </td>
                    <td>{{notification.type}}</td>
                    <td>{{channels(notification.channels)}}</td>
                    <td>{{notification.subject}}</td>
                    <td>{{notification.sender_id}}</td>
                    <td>
                        <b-badge v-if="notification.active" variant="success">
                            да
                        </b-badge>
                        <b-badge v-if="!notification.active" variant="danger">
                            нет
                        </b-badge>
                    </td>
                    <td v-if="canUpdate(blocks.communications)">
                        <v-delete-button @delete="() => deleteNotifications([notification.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editNotification(notification)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
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
            <modal :close="closeModal" v-if="isModalOpen('NotificationFormModal')">
                <div slot="header">
                    Сервисное уведомление
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-input v-model="$v.form.name.$model" :error="errorName">Название*</v-input>
                        <v-input v-model="$v.form.type.$model" :error="errorType">Тип*</v-input>
                        <v-input v-model="$v.form.subject.$model" :error="errorType">Тема</v-input>
                        <v-input v-model="$v.form.sender_id.$model" :error="errorType">Отправить от лица пользователя</v-input>
                        <div class="form-group">
                            <input type="checkbox"
                                   id="active"
                                   v-model="$v.form.active.$model"
                            >
                            <label for="active">
                                Активно
                            </label>
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
        ACT_DELETE_NOTIFICATIONS,
        ACT_LOAD_PAGE,
        ACT_SAVE_NOTIFICATION,
        GET_LIST,
        GET_NUM_PAGES,
        GET_PAGE_NUMBER,
        GET_PAGE_SIZE,
        GET_TOTAL,
        NAMESPACE,
        SET_PAGE,
    } from '../../../store/modules/service-notifications';

    import modalMixin from '../../../mixins/modal';
    import mediaMixin from '../../../mixins/media';
    import massSelectionMixin from '../../../mixins/mass-selection';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';
    import ScrollBtns from '../../../components/scroll-btns/scroll-btns.vue'
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
            VDeleteButton,
            ScrollBtns
        },
        props: {
            iNotifications: {},
            iTotal: {},
            iCurrentPage: {},
        },
        data() {
            this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
                list: this.iNotifications,
                total: this.iTotal,
                page: this.iCurrentPage
            });

            return {
                showScroll: false,
                editNotificationId: null,
                form: {
                    name: null,
                    type: null,
                    subject: null,
                    sender_id: null,
                    active: null
                },
                massSelectionType: 'serviceNotifications',
            };
        },
        validations: {
            form: {
                name: {required},
                type: {
                    pattern: (value) => /^[a-zA-Z0-9_]*$/.test(value),
                    required
                },
                subject: {},
                sender_id: {},
                active: {}
            }
        },
        methods: {
            ...mapActions(NAMESPACE, [
                ACT_LOAD_PAGE,
                ACT_SAVE_NOTIFICATION,
                ACT_DELETE_NOTIFICATIONS,
            ]),
            onScroll(direction) {
                const menu = this.$refs.menu

                if (direction === 'left') {
                    menu.scrollTo({
                        left: menu.scrollLeft - 200,
                        behavior: 'smooth'
                    })
                }

                if (direction === 'right') {
                    menu.scrollTo({
                        left: menu.scrollLeft + 200,
                        behavior: 'smooth'
                    })

                }
            },
            loadPage(page) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: page,
                }));

                return this[ACT_LOAD_PAGE]({page});
            },

            createNotification() {
                this.$v.form.$reset();
                this.editNotificationId = null;
                this.form.name = null;
                this.form.type = null;
                this.form.subject = null;
                this.form.sender_id = null;
                this.form.active = null;
                this.openModal('NotificationFormModal');
            },

            editNotification(notification) {
                this.$v.form.$reset();
                this.editNotificationId = notification.id;
                this.form.name = notification.name;
                this.form.type = notification.type;
                this.form.subject = notification.subject;
                this.form.sender_id = notification.sender_id;
                this.form.active = notification.active;
                this.openModal('NotificationFormModal');
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this[ACT_SAVE_NOTIFICATION]({
                    id: this.editNotificationId,
                    notification: this.form
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
            deleteNotifications(ids) {
                Services.showLoader();
                this[ACT_DELETE_NOTIFICATIONS]({ids})
                    .then(() => {
                        Services.msg("Запись успешно удалена!");
                        return this[ACT_LOAD_PAGE]({page: this.page});
                    })
                    .catch((error) => {
                        Services.hideLoader()
                        Services.msg("Не удалось удалить запись", 'danger');
                    })
                    .finally(() => {
                        Services.hideLoader();
                        this.massClear(this.massSelectionType);
                    });
            },
            channels(channels) {
                let result = [];
                channels.forEach(channel => {
                    result.push(channel.name);
                })
                return result.join(', ');
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
                notifications: GET_LIST,
            }),
            page: {
                get: function () {
                    return this.GET_PAGE_NUMBER;
                },
                set: function (page) {
                    this.loadPage(page);
                }
            },
            isMobile(){
                return window.innerWidth <= 768
            },
            errorName() {
                if (this.$v.form.name.$dirty) {
                    if (!this.$v.form.name.required) return "Обязательное поле!";
                }
            },
            errorType() {
                if (this.$v.form.type.$dirty) {
                    if (!this.$v.form.type.required) return "Обязательное поле!";
                    if (!this.$v.form.type.pattern) return "Только латиница, цифры и подчёркивание!";
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
