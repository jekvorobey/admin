<template>
    <div>
        <div class="card">
            <div class="card-body">
                <b-form @submit.prevent="">
                    <b-row class="mb-2" v-if="roles">
                        <b-col cols="3">
                            <label for="chat-users-role">Роли пользователей</label>
                        </b-col>
                        <b-col cols="9">
                            <v-select2 v-model="form.role_ids" class="form-control form-control-sm" multiple>
                                <b-form-select-option :value="role_id" v-for="(role_name, role_id) in roles" :key="role_id">
                                    {{ role_name }}
                                </b-form-select-option>
                            </v-select2>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label>Мерчант</label>
                        </b-col>
                        <b-col cols="9">
                            <v-select2 v-model="form.merchant_id" class="form-control form-control-sm">
                                <option value="null">Все</option>
                                <option v-for="merchant in merchants" :value="merchant.id">{{ merchant.legal_name }}</option>
                            </v-select2>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2" v-if="usersProp && (!userSendIds || (usersProp.length !== userSendIds.length))">
                        <b-col cols="3">
                            <label for="chat-users">Пользователи</label>
                        </b-col>
                        <b-col cols="9">
                            <v-select2 v-model="form.user_ids" class="form-control form-control-sm" multiple>
                                <option v-for="user in users" :value="user.id">{{ user.title }} - {{ getMerchantName(user.merchant_id) }}</option>
                            </v-select2>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="chat-channel">Канал</label>
                        </b-col>
                        <b-col cols="9">
                            <b-form-select v-model="form.channel_id" id="chat-channel">
                                <b-form-select-option :value="channel.id" v-for="channel in availableChannels" :key="channel.id">
                                    {{ channel.name }}
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                    </b-row>

                    <b-row class="mb-2" v-if="!usersProp && !userSendIds">
                        <b-col cols="3">
                            <label for="chat-users">Пользователи</label>
                            <div class="form-group form-check">
                                <input type="checkbox" v-model="user.accept" id="accept" class="form-check-input">
                                <label class="form-check-label" for="accept">добавить всех</label>
                            </div>
                        </b-col>
                        <b-col cols="9">
                            <v-select2 v-if="user.accept === true" v-model="form.user_ids = Object.keys(users)"
                                    class="form-control form-control-sm" multiple>
                                <option v-for="user in availableUsers" :value="user.id">{{ user.title }} - {{ getMerchantName(user.merchant_id) }}</option>
                            </v-select2>

                            <v-select2 v-else v-model="form.user_ids" class="form-control form-control-sm" multiple>
                                <option v-for="user in availableUsers" :value="user.id">{{ user.title }} - {{ getMerchantName(user.merchant_id) }}</option>
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
    import VSelect2 from '../../controls/VSelect2/v-select2.vue';
    import CommunicationChatMessage from "../communication-chat-message/communication-chat-message.vue";

    import { mapGetters } from 'vuex';
    import Services from "../../../../scripts/services/services";

    export default {
        name: 'communication-chat-creator',
        components: {VSelect2, CommunicationChatMessage},
        props: ['usersProp', 'userSendIds', 'roles', 'merchants'],
        data() {
            let users = this.usersProp ? this.usersProp : {};
            let userIds = this.userSendIds ? this.userSendIds : [];

            return {
                users: users,
                user: {
                    accept: false
                },
                form: {
                    channel_id: null,
                    role_ids: [],
                    user_ids: userIds,
                    theme: '',
                    status_id: null,
                    type_id: null,
                    merchant_id: null,
                },
            }
        },
        methods: {
            initUsers() {
                this.users = this.usersProp ? this.usersProp : {};
                this.form.user_ids = this.userSendIds ? this.userSendIds : [];
            },
            handleSubmit() {
                alert(JSON.stringify(this.user));
            },
            initComponent() {
                this.form.channel_id = null;
                this.form.role_ids = [];
                this.initUsers();
                this.form.theme = '';
                this.form.status_id = null;
                this.form.type_id = null;
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
                    this.initComponent();
                    Services.event().$emit('closeModalCreate');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            getMerchantName(id) {
                let merchant_name;
                Object.values(this.merchants).map(merchant => (
                    merchant.id === id ? merchant_name = merchant.legal_name : null
                ));
                return merchant_name;
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
            availableChannels() {
                if (this.usersProp) {
                    return Object.values(this.communicationChannels).filter(channel => {
                        let hasEmail = true;
                        this.usersProp.forEach(userProp => {
                            hasEmail = this.form.user_ids.includes(userProp.id) ? userProp.email : hasEmail;
                        });
                        return Number(channel.id) !== this.communicationChannelTypes.internal_email || hasEmail;
                    });
                } else {
                    return this.communicationChannels;
                }
            },
            availableUsers() {
                return Object.values(this.users).filter(user => {
                    if (Number(this.form.merchant_id)) {
                        return user.merchant_id == this.form.merchant_id;
                    } else {
                        return true;
                    }
                }).filter(user => {
                    switch (Number(this.form.channel_id)) {
                        case this.communicationChannelTypes.internal_email:
                            return user.email;
                        case this.communicationChannelTypes.smsc:
                            return user.receive_sms !== 0;
                        default: return true;
                    }
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
            'form.role_ids': function (val, oldVal) {
                this.initUsers();

                if (val.length > 0) {
                    Services.showLoader();
                    Services.net().get(this.getRoute('user.byRoles'), {
                        'role_ids': this.form.role_ids
                    }).then(data => {
                        this.users = data.users;
                    }).finally(() => {
                        Services.hideLoader();
                    });
                }
            },
            'form.merchant_id': function () {
                this.initUsers();
                Services.showLoader();
                Services.net().get(this.getRoute('user.byRoles'), {
                    'role_ids': this.form.role_ids
                }).then(data => {
                    this.users = data.users;
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
    };
</script>

<style scoped>

</style>
