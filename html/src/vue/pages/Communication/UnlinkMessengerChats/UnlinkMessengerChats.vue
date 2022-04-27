<template>
    <layout-main>
        <table class="table">
            <thead>
            <tr>
                <th>ID чата</th>
                <th>Тема</th>
                <th>Канал</th>
                <th>Последнее сообщение</th>
                <th>Получатель</th>
                <th v-if="canUpdate(blocks.communications)">Действия</th>
            </tr>
            </thead>
            <tbody>
            <template v-for="chat in chats">
                <tr style="cursor: pointer;">
                    <td @click="openChat(chat)">{{ chat.id }}</td>
                    <td @click="openChat(chat)">{{ chat.theme }}</td>
                    <td @click="openChat(chat)">{{ communicationChannels[chat.channel_id].name }}</td>
                    <td @click="openChat(chat)">
                        {{ datePrint(chat.messages[chat.messages.length-1].created_at) }}
                    </td>
                    <td @click="openChat(chat)">{{ chat.external_user_id }}</td>
                    <td v-if="canUpdate(blocks.communications)">
                        <b-button
                                class="btn btn-info btn-sm"
                                v-b-modal.modal-select-user
                                @click="onShowModalSelectUser(chat.id)"
                        >
                            <fa-icon icon="pencil-alt"/> Пользователь
                        </b-button>
                    </td>
                </tr>
                <template v-if="showChat === chat.id">
                    <tr v-for="message in chat.messages">
                        <td colspan="4" v-html="message.message"></td>
                        <td colspan="1">
                            {{ datetimePrint(message.created_at) }}
                        </td>
                    </tr>
                </template>
            </template>
            <tr v-if="!chats.length">
                <td :colspan="6">Чаты отсутствуют</td>
            </tr>
            </tbody>
        </table>

        <b-modal id="modal-select-user" title="Выбрать пользователя" hide-footer>
            <div class="card">
                <div class="card-body">
                    <b-form @submit.prevent="">
                        <b-row class="mb-2">
                            <b-col cols="3">
                                <label for="chat-user">Пользователь</label>
                            </b-col>
                            <b-col cols="9">
                                <v-select2 id="chat-user" v-model="editForm.userId" class="form-control form-control-sm">
                                    <option v-for="user in iUsers" :value="user.id">{{ user.name }}</option>
                                </v-select2>
                            </b-col>

                        </b-row>

                        <b-row class="mb-2">
                            <b-col>
                                <b-button @click="onSelectUser()" class="btn btn-success">Сохранить изменения</b-button>
                            </b-col>
                        </b-row>
                    </b-form>
                </div>
            </div>
        </b-modal>
    </layout-main>
</template>

<script>
    import VSelect2 from '../../../components/controls/VSelect2/v-select2.vue';

    import Services from "../../../../scripts/services/services";

    export default {
        props: ['iChats', 'iUsers'],
        components: {VSelect2},
        data() {
            return {
                showChat: null,
                searchForm: {
                    channel_ids: [],
                },
                chats: this.iChats,
                editForm: {
                    chatId: null,
                    userId: null,
                },
            };
        },
        methods: {
            openChat(chat) {
                this.showChat = this.showChat === chat.id ? null : chat.id;
            },
            onShowModalSelectUser(chatId) {
                this.editForm.chatId = chatId;
                this.$bvModal.show('modal-select-user');
            },
            onSelectUser() {
                Services.showLoader();
                Services.net().put(this.getRoute('communications.chats.updateChatUser'), {}, {
                    chat_id: this.editForm.chatId,
                    user_id: this.editForm.userId,
                }).then((data)=> {
                    this.chats = data.chats;
                    this.$bvModal.hide('modal-select-user');
                    Services.hideLoader();
                });
            },
        },
    };
</script>
