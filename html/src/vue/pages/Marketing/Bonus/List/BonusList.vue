<template>
    <layout-main>
        <div class="mt-3 mb-3 shadow p-3">
            <div class="row">
                <f-date v-model="filter.created_at" class="col-lg-3 col-md-6" range>
                    Дата создания
                </f-date>
                <f-date v-model="filter.validity_period" class="col-lg-3 col-md-6" range>
                    Период действия
                </f-date>
                <f-checkbox v-model="filter.is_perpetual" class="col-lg-3 col-md-6">Бессрочный</f-checkbox>
                <div class="form-group col-lg-3 col-md-6">
                    <label for="type">Тип</label>
                    <div class="input-group input-group-sm">
                        <select class="form-control" v-model="filter.type" id="type">
                            <option :value="null">-</option>
                            <option v-for="type in types" :value="type.id">{{ type.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group col-lg-3 col-md-6">
                    <label for="status">Статус</label>
                    <div class="input-group input-group-sm">
                        <select class="form-control" v-model="filter.status" id="status">
                            <option :value="null">-</option>
                            <option v-for="status in statuses" :value="status.id">{{ status.name }}</option>
                        </select>
                    </div>
                </div>
                <f-input v-model="filter.id" class="col-lg-3 col-md-6">ID</f-input>
                <f-input v-model="filter.name" class="col-lg-3 col-md-6">Название</f-input>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 mt-3">
                <a :href="getRoute('bonus.create')" class="btn btn-success">Создать новое правило</a>
            </div>
        </div>

        <template v-if="selectBonuses.length">
            <div class="mb-3">
                Выбрано: {{ selectBonuses.length }}
            </div>

            <div class="btn-toolbar mb-3">
                <div class="input-group">
                    <select class="custom-select" v-model="newStatus">
                        <option :value="null">Выбрать статус</option>
                        <option v-for="status in statuses" :value="status.id">{{ status.name }}</option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" :disabled="!newStatus" @click="statusBonuses">
                            <fa-icon icon="save"/>
                        </button>
                    </div>
                </div>

                <v-delete-button @delete="deleteBonuses()" btn-class="btn-danger" class="ml-3"/>

            </div>
        </template>

        <div class="row">
            <table class="table table-hover">
                <thead class="thead-light">
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Дата создания</th>
                    <th>Название</th>
                    <th>Тип</th>
                    <th>Период действия</th>
                    <th>Срок действия (дней)</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="filtrateBonuses.length < 1">
                    <td colspan="8" class="text-center">Бонусы не найдены!</td>
                </tr>
                <tr v-if="filtrateBonuses.length" v-for="(bonus, index) in filtrateBonuses">
                    <td>
                        <input v-model="selectBonuses" type='checkbox' :value="bonus.id"/>
                    </td>
                    <td>{{ bonus.id }}</td>
                    <td>{{ datePrint(bonus.created_at) }}</td>
                    <td>{{ bonus.name }}</td>
                    <td>{{ bonusTypeName(bonus.type) }}</td>
                    <td>{{ bonus.validityPeriod }}</td>
                    <td>{{ validPeriod(bonus.valid_period) }}</td>
                    <td>{{ bonusStatusName(bonus.status) }}</td>
                </tr>
                </tbody>
            </table>
        </div>

    </layout-main>
</template>

<script>
    import axios from 'axios';
    import FDate from '../../../../components/filter/f-date.vue';
    import FInput from '../../../../components/filter/f-input.vue';
    import FCheckbox from '../../../../components/filter/f-checkbox.vue';
    import moment from 'moment';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from '../../../../../scripts/services/services.js';

    export default {
        name: 'page-bonus-list',
        components: {VDeleteButton, FCheckbox, FInput, FDate,},
        props: {
            iBonuses: Array,
            types: Object,
            statuses: Object,
        },
        data() {
            return {
                bonuses: [...this.iBonuses],
                filter: {
                    created_at: '',
                    validity_period: '',
                    is_perpetual: false,
                    type: null,
                    status: null,
                    id: '',
                    name: '',
                },
                selectBonuses: [],
                newStatus: null,
            };
        },
        methods: {
            deleteBonuses() {
                Services.showLoader();
                let uri = this.getRoute('bonus.delete');
                let params = Services.net().params('delete', uri, {ids: this.selectBonuses});
                axios.request(params).then(() => {
                    location.reload();
                }).catch((err) => {
                    let response = err.response.data;
                    let error = response.error;
                    if (!error) {
                        return Services.msg('Ошибка при выполнении операции', 'danger');
                    }

                    let items = error.items;
                    if (!items) {
                        return Services.msg(error.message, 'danger');
                    }

                    let msg = 'Невозможно удалить следующие бонусы, так как они привязаны к промокоду: ';
                    let names = [];
                    for (let i in items) {
                        names.push('«' + this.bonusNames[items[i]] + '»');
                    }
                    msg += names.join(', ');

                    return Services.msg(msg, 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            statusBonuses() {
                Services.showLoader();
                Services.net().post(this.getRoute('bonus.status'), {}, {
                    ids: this.selectBonuses,
                    status: this.newStatus,
                }).then(() => {
                    location.reload();
                }).finally(() => {
                    Services.hideLoader();
                })
            },
            bonusTypeName(type) {
                return (type in this.types) ? this.types[type]['name'] : 'N/A';
            },
            bonusStatusName(status) {
                return (status in this.statuses) ? this.statuses[status]['name'] : 'N/A';
            },
            validPeriod(days) {
                return days ? days : 'Без ограничений';
            }
        },
        computed: {
            bonusNames() {
                return Object.fromEntries(this.iBonuses.map(bonus => [bonus.id, bonus.name]));
            },
            filtrateBonuses() {
                let bonuses = [];

                this.bonuses.forEach(bonus => {
                    if (this.filter.created_at) {
                        if (this.filter.created_at[0] && this.filter.created_at[1]) {
                            let created_at = moment(bonus.created_at);
                            let date_from = moment(this.filter.created_at[0]);
                            let date_to = moment(this.filter.created_at[1]).add(1, 'day');
                            if (!(date_from <= created_at && created_at <= date_to)) {
                                return;
                            }
                        }
                    }

                    if (this.filter.validity_period) {
                        if (this.filter.validity_period[0] && this.filter.validity_period[1]) {
                            let date_from = moment(this.filter.validity_period[0]);
                            let date_to = moment(this.filter.validity_period[1]).add(1, 'day');

                            if (!bonus.start_date && !bonus.end_date) {
                                return;
                            }

                            if (bonus.start_date) {
                                let start_date = moment(bonus.start_date);
                                if (!(date_from <= start_date && start_date <= date_to)) {
                                    return;
                                }
                            }

                            if (bonus.end_date) {
                                let end_date = moment(bonus.end_date);
                                if (!(date_from <= end_date && end_date <= date_to)) {
                                    return;
                                }
                            }
                        }
                    }

                    if (this.filter.is_perpetual) {
                        if (bonus.start_date || bonus.end_date) {
                            return;
                        }
                    }

                    if (this.filter.type) {
                        if (bonus.type !== this.filter.type) {
                            return;
                        }
                    }

                    if (this.filter.id) {
                        if (String(bonus.id).toLowerCase().indexOf(this.filter.id.toLowerCase()) === -1) {
                            return false;
                        }
                    }
                    if (this.filter.name) {
                        if (bonus.name.toLowerCase().indexOf(this.filter.name.toLowerCase()) === -1) {
                            return false;
                        }
                    }

                    if(this.filter.status) {
                        if(bonus.status !== this.filter.status) {
                            return;
                        }
                    }

                    bonuses.push(bonus);
                });

                return bonuses;
            }
        }
    }
</script>
