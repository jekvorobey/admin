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
                            <b-form-select v-model="form.channel_id" id="chat-channel">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="channel.id" v-for="channel in channels" :key="channel.id">
                                    {{ channel.name }}
                                </b-form-select-option>
                            </b-form-select>
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

                    <b-row class="mb-2" v-if="showUsersInput">
                        <b-col cols="3">
                            <label for="chat-users">Пользователи</label>
                        </b-col>
                        <b-col cols="9">
                            <b-form-select v-model="form.user_ids" multiple id="chat-users">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="user.id" v-for="user in users" :key="user.id">
                                    {{ user.short_name + ': ' + user.phone }}
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="chat-status">Статус</label>
                        </b-col>
                        <b-col cols="9">
                            <b-form-select v-model="form.status_id" id="chat-status">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="status.id" v-for="status in availableStatuses" :key="status.id">
                                    {{ status.name }}
                                    <template v-if="status.channel_id">
                                        (
                                        {{ channels[status.channel_id].name }}
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
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="type.id" v-for="type in availableTypes" :key="type.id">
                                    {{ type.name }}
                                    <template v-if="type.channel_id">
                                        (
                                        {{ channels[type.channel_id].name }}
                                        )
                                    </template>
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2">
                        <b-col>
                            <communication-chat-message :type="'createChat'" @createChat="({message, files}) => onCreateChat(message, files)"/>
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

export default {
    name: 'communication-chat-creator',
    components: {CommunicationChatMessage},
    props: ['kind','user_id'],
    data() {
        return {
            channels: {},
            users: {},
            statuses: {},
            types: {},
            form: {
                channel_id: null,
                theme: '',
                user_ids: null,
                status_id: null,
                type_id: null,
            },
            showChatForm: true,
            showUsersInput: true,
        }
    },
    methods: {
        showHideChatForm(chat) {
            if (this.showChatForm) {
                this.showMessageForm = !this.showMessageForm;
            }
            this.showChatForm = !this.showChatForm;
        },
        initComponentVar() {
            this.form.user_ids = null;
            this.showChatForm = true;
        },
        initComponent() {
            this.form.channel_id = null;
            this.form.theme = '';
            this.form.status_id = null;
            this.form.type_id = null;
            this.initComponentVar();
        },
        onCreateChat(message, files) {
            Services.showLoader();
            Services.net().post(this.getRoute('communications.chats.create'), {}, {
                channel_id: this.form.channel_id,
                theme: this.form.theme,
                user_ids: this.form.user_ids,
                direction: 2,
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
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        availableThemes() {
            return Object.values(this.themes).filter(theme => {
                return !this.form.channel_id ||
                    !theme.channel_id ||
                    Number(theme.channel_id) === Number(this.form.channel_id);
            })
        },
        availableStatuses() {
            return Object.values(this.statuses).filter(status => {
                return !this.form.channel_id ||
                    !status.channel_id ||
                    Number(status.channel_id) === Number(this.form.channel_id);
            })
        },
        availableTypes() {
            return Object.values(this.types).filter(type => {
                return !this.form.channel_id ||
                    !type.channel_id ||
                    Number(type.channel_id) === Number(this.form.channel_id);
            })
        },
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('communications.chats.directories')).then(data => {
            this.channels = data.channels;
            this.themes = data.themes;
            this.statuses = data.statuses;
            this.types = data.types;
            this.users = data.users;
            Services.hideLoader();
        });
        switch (this.kind) {
            case 'selectedUser':
                this.showChatForm = false;
                this.showUsersInput = false;
                this.test1 = 'new1';
                this.initComponentVar = () => {
                    this.test2 = 'new2';
                    this.form.user_ids = [this.user_id];
                    this.showChatForm = false;
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
