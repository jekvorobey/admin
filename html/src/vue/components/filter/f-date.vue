<template>
    <div class="form-group">
        <label :for="id">
            <slot />
            <fa-icon v-if="$slots.help" icon="question-circle" v-b-popover.hover="$slots.help[0].text"></fa-icon>
        </label>
        <date-picker
                :id="id"
                v-bind="$attrs"
                v-on="$listeners"
                :value="value"
                :lang="lang"
                :first-day-of-week="1"
                input-class="form-control form-control-sm"
                value-type="format"
        >
            <template #calendar-icon>
                <fa-icon icon="calendar-alt"></fa-icon>
            </template>
        </date-picker>
    </div>
</template>

<script>
    import DatePicker from 'vue2-datepicker';
    export default {
        name: "f-date",
        components: {
            DatePicker
        },
        inheritAttrs: false,
        props: {
            value: {},
            type: { type: String, default: 'text' }
        },
        data() {
            return  {
                lang: {
                    days: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                    months: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
                    pickers: ['+ 7 дней', '+ 30 дней', '- 7 дней', '- 30 дней'],
                    placeholder: {
                        date: 'Выбрать дату',
                        dateRange: 'Выбрать период'
                    }
                }
            };
        },
        computed: {
            id() {
                return `filter-input-${this._uid}`
            },
        },
        methods: {

        },
    }
</script>

<style scoped>
 label {
     display: block;
 }
 .mx-datepicker {
     width: 100% !important;
 }
</style>
