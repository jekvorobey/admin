<template>
    <div>
        <div class="card">
            <div class="card-body">
                <b-button @click="showHideChatForm()" type="submit" variant="dark" v-if="!showChatForm">Добавить чат</b-button>
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
                            <b-form-input id="chat-theme" v-model="form.theme" placeholder="Введите тему чата"/>
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
                            <b-button @click="showHideMessageForm()" type="submit" variant="dark" v-if="!showMessageForm">Добавить сообщение к чату</b-button>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2" v-if="showMessageForm">
                        <b-col>
                            <communication-chat-message :type="'createChat'" @createChat="({message, files}) => onCreateChat(message, files)"/>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2" v-if="!showMessageForm">
                        <b-col>
                            <b-button @click="onCreateChat(null, null)" class="btn btn-success">Добавить чат</b-button>
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
    props: ['user_id'],
    data() {
        return {
            channels: {},
            statuses: {},
            types: {},
            showChatForm: false,
            showMessageForm: false,
            form: {
                channel_id: null,
                theme: '',
                status_id: null,
                type_id: null,
            },
        }
    },
    methods: {
        showHideChatForm(chat) {
            if (this.showChatForm) {
                this.showMessageForm = !this.showMessageForm;
            }
            this.showChatForm = !this.showChatForm;
        },
        showHideMessageForm(chat) {
            this.showMessageForm = !this.showMessageForm;
        },
        initComponent() {
            this.channels = {};
            this.statuses = {};
            this.types = {};
            this.showChatForm = false;
            this.showMessageForm = false;
            this.form.channel_id = null;
            this.form.theme = '';
            this.form.status_id = null;
            this.form.type_id = null;
        },
        onCreateChat(message, files) {
            Services.showLoader();
            Services.net().post(this.getRoute('communications.chats.create'), {}, {
                channel_id: this.form.channel_id,
                theme: this.form.theme,
                user_ids: [this.user_id],
                direction: 2,
                status_id: this.form.status_id,
                type_id: this.form.type_id,
                message: message,
                files: files,
            }).then((data)=> {
                // this.chats = data.chats;
                // this.users = data.users;
                // this.files = data.files;
                Services.event().$emit('updateListEvent');
                Services.hideLoader();
                this.initComponent();
            });
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
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
            this.statuses = data.statuses;
            this.types = data.types;
            Services.hideLoader();
        });
    }
};
</script>

<style scoped>

</style>
