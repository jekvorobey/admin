<template>
  <div>
    <h4>Отчеты</h4>
    <table>
      <tr>
        <td>
          <date-picker
              v-model="billing.period"
              value-type="format"
              format="YYYY-MM-DD"
              range
              input-class="form-control form-control-sm"
          />
        </td>
        <td>
          <button
            class="btn btn-success btn-sm"
            :disabled="!isPeriod(billing.period)"
            @click="downloadReportUrl">Скачать отчет за период
          </button>
        </td>
      </tr>
    </table>
    <hr>
    <hr>
    <div class="table-responsive">
      <table class="table table-condensed">
      <thead>
      <tr>
        <th>Номер Заказа</th>
        <th>Дата продажи</th>
        <th>Название</th>
        <th>Количество</th>
        <th>Цена за единицу, р</th>
        <th>Сумма</th>
        <th>Скидка</th>
        <th>Статус</th>
      </tr>
      </thead>
      <tbody>
        <tr v-for="billingOperation in billingList">
          <td><a :href="$store.getters.getRoute('orders.detail', {id:billingOperation.id})">{{ billingOperation.number }}</a></td>
          <td>{{ billingOperation.created_at }}</td>
          <td>
            <p v-for="item in billingOperation.basket.items">{{ item.name }}</p>
          </td>
          <td>{{ billingOperation.count_tickets }}</td>
          <td v-if="billingOperation.count_tickets > 0">{{ billingOperation.price / billingOperation.count_tickets }}</td>
          <td v-else>0</td>
          <td>{{ parseInt(billingOperation.price) }}</td>
          <td>{{ billingOperation.cost - billingOperation.price }}</td>
          <td>
            <b-badge v-if="billingOperation.is_canceled === 1" variant="danger">Отменен</b-badge>
            <b-badge v-if="billingOperation.is_canceled === 0" variant="success">Доставлен</b-badge>
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
  name: 'tab-public-event',
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
    Modal
  },
  data() {
    return {
      opened: false,
      filter: {},
      appliedFilter: {},
      currentPage: 1,
      pager: {},
      form: {
        billing_cycle: null,
      },
      billing: {
        period: null,
      },
      billingList: {},
      billingReports: {},
      newReportDates: {
        dateFrom:null,
        dateTo:null,
      },
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
    }
  },
  methods: {
    ...mapActions({
      showMessageBox: 'modal/showMessageBox',
    }),
    getMerchantId() {
      return this.model.id;
    },
    downloadReportUrl() {
      Services.showLoader();
      let url = '/merchant/detail/'+ this.model.id +'/eventBillingList/download-report?date_from='+ this.newReportDates.dateFrom +'&date_to=' + this.newReportDates.dateTo;
      window.open(url);
      this.newReportDates = {dateFrom: null, dateTo: null};
      this.billing.period = null;
      Services.hideLoader();
    },
    paginationPromise() {
      return Services.net().get(
          this.getRoute('merchant.detail.eventBillingList', {id: this.model.id}),
          {
            page: this.currentPage,
          }
      );
    },
    loadPage() {
      Services.showLoader();
      this.paginationPromise().then(data => {
        this.billingList = data.billingList.items;
        if (data.billingList) {
          this.setPager(data.billingList);
        }
      })
      .finally(() => {
        Services.hideLoader();
      });
    },
    isPeriod(period) {
      if (!period || period.length !== 2) {
        return false;
      }
      this.newReportDates = {
        dateFrom: period[0],
        dateTo: period[1],
      }
      return period[0] && period[1];
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
    Services.net().get(this.getRoute('merchant.detail.eventBillingList', {id: this.model.id}), {
      page: this.currentPage,
    })
    .then(data => {
      this.billingList = data.billingList.items;
      this.setPager(data.billingList);
    })
    .finally(() => {
      Services.hideLoader();
    });
  },
  computed: {
    errorDate() {
      if (this.$v.correctionForm.billingDate.$dirty) {
        if (!this.$v.correctionForm.billingDate.required) return "Обязательное поле";
      }
    },
  },
  watch: {
    currentPage() {
      this.loadPage();
    }
  }
};
</script>
<style lang="css">
.mx-datepicker-popup {
  overflow: visible !important;
  z-index: 9999;
}
</style>
