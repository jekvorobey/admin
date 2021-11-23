<template>
    <table class="table">
        <thead>
        <tr>
            <th>Тип билета</th>
            <th>МК</th>
            <th>Количество</th>
            <th>Стоимость</th>
            <th>Участники</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="event in orderInfo.publicEvents">
            <td>{{ event.ticketsInfo.ticket_type_name }}</td>
            <td v-if="canView(blocks.events)">
                <a :href="getRoute('public-event.detail', { event_id: event.id })" target="_blank">
                    {{ event.ticketsInfo.name }}
                </a>
            </td>
            <td v-else>{{ event.ticketsInfo.name }}</td>
            <td>{{ preparePrice(event.ticketsInfo.tickets_qty) }} шт.</td>
            <td>{{ preparePrice(event.ticketsInfo.price) }} руб.</td>
            <td>
                <ul>
                    <li v-for="ticket in event.ticketsInfo.tickets">
                        {{ ticket.first_name }} {{ ticket.middle_name }} {{ ticket.last_name }}
                    </li>
                </ul>
            </td>
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
            orderInfo: {
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