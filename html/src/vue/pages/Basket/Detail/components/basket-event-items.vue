<template>
    <table class="table">
        <thead>
            <th>МК</th>
            <th>Тип билета</th>
            <th>Количество</th>
            <th>Цена за единицу</th>
        </thead>
        <tbody>
            <tr v-for="basketItem in basket.items">
                <td v-if="canView(blocks.events) && basketItem.event_info">
                    <a :href="getRoute('public-event.detail', { event_id: basketItem.event_info.id })" target="_blank">
                        {{ basketItem.name }}
                    </a>
                </td>
                <td v-else>{{ basketItem.name }}</td>
                <td>{{ basketItem.event_info ? basketItem.event_info.ticket_type_name : 'N/A' }}</td>
                <td>{{ basketItem.qty | integer }} шт.</td>
                <td>{{ preparePrice(basketItem.price / basketItem.qty) }} руб.</td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    name: "basket-event-items",
    props: [
        'model',
    ],
    methods: {

    },
    computed: {
        basket: {
            get() {
                console.dir(this.model);
                return this.model;
            },
            set(value) {
                this.$emit('update:model', value);
            },
        },
    }
}
</script>