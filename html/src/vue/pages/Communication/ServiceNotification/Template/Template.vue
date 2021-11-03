<template>
    <layout-main>
        <span>
            <a :href="getRoute('communications.service-notification.list')">Назад</a>
        </span>
        </br>
        <!-- <span>Системные предупреждения</span>
        <system-alert :serviceNotificationId="service_notification_id"></system-alert> -->
        <span>Шаблоны</span>
        <div class="d-flex justify-content-between mt-3 mb-3" v-if="canUpdate(blocks.communications)">
            <button class="btn btn-success" @click="createTemplate">Добавить шаблон</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Канал</th>
                    <th>Текст</th>
                    <th class="text-right" v-if="canUpdate(blocks.communications)">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="template in templates" :key="template.id">
                    <td>{{template.id}}</td>
                    <td>{{channel(template.channel_id)}}</td>
                    <td>{{template.body}}</td>
                    <td v-if="canUpdate(blocks.communications)">
                        <v-delete-button @delete="() => onDelete([template.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editTemplate(template)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('TemplateFormModal')">
                <div slot="header">
                    Шаблон
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-select v-model="$v.form.channel_id.$model" :error="errorChannelId" text-field="name" value-field="id" :options="channels">Канал*</v-select>
                        <label for="textarea">Тело*</label>
                        <b-form-textarea id="textarea" rows="10" v-model="$v.form.body.$model" :error="errorBody" />
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
    import {mapActions} from "vuex";
    import {
        NAMESPACE,
        ACT_LOAD_NOTIFICATION_TEMPLATES,
        ACT_LOAD_CHANNELS,
        ACT_SAVE_NOTIFICATION_TEMPLATE,
        ACT_DELETE_NOTIFICATION_TEMPLATE
    } from '../../../../store/modules/service-notifications';
    
    import Helpers from '../../../../../scripts/helpers';
    import modalMixin from '../../../../mixins/modal';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../../scripts/services/services";
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import SystemAlert from '../components/system-alert.vue';

    export default {
        mixins: [
            modalMixin,
            validationMixin,
        ],
        components: {
            Modal,
            VInput,
            VDeleteButton,
            VSelect,
            SystemAlert
        },
        props: {
            iTemplates: {},
            service_notification_id: null
        },
        data() {
            return {
                channels: [],
                templates: [],
                editTemplateId: null,
                form: {
                    service_notification_id: null,
                    channel_id: null,
                    body: null
                },
            };
        },
        validations: {
            form: {
                service_notification_id: {},
                channel_id: {required},
                body: {required},
            }
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadNotificationTemplates: ACT_LOAD_NOTIFICATION_TEMPLATES,
                loadChannels: ACT_LOAD_CHANNELS,
                deleteNotificationTemplate: ACT_DELETE_NOTIFICATION_TEMPLATE,
                saveNotificationTemplate: ACT_SAVE_NOTIFICATION_TEMPLATE
            }),
            reload() { 
                this.loadNotificationTemplates({id: this.service_notification_id})
                    .then(data => {
                        this.templates = data.templates;
                    });
            },
            onDelete(ids) {
                Services.showLoader();
                this.deleteNotificationTemplate({
                    ids
                }).then(() => {
                    this.reload();
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            createTemplate() {
                this.$v.form.$reset();
                this.form.service_notification_id = this.service_notification_id;
                this.editTemplateId = null;
                this.form.channel_id = null;
                this.form.body = null;
                this.openModal('TemplateFormModal');
            },
            editTemplate(template) {
                this.$v.form.$reset();
                this.form.service_notification_id = this.service_notification_id;
                this.editTemplateId = template.id;
                this.form.channel_id = template.channel_id;
                this.form.body = template.body;
                this.openModal('TemplateFormModal');
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this.saveNotificationTemplate({
                    id: this.editTemplateId,
                    template: this.form
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
            channel(channelId) {
                let channel = this.channels.find(channel => channel.id === channelId);
                return channel ? channel.name : 'N/A';
            },
        },
        computed: {
            errorChannelId() {
                if (this.$v.form.channel_id.$dirty) {
                    if (!this.$v.form.channel_id.required) return "Обязательное поле!";
                }
            },
            errorBody() {
                if (this.$v.form.body.$dirty) {
                    if (!this.$v.form.body.required) return "Обязательное поле!";
                }
            },
        },
        created() {
            this.templates = this.iTemplates;
            this.loadChannels()
                .then(data => {
                    this.channels = data.channels;
                });
        }
    }
</script>

<style scoped>

</style>