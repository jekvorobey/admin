<template>
    <div>
        <h4>Настройки</h4>
        <div class="row">
            <v-input
                    v-model="form.billing_cycle"
                    class="col-4"
                    type="number"
                    min="1"
                    step="1"
                    help="период указывается в днях">Биллинговый период</v-input>

            <div class="col-12">
                <button class="btn btn-sm btn-success" :disabled="form.billing_cycle <= 0" @click="saveBillingCycle">Сохранить</button>
            </div>
        </div>

        <hr>
        <h4>Отчеты</h4>
        <table>
            <tr>
                <td>
                <date-picker
                        v-model="billing.period"
                        value-type="format"
                        format="YYYY-MM-DD"
                        range
                        input-class="form-control form-control-sm"
                />
                </td>
                <td>
                    <button class="btn btn-success btn-sm" :disabled="!isPeriod(billing.period)">Сделать внеочередной биллинг</button>
                </td>
            </tr>
        </table>

        <hr>
        <h4 class="mt-4">Биллинг #1</h4>
        <table class="table mt-2">
            <tr>
                <td>Статус</td>
                <td>Сформирован</td>
            </tr>
            <tr>
                <td>Корректировка биллинга</td>
                <td>–</td>
            </tr>
            <tr>
                <td>Документы</td>
                <td>–</td>
            </tr>
        </table>


        <table class="table mt-3">
            <thead>
            <tr>
                <th>Товар</th>
                <th>Ставка комиссии, %</th>
                <th>Комиссия, ₽</th>
            </tr>
            </thead>
            <tbody>
                <tr>

                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import DatePicker from 'vue2-datepicker';
    import 'vue2-datepicker/index.css';
    import 'vue2-datepicker/locale/ru.js';
    import Services from "../../../../../scripts/services/services";

    export default {
        name: 'tab-billing',
        props: ['model'],
        components: {
            VInput,
            DatePicker
        },
        data() {
            return {
                form: {
                    billing_cycle: null,
                },
                billing: {
                    period: null,
                }
            }
        },
        methods: {
            saveBillingCycle() {
                Services.showLoader();
                Services.net().put(this.getRoute('merchant.detail.billing.billing_cycle',
                    {id: this.model.id}),
                    {
                        billing_cycle: this.form.billing_cycle
                    }).then(data => {
                    Services.msg('Изменения успешно сохранены')
                }, () => {
                    Services.msg('Не удалось сохранить изменения', 'danger')
                }).finally(() => {
                    Services.hideLoader();
                })
            },
            isPeriod(period) {
                if (!period || period.length !== 2) {
                    return false;
                }

                return period[0] && period[1];
            }
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('merchant.detail.billing', {id: this.model.id})).then(data => {
                this.form.billing_cycle = data.billing_cycle;
            }).finally(() => {
                Services.hideLoader();
            })
        }
    };
</script>
