<template>
    <div>
        <div class="card">
            <div class="card-body">
                <b-form @submit.prevent="filterChats">
                    <b-row class="mb-2">
                        <b-col>
                            <label for="filter-theme">Тема</label>
                            <b-form-input id="filter-theme" v-model="form.theme" placeholder="Введите тему"/>
                        </b-col>
                        <b-col>
                            <label for="filter-channel">Канал</label>
                            <b-form-select v-model="form.channel_id" id="filter-channel">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="channel.id" v-for="channel in channels" :key="channel.id">
                                    {{ channel.name }}
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                        <b-col>
                            <label for="filter-status">Статус</label>
                            <b-form-select v-model="form.status_id" id="filter-status">
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
                        <b-col>
                            <label for="filter-type">Тип</label>
                            <b-form-select v-model="form.type_id" id="filter-type">
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

                    <b-button type="submit" variant="dark">Искать</b-button>
                </b-form>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Тема</th>
                    <th>Пользователь</th>
                    <th>Канал</th>
                    <th>Статус</th>
                    <th>Тип</th>
                </tr>
            </thead>
            <tbody>
                <template v-for="chat in chats">
                    <tr :class="chat.unread_admin ? 'table-primary' : 'table-secondary'" style="cursor: pointer;" @click="openChat(chat)">
                        <td>{{ chat.theme }}</td>
                        <td>{{ users[chat.user_id].short_name }}</td>
                        <td>{{ channels[chat.channel_id].name }}</td>
                        <td>{{ statuses[chat.status_id].name }}</td>
                        <td>{{ chat.type_id ? types[chat.type_id].name : '-' }}</td>
                    </tr>
                    <tr v-for="message in chat.messages" v-if="showChat === chat.id">
                        <td>{{ users[message.user_id].short_name }}</td>
                        <td colspan="3">{{ message.message }}</td>
                        <td>
                            <div v-for="file in message.files">
                                <a :href="files[file].url" target="_blank">{{ files[file].name }}</a>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="showChat === chat.id">
                        <td colspan="5"><communication-chat-message @send="({files, message}) => onSend(files, message, chat.id)"/></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script>
import Services from '../../../scripts/services/services.js';
import CommunicationChatMessage from '../communication-chat-message/communication-chat-message.vue';

export default {
    name: 'communication-chat-list',
    components: {CommunicationChatMessage},
    props: {
        filter: Object,
    },
    data() {
        return {
            channels: {},
            statuses: {},
            types: {},
            showChat: null,
            form: {
                theme: '',
                channel_id: null,
                status_id: null,
                type_id: null,
            },
            chats: [],
            users: {},
        };
    },
    watch: {
        'form.channel_id': function (val, oldVal) {
            if (this.form.status_id) {
                const status = this.availableStatuses.find(status => status.id === Number(this.form.status_id));
                if (!status) {
                    this.form.status_id = null;
                }
            }

            if (this.form.type_id) {
                const type = this.availableTypes.find(type => type.id === Number(this.form.type_id));
                if (!type) {
                    this.form.type_id = null;
                }
            }
        }
    },
    methods: {
        filterChats() {
            Services.showLoader();
            let filter = {};
            if (this.form.theme) {
                filter.theme = this.form.theme;
            }
            if (this.form.channel_id) {
                filter.channel_id = this.form.channel_id;
            }
            if (this.form.status_id) {
                filter.status_id = this.form.status_id;
            }
            if (this.form.type_id) {
                filter.type_id = this.form.type_id;
            }
            filter = Object.assign(filter, this.filter);
            Services.net().get(this.getRoute('communications.chats.filter'), filter).then((data)=> {
                this.chats = data.chats;
                this.users = data.users;
                this.files = data.files;
                Services.hideLoader();
            });
        },
        openChat(chat) {
            this.showChat = this.showChat === chat.id ? null : chat.id;
            if (this.showChat && chat.unread_admin) {
                chat.unread_admin = false;
                //Services.net().put(this.getRoute('communications.chats.read'), {id: chat.id});
            }
        },
        onSend(files, message, chat_id) {
            Services.net().post(this.getRoute('communications.chats.send'), {}, {
                chat_ids: [chat_id],
                files: files,
                message: message,
            }).then(data => {
                this.users = Object.assign(this.users, data.users);
                this.files = Object.assign(this.files, data.files);
                data.chats.forEach(data_chat => {
                    this.chats.forEach((chat, key) => {
                        if (chat.id === data_chat.id) {
                            this.$set(this.chats, key, data_chat);
                        }
                    })
                });
            });
        },
    },
    computed: {
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
            this.filterChats();
        });
    }
};
</script>

<style scoped>

</style>
