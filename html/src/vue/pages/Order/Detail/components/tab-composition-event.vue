<template>
    <table class="table">
        <thead>
        </thead>
        <tbody>
        <tr v-for="event in orderInfo.publicEvents">
            <td class="inner">
                <table class="table">
                    <tr>
                        <th>МК</th>
                        <th>Тип билета</th>
                        <th>Количество</th>
                        <th>Стоимость</th>
                        <th>Участники</th>
                    </tr>
                    <tr v-for="info in event.ticketsInfo">
                        <td v-if="canView(blocks.events)">
                            <a :href="getRoute('public-event.detail', { event_id: event.id })" target="_blank">
                                {{ info.name }}
                            </a>
                        </td>
                        <td v-else>{{ info.name }}</td>
                        <td>{{ info.ticket_type_name }}</td>
                        <td>{{ info.tickets_qty }} шт.</td>
                        <td>{{ preparePrice(info.price) }} руб.</td>
                        <td class="inner">
                            <table class="table">
                                <tr v-for="ticket in info.tickets">
                                    <td>{{ ticket.first_name }} {{ ticket.middle_name }} {{ ticket.last_name }}</td>
                                    <td>{{ ticket.phone }}</td>
                                    <td>{{ ticket.email }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
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