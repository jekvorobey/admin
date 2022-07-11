<template>
    <layout-main>
        <table class="table">
            <thead>
            <tr>
                <th>Название</th>
                <th>Активность</th>
                <th>Доступен при сумме</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="paymentMethod in paymentMethodsList">
                <td v-if="canUpdate(blocks.settings)">
                    <a :href="getRoute('settings.paymentMethods.edit', {id: paymentMethod.id})">
                        {{ paymentMethod.name }}
                    </a>
                </td>
                <td v-else>{{ paymentMethod.name }}</td>
                <td>
                    <b-badge :variant="paymentMethod.active ? 'success' : 'danger'">
                        {{ paymentMethod.active ? 'Да' : 'Нет' }}
                    </b-badge>
                </td>
                <td>
                    {{ paymentMethod.min_available_price ? `от ${preparePrice(paymentMethod.min_available_price)} руб.` : '' }}
                    {{ paymentMethod.max_available_price ? `до ${preparePrice(paymentMethod.max_available_price)} руб. ` : '' }}
                    <span v-if="!paymentMethod.min_available_price && !paymentMethod.max_available_price">
                        Без ограничений
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
    </layout-main>
</template>

<script>
    export default {
        props: [
            'iMethods',
        ],
        data() {
            return {
                paymentMethodsList: this.iMethods,
            }
        },
    }
</script>

<style scoped>

</style>
