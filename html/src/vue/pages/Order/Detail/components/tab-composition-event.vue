<template>
    <table class="table">
        <thead>
        <tr>
            <th>Тип билета</th>
            <th>МК</th>
            <th>Количество</th>
            <th>Стоимость</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="item in order.basket.items">
            <td>{{ item.product.ticket_type_name }}</td>
            <td v-if="canView(blocks.events)">
                <a :href="getRoute('public-event.detail', {event_id: item.id})" target="_blank">
                    {{item.name}}
                </a>
            </td>
            <td v-else>{{ item.name }}</td>
            <td>{{preparePrice(item.qty)}} шт.</td>
            <td>{{preparePrice(item.price)}} руб.</td>
        </tr>
        </tbody>
    </table>
</template>

<script>
    import Services from "../../../../../scripts/services/services";

    export default {
        name: "tab-composition-event",
        props: [
            'model',
        ],
        methods: {
            openTab(tab) {
                Services.event().$emit('showTab', tab);
            },
        },
        computed: {
            order: {
                get() {
                    return this.model
                },
                set(value) {
                    this.$emit('update:model', value)
                },
            }
        }
    }
</script>