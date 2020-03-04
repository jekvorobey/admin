<template>
    <div>
        <div class="card">
            <div class="card-body">
                <b-form @submit.prevent="">
                    <b-row>
                        <b-col cols="3">
                            <label for="chat-channel">Канал</label>
                        </b-col>
                        <b-col cols="4">
                            <b-form-select v-model="form.channel_id" id="chat-channel">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="channel.id" v-for="channel in channels" :key="channel.id">
                                    {{ channel.name }}
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col cols="3">
                            <label for="chat-theme">Тема</label>
                        </b-col>
                        <b-col cols="4">
                            <b-form-input id="chat-theme" v-model="form.theme" placeholder="Введите тему чата"/>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col cols="3">
                            <label for="chat-status">Статус</label>
                        </b-col>
                        <b-col cols="4">
                            <b-form-select v-model="form.status_id" id="chat-status">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="status.id + 'value'" v-for="status in availableStatuses" :key="status.id + 'key'">
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

                    <b-row>
                        <b-col cols="3">
                            <label for="chat-type">Тип</label>
                        </b-col>
                        <b-col cols="4">
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
                    <b-button type="submit" variant="dark">Добавить чат</b-button>
                </b-form>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import Services from "../../../scripts/services/services";

export default {
    name: 'communication-chat-creator',
    props: ['user_id'],
    data() {
        return {
            channels: {},
            form: {
                channel_id: null,
                theme: '',
                status_id: null,
                type_id: null
            }
        }
    },
    methods: {
        createChat() {
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
    //         // this.filterChats();
        });
    }
};
</script>

<style scoped>

</style>
