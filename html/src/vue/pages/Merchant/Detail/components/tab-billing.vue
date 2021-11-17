<template>
  <div>
    <billing-report :model.sync="model" :type="billingReportType.billing"></billing-report>
    <hr>
    <div class="card">
      <div class="card-header">
        Фильтр
        <button @click="toggleHiddenFilter" class="btn btn-sm btn-light float-right">
          {{ opened ? 'Меньше' : 'Больше' }} фильтров
          <fa-icon :icon="opened ? 'compress-arrows-alt' : 'expand-arrows-alt'"></fa-icon>
        </button>
      </div>
      <div class="card-body">
        <div class="row">
          <f-input v-model="filter.offer_id" class="col-3">
            ID оффера
          </f-input>
          <f-input v-model="filter.order_id" class="col-3">
            ID заказа
          </f-input>

          <f-input v-model="filter.price_from" type="number" class="col-3">
            Текущая цена оффера
            <template #prepend><span class="input-group-text">от</span></template>
            <template #append><span class="input-group-text">руб.</span></template>
          </f-input>
          <f-input v-model="filter.price_to" type="number" class="col-3">
            &nbsp;
            <template #prepend><span class="input-group-text">до</span></template>
            <template #append><span class="input-group-text">руб.</span></template>
          </f-input>
        </div>
        <transition name="slide">
          <div v-if="opened" class="additional-filter pt-3 mt-3">
            <div class="row">
              <f-input v-model="filter.name" class="col-6">
                Название оффера
              </f-input>
              <f-date v-model="filter.status_at" class="col-6" range confirm>
                Дата продажи
              </f-date>
            </div>
            <div class="row">
              <f-input v-model="filter.percent_from" type="number" class="col-3">
                Комиссия оператора
                <template #prepend><span class="input-group-text">от</span></template>
                <template #append><span class="input-group-text">%.</span></template>
              </f-input>
              <f-input v-model="filter.percent_to" type="number" class="col-3">
                &nbsp;
                <template #prepend><span class="input-group-text">до</span></template>
                <template #append><span class="input-group-text">%.</span></template>
              </f-input>


              <f-input v-model="filter.commission_from" type="number" class="col-3">
                Выплата мерчанту
                <template #prepend><span class="input-group-text">от</span></template>
                <template #append><span class="input-group-text">р.</span></template>
              </f-input>
              <f-input v-model="filter.commission_to" type="number" class="col-3">
                &nbsp;
                <template #prepend><span class="input-group-text">до</span></template>
                <template #append><span class="input-group-text">р.</span></template>
              </f-input>
            </div>

            <div class="row">
              <f-input v-model="filter.discount_from" type="number" class="col-3">
                Скидка
                <template #prepend><span class="input-group-text">от</span></template>
                <template #append><span class="input-group-text">%.</span></template>
              </f-input>
              <f-input v-model="filter.discount_to" type="number" class="col-3">
                &nbsp;
                <template #prepend><span class="input-group-text">до</span></template>
                <template #append><span class="input-group-text">%.</span></template>
              </f-input>

            </div>
          </div>
        </transition>
      </div>
      <div class="card-footer">
        <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
        <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
      </div>
    </div>
    <div class="row mb-3" v-if="canUpdate(blocks.merchants)">
      <div class="col-12 mt-3">
        <b-button class="btn btn-primary btn-sm" @click="openCorrectionModal()">
          Скорректировать биллинг <fa-icon icon="edit"/>
        </b-button>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-condensed">
      <thead>
      <tr>
        <th>Продажа</th>
        <th>Название</th>
        <th>Статус</th>
        <th>Цена, р</th>
        <th>Кол-во</th>
        <th>Скидка, р</th>
        <th>Бонусы</th>
        <th>Цена на витрине, р</th>
        <th>Комиссия Оператора, %</th>
        <th>Акционная Комиссия Оператора, %</th>
        <th>Вознаграждение Оператора, руб</th>
        <th>Выплата Мерчанту, руб</th>
        <th v-if="canUpdate(blocks.merchants)">Действия</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="billingOperation in billingList">
        <td v-if="billingOperation.order_id">
          <a  :href="$store.getters.getRoute('orders.detail', {id:billingOperation.order_id})">{{ billingOperation.status_at }}</a>
        </td>
        <td v-else>
          {{ billingOperation.status_at }}
        </td>
        <td v-if="billingOperation.offer_id">
          <a :href="$store.getters.getRoute('offers.detail', {id:billingOperation.offer_id})">{{ billingOperation.name }}</a>
        </td>
        <td v-else>
          <a v-if="billingOperation.document_id"
             :href="$store.getters.getRoute('merchant.detail.download-correction-document',
          {id:getMerchantId, fileId:billingOperation.document_id})">
              {{ availableTypes[billingOperation.correction_type].text }}
            </a>
            <p v-else >{{ availableTypes[billingOperation.correction_type].text }}</p>
          </td>
          <td>
            <b-badge v-if="billingOperation.shipment_status === 2" variant="danger">Отменен</b-badge>
            <b-badge v-if="billingOperation.shipment_status === 1" variant="success">Доставлен</b-badge>
            <b-badge v-if="billingOperation.shipment_status === 3" variant="warning">Возврат</b-badge>
          </td>
          <td>{{ billingOperation.cost }}</td>
          <td>{{ billingOperation.qty }}</td>

          <td v-if="(billingOperation.discounts && billingOperation.discounts.hasOwnProperty('marketplace')) || (billingOperation.discounts && billingOperation.discounts.hasOwnProperty('merchant'))">
            {{ billingOperation.discounts.hasOwnProperty('marketplace') && billingOperation.discounts.marketplace.sum > 0 ? 'М-с:' + billingOperation.discounts.marketplace.sum : '' }}
            {{ billingOperation.discounts.hasOwnProperty('merchant') && billingOperation.discounts.merchant.sum > 0 ? 'М-т:' + billingOperation.discounts.merchant.sum : ''  }}
          </td>
          <td v-else>0</td>
          <td>{{ billingOperation.bonuses && billingOperation.bonuses.hasOwnProperty('bonus_discount') ? billingOperation.bonuses.bonus_discount : 0 }}</td>
          <td>{{ billingOperation.price }}</td>
          <td>{{ billingOperation.percent ? billingOperation.percent : 0 }}</td>
          <td>{{ billingOperation.action_percent ? billingOperation.action_percent : '-' }}</td>
          <td>{{ billingOperation.commission }}</td>
          <td>{{ billingOperation.price - billingOperation.commission }}</td>
          <td v-if="canUpdate(blocks.merchants)">
            <b-button v-if="billingOperation.shipment_status === 3" class="btn btn-danger btn-sm" @click="deleteOperation(billingOperation.id)">
              Удалить
            </b-button>
            <b-button v-else-if="billingOperation.shipment_status === 1" class="btn btn-warning btn-sm" @click="addReturn(billingOperation.id)">
              Возврат
            </b-button>
          </td>
        </tr>
        <tr v-if="!billingList.length">
          <td :colspan="billingList.length + 1">Заказы отсутствуют</td>
        </tr>
        </tbody>
      </table>
    </div>
    <b-pagination v-if="pager.last_page > 1"
                  v-model="currentPage"
                  :total-rows="pager.total"
                  :per-page="pager.per_page"
                  :hide-goto-end-buttons="pager.last_page < 10"
                  class="float-right"
    ></b-pagination>

    <transition name="modal">
      <modal :close="closeModal" v-if="isModalOpen('BillingCorrectionModal')">
        <div slot="header">
          Корректировка биллинг-операции
        </div>
        <div slot="body">
          <div class="form-group">
            <f-date v-model="$v.correctionForm.billingDate.$model" :error="errorDate">Дата</f-date>
            <v-input v-model="$v.correctionForm.correctionSum.$model" :error="errorSum">Выплата мерчанту (может быть отрицательной)</v-input>
            <v-select v-model="correctionForm.correctionType"
                      label="label"
                      :options="availableTypes"
                      help="Обязательное поле">
              Тип корректировки
            </v-select>
            <a v-if="correctionForm.file_id"
               :href="$store.getters.getRoute('merchant.detail.download-correction-document',
            {id:getMerchantId, fileId:correctionForm.file_id})">
              {{ correctionForm.file_name }}
            </a>
            <file-input destination="billing-correction-document" @uploaded="onFileUpload">Прикрепить документ</file-input>
          </div>
          <div class="form-group">
            <button @click="onSave" type="button" class="btn btn-primary">Сохранить</button>
            <button @click="onCancel" type="button" class="btn btn-secondary">Отмена</button>
          </div>
        </div>
      </modal>
    </transition>
  </div>
