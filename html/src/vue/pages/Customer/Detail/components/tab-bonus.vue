<template>
    <div>
        <form novalidate v-on:submit.prevent.stop="createBonus">
            <div class="row">
                <v-input v-model="bonus.name" class="col-6">Название</v-input>
                <v-input v-model="bonus.value" type="number" class="col-3">Сумма</v-input>
            </div>
            <div class="row">
                <v-select v-model="bonus.status" :options="statusNames" class="col-3">Статус</v-select>


                <div class="col-3">
                    <label for="expiration_date">Срок действия</label>
                    <b-form-input id="expiration_date" v-model="bonus.expiration_date" type="date"></b-form-input>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mt-1">
                    <button type="submit" class="btn btn-success" :disabled="!valid">Добавить бонус</button>
                </div>
            </div>
        </form>

        <table class="table table-sm mt-5">
            <thead>
            <tr class="table-secondary">
                <th colspan="2">
                    Общая информация
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th>Доступных бонусов</th>
                <td>{{ bonuses.available }}</td>
            </tr>
            <tr>
                <th>На удержании</th>
                <td>{{ bonuses.on_hold }}</td>
            </tr>
            <tr>
                <th>Всего</th>
                <td>{{ parseInt(bonuses.available) + parseInt(bonuses.on_hold) }}</td>
            </tr>
            </tbody>
        </table>

        <table class="table table-sm mt-5" v-if="bonuses.items.length > 0">
            <thead>
            <tr class="table-secondary">
                <th>ID</th>
                <th>Дата создания</th>
                <th>Название</th>
                <th>Инициатор</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Срок действия</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in bonuses.items">
                <th>{{ item.id }}</th>
                <td>{{ datetimePrint(item.created_at) }}</td>
                <td>{{ item.name }}</td>
                <td>
                    <template v-if="item.user_id">
                        <a :href="getRoute('settings.userDetail', {id: item.user_id})" target="_blank">{{ userName(item.user_id) }}</a>
                    </template>
                    <template v-else>
                        Маркетплейс
                    </template>
                </td>
                <td>{{ item.value }}</td>
                <td>{{ statusName(item.status) }}</td>
                <td>{{ item.expiration_date ? datePrint(item.expiration_date) : 'Без ограничений' }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import Services from '../../../../../scripts/services/services.js';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';

    export default {
        name: 'tab-bonus',
        components: {
            VInput,
            VSelect
        },
        props: {
            model: Object,
        },
        data() {
            return {
                userNames: {},
                statusNames: {},
                bonuses: {
                    available: null,
                    on_hold: null,
                    items: [],
                },
                bonus: {
                    name: '',
                    value: '',
                    status: '',
                    expiration_date: '',
                },

                STATUS_ON_HOLD: 1,
                STATUS_ACTIVE: 2,
                STATUS_EXPIRED: 3,
            }
        },
        methods: {
            createBonus() {
                Services.net().post(this.getRoute('customers.detail.bonus.add', {id: this.model.id}), {}, this.bonus).then(data => {
                    Services.msg("Бонус добавлен");
                    this.refresh();
                }, () => {
                    Services.msg("Ошибка при добавлении бонуса", 'danger');
                }).finally(data => {
                    Services.hideLoader();
                });
            },
            refresh() {
                Services.showLoader();
                Services.net().get(this.getRoute('customers.detail.bonuses', {id: this.model.id}), {
                    user_id: this.model.user_id,
                }).then(data => {
                    this.bonuses = data.bonuses;
                    this.userNames = data.userNames;
                    this.statusNames = data.statusNames;
                    this.bonus = {
                        name: '',
                        value: '',
                        status: this.STATUS_ACTIVE,
                        expiration_date: '',
                    };
                }).finally(() => {
                    Services.hideLoader();
                })
            },
            userName(id) {
                return (this.userNames && (id in this.userNames)) ? this.userNames[id] : 'N/A';
            },
            statusName(id) {
                return (this.statusNames && (id in this.statusNames)) ? this.statusNames[id] : 'N/A';
            }
        },
        computed: {
            valid() {
                return this.bonus.name
                    && (parseInt(this.bonus.value) > 0)
                    && this.bonus.status;
            }
        },
        created() {
            this.refresh();
        }
    }
</script>
