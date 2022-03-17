<template>
    <layout-main>
        <table class="table">
            <thead>
            <tr>
                <th>Название</th>
                <th>Активность</th>
                <th v-if="canUpdate(blocks.settings)"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="paymentMethod in paymentMethodsList">
                <td>{{ paymentMethod.name }}</td>
                <td>
                    <b-badge v-if="paymentMethod.active" variant="success">
                        Да
                    </b-badge>
                    <b-badge v-if="!paymentMethod.active" variant="danger">
                        Нет
                    </b-badge>
                </td>
                <td v-if="canUpdate(blocks.settings)">
                    <button @click="openEditModal(paymentMethod.id)"
                            class="btn btn-light">
                        <fa-icon icon="cog"/>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <payment-method-edit-modal @saved="updatePaymentMethod" :editing-model="methodToEdit"/>
    </layout-main>
</template>

<script>
    import PaymentMethodEditModal from './components/payment-method-edit-modal.vue';

    export default {
        components: {
            PaymentMethodEditModal
        },
        props: [
            'iMethods',
        ],
        data() {
            return {
                paymentMethodsList: this.iMethods,
                methodToEdit: null,
            }
        },
        methods: {
            updatePaymentMethod(paymentMethod) {
                Object.assign(this.paymentMethodsList[paymentMethod.id], paymentMethod)
            },
            openEditModal: async function (id) {
                this.methodToEdit = Object.assign({}, this.paymentMethodsList[id]);
                await this.$nextTick();
                this.$bvModal.show('payment-method-edit-modal');
            },
        }
    }
</script>

<style scoped>

</style>