</template>

<script>
import modalMixin from '../../../../mixins/modal';
import mediaMixin from '../../../../mixins/media';
import massSelectionMixin from '../../../../mixins/mass-selection';
import {validationMixin} from 'vuelidate';
import {required} from 'vuelidate/lib/validators';
import Modal from '../../../../components/controls/modal/modal.vue';

import FInput from '../../../../components/filter/f-input.vue';
import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
import FDate from '../../../../components/filter/f-date.vue';
import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
import VInput from "../../../../components/controls/VInput/VInput.vue";
import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import 'vue2-datepicker/locale/ru.js';
import Services from "../../../../../scripts/services/services";
import {mapActions, mapGetters} from "vuex";
import BillingReport from "../../../../components/billing-report/billing-report.vue";

const cleanHiddenFilter = {
  name: null,
  percent_from: null,
  percent_to: null,
  order_id: null,
  price_from: null,
  price_to: null,
  commission_from: null,
  commission_to: null,
  discount_from: null,
  discount_to: null,
  status_at: [],
};

const cleanFilter = Object.assign({
  offer_id: null,
  name: null,
  percent_from: null,
  percent_to: null,
  order_id: null,
  price_from: null,
  price_to: null,
  discount_from: null,
  discount_to: null,
  commission_from: null,
  commission_to: null,
  status_at: [],

}, cleanHiddenFilter);

