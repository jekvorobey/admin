<template>
    <div>
        <div class="card">
            <div class="card-body">
                <b-form @submit.prevent="filterChats">
                    <b-row>
                        <b-col>
                            <label for="filter-theme">Тема</label>
                            <b-form-input id="filter-theme" v-model="form.theme" placeholder="Введите тему"/>
                        </b-col>
                        <b-col>
                            <label for="filter-channel">Канал</label>
                            <b-form-select v-model="form.channel_id" id="filter-channel">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="channel.id" v-for="channel in channels">
                                    {{ channel.name }}
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                        <b-col>
                            <label for="filter-status">Статус</label>
                            <b-form-select v-model="form.status_id" id="filter-status">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="status.id" v-for="status in availableStatuses">
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
                                <b-form-select-option :value="type.id" v-for="type in availableTypes">
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
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import Services from '../../../scripts/services/services.js';

export default {
    name: 'communication-chat-list',
    props: {
        channels: Object,
        statuses: Object,
        types: Object,
        filter: Object,
    },
    data() {
        return {
            form: {
                theme: '',
                channel_id: null,
                status_id: null,
                type_id: null,
            },
            chats: [],
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
            const filter = {

            };
            Services.net().post(this.getRoute('communications.chats.filter'), {}, {filter: filter}).then((data)=> {
                this.chats = data.chats;
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
        this.filterChats();
    }
};
</script>

<style scoped>

</style>