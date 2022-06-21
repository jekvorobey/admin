<template>
    <div>
        <div v-if="!extSystem && canUpdate(blocks.merchants)">
            <v-select class="col-md-4 col-6" :options="extSystemOptions" v-model="extSystemsSelect.driver_id"><h4>
                Выберите интеграцию</h4></v-select>
            <button v-if="extSystemsSelect.driver_id && !is1C(extSystemsSelect.driver_id)" @click="openModal('integrationModal')"
                    class="btn btn-success btn-md">
                Добавить интеграцию
            </button>
            <button v-if="extSystemsSelect.driver_id && is1C(extSystemsSelect.driver_id)" @click="create1C"
                    class="btn btn-success btn-md">
                Добавить интеграцию с 1C
            </button>
        </div>

        <div v-else-if="extSystem">
            <table class="table table-sm">
                <tbody>
                <tr>
                    <th>ID</th>
                    <td>{{ extSystem.id }}</td>
                </tr>
                <tr v-if="isMoySklad(extSystem.driver)">
                    <th>Токен</th>
                    <td>{{ extSystem.connection_params.token }}</td>
                </tr>
                <tr>
                    <th>Логин</th>
                    <td>{{ extSystem.connection_params.login }}</td>
                </tr>
                <tr>
                    <th>Пароль</th>
                    <td>{{ extSystem.connection_params.password }}</td>
                </tr>
                <tr v-if="isFileSharing(extSystem.driver)">
                    <th>Хост</th>
                    <td>{{ extSystem.connection_params.host }}</td>
                </tr>
                <tr v-if="isFileSharing(extSystem.driver)">
                    <th>Порт</th>
                    <td>{{ extSystem.connection_params.port }}</td>
                </tr>
                <tr v-if="isFileSharing(extSystem.driver)">
                    <th>Наименование файла</th>
                    <td>{{ extSystem.connection_params.fileName }}</td>
                </tr>
                <tr v-if="isFileSharing(extSystem.driver)">
                    <th>Период проверки (мин)</th>
                    <td>{{ paramOptions.paramPriceStock.params.period }}</td>
                </tr>
                <tr v-if="isFileSharing(extSystem.driver)">
                    <th>Статус</th>
                    <td>{{ checkStatus(paramOptions.paramPriceStock.active) }}</td>
                </tr>
                <tr v-if="isMoySklad(extSystem.driver)">
                    <th>Интеграция</th>
                    <td>Импорт цен (ID {{ paramOptions.paramPrice.id }})</td>
                    <td>Импорт остатков (ID {{ paramOptions.paramStock.id }})</td>
                    <td>Экспорт заказов (ID {{ paramOptions.paramOrder.id }})</td>
                </tr>
                <tr v-if="isMoySklad(extSystem.driver)">
                    <th>Период проверки (мин)</th>
                    <td>{{ paramOptions.paramPrice.params.period }}</td>
                    <td>{{ paramOptions.paramStock.params.period }}</td>
                    <td>{{ paramOptions.paramOrder.params.period }}</td>
                </tr>
                <tr v-if="isMoySklad(extSystem.driver)">
                    <th>Статус</th>
                    <td>{{ checkStatus(paramOptions.paramPrice.active) }}</td>
                    <td>{{ checkStatus(paramOptions.paramStock.active) }}</td>
                    <td>{{ checkStatus(paramOptions.paramOrder.active) }}</td>
                </tr>
                <tr v-if="isMoySklad(extSystem.driver)">
                    <th>Настройка цены</th>
                    <td>{{ paramOptions.merchantPriceSetting }}</td>
                </tr>
                <tr v-if="isMoySklad(extSystem.driver)">
                    <th>Код Юр.лица</th>
                    <td>{{ paramOptions.merchantOrganizationSetting }}</td>
                </tr>
                <tr v-if="isMoySklad(extSystem.driver)">
                    <th>Код контрагента</th>
                    <td>{{ paramOptions.merchantAgentSetting }}</td>
                </tr>
                <tr v-if="isMoySklad(extSystem.driver)">
                    <th>Код отвественного сотрудника</th>
                    <td>{{ paramOptions.merchantOwnerSetting }}</td>
                </tr>
                <tr>
                    <th>Дата создания</th>
                    <td>{{ datetimePrint(extSystem.created_at) }}</td>
                </tr>
                </tbody>
            </table>
            <button v-if="!is1C(extSystem.driver)" @click="openModal('integrationModal')"
                    class="btn btn-sm btn-success">
                Редактировать
            </button>
        </div>
        <transition name="modal" v-if="canUpdate(blocks.merchants)">
            <modal :close="closeModal" v-if="isModalOpen('integrationModal')">
                <div slot="header">
                    {{ extSystem ? 'Редактирование интеграции' : 'Добавление интеграции' }}
                </div>
                <div slot="body">
                    <integration-modal :id="id" :extSystem="extSystem" :options="paramOptions"
                                       :extSystemsSelect="extSystemsSelect"
                                       @onSave="onIntegrationSave"></integration-modal>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>

import Services from "../../../../../scripts/services/services";
import {mapActions} from "vuex";
import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
import IntegrationModal from '../components/tab-ext-systems-components/integration-modal.vue';
import modalMixin from '../../../../mixins/modal.js';
import Modal from '../../../../components/controls/modal/modal.vue';

export default {
    mixins: [modalMixin],
    components: {
        VSelect,
        IntegrationModal,
        Modal,
    },
    name: 'tab-ext-systems',
    props: ['id'],
    data() {
        return {
            extSystem: null,
            extSystemsOptions: [],
            paramOptions: {},
            extSystemsSelect: {
                driver_id: this.extSystem ? this.extSystem.driver : null,
            },
        }
    },
    methods: {
        ...mapActions({
            showMessageBox: 'modal/showMessageBox',
        }),
        loadExtSystem() {
            Services.showLoader();
            Services.net().get(this.getRoute('merchant.detail.extSystems', {id: this.id})).then(data => {
                this.extSystem = data.extSystem ? data.extSystem : null;
                this.extSystemsOptions = data.extSystemsOptions;
                this.paramOptions = data.paramOptions;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        create1C() {
            Services.showLoader();
            let formData = {
                driver: this.extSystemsSelect.driver_id,
            };
            Services.net().post(
                this.getRoute('merchant.detail.extSystems.store', {id: this.id}), {}, formData
            ).then(() => {
                Services.msg('Интеграция с 1C успешно создана');
                this.loadExtSystem();
            }).finally(() => {
                Services.hideLoader();
            })
        },
        is1C(driverId) {
            return parseInt(driverId) === this.merchantExtSystemDrivers.one_c
        },
        isMoySklad(driverId) {
            return parseInt(driverId) === this.merchantExtSystemDrivers.moysklad
        },
        isFileSharing(driverId) {
            return parseInt(driverId) === this.merchantExtSystemDrivers.filesharing
        },
        onIntegrationSave() {
            this.loadExtSystem();
        },
        checkStatus(status) {
            return status ? 'Активна' : 'Неактивна';
        },
    },
    computed: {
        extSystemOptions() {
            let options = Object.values(this.extSystemsOptions)
                .map(extSystem => ({value: extSystem.id, text: extSystem.name}));
            options.unshift({value: null, text: '-'});

            return options;
        },
    },
    created() {
        this.loadExtSystem();
    }
}
</script>
