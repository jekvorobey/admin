<template>
    <layout-main>
        <div class="card">
            <div class="card-body">
                <b-form @submit.prevent="filterUsers">
                    <b-row class="mb-2">
                        <b-col>
                            <label for="filter-status">Статус</label>
                            <b-form-select v-model="filter.status" id="filter-status">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="id" v-for="(title, id) in statuses" :key="id">
                                    {{ title }}
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                        <b-col>
                            <v-input v-model="filter.phone" v-mask="telMask">Телефон</v-input>
                        </b-col>
                    </b-row>
                    <div class="row mb-2">
                        <v-input v-model="filter.last_name" class="col-md-4 col-12">Фамилия</v-input>
                        <v-input v-model="filter.first_name" class="col-md-4 col-12">Имя</v-input>
                        <v-input v-model="filter.middle_name" class="col-md-4 col-12">Отчество</v-input>
                    </div>

                    <b-button type="submit" variant="dark">Искать</b-button>
                    <b-button type="button" variant="outline-dark" v-if="!isReferral" v-b-modal="modalIdCreateUser">Создать</b-button>
                </b-form>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Статус</th>
                    <th>Телефон</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users">
                    <td><a :href="getRoute('customers.detail', {id: user.id})">{{ user.full_name || 'Не задано' }}</a></td>
                    <td>{{ statuses[user.status] }}</td>
                    <td>{{ user.phone || '-' }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="pager.count > pager.perPage"
                v-model="pager.page"
                :total-rows="pager.count"
                :per-page="pager.perPage"
                class="mt-3 float-right"
        />

        <modal-create-user v-if="!isReferral" :id="modalIdCreateUser"/>
    </layout-main>
</template>

<script>

import VInput from '../../../components/controls/VInput/VInput.vue';
import { telMask } from '../../../../scripts/mask.js';
import Services from '../../../../scripts/services/services.js';
import ModalCreateUser from './components/modal-create-user.vue';

export default {
    components: {ModalCreateUser, VInput},
    props: ['statuses', 'perPage', 'isReferral'],
    data() {
        return {
            modalIdCreateUser: 'modalIdCreateUser',
            filter: {
                status: null,
                phone: '',
                last_name: '',
                first_name: '',
                middle_name: '',
            },

            users: [],
            pager: {
                page: 1,
                count: 1,
                perPage: this.perPage,
            }
        };
    },
    watch: {
        'pager.page': 'filterUsers',
    },
    computed: {
        telMask() {
            return telMask;
        }
    },
    methods: {
        filterUsers() {
            let filter = {};
            if (this.filter.status) {
                filter.status = this.filter.status;
            }
            if (this.filter.phone) {
                filter.phone = this.filter.phone;
            }
            if (this.filter.last_name) {
                filter.last_name = this.filter.last_name;
            }
            if (this.filter.first_name) {
                filter.first_name = this.filter.first_name;
            }
            if (this.filter.middle_name) {
                filter.middle_name = this.filter.middle_name;
            }
            filter.isReferral = this.isReferral ? 1 : 0;
            filter.page = this.pager.page;
            Services.net().get(this.getRoute('customers.filter'), filter).then((data)=> {
                this.users = data.users;
                this.pager.count = data.count;
            });
        }
    },
    created() {
        this.filterUsers();
    }
};
</script>
