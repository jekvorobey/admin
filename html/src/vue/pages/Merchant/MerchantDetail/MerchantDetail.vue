<template>
    <layout-main>
        <div class="d-flex justify-contend-start align-items-stretch">
            <div>
                <shadow-card title="Мерчант">
                    <h3>{{ merchant.display_name }}</h3>
                    <span class="badge" :class="[statusClass(merchant.status)]">{{ statusName(merchant.status) }}</span>
                </shadow-card>
                <shadow-card title="Назначенный менеджер" :edit-btn="true">
                    <h5>Вышненижний Виталий Аркадьевич</h5>
                </shadow-card>
            </div>
            <shadow-card title="Реквизиты" :edit-btn="true" @on-edit="startLegalEdit">
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
    </layout-main>
</template>

<script>
    import ShadowCard from '../../../components/shadow-card.vue';
    import ValuesTable from '../../../components/values-table.vue';
    import VTabs from '../../../components/tabs.vue';
    import modalMixin from '../../../mixins/modal.js'

    export default {
        name: 'page-index',
        mixins: [modalMixin],
        components: {
            ShadowCard,
            ValuesTable,
            VTabs
        },
        props: {
            iMerchant: {},
            iOperators: Array,
            options: {}
        },
        data() {
            return {
                merchant: this.iMerchant,
                operators: this.iOperators,
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
                this.showMessageBox({text: "Banana!", title: "fuk"})
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
