<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
            </div>
            <div class="card-body">
                <b-row class="mb-2">
                    <f-input v-model="searchForm.theme" placeholder="Введите тему" list="themesList" class="col-3">
                        Тема
                    </f-input>
                    <datalist id="themesList">
                        <option v-for="theme in communicationThemes" :value="theme.name"/>
                    </datalist>
                    <f-multi-select v-model="searchForm.channel_ids" :options="communicationChannelsOptions" class="col-3">
                        Канал
                    </f-multi-select>
                    <f-multi-select v-model="searchForm.status_ids" :options="availableStatusesForSearchFormOptions" class="col-3">
                        Статус
                    </f-multi-select>
                    <f-multi-select v-model="searchForm.type_ids" :options="availableTypesForSearchFormOptions" class="col-3">
                        Тип
                    </f-multi-select>
                </b-row>
            </div>
            <div class="card-footer">
                <button @click="filterChats" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 mt-3">
                <button class="btn btn-success" @click="onShowModalCreate()">Создать чат</button>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Тема</th>
                    <th>Пользователь</th>
                    <th>Канал</th>
                    <th>ID коммуникации</th>
                    <th>Статус</th>
                    <th>Тип</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <template v-for="chat in chats">
                    <tr :class="chat.unread_admin ? 'table-primary' : 'table-secondary'" style="cursor: pointer;">
                        <td @click="openChat(chat)">{{ chat.theme }}</td>
                        <td @click="openChat(chat)">{{ userShortName(chat.user_id) }}</td>
                        <td @click="openChat(chat)">{{ communicationChannels[chat.channel_id].name }}</td>
                        <td @click="openChat(chat)">{{ chat.id }}</td>
                        <td @click="openChat(chat)">{{ communicationStatuses[chat.status_id].name }}</td>
                        <td @click="openChat(chat)">{{ chat.type_id ? communicationTypes[chat.type_id].name : '-' }}</td>
                        <td>
                            <b-button
                                    class="btn btn-info btn-sm"
                                    v-b-modal.modal-edit
                                    @click="onShowModalEdit(chat.id, chat.channel_id, chat.theme, chat.status_id, chat.type_id)"
                            >
                                <fa-icon icon="pencil-alt"/>
                            </b-button>
                        </td>
                    </tr>
                    <template v-if="showChat === chat.id">
                        <tr v-for="message in chat.messages">
                            <td>{{ userShortName(message.user_id) }}</td>
                            <td colspan="4">{{ message.message }}</td>
                            <td>
                                <div v-for="file in message.files">
                                    <a :href="files[file].url" target="_blank">{{ files[file].name }}</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5"><communication-chat-message @send="({message, files}) => onSend(message, files, chat.id)"/></td>
                        </tr>
                    </template>
                </template>
                <tr v-if="!chats.length">
                    <td :colspan="6">Чаты отсутствуют</td>
                </tr>
            </tbody>
        </table>

        <b-modal id="modal-create" title="Создание чата" hide-footer>
            <communication-chat-creator :usersProp="usersProp"
                                        :userSendIds="usersProp ? [usersProp[0].id] : null"
                                        :roles="roles"
            />
        </b-modal>
        <b-modal id="modal-edit" title="Редактирование чата" hide-footer>
            <div class="card">
                <div class="card-body">
                    <b-form @submit.prevent="">
                        <b-row class="mb-2">
                            <b-col cols="3">
                                <label for="chat-theme">Тема</label>
                            </b-col>
                            <b-col cols="9">
                                <b-form-input id="chat-theme" v-model="editForm.theme" placeholder="Введите тему чата" list="list-theme"/>
                                <datalist id="list-theme">
                                    <option v-for="theme in availableThemesForEditForm">{{ theme.name }}</option>
                                </datalist>
                            </b-col>
                        </b-row>

                        <b-row class="mb-2">
                            <b-col cols="3">
                                <label for="chat-status">Статус</label>
                            </b-col>
                            <b-col cols="9">
                                <b-form-select v-model="editForm.status_id" id="chat-status">
                                    <b-form-select-option :value="status.id" v-for="status in availableStatusesForEditForm" :key="status.id">
                                        {{ status.name }}
                                        <template v-if="status.channel_id">
                                            ({{ communicationChannels[status.channel_id].name }})
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
                                <b-form-select v-model="editForm.type_id" id="chat-type">
                                    <b-form-select-option :value="type.id" v-for="type in availableTypesForEditForm" :key="type.id">
                                        {{ type.name }}
                                        <template v-if="type.channel_id">
                                            ({{ communicationChannels[type.channel_id].name }})
                                        </template>
                                    </b-form-select-option>
                                </b-form-select>
                            </b-col>
                        </b-row>

                        <b-row class="mb-2">
                            <b-col>
                                <b-button @click="onEditChat(null, null)" class="btn btn-success">Сохранить изменения</b-button>
                            </b-col>
                        </b-row>
                    </b-form>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import FInput from "../../filter/f-input.vue";
    import FMultiSelect from "../../filter/f-multi-select.vue";

    import CommunicationChatCreator from "../communication-chat-creator/communication-chat-creator.vue";
    import CommunicationChatMessage from '../communication-chat-message/communication-chat-message.vue';

    import Services from '../../../../scripts/services/services.js';

    export default {
        name: 'communication-chat-list',
        components: {
            FInput,
            FMultiSelect,
            CommunicationChatCreator,
            CommunicationChatMessage,
        },
        props: {
            filter: Object,
            usersProp: Array,
            roles: Object,
        },
        data() {
            return {
                showChat: null,
                searchForm: {
                    theme: '',
                    channel_ids: [],
                    status_ids: [],
                    type_ids: [],
                },
                editForm: {
                    theme: '',
                    channel_id: null,
                    status_id: null,
                    type_id: null,
                },
                chats: [],
                users: {},
                files: {},
            };
        },
        watch: {
            'searchForm.channel_id': function (val, oldVal) {
                if (this.searchForm.status_id) {
                    const status = this.availableStatusesForSearchFormOptions.find(status => status.value === Number(this.searchForm.status_id));
                    if (!status) {
                        this.searchForm.status_id = null;
                    }
                }

                if (this.searchForm.type_id) {
                    const type = this.availableTypesForSearchFormOptions.find(type => type.value === Number(this.searchForm.type_id));
                    if (!type) {
                        this.searchForm.type_id = null;
                    }
                }
            },
            'editForm.channel_id': function (val, oldVal) {
                if (this.editForm.status_id) {
                    const status = this.availableStatusesForEditFormOptions.find(status => status.value === Number(this.editForm.status_id));
                    if (!status) {
                        this.editForm.status_id = null;
                    }
                }

                if (this.editForm.type_id) {
                    const type = this.availableTypesForEditFormOptions.find(type => type.value === Number(this.editForm.type_id));
                    if (!type) {
                        this.editForm.type_id = null;
                    }
                }
            }
        },
        methods: {
            userShortName(userId) {
                if (!userId) {
                    return 'Маркетплейс';
                }

                return this.users[userId].short_name;
            },
            filterChats() {
                Services.showLoader();
                let filter = {};
                if (this.searchForm.theme) {
                    filter.theme = this.searchForm.theme;
                }
                if (this.searchForm.channel_ids) {
                    filter.channel_ids = this.searchForm.channel_ids;
                }
                if (this.searchForm.status_ids) {
                    filter.status_ids = this.searchForm.status_ids;
                }
                if (this.searchForm.type_ids) {
                    filter.type_ids = this.searchForm.type_ids;
                }
                filter = Object.assign(filter, this.filter);
                Services.net().get(this.getRoute('communications.chats.filter'), filter).then((data) => {
                    this.chats = data.chats;
                    this.users = data.users;
                    this.files = data.files;
                    this.statuses = data.statuses;
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            openChat(chat) {
                this.showChat = this.showChat === chat.id ? null : chat.id;
                if (this.showChat && chat.unread_admin) {
                    chat.unread_admin = false;
                    Services.net().put(this.getRoute('communications.chats.read'), {id: chat.id});
                }
            },
            onSend(message, files, chat_id) {
                Services.net().post(this.getRoute('communications.chats.send'), {}, {
                    message: message,
                    files: files,
                    chat_ids: [chat_id],
                }).then(data => {
                    this.updateChatsList(data.chats, data.users, data.files);
                });
            },
            updateChatsList(chats, users, files) {
                this.users = Object.assign(this.users, users);
                this.files = Object.assign(this.files, files);
                chats.forEach(data_chat => {
                    let keyAdd = false;
                    this.chats.forEach((chat, key) => {
                        if (chat.id === data_chat.id) {
                            this.$set(this.chats, key, data_chat);
                            keyAdd = true;
                        }
                    });
                    if (!keyAdd) {
                        this.chats.push(data_chat);
                    }
                });
            },
            onShowModalEdit(chat_id, channel_id, theme, status_id, type_id) {
                this.editForm.chat_id = chat_id;
                this.editForm.channel_id = channel_id;
                this.editForm.theme = theme;
                this.editForm.status_id = status_id;
                this.editForm.type_id = type_id;
                this.$bvModal.show('modal-edit');
            },
            onCloseModalCreate() {
                this.$bvModal.hide('modal-create');
            },
            onShowModalCreate() {
                this.$bvModal.show('modal-create');
            },
            onEditChat() {
                Services.showLoader();
                Services.net().post(this.getRoute('communications.chats.update'), {}, {
                    chat_id: this.editForm.chat_id,
                    theme: this.editForm.theme,
                    status_id: this.editForm.status_id,
                    type_id: this.editForm.type_id,
                }).then((data)=> {
                    this.updateChatsList(data.chats, data.users, data.files);
                    this.$bvModal.hide('modal-edit');
                    Services.hideLoader();
                });
            },
            availableThemes(form) {
                return Object.values(this.communicationThemes).filter(theme => {
                    return !form.channel_id ||
                        !theme.channel_id ||
                        Number(theme.channel_id) === Number(form.channel_id);
                })
            },
            availableStatuses(form) {
                return Object.values(this.communicationStatuses).filter(status => {
                    return !form.channel_id ||
                        !status.channel_id ||
                        Number(status.channel_id) === Number(form.channel_id);
                })
            },
            availableTypes(form) {
                return Object.values(this.communicationTypes).filter(type => {
                    return !form.channel_id ||
                        !type.channel_id ||
                        Number(type.channel_id) === Number(form.channel_id);
                })
            },
            clearFilter() {
                this.searchForm.theme = '';
                this.searchForm.channel_ids = [];
                this.searchForm.status_ids = [];
                this.searchForm.type_ids = [];
                this.filterChats();
            },
            objectToOptions(object) {
                return Object.values(object).map(innerObject => ({
                    value: innerObject.id,
                    text: innerObject.name
                }));
            },
        },
        computed: {
            communicationChannelsOptions() {
                return this.objectToOptions(this.communicationChannels);
            },
            availableStatusesForSearchFormOptions() {
                return this.objectToOptions(this.availableStatuses(this.searchForm));
            },
            availableTypesForSearchFormOptions() {
                return this.objectToOptions(this.availableTypes(this.searchForm));
            },
            availableThemesForEditForm() {
                return this.availableThemes(this.editForm);
            },
            availableStatusesForEditForm() {
                return this.availableStatuses(this.editForm);
            },
            availableTypesForEditForm() {
                return this.availableTypes(this.editForm);
            },
        },
        created() {
            Services.event().$on('updateListEvent', ({chats, users, files}) => {this.updateChatsList(chats, users, files)});
            Services.event().$on('closeModalCreate', this.onCloseModalCreate);
            this.filterChats();
        }
    };
</script>

<style scoped>

</style>
