<template>
    <div>
        <form novalidate v-on:submit.prevent.stop="createBonus">
            <div class="row">
                <v-input v-model="$v.bonus.name.$model" class="col-12" :error="errorBonusName">Название</v-input>

                <div class="col-12">
                    <label for="bonusMessage">Сообщение для клиента</label>
                    <textarea class="form-control" v-model="bonus.message" id="bonusMessage" :class="{ 'is-invalid': errorBonusMessage }"></textarea>
                    <small class="invalid-feedback" v-if="errorBonusMessage" role="alert">{{ errorBonusMessage }}</small>
                </div>
            </div>
            <div class="row mt-2">
                <v-input v-model="$v.bonus.value.$model" type="number" class="col-3" :error="errorBonusValue">Сумма</v-input>

                <v-select v-model="$v.bonus.status.$model" :options="statusNames" class="col-3" :error="errorBonusStatus">Статус</v-select>

                <div class="col-3" v-if="bonus.status != STATUS_DEBITED">
                    <label for="expiration_date">Срок действия</label>
                    <b-form-input id="expiration_date" v-model="bonus.expiration_date" type="date" :min="today"></b-form-input>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mt-1">
                    <button type="submit" class="btn btn-success">Добавить бонус</button>
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
                <td>{{ item.status === customerBonusStatus.debited ? -item.value : item.value }}</td>
                <td>{{ statusName(item.status) }}</td>
                <td>{{ item.expiration_date ? datePrint(item.expiration_date) : 'Без ограничений' }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {required, minValue, integer} from 'vuelidate/lib/validators';
    import Services from '../../../../../scripts/services/services.js';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';

    export default {
        name: 'tab-bonus',
        components: {
            VInput,
            VSelect
        },
        mixins: [validationMixin],
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
                    message: '',
                },
                afterMounted: false,
                STATUS_ON_HOLD: 1,
                STATUS_ACTIVE: 2,
                STATUS_EXPIRED: 3,
                STATUS_DEBITED: 4,
            }
        },
        validations: {
            bonus: {
                name: {required},
                value: {required, integer, minValue: minValue(1)},
                status: {required},
                message: {required},
            }
        },
        methods: {
            createBonus() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

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
                        message: '',
                    };

                    this.$nextTick(() => {
                        this.$v.$reset();
                    });
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
            today() {
                const today = new Date()
                const tomorrow = new Date(today)
                tomorrow.setDate(tomorrow.getDate() + 1);
                return tomorrow.toISOString().split("T")[0];
            },
            errorBonusName() {
                if (this.$v.bonus.name.$dirty) {
                    if (!this.$v.bonus.name.required) return "Обязательное поле!";
                }
            },
            errorBonusValue() {
                if (this.$v.bonus.value.$dirty) {
                    if (!this.$v.bonus.value.required) return "Обязательное поле!";
                    if (!this.$v.bonus.value.integer) return "Введите целое число!";
                    if (!this.$v.bonus.value.minValue) return "Значение должно быть > 0";
                }
            },
            errorBonusStatus() {
                if (this.$v.bonus.status.$dirty) {
                    if (!this.$v.bonus.status.required) return "Обязательное поле!";
                }
            },
            errorBonusMessage() {
                if (this.$v.bonus.message.$dirty) {
                    if (!this.$v.bonus.message.required) return "Обязательное поле!";
                }
            },
        },
        created() {
            this.refresh();
        },
        watch: {
            'bonus.message': function (val, oldVal) {
                if (!this.afterMounted) {
                    this.afterMounted = true;
                    return;
                }

                this.$v.bonus.message.$touch();
            },
            'bonus.status': function (val, oldVal) {
                if (this.bonus.status == this.STATUS_DEBITED) {
                    this.bonus.expiration_date = null;
                }
            }
        }
    }
</script>