const serverKeys = [
  'name',
  'offer_id',
  'percent_from',
  'percent_to',
  'order_id',
  'sale_status',
  'price_from',
  'price_to',
  'discount_from',
  'discount_to',
  'commission_from',
  'commission_to',
  'status_at'
];

export default {
  name: 'tab-billing',
  props: ['model'],
  mixins: [
    modalMixin,
    mediaMixin,
    massSelectionMixin,
    validationMixin,
  ],
  components: {
    FInput,
    FMultiSelect,
    FDate,
    VInput,
    FileInput,
    VSelect,
    DatePicker,
    Modal,
    BillingReport,
  },
  data() {
    return {
      opened: false,
      filter: {},
      appliedFilter: {},
      currentPage: 1,
      pager: {},
      billingList: {},
      billingOperation: {},
      correctionForm: {
        billingDate: null,
        correctionSum: null,
        correctionType: null,
        file_id: null
      },
    }
  },
  validations: {
    correctionForm: {
      billingDate: {required},
      correctionSum: {
        pattern: (value) => /^[0-9\-]*$/.test(value)
      }
    }
  },
  methods: {
    ...mapActions({
      showMessageBox: 'modal/showMessageBox',
    }),
    onFileUpload(file) {
      this.correctionForm.file_id = file.id;
      this.correctionForm.file_name = file.name;
    },
    onSave() {
      this.$v.$touch();
      if (this.$v.$invalid) {
        return;
      }
      Services.showLoader();
      this.saveBillingCorrection();
    },
    onCancel() {
      this.correctionForm = {
        billingDate: null,
        correctionSum: null,
        file_id: null
      };
      this.closeModal();
    },
    openCorrectionModal() {
      this.openModal('BillingCorrectionModal');
    },
    getMerchantId() {
      return this.model.id;
    },
    saveBillingCorrection() {
      Services.hideLoader();
      Services.net()
          .post(this.getRoute('merchant.detail.billingList.addCorrection', {id: this.model.id}), {},
              {
                date: this.correctionForm.billingDate,
                correction_sum: this.correctionForm.correctionSum,
                correction_type: this.correctionForm.correctionType,
                document_id: this.correctionForm.file_id,
              })
          .then(() => {
            this.loadPage();

          })
          .catch(() => {
            this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
          }).finally(() => {
        Services.hideLoader();

        this.showMessageBox({title: 'Биллинг скорректирован'});
      });
    },
    removeItem(id) {
      Services.showLoader();
      Services.net()
          .delete(this.getRoute('merchant.detail.billingReport.delete', {id: this.model.id, reportId: id,}))
          .then(() => {
            this.loadReports();
          })
          .catch(() => {
            this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
          }).finally(() => {
        Services.hideLoader();
        this.showMessageBox({title: 'Отчет удалён'});
      });
    },
    addReturn(id) {
      Services.showLoader();
      Services.net()
          .get(this.getRoute('merchant.detail.billingList.addReturn', {id: this.model.id, operationId: id}))
          .then(() => {
            this.loadPage();
          })
          .catch(() => {
            this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
          }).finally(() => {
        Services.hideLoader();
        this.showMessageBox({title: 'На данную операцию добавлен возврат'});
      });
    },
    deleteOperation(id) {
      Services.showLoader();
      Services.net()
          .delete(this.getRoute('merchant.detail.billingList.deleteOperation', {id: this.model.id, operationId: id,}))
          .then(() => {
            this.loadPage();
          })
          .catch(() => {
            this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
          }).finally(() => {
        Services.hideLoader();
        this.showMessageBox({title: 'Данная операция удалена'});
      });
    },
    paginationPromise() {
      return Services.net().get(
          this.getRoute('merchant.detail.billingList', {id: this.model.id}),
          {
            page: this.currentPage,
            filter: this.appliedFilter,
          }
      );
    },
    loadPage() {
      Services.showLoader();
      this.paginationPromise().then(data => {
        this.billingList = data.billingList.items;
        if (data.billingList) {
          this.setPager(data);
        }
      }).finally(() => {
        Services.hideLoader();
      });
    },
    toggleHiddenFilter() {
      this.opened = !this.opened;
      if (this.opened === false) {
        for (let entry of Object.entries(cleanHiddenFilter)) {
          this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
        }
        this.applyFilter();
      }
    },
    applyFilter() {
      let tmpFilter = {};
      for (let [key, value] of Object.entries(this.filter)) {
        if (value && serverKeys.indexOf(key) !== -1) {
          tmpFilter[key] = value;
        }
      }
      this.appliedFilter = tmpFilter;
      this.currentPage = 1;
      this.loadPage();
    },
    clearFilter() {
      for (let entry of Object.entries(cleanFilter)) {
        this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
      }
      this.applyFilter();
    },
    setPager(data) {
      if (data.pager) {
        this.pager = {
          last_page: data.pager.pages,
          total: data.pager.total,
          current_page: data.pager.current_page,
          per_page: data.pager.pageSize,
        };
      }
    }
  },
  created() {
    Services.showLoader();
    Services.net().get(this.getRoute('merchant.detail.billingList', {id: this.model.id}), {
      page: this.currentPage,
    })
        .then(data => {
          this.billingList = data.billingList.items;
          this.setPager(data);
        });
  },
  computed: {
    availableTypes() {
      return [
        {text: 'Корректировка по обоснованным Комитентом причинам', value: 0},
        {text: 'Корректировка по обоснованным Комиссионером причинам (включая сумму компенсации дополнительных расходов Комиссионера)', value: 1},
      ];
    },
    errorDate() {
      if (this.$v.correctionForm.billingDate.$dirty) {
        if (!this.$v.correctionForm.billingDate.required) return "Обязательное поле";
      }
    },
    errorSum() {
      if (this.$v.correctionForm.correctionSum.$dirty) {
        if (!this.$v.correctionForm.correctionSum.pattern) return "Допустимы только цифры и знак минус";
      }
    },
  },
  watch: {
    currentPage() {
      this.loadPage();
    },
  }
};
</script>
<style lang="css">
.mx-datepicker-popup {
  overflow: visible !important;
  z-index: 9999;
}
</style>
