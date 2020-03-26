<template>
    <div>
        <div class="card">
            <div class="card-body">
                <b-button @click="showHideChatForm()" type="submit" variant="dark" v-if="!showChatForm">Создать чат</b-button>
                <b-form @submit.prevent="" v-if="showChatForm">
                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="chat-channel">Канал</label>
                        </b-col>
                        <b-col cols="9">
                            <b-form-select v-model="form.channel_id" id="chat-channel" @input="initUserThemeStatusType">
                                <b-form-select-option :value="channel.id" v-for="channel in availableChannels" :key="channel.id">
                                    {{ channel.name }}
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2" v-if="showUserRoleAndIdInput">
                        <b-col cols="3">
                            <label for="chat-users-role">Роли пользователей</label>
                        </b-col>
                        <b-col cols="9">
                            <v-select2 v-model="form.role_ids" class="form-control form-control-sm" @input="getUsersByRole" multiple>
                                <b-form-select-option :value="role_id" v-for="(role_name, role_id) in roles" :key="role_id">
                                    {{ role_name }}
                                </b-form-select-option>
                            </v-select2>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2" v-if="showUserRoleAndIdInput">
                        <b-col cols="3">
                            <label for="chat-users">Пользователи</label>
                        </b-col>
                        <b-col cols="9">
                            <v-select2 v-model="form.user_ids" class="form-control form-control-sm" multiple>
                                <option v-for="user in availableUsers" :value="user.id">{{ user.title }}</option>
                            </v-select2>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="chat-theme">Тема</label>
                        </b-col>
                        <b-col cols="9">
                            <b-form-input id="chat-theme" v-model="form.theme" placeholder="Введите тему чата" list="list-theme"/>
                            <datalist id="list-theme">
                                <option v-for="theme in availableThemes">{{ theme.name }}</option>
                            </datalist>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="chat-status">Статус</label>
                        </b-col>
                        <b-col cols="9">
                            <b-form-select v-model="form.status_id" id="chat-status">
                                <b-form-select-option :value="status.id" v-for="status in availableStatuses" :key="status.id">
                                    {{ status.name }}
                                    <template v-if="status.channel_id">
                                        (
                                        {{ communicationChannels[status.channel_id].name }}
                                        )
                                    </template>
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="chat-type">Тип</label>
                        </b-col>
                        <b-col cols="9">
                            <b-form-select v-model="form.type_id" id="chat-type">
                                <b-form-select-option :value="type.id" v-for="type in availableTypes" :key="type.id">
                                    {{ type.name }}
                                    <template v-if="type.channel_id">
                                        (
                                        {{ communicationChannels[type.channel_id].name }}
                                        )
                                    </template>
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2">
                        <b-col>
                            <communication-chat-message kind='createChat' @send="({message, files}) => onCreateChat(message, files)"/>
                        </b-col>
                    </b-row>
                </b-form>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import Services from "../../../scripts/services/services";
import CommunicationChatMessage from "../communication-chat-message/communication-chat-message.vue";
import VSelect2 from '../controls/VSelect2/v-select2.vue';

export default {
    name: 'communication-chat-creator',
    components: {VSelect2, CommunicationChatMessage},
    props: ['kind','customer', 'roles'],
    data() {
        return {
            users: {},
            form: {
                channel_id: null,
                role_ids: null,
                user_ids: null,
                theme: '',
                status_id: null,
                type_id: null,
            },
            showChatForm: true,
            showUserRoleAndIdInput: true,
        }
    },
    methods: {
        showHideChatForm() {
            this.showChatForm = !this.showChatForm;
        },
        initComponentVar() {
            this.form.user_ids = null;
            this.showChatForm = true;
        },
        initComponent() {
            this.form.channel_id = null;
            this.form.role_ids = null;
            this.form.theme = '';
            this.form.status_id = null;
            this.form.type_id = null;
            this.initComponentVar();
        },
        initUserThemeStatusTypeVar() {
            this.form.user_ids = null;
        },
        initUserThemeStatusType() {
            this.form.theme = '';
            this.form.status_id = null;
            this.form.type_id = null;
            this.initUserThemeStatusTypeVar();
        },
        onCreateChat(message, files) {
            Services.showLoader();
            Services.net().post(this.getRoute('communications.chats.create'), {}, {
                channel_id: this.form.channel_id,
                theme: this.form.theme,
                user_ids: this.form.user_ids,
                status_id: this.form.status_id,
                type_id: this.form.type_id,
                message: message,
                files: files,
            }).then((data)=> {
                Services.event().$emit('updateListEvent', {
                    'chats': data.chats,
                    'users': data.users,
                    'files': data.files,
                });
                Services.hideLoader();
                this.initComponent();
            });
        },
        availableChannelsVar() {
            return this.communicationChannels;
        },
        getUsersByRole() {
            if (this.form.role_ids.length > 0) {
                Services.showLoader();
                Services.net().get(this.getRoute('settings.userListTitle'), {
                    'role_ids': this.form.role_ids
                }).then(data => {
                    this.users = data.users;
                }).finally(() => {
                    Services.hideLoader();
                });
            }

            this.initUserThemeStatusType();
        },
    },
    computed: {
        ...mapGetters(['getRoute']),
        availableChannels() {
            return this.availableChannelsVar();
        },
        availableUsers() {
            return Object.values(this.users).filter(user => {
                return Number(this.form.channel_id) !== this.communicationChannelTypes.internal_email || user.email;
            });
        },
        availableThemes() {
            return Object.values(this.communicationThemes).filter(theme => {
                return !this.form.channel_id ||
                    !theme.channel_id ||
                    Number(theme.channel_id) === Number(this.form.channel_id);
            })
        },
        availableStatuses() {
            return Object.values(this.communicationStatuses).filter(status => {
                return !this.form.channel_id ||
                    !status.channel_id ||
                    Number(status.channel_id) === Number(this.form.channel_id);
            })
        },
        availableTypes() {
            return Object.values(this.communicationTypes).filter(type => {
                return !this.form.channel_id ||
                    !type.channel_id ||
                    Number(type.channel_id) === Number(this.form.channel_id);
            })
        },
    },
    watch: {

    },
    created() {
        switch (this.kind) {
            case 'selectedUser':
                this.showChatForm = false;
                this.showUserRoleAndIdInput = false;
                this.initComponentVar = () => {
                    this.form.user_ids = [this.customer.user_id];
                    this.showChatForm = false;
                };
                this.initUserThemeStatusTypeVar = () => {
                    ;
                },
                this.availableChannelsVar = () => {
                    return Object.values(this.communicationChannels).filter(channel => {
                        return Number(channel.id) !== this.communicationChannelTypes.internal_email || this.customer.email;
                    });
                };
                break;
            default:
                break;
        }
        this.initComponent();
    }
};
</script>

<style scoped>

</style>
