<template>
    <div>
        <div class="card">
            <div class="card-body">
                <b-form @submit.prevent="">
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

                    <b-row class="mb-2" v-if="!showMessageForm">
                        <b-col>
                            <b-button @click="onEditChat(null, null)" class="btn btn-success">Редактировать чат</b-button>
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
    name: 'communication-chat-editor',
    props: ['chat_id', 'channel_id', 'theme', 'status_id', 'type_id'],
    data() {
        return {
            themes: {},
            statuses: {},
            types: {},
            form: {
                theme: this.theme,
                status_id: this.status_id,
                type_id: this.type_id,
            },
        }
    },
    methods: {
        onEditChat(message, files) {
            Services.showLoader();
            Services.net().post(this.getRoute('communications.chats.update'), {}, {
                chat_id: this.chat_id,
                theme: this.form.theme,
                status_id: this.form.status_id,
                type_id: this.form.type_id,
            }).then((data)=> {
                Services.event().$emit('updateListEvent', {
                    'chats': data.chats,
                    'users': data.users,
                    'files': data.files,
                });
                Services.event().$emit('closeModalEdit');
                Services.hideLoader();

            });
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        availableThemes() {
            return Object.values(this.themes).filter(theme => {
                return !this.channel_id ||
                    !theme.channel_id ||
                    Number(theme.channel_id) === Number(this.channel_id);
            })
        },
        availableStatuses() {
            return Object.values(this.statuses).filter(status => {
                return !this.channel_id ||
                    !status.channel_id ||
                    Number(status.channel_id) === Number(this.channel_id);
            })
        },
        availableTypes() {
            return Object.values(this.types).filter(type => {
                return !this.channel_id ||
                    !type.channel_id ||
                    Number(type.channel_id) === Number(this.channel_id);
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
            Services.hideLoader();
        });
    }
};
</script>

<style scoped>

</style>
