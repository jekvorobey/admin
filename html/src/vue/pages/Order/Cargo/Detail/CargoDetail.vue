<template>
    <layout-main back>
        <div class="row align-items-stretch justify-content-start cargo-header">
            <div class="col-md-4">
                <div class="shadow p-3 height-100">
                    <h2>Груз #{{ cargo.id }} от {{ cargo.created_at }}</h2>
                    <div style="height: 40px">
                        <cargo-status :status='cargo.status'/>
                        <span class="badge badge-danger" v-if="isCancel">Отменен</span>

                        <template v-if="isCreatedStatus && !isCancel">
                            <button class="btn btn-primary" v-if="isRequestSend"
                                    @click="changeCargoStatus(cargoStatuses.shipped.id)">Груз передан курьеру</button>
                            <button class="btn btn-warning" v-else
                                    title="Задание на забор груза не создано">Груз передан курьеру</button>
                        </template>

                        <b-dropdown text="Действия" class="float-right" size="sm" v-if="!isTakenStatus && !isCancel">
                            <template v-if="isCreatedStatus">
                                <b-dropdown-item-button v-if="isRequestSend" @click="cancelCourierCall()">
                                    Отменить задание на забор груза
                                </b-dropdown-item-button>
                                <b-dropdown-item-button v-if="isRequestSend
                                                        && cargo.delivery_service.support_courier_check === true"
                                                        @click="checkCourierCallStatus()">
                                    Проверить заявку на забор груза
                                </b-dropdown-item-button>
                                <b-dropdown-item-button v-else @click="createCourierCall()">
                                    Создать задание на забор груза
                                </b-dropdown-item-button>
                            </template>
                            <b-dropdown-item-button v-if="isShippedStatus" @click="changeCargoStatus(cargoStatuses.taken.id)">
                                Принят Логистическим Оператором
                            </b-dropdown-item-button>
                            <b-dropdown-item-button v-if="isCreatedStatus" @click="cancelCargo()">
                                Отменить груз
                            </b-dropdown-item-button>
                        </b-dropdown>
                    </div>

                    <p class="text-secondary mt-3">
                        Служба доставки:<span class="float-right">{{ cargo.delivery_service.name }}</span>
                    </p>
                    <p  v-if="cargo.delivery_service.support_courier_check === true"
                        class="text-secondary mt-3">
                        UUID Заявки:
                        <span class="float-right">
                            {{ cargo.xml_id ? cargo.xml_id : 'N/A' }}
                        </span>
                    </p>
                    <p class="text-secondary mt-3">
                        Номер задания на забор груза:
                        <span class="float-right">
                            <template v-if="cargo.delivery_service.support_courier_check === true">
                                {{ cargo.cdek_intake_number ? cargo.cdek_intake_number : 'N/A' }}
                            </template>
                            <template v-else>
                                {{ cargo.xml_id ? cargo.xml_id : 'N/A' }}
                            </template>
                        </span>
                    </p>
                    <p class="mt-3" v-if="cargo.error_xml_id">
                        <b>Последняя ошибка при создании задания на забор груза:</b>
                        <span class="text-danger float-right">{{ cargo.error_xml_id}}</span>
                    </p>
                    <p class="text-secondary mt-3">
                        Последнее изменение:<span class="float-right">{{ cargo.updated_at }}</span>
                    </p>
                    <p class="text-secondary mt-3" v-if="cargo.shipping_problem_comment">
                        Комментарий:<span class="float-right">{{ cargo.shipping_problem_comment }}</span>
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
                        <span class="float-right"><a :href="getRoute('merchantStore.edit', {id: cargo.store.id})">{{cargo.store.name}}</a></span>
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
        isStatus(statusId) {
            return this.cargo.status.id === statusId;
        },
        changeCargoStatus(statusId) {
            let errorMessage = 'Ошибка при изменении статуса груза.';

            Services.showLoader();
            Services.net().put(this.getRoute('cargo.changeStatus', {id: this.cargo.id}), null,
                {'status': statusId}).then(data => {
                if (data.cargo) {
                    this.cargo = data.cargo;
                }
                if (data.result === 'ok') {
                    Services.msg("Изменения сохранены");
                } else {
                    Services.msg(errorMessage + ' ' + data.error, 'danger');
                }
            }).finally(data => {
                Services.hideLoader();
            });
        },
        createCourierCall() {
            let errorMessage = 'Ошибка при создании задания на забор груза.';

            Services.showLoader();
            Services.net().post(this.getRoute('cargo.createCourierCall', {id: this.cargo.id})).then(data => {
                if (data.cargo) {
                    this.cargo = data.cargo;
                }
                if (data.result === 'ok') {
                    Services.msg("Задание на забор груза создано");
                } else {
                    Services.msg(errorMessage, 'danger');
                }
            }).finally(data => {
                Services.hideLoader();
            });
        },
        cancelCourierCall() {
            let errorMessage = 'Ошибка при отмене задания на забор груза.';

            Services.showLoader();
            Services.net().put(this.getRoute('cargo.cancelCourierCall', {id: this.cargo.id})).then(data => {
                if (data.cargo) {
                    this.cargo = data.cargo;
                }
                if (data.result === 'ok') {
                    Services.msg("Задание на забор груза отменено");
                } else {
                    Services.msg(errorMessage, 'danger');
                }
            }).finally(data => {
                Services.hideLoader();
            });
        },
        checkCourierCallStatus() {
            Services.showLoader();
            Services.net().get(this.getRoute('cargo.checkCourierCallStatus', {id: this.cargo.id})).then(data => {
                if (data.cargo) {
                    this.cargo = data.cargo;
                }
                if (data.result === 'ok') {
                    Services.msg("Получена актуальная информация о заявке на вызов курьера");
                } else {
                    Services.msg('Не удалось проверить заявку', 'danger');
                }
            }).finally(data => {
                Services.hideLoader();
            });
        },
        cancelCargo() {
            let errorMessage = 'Ошибка при отмене груза.';

            Services.showLoader();
            Services.net().put(this.getRoute('cargo.cancel', {id: this.cargo.id})).then(data => {
                if (data.cargo) {
                    this.cargo = data.cargo;
                }
                if (data.result === 'ok') {
                    Services.msg("Груз отменен");
                } else {
                    Services.msg(errorMessage, 'danger');
                }
            }).finally(data => {
                Services.hideLoader();
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
        isCreatedStatus() {
            return this.isStatus(this.cargoStatuses.created.id);
        },
        isShippedStatus() {
            return this.isStatus(this.cargoStatuses.shipped.id);
        },
        isTakenStatus() {
            return this.isStatus(this.cargoStatuses.taken.id);
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
