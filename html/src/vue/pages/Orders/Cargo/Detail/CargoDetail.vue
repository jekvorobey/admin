<template>
    <layout-main back>
        <div class="row align-items-stretch justify-content-start cargo-header">
            <div class="col-md-4">
                <div class="shadow p-3 height-100">
                    <h2>Груз #{{ cargo.id }} от {{ cargo.created_at }}</h2>
                    <div style="height: 40px">
                        <span class="badge" :class="statusClass(cargo.status.id)">
                            {{ cargo.status.name || 'N/A' }}
                        </span>
                        <span class="badge badge-danger" v-if="isCancel">Отменен</span>
                        <button class="btn btn-primary" v-if="isRequestSend"
                                @click="changeCargoStatus(2)">Груз передан курьеру</button>
                        <button class="btn btn-primary" v-if="!isCancel"
                                @click="cancelCargo()">Отменить</button>
                    </div>

                    <p class="text-secondary mt-3">
                        Служба доставки:<span class="float-right">{{ cargo.delivery_service.name }}</span>
                    </p>
                    <p class="text-secondary mt-3">
                        Последнее изменение:<span class="float-right">{{ cargo.updated_at }}</span>
                    </p>
                    <p class="text-secondary mt-3" v-if="isShippingProblem">
                        Описание проблемы:<span class="float-right">{{ cargo.shipping_problem_comment }}</span>
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="shadow p-3 height-100">
                    <h2>
                        Сумма груза {{preparePrice(cargo.cost)}} руб.
                        <fa-icon icon="question-circle" v-b-popover.hover="tooltipShipmentCost"></fa-icon>
                    </h2>
                    <p class="text-secondary" v-if="cargo.discount">
                        Скидка:  <span class="float-right text-danger measure">руб. </span>
                        <span class="float-right text-danger">-{{cargo.discount}}</span>
                    </p>
                    <p class="text-secondary mt-3">
                        Сумма без скидки: <span class="float-right text-danger measure">руб.</span>
                        <span class="float-right text-danger">{{preparePrice(cargo.cost_without_discount)}}</span>
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="shadow p-3 height-100">
                    <h2>{{cargo.total_qty}} ед. товара</h2>
                    <div style="height: 40px">
                        <span class="float-right text-secondary"> кол-во коробок: {{cargo.package_qty || 0}} шт.</span>
                    </div>
                    <p class="text-secondary">
                        Вес:<span class="float-right">{{ cargo.weight }} г</span>
                    </p>
                    <p class="text-secondary mt-3">
                        Склад отгрузки:
                        <span class="float-right">{{cargo.store.name}}</span>
                    </p>
                </div>
            </div>
        </div>

        <v-tabs :current="nav.currentTab" :items="nav.tabs" @nav="tab => nav.currentTab = tab"></v-tabs>
        <shipments-tab
                v-if="nav.currentTab === 'shipments'"
                :cargo="cargo"
                @onChange="onChange"
        ></shipments-tab>
        <history-tab
                v-if="nav.currentTab === 'history'"
                :history="cargo.history"
        ></history-tab>
    </layout-main>
</template>

<script>

    import Services from '../../../../../scripts/services/services';
    import modalMixin from '../../../../mixins/modal.js';

    import VTabs from '../../../../components/tabs/tabs.vue';
    import ShipmentsTab from './components/shipments-tab.vue';
    import HistoryTab from './components/history-tab.vue';
    import Modal from '../../../../components/controls/modal/modal.vue';

    export default {
    components: {
        VTabs,
        Modal,

        ShipmentsTab,
        HistoryTab,
    },
    mixins: [modalMixin],
    props: {
        iCargo: {},
    },
    data() {
        return {
            cargo: this.iCargo,

            nav: {
                currentTab: 'shipments',
                tabs: [
                    {value: 'shipments', text: 'Состав груза'},
                    {value: 'history', text: 'История'},
                ]
            }
        };
    },

    methods: {
        statusClass(statusId) {
            switch (statusId) {
                case 1: return 'badge-info';
                case 2: return 'badge-primary';
                case 3: return 'badge-success';
                default: return 'badge-light';
            }
        },
        isStatus(statusId) {
            return this.cargo.status.id === statusId;
        },
        changeCargoStatus(statusId) {
            let errorMessage = 'Ошибка при изменении статуса груза.';

            Services.net().put(this.getRoute('cargo.changeStatus', {id: this.cargo.id}), null,
                {'status': statusId}).then(data => {
                if (data.result === 'ok') {
                    this.cargo = data.cargo;
                } else {
                    this.showMessageBox({title: 'Ошибка', text: errorMessage + ' ' + data.error});
                }
            }, () => {
                this.showMessageBox({title: 'Ошибка', text: errorMessage});
            });
        },
        cancelCargo() {
            let errorMessage = 'Ошибка при изменении отмене груза.';

            Services.net().put(this.getRoute('cargo.cancel', {id: this.cargo.id})).then(data => {
                if (data.result === 'ok') {
                    this.cargo = data.cargo;
                } else {
                    this.showMessageBox({title: 'Ошибка', text: errorMessage + ' ' + data.error});
                }
            }, () => {
                this.showMessageBox({title: 'Ошибка', text: errorMessage});
            });
        },
        onChange(data) {
            this.cargo = data.cargo;
        },
        onReportProblem(data) {
            this.onChange(data);

            this.closeModal();
        },
    },
    computed: {
        tooltipShipmentCost() {
            return 'С учётом скидки';
        },
        isRequestSend() {
            return this.cargo.xml_id;
        },
        isShippingProblem() {
            return this.cargo.is_problem;
        },
        isCancel() {
            return this.cargo.is_canceled;
        },
    },
};
</script>
<style scoped>
    .cargo-header {
        min-height: 200px;
    }
    .cargo-header > div {
        padding: 16px 0 16px 16px;
    }
    .cargo-header img {
        max-height: calc( 200px - 16px * 2 );
    }
    .cargo-header p {
        margin: 0;
        padding: 0;
    }
    .height-100 {
        min-height: 100%;
        height: 100%;
    }
    .measure {
        width: 30px;
        margin-left: 10px;
    }
</style>
