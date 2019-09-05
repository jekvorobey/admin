<template>
    <layout-main>
        <div class="d-flex justify-contend-start align-items-stretch">
            <div>
                <shadow-card title="Мерчант" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('merchantEdit')">
                    <h3>{{ merchant.display_name }}</h3>
                    <span class="badge" :class="[statusClass(merchant.status)]">{{ statusName(merchant.status) }}</span>
                </shadow-card>
                <shadow-card title="Назначенный менеджер" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('managerEdit')" :padding="3">
                    <h5 v-if="manager.id">{{manager.name}}</h5>
                    <p v-else class="text-light rounded bg-danger text-center">Менеджер не назначен</p>
                </shadow-card>
            </div>
            <shadow-card title="Реквизиты" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('legalEdit')">
                <values-table :values="merchantLegalValues" :names="merchantLegalNames"/>
            </shadow-card>
        </div>
        <v-tabs :current="currentTab" :items="tabs" @nav="openTab"/>
        <div v-if="currentTab === 'main'">
            <div class="mt-3 mb-3">
                <button class="btn btn-sm btn-dark">
                    <fa-icon icon="plus"></fa-icon>
                    Добавить оператора
                </button>
            </div>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Телефон</th>
                        <th>Получает СМС</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <tr v-for="operator in operators">
                    <td>{{ operator.name }}</td>
                    <td>{{ operator.phone }}</td>
                    <td>{{ operator.is_receive_sms ? 'Да' : 'Нет' }}</td>
                    <td>
                        <fa-icon icon="pencil-alt"></fa-icon>
                        <fa-icon icon="trash-alt"></fa-icon>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <legal-edit-modal :source="merchant" @edited="replaceMerchant"></legal-edit-modal>
        <merchant-edit-modal :source="merchant" :statuses="options.statuses" @edited="replaceMerchant"></merchant-edit-modal>
        <manager-edit-modal :source="merchant"  @edited="replaceMerchantAndManager"></manager-edit-modal>
    </layout-main>
</template>

<script>
    import ShadowCard from '../../../components/shadow-card.vue';
    import ValuesTable from '../../../components/values-table.vue';
    import VTabs from '../../../components/tabs.vue';
    import modal from '../../../components/controls/modal/modal.vue';
    import VInput from '../../../components/controls/VInput/VInput.vue';

    import LegalEditModal from './components/legal-edit-modal.vue';
    import MerchantEditModal from './components/merchant-edit-modal.vue';
    import ManagerEditModal from './components/manager-edit-modal.vue';

    import modalMixin from '../../../mixins/modal.js';

    export default {
        name: 'page-index',
        mixins: [modalMixin],
        components: {
            ShadowCard,
            ValuesTable,
            VTabs,

            modal,
            VInput,

            LegalEditModal,
            MerchantEditModal,
            ManagerEditModal
        },
        props: {
            iMerchant: {},
            iOperators: Array,
            iManager: {},
            options: {}
        },
        data() {
            return {
                merchant: this.iMerchant,
                operators: this.iOperators,
                manager: this.iManager,
                merchantLegalNames: {
                    correspondent_account: 'Корреспонденский счёт',
                    correspondent_account_bank: 'Корреспонденский банк',
                    inn: 'ИНН',
                    kpp: 'КПП',
                    legal_address: 'Юридический адрес',
                    legal_name: 'Юридическое название',
                    payment_account: 'Расчётный счёт',
                    payment_account_bank: 'Расчётный банк',
                },
                tabs: [
                    {value: 'main', text: 'Операторы'}
                ],
                currentTab: 'main',
            };
        },
        methods: {
            statusName(id) {
                let status = this.options.statuses[id];
                return status ? status.name : 'N/A';
            },
            statusClass(id) {
                switch (id) {
                    case 1:
                        return 'badge-secondary';
                    case 2:
                        return 'badge-info';
                    case 3:
                        return 'badge-warning';
                    case 4:
                        return 'badge-danger';
                    case 5:
                        return 'badge-success';
                }
            },
            startLegalEdit() {
                this.openModal('legalEdit');
            },
            replaceMerchant(merchant) {
                this.$set(this, 'merchant', merchant);
            },
            replaceMerchantAndManager({merchant, manager}) {
                this.$set(this, 'merchant', merchant);
                this.$set(this, 'manager', manager);
            },
            openTab(name) {
                this.currentTab = name;
            }
        },
        computed: {
            merchantLegalValues() {
                return {
                    legal_name: this.merchant.legal_name,
                    legal_address: this.merchant.legal_address,
                    inn: this.merchant.inn,
                    kpp: this.merchant.kpp,
                    correspondent_account: this.merchant.correspondent_account,
                    correspondent_account_bank: this.merchant.correspondent_account_bank,
                    payment_account: this.merchant.payment_account,
                    payment_account_bank: this.merchant.payment_account_bank,
                };
            }
        }
    };
</script>
