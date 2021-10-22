<template>
    <div>
        <div class="d-flex justify-content-between mt-3 mb-3" v-if="canUpdate(blocks.communications)">
            <button class="btn btn-success" @click="createAlert" v-if="!alertExists">Добавить предупреждение</button>
        </div>
        <table class="table" v-if="alertExists">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Тело</th>
                    <th>Ссылка</th>
                    <th>Тип</th>
                    <th class="text-right" v-if="canUpdate(blocks.communications)">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{alert.id}}</td>
                    <td>{{alert.title}}</td>
                    <td>{{alert.body}}</td>
                    <td>{{alert.url}}</td>
                    <td>{{typeName(alert.type)}}</td>
                    <td v-if="canUpdate(blocks.communications)">
                        <v-delete-button @delete="() => onDeleteAlert([alert.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editAlert(alert)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('AlertFormModal')">
                <div slot="header">
                    Предупреждение
                </div>
                <div slot="body">
                    <div class="form-group">        
                        <v-input v-model="$v.form.title.$model" :error="errorTitle">Название*</v-input>
                        <span>Тело*</span>
                        <b-form-textarea rows="10" v-model="$v.form.body.$model" :error="errorBody" />
                        <v-input v-model="$v.form.url.$model" :error="errorUrl">Ссылка*</v-input>
                        <span>Тип*</span>
                        <b-form-select v-model="typeId" text-field="name" value-field="id" :options="types" :error="errorType"/>
                    </div>
                    <div class="form-group">
                        <button @click="onSave" type="button" class="btn btn-primary">Сохранить</button>
                        <button @click="onCancel" type="button" class="btn btn-secondary">Отмена</button>
                    </div>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {
        ACT_LOAD_NOTIFICATION_ALERT,
        ACT_DELETE_NOTIFICATION_ALERT,
        ACT_SAVE_NOTIFICATION_ALERT,
        NAMESPACE
    } from '../../../../store/modules/service-notifications';
    
    import Helpers from '../../../../../scripts/helpers';
    import modalMixin from '../../../../mixins/modal';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../../scripts/services/services";

    export default {
        mixins: [
            modalMixin,
            validationMixin,
        ],
        components: {
            Modal,
            VInput,
            VDeleteButton
        },
        props: {
            serviceNotificationId: null,
        },
        data() {
            return {
                alert: {},
                alertExists: false,
                types: [],
                editAlertId: null,
                typeId: null,
                form: {
                    service_notification_id: null,
                    title: null,
                    body: null,
                    url: null,
                    type: null,
                },
            };
        },
        validations: {
            form: {
                service_notification_id: {required},
                title: {required},
                body: {required},
                url: {required},
                type: {required},
            }
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadSystemAlert: ACT_LOAD_NOTIFICATION_ALERT,
                saveSystemAlert: ACT_SAVE_NOTIFICATION_ALERT,
                deleteSystemAlert: ACT_DELETE_NOTIFICATION_ALERT,
            }),
            reload() {
                this.loadSystemAlert({service_notification_id: this.serviceNotificationId})
                    .then(response => {
                        this.alert = response.alert;
                        this.types = response.types;
                        if (this.alert) {
                            this.alertExists = true;
                        }
                    });
            },
            typeName(typeId) {
                const type = this.types.filter(type => type.id == typeId);

                return type.length ? type[0].name : '---';
            },
            createAlert() {
                this.$v.form.$reset();
                this.editAlertId = null;
                this.form.service_notification_id = this.serviceNotificationId;
                this.form.title = null;
                this.form.body = null;
                this.form.url = null;
                this.form.type = null;
                this.openModal('AlertFormModal');
            },
            editAlert(alert) {
                this.$v.form.$reset();
                this.editAlertId = alert.id;
                this.form.service_notification_id = this.serviceNotificationId;
                this.form.title = alert.title;
                this.form.body = alert.body;
                this.form.url = alert.url;
                this.typeId = alert.type;
                this.openModal('AlertFormModal');
            },
            onDeleteAlert(ids) {
                Services.showLoader();
                this.deleteSystemAlert({
                    ids
                }).then(() => {
                    this.alertExists = false;
                    this.alert = {};
                    this.reload();
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            onSave() {
                this.$v.$touch();
                this.form.type = this.typeId;
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this.saveSystemAlert({
                    id: this.editAlertId,
                    alert: this.form
                }).then(() => {
                    this.reload()
                }).finally(() => {
                    this.closeModal();
                    Services.hideLoader();
                });
            },
            onCancel() {
                this.closeModal();
            },
        },
        computed: {
            errorTitle() {
                if (this.$v.form.title.$dirty) {
                    if (!this.$v.form.title.required) return "Обязательное поле!";
                }
            },
            errorBody() {
                if (this.$v.form.body.$dirty) {
                    if (!this.$v.form.body.required) return "Обязательное поле!";
                }
            },
            errorUrl() {
                if (this.$v.form.url.$dirty) {
                    if (!this.$v.form.url.required) return "Обязательное поле!";
                }
            },
            errorType() {
                if (this.$v.form.type.$dirty) {
                    if (!this.$v.form.type.required) return "Обязательное поле!";
                }
            },
        },
        created() {
            this.reload();
        }
    }
</script>

<style scoped>

</style>