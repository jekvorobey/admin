<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="1">
                <h3>Текущий отчетный период <em>({{ period }})</em></h3>
            </th>
            <th colspan="1" style="text-align: right">
                <button v-if="canUpdate(blocks.merchants)" @click="authByMerchant" class="btn btn-warning btn-sm">Войти под мерчантом <fa-icon icon="eye"/></button>
                <a :href="masUrl" target="_blank" ref="masAnchor" class="d-none"></a>
                <button @click="save()" class="btn btn-success btn-md" v-if="canUpdate(blocks.merchants)">
                    Сохранить изменения <fa-icon icon="check"/>
                </button>
                <button v-if="!inputMode"
                        class="btn btn-info btn-md"
                        @click="inputMode = !inputMode">
                    Поиск информации <fa-icon icon="question-circle"/>
                </button>
                <div v-else style="float: right">
                    <div class="input-group ml-1">
                        <input type="text"
                               @blur="inputMode = !inputMode"
                               class="form-control"
                               placeholder="Поиск"
                               aria-label="Поиск по ключевым словам"
                               aria-describedby="question_icon">
                        <div class="input-group-append">
                            <span class="input-group-text" id="question_icon">
                                <fa-icon icon="question-circle"/>
                            </span>
                        </div>
                    </div>
                </div>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th width="400px">Товаров на витрине</th>
            <td v-if="products.count">
                <b>{{ products.count }}</b> активных товаров общей стоимостью
                <b>{{ products.price | integer }} руб.</b>
            </td>
            <td v-else>0</td>
        </tr>
        <tr>
            <th>Принято заказов</th>
            <td v-if="orders.count">
                <b>{{ orders.count }}</b> заказов на сумму
                <b>{{ orders.price | integer }} руб.</b>
            </td>
            <td v-else>0</td>
        </tr>
        <tr>
            <th>Доставлено заказов</th>
            <td v-if="shipments.count">
                <b>{{ shipments.count }}</b> заказов на сумму
                <b>{{ shipments.price | integer }} руб.</b>
            </td>
            <td v-else>0</td>
        </tr>
        <tr>
            <th>Продано товаров</th>
            <td v-if="sold.count">
                <b>{{ sold.count }}</b> товаров на сумму
                <b>{{ sold.price | integer }} руб. </b>
            </td>
            <td v-else>0</td>        </tr>
        <tr>
            <th>Начислено комиссии</th>
            <td>{{ commission || 0 | integer }} руб.</td>
        </tr>
        <tr>
            <th>Примечание к мерчанту</th>
            <td><textarea class="form-control"
                          v-model="comment"
                          placeholder="Примечание к мерчанту"/></td>
        </tr>
        </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import 'vue2-datepicker/locale/ru.js';
import FInput from '../../../../components/filter/f-input.vue';
export default {
    name: 'tab-digest',
    components: {FInput, FileInput, VDeleteButton, DatePicker},
    props: ['model'],
    data() {
        return {
            products: {
                count: '',
                price: ''
            },
            orders: {
                count: '',
                price: '',
            },
            shipments: {
                count: '',
                price: '',
            },
            sold: {
                count: '',
                price: '',
            },
            commission: '',
            comment: '',
            inputMode: false,
            masUrl: null,
            operatorUser: null,
        }
    },
    methods: {
        save() {
            Services.showLoader();
            Services.net().put(this.getRoute('merchant.detail.digest.comment',
                {id: this.model.id}),
                {
                    comment: this.comment
                }).then(data => {
                Services.msg('Изменения успешно сохранены')
            }, () => {
                Services.msg('Не удалось сохранить изменения', 'danger')
            }).finally(() => {
                Services.hideLoader();
            })
        },
        authByMerchant() {
            if (this.model.operators.length === 0 ) {
                Services.msg('У мерчанта нет оператора');
                return false;
            }
            this.operatorUser = this.model.operators.filter((operator) => { return operator.is_admin });
            if (!this.operatorUser) {
                this.operatorUser = this.model.operators[0];
            }

            Services.showLoader();
            console.log(this.operatorUser[0].id);
            Services.net().post(this.getRoute('merchant.detail.digest.auth', {id: this.operatorUser[0].id}), null, )
                .then(data => {
                    if (data.url && data.status) {
                        this.masUrl = data.url;
                        setTimeout(() => {
                            this.$nextTick(() => this.$refs.masAnchor.click());
                        }, 1000);
                        Services.msg("Авторизация выполнена");
                    } else {
                        Services.msg('Ошибка при авторизации');
                    }
                }).finally(() => {
                Services.hideLoader();
            })
        },
    },
    computed: {
        merchant: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        /**
         * @returns {string}
         */
        period() {
            let months_rus = [
                'Январь','Февраль','Март','Апрель',
                'Май','Июнь','Июль','Август',
                'Сентябрь','Октябрь','Ноябрь','Декабрь'
            ];
            let today = new Date();
            let year = today.getFullYear();
            let month = today.getMonth();

            return months_rus[month] + ' ' + year;
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('merchant.detail.digest', {id: this.model.id})).then(data => {
            this.products = data.products;
            this.orders = data.orders;
            this.shipments = data.shipments;
            this.sold = data.sold
            this.commission = data.commission;
            this.comment = data.comment;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>
