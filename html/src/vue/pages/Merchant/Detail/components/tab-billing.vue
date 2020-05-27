<template>
    <div>
        <table>
            <tr>
                <td>
                <date-picker
                        v-model="form.billing.period"
                        value-type="format"
                        format="YYYY-MM-DD"
                        range
                        input-class="form-control form-control-sm"
                />
                </td>
                <td>
                    <button class="btn btn-success btn-sm" :disabled="!isPeriod(form.billing.period)">Сделать внеочередной биллинг</button>
                </td>
            </tr>
        </table>

        <h3 class="mt-4">Биллинг #1</h3>
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
    import DatePicker from 'vue2-datepicker';
    import 'vue2-datepicker/index.css';
    import 'vue2-datepicker/locale/ru.js';

    export default {
        name: 'tab-billing',
        props: ['id'],
        components: {
            DatePicker
        },
        data() {
            return {
                form: {
                    billing: {
                        period: null,
                    }
                }
            }
        },
        methods: {
            isPeriod(period) {
                if (!period || period.length !== 2) {
                    return false;
                }

                return period[0] && period[1];
            }
        },
        created() {

        },
    };
</script>
