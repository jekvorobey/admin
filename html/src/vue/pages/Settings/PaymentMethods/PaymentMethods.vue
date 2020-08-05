<template>
    <layout-main>
        <table class="table">
            <thead>
            <tr>
                <th>Способ оплаты</th>
                <th>Макс. доля оплаты</th>
                <th>Макс. за одну операцию</th>
                <th>Поддержка банковских карт</th>
                <th>Ограничения</th>
                <th>Настройка</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="payment_method in payment_methods">
                <td>
                    <h5 class="d-block">{{ payment_method.name }}</h5>
                    <small v-if="payment_method.active"
                           class="d-block text-success">
                        <fa-icon icon="check"/> Активен
                    </small>
                    <small v-else
                           class="d-block text-danger">
                        <fa-icon icon="times"/> Деактивирован
                    </small>
                </td>
                <td>
                    {{ roundValue(payment_method.covers * 100) }}%
                </td>
                <td>
                    {{ preparePrice(payment_method.max_limit) }} руб.
                </td>
                <td>
                    <em v-if="showBankCardsSupport(payment_method).length === 0">
                        Банковские карты не поддерживаются
                    </em>
                    <ul v-else class="list-unstyled">
                        <li v-for="item in showBankCardsSupport(payment_method)">
                            <small class="text-muted">
                                {{ item }}
                            </small>
                        </li>
                    </ul>
                </td>
                <td>
                    <em v-if="showLimitations(payment_method).length === 0">
                        Ограничения не установлены
                    </em>
                    <ul v-else class="list-unstyled">
                        <li v-for="limitation in showLimitations(payment_method)">
                            <small class="text-muted">
                                <fa-icon icon="exclamation-triangle"/> {{ limitation }}
                            </small>
                        </li>
                    </ul>
                </td>
                <td>
                    <button @click="openEditModal(payment_method.id)"
                            class="btn btn-light">
                        <fa-icon icon="cog"/>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <method-edit-modal @saved="updatePaymentMethod"
                           :payment_methods="payment_methods"
                           :editing-method="methodToEdit"
                           :regions="regions"
                           :delivery_services="delivery_services"
                           :offer-statuses="offer_statuses"/>
    </layout-main>
</template>

<script>

    import MethodEditModal from './components/modal-edit-form.vue';
    import Helpers from "../../../../scripts/helpers.js";

    export default {
        components: {
            MethodEditModal
        },
        props: [
            'iMethods',
            'regions',
            'delivery_services',
            'offer_statuses'
        ],
        data() {
            return {
                payment_methods: this.iMethods,
                methodToEdit: {},
            }
        },
        methods: {
            updatePaymentMethod(paymentMethod) {
                Object.assign(this.payment_methods[paymentMethod.id], paymentMethod)
            },
            openEditModal: async function (id) {
                this.methodToEdit = Object.assign(this.methodToEdit, this.payment_methods[id]);
                await this.$nextTick();
                this.$bvModal.show('modal-paymentMethod-edit');
            },
            showBankCardsSupport(method) {
                let cards = {
                    prepaid: 'Предоплаченные карты',
                    virtual: 'Виртуальные карты',
                    real: 'Пластиковые карты',
                    postpaid: 'Дебетовые и кредитные',
                }

                let support = [];
                if (method.accept_prepaid === 1) support.push(cards.prepaid);
                if (method.accept_virtual === 1) support.push(cards.virtual);
                if (method.accept_real === 1) support.push(cards.real);
                if (method.accept_postpaid === 1) support.push(cards.postpaid);

                return support;
            },
            showLimitations(method) {
                let cases = {
                    payment_methods: 'Сочетается не со всеми методами',
                    regions: 'Недоступно в некоторых регионах',
                    delivery_services: 'Недоступно для некоторых Логистических Операторов',
                    offer_statuses: 'Не для всех офферов',
                    customers: 'Недоступно для некоторых клиентов'
                }

                let limitations = [];
                if (method.excluded_payment_methods) limitations.push(cases.payment_methods);
                if (method.excluded_regions) limitations.push(cases.regions);
                if (method.excluded_delivery_services) limitations.push(cases.delivery_services);
                if (method.excluded_offer_statuses) limitations.push(cases.offer_statuses);
                if (method.excluded_customers) limitations.push(cases.customers);

                return limitations;
            },
            roundValue(number) {
                return Helpers.roundValue(number);
            }
        }
    }
</script>

<style scoped>

</style>