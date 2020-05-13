<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="1">
                <h3>Текущий отчетный период <em>({{ period }})</em></h3>
            </th>
            <th colspan="1" style="text-align: right">
                <a href="#" class="btn btn-warning btn-md">
                    Войти под мерчантом <fa-icon icon="eye"/>
                </a>
                <a href="#" class="btn btn-info btn-md">
                    Поиск информации <fa-icon icon="question-circle"/>
                </a>
                <button @click="save()" class="btn btn-success btn-md">
                    Сохранить изменения <fa-icon icon="check"/>
                </button>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th width="400px">Товаров на витрине</th>
            <td>{{ products_count }}</td>
        </tr>
        <tr>
            <th>Принято заказов</th>
            <td>{{ shipments_count }}</td>
        </tr>
        <tr>
            <th>Доставлено заказов</th>
            <td>{{ arrived_count }}</td>
        </tr>
        <tr>
            <th>Продано товаров</th>
            <td>Продано <b>{{ sold_count }}</b> товаров на сумму <b>{{ sold_price }} руб.</b></td>
        </tr>
        <tr>
            <th>Начислено комиссии</th>
            <td>{{ commission }} руб.</td>
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

export default {
    name: 'tab-digest',
    components: {FileInput, VDeleteButton, DatePicker},
    props: ['model'],
    data() {
        return {
            products_count: '',
            shipments_count: '',
            arrived_count: '',
            sold_count: '',
            sold_price: '',
            commission: '',
            comment: '',
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
            this.products_count = data.products_count;
            this.shipments_count = data.shipments_count;
            this.arrived_count = data.arrived_count;
            this.sold_count = data.sold_count
            this.sold_price = data.sold_price;
            this.commission = data.commission;
            this.comment = data.comment;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>