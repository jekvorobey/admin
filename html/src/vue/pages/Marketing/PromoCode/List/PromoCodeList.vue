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
                    <label for="sponsor">Спонсор</label>
                    <div class="input-group input-group-sm">
                        <select class="form-control" v-model="filter.sponsor" id="sponsor">
                            <option :value="null">-</option>
                            <option value="ibt">Маркетплейс</option>
                            <option value="merchant">Мерчант</option>
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
                <div class="form-group col-lg-3 col-md-6">
                    <label for="creator">Пользователь</label>
                    <div class="input-group input-group-sm">
                        <select class="form-control" v-model="filter.creator" id="creator">
                            <option :value="null">-</option>
                            <option v-for="creator in creators" :value="creator.id">{{ creator.title }}</option>
                        </select>
                    </div>
                </div>
                <f-input v-model="filter.id" class="col-lg-3 col-md-6">ID</f-input>
                <f-input v-model="filter.name" class="col-lg-3 col-md-6">Название</f-input>
                <f-input v-model="filter.code" class="col-lg-3 col-md-6">Код</f-input>
                <div class="form-group col-lg-3 col-md-6">
                    <label for="owner">Реферальный партнер</label>
                    <div class="input-group input-group-sm">
                        <select class="form-control" v-model="filter.owner" id="owner">
                            <option :value="null">-</option>
                            <option v-for="owner in owners" :value="owner.id">{{ owner.title }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12 mt-3">
                <a :href="getRoute('promo-code.create')" class="btn btn-success">Создать промокод</a>
            </div>
        </div>

        <template v-if="selectPromoCodes.length">
            <div class="mb-3">
                Выбрано: {{ selectPromoCodes.length }}
            </div>

            <div class="btn-toolbar mb-3">
                <div class="input-group">
                    <select class="custom-select" v-model="newStatus">
                        <option :value="null">Выбрать статус</option>
                        <option v-for="status in statuses" :value="status.id">{{ status.name }}</option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" :disabled="!newStatus" @click="statusPromoCodes">
                            <fa-icon icon="save"/>
                        </button>
                    </div>
                </div>

                <v-delete-button @delete="deletePromoCodes()" btn-class="btn-danger" class="ml-3"/>

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
                    <th>Код</th>
                    <th>Тип</th>
                    <th>Период действия</th>
                    <th>Спонсор</th>
                    <th>Пользователь</th>
                    <th>Реферальный партнер</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="filtratePromoCodes.length < 1">
                    <td colspan="11" class="text-center">Промокоды не найдены!</td>
                </tr>
                <tr v-if="filtratePromoCodes.length" v-for="(promoCode, index) in filtratePromoCodes">
                    <td>
                        <input v-model="selectPromoCodes" type='checkbox' :value="promoCode.id"/>
                    </td>
                    <td>{{ promoCode.id }}</td>
                    <td>{{ datePrint(promoCode.created_at) }}</td>
                    <td>
                        {{ promoCodeTypeName(promoCode.type) }}
                        <span v-if="promoCode.discount_id">
                            (<a :href="getRoute('discount.detail', {id: promoCode.discount_id})">{{ promoCode.discount_id }}</a>)
                        </span>
                    </td>
                    <td>{{ promoCode.name }}</td>
                    <td>{{ promoCode.code }}</td>
                    <td>{{ promoCode.validityPeriod }}</td>
                    <td>
                        <a v-if="promoCode.merchant_id" :href="getRoute('merchant.detail', {id: promoCode.merchant_id})">
                            Мерчант {{promoCode.merchant_id}}
                        </a>
                        <template v-else>Маркетплейс</template>
                    </td>
                    <td>
                        <a v-if="promoCode.creator" :href="getRoute('settings.userDetail', {id: promoCode.creator.id})">
                            {{ promoCode.creator.title }}
                        </a>
                        <template v-else>-</template>
                    </td>
                    <td>
                        <a v-if="promoCode.owner" :href="getRoute('customers.detail', {id: promoCode.owner.id})">
                            {{ promoCode.owner.id }}: {{ promoCode.owner.title }}
                        </a>
                        <template v-else>Нет</template>
                    </td>
                    <td>{{ promoCodeStatusName(promoCode.status) }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </layout-main>
</template>

<script>
import FDate from '../../../../components/filter/f-date.vue';
import FInput from '../../../../components/filter/f-input.vue';
import FCheckbox from '../../../../components/filter/f-checkbox.vue';
import moment from 'moment';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'page-promo-code-list',
    components: {VDeleteButton, FCheckbox, FInput, FDate,},
    props: ['iPromoCodes', 'types', 'statuses', 'creators', 'owners',],
    data() {
        return {
            filter: {
                created_at: '',
                validity_period: '',
                is_perpetual: false,
                type: null,
                sponsor: null,
                status: null,
                creator: null,
                id: '',
                name: '',
                code: '',
                owner: null,
            },
            total: 0,
            currentPage: this.iCurrentPage,
            promoCodes: [...this.iPromoCodes],
            selectPromoCodes: [],
            newStatus: null,
        }
    },
    methods: {
        deletePromoCodes() {
            Services.showLoader();
            Services.net().delete(this.getRoute('promo-code.delete'), {
                ids: this.selectPromoCodes,
            }).then(() => {
                location.reload();
            })
        },
        statusPromoCodes() {
            Services.showLoader();
            Services.net().post(this.getRoute('promo-code.status'), {}, {
                ids: this.selectPromoCodes,
                status: this.newStatus,
            }).then(() => {
                location.reload();
            })
        },
        promoCodeTypeName(type) {
            return (type in this.types) ? this.types[type]['name'] : 'N/A';
        },
        promoCodeStatusName(status) {
            return (status in this.statuses) ? this.statuses[status]['name'] : 'N/A';
        },
    },
    computed: {
        filtratePromoCodes() {
            let promoCodes = [];

            this.promoCodes.forEach(promoCode => {
                if(this.filter.created_at) {
                    if (this.filter.created_at[0] && this.filter.created_at[1]) {
                        let created_at = moment(promoCode.created_at);
                        let date_from = moment(this.filter.created_at[0]);
                        let date_to = moment(this.filter.created_at[1]).add(1, 'day');
                        if (!(date_from <= created_at && created_at <= date_to)) {
                            return;
                        }
                    }
                }
                if(this.filter.validity_period) {
                    if (this.filter.validity_period[0] && this.filter.validity_period[1]) {
                        let date_from = moment(this.filter.validity_period[0]);
                        let date_to = moment(this.filter.validity_period[1]).add(1, 'day');

                        if (!promoCode.start_date && !promoCode.end_date) {
                            return;
                        }

                        if (promoCode.start_date) {
                            let start_date = moment(promoCode.start_date);
                            if (!(date_from <= start_date && start_date <= date_to)) {
                                return;
                            }
                        }

                        if (promoCode.end_date) {
                            let end_date = moment(promoCode.end_date);
                            if (!(date_from <= end_date && end_date <= date_to)) {
                                return;
                            }
                        }
                    }

                }
                if(this.filter.is_perpetual) {
                    if (promoCode.start_date || promoCode.end_date) {
                        return;
                    }
                }
                if(this.filter.type) {
                    if(promoCode.type !== this.filter.type) {
                        return;
                    }
                }
                if(this.filter.sponsor) {
                    switch (this.filter.sponsor) {
                        case 'ibt':
                            if (promoCode.merchant_id) {
                                return;
                            }
                            break;
                        case 'merchant':
                            if (!promoCode.merchant_id) {
                                return;
                            }
                            break;
                    }
                }
                if(this.filter.status) {
                    if(promoCode.status !== this.filter.status) {
                        return;
                    }
                }
                if(this.filter.creator) {
                    if(promoCode.creator_id !== this.filter.creator) {
                        return;
                    }
                }
                if(this.filter.id) {
                    if (String(promoCode.id).toLowerCase().indexOf(this.filter.id.toLowerCase()) === -1) {
                        return false;
                    }
                }
                if(this.filter.name) {
                    if (promoCode.name.toLowerCase().indexOf(this.filter.name.toLowerCase()) === -1) {
                        return false;
                    }
                }
                if(this.filter.code) {
                    if (promoCode.code.toLowerCase().indexOf(this.filter.code.toLowerCase()) === -1) {
                        return false;
                    }
                }
                if(this.filter.owner) {
                    if(promoCode.owner_id !== this.filter.owner) {
                        return;
                    }
                }

                promoCodes.push(promoCode);
            });
            return promoCodes;
        },
    }
}
</script>
