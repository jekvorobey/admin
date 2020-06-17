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
                        <v-input v-model="filter.full_name" class="col-md-4 col-12">ФИО</v-input>
                        <v-select v-model="filter.gender" :options="genders" class="col-md-4 col-12">Пол</v-select>


                        <f-date v-if="!filter.use_period"
                                v-model="filter.created_at"
                                @change="filter.created_between = []"
                                class="col-md-4 col-12">
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       v-model="filter.use_period"
                                       class="custom-control-input"
                                       id="created_at">
                                <label class="custom-control-label" for="created_at">Дата регистрации</label>
                            </div>
                        </f-date>
                        <f-date v-else
                                v-model="filter.created_between"
                                @change="filter.created_at = []"
                                class="col-md-4 col-12"
                                range confirm>
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       v-model="filter.use_period"
                                       class="custom-control-input"
                                       id="created_between">
                                <label class="custom-control-label" for="created_between">Период регистрации</label>
                            </div>
                        </f-date>
                    </div>
                    <div class="row mb-2">

                    </div>

                    <b-button type="submit" variant="dark">Искать</b-button>
                    <b-button type="button" variant="outline-dark" v-if="!isReferral" v-b-modal="modalIdCreateUser">Создать</b-button>
                    <b-button @click="cleanFilter" type="button" variant="light">Очистить поля</b-button>
                </b-form>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID клиента</th>
                    <th>Дата регистарции</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Сегмент RFM</th>
                    <th>Дата последнего посещения</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users">
                    <td><a :href="getRoute('customers.detail', {id: user.id})">{{ user.id }}</a></td>
                    <td>{{ user.register_date}}</td>
                    <td>{{ user.full_name || 'Не задано' }}</td>
                    <td>{{ user.phone }}</td>
                    <td>{{ user.email || '-' }}</td>
                    <td>{{ user.segment|| '-'  }}</td>
                    <td>{{ user.last_visit|| '-'  }}</td>
                    <td>{{ statuses[user.status] }}</td>
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
import VSelect from '../../../components/controls/VSelect/VSelect.vue';
import VDate from '../../../components/controls/VDate/VDate.vue';
import FDate from '../../../components/filter/f-date.vue';
import { telMask } from '../../../../scripts/mask.js';
import Services from '../../../../scripts/services/services.js';
import ModalCreateUser from './components/modal-create-user.vue';

export default {
    components: {ModalCreateUser, VInput, VSelect, VDate, FDate},
    props: ['statuses', 'perPage', 'isReferral'],
    data() {
        return {
            modalIdCreateUser: 'modalIdCreateUser',
            filter: {
                status: null,
                phone: '',
                full_name: '',
                gender: 0,
                created_between: [],
                created_at: null,
                use_period: false,
            },

            users: [],
            pager: {
                page: 1,
                count: 1,
                perPage: this.perPage,
            },
            genders: {
                0:'Все',
                2:'Мужской',
                1:'Женский'
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
            let filter = {...this.filter};

            filter.isReferral = this.isReferral ? 1 : 0;
            filter.page = this.pager.page;
            Services.net().get(this.getRoute('customers.filter'), filter).then((data)=> {
                this.users = data.users;
                this.pager.count = data.count;
            });
        },
        cleanFilter() {
            Object.keys(this.filter).forEach(key =>
                this.filter[key] = null
            )
        }
    },
    created() {
        this.filterUsers();
    }
};
</script>
