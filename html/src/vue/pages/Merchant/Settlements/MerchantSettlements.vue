<template>
  <layout-main>
    <h4>Выплаты</h4>
    <table class="table">
      <thead>
      <tr>
        <th>ID</th>
        <th>Дата создания</th>
        <th>Документ</th>
        <th v-if="canUpdate(blocks.merchants)">Действия</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="registry in payRegisters">

        <td>{{ registry.id }}</td>
        <td>{{ datetimePrint(registry.created_at) }}</td>
        <td>
          <a target="_blank" :href="$store.getters.getRoute('merchant.settlements.downloadPayRegistry',
          {registryFileId:registry.file})">Скачать</a>
        </td>
        <td v-if="canUpdate(blocks.merchants)">
          <b-button class="btn btn-danger btn-sm" @click="removeRegistry(registry.id)">
            Удалить <fa-icon icon="trash-alt"/>
          </b-button>
        </td>
        <td></td>
      </tr>
      </tbody>
    </table>
    <hr>
    <h4 class="mt-4">Отчеты комиссионера</h4>
    <div class="mt-3 mb-3 shadow p-3">
      <div class="row">
        <f-date v-model="filter.created_at" class="col-lg-3 col-md-6" range>
          Период
        </f-date>
        <f-multi-select v-model="filter.status" :options="statusOptions" class="col-lg-3 col-md-6">
          Статус
        </f-multi-select>
        <f-multi-select v-model="filter.merchant_id" :options="merchantOptions" class="col-lg-3 col-md-6">
          Мерчант
        </f-multi-select>
      </div>
      <button @click="loadPage" class="btn btn-dark">Применить</button>
      <button @click="clearFilter" class="btn btn-secondary">Очистить</button>
    </div>
    <div class="mb-3">
      Всего: {{ pager.total }}. <span v-if="selectedReportsIds.length">Выбрано: {{selectedReportsIds.length}}</span>
    </div>

    <div class="btn-toolbar mb-3" v-if="canUpdate(blocks.merchants)">
      <div class="input-group">
        <div class="input-group-append">
          <button class="btn btn-outline-success" type="button" :disabled="!selectedReportsIds.length" @click="toPay">
            <fa-icon icon="save"/> К оплате
          </button>
        </div>
      </div>
    </div>

    <table class="table">
      <thead>
      <tr>
        <th>
          <input type="checkbox" id="select-all-page-reports" v-model="isSelectAllPageReports" @click="selectAllPageReports()">
          <label for="select-all-page-reports" class="mb-0">Все</label>
        </th>
        <th>ID</th>
        <th>Период</th>
        <th>Дата подтверждения</th>
        <th>Название организации</th>
        <th>Сумма</th>
        <th>Документ</th>
        <th>Статус</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="report in billingReports">
        <td>
          <label>
            <input type="checkbox"
                   class="report-select"
                   :checked="reportSelected(report.id)"
                   @change="e => selectReport(e, report)">
          </label>
        </td>
        <td>{{ report.id }}</td>
        <td>{{ datePrint(report.date_from) }} &ndash; {{ datePrint(report.date_to) }}</td>
        <td>{{ datetimePrint(report.updated_at) }}</td>
        <td v-if="canView(blocks.merchants)"><a :href="getRoute('merchant.detail', {id: report.data.merchant_id})">{{ merchantName(report.data.merchant_id) }}</a></td>
        <td v-else>{{ merchantName(report.data.merchant_id) }}</td>
        <td>{{ report.total_sum.toLocaleString() }}</td>
        <td>
          <a target="_blank" :href="getRoute('billingReport.detail.download', { entityId: report.data.merchant_id, reportId: report.id, type: billingReportTypes.billing })">
              Скачать
          </a>
        </td>
        <td>
          <span class="badge" :class="statusClass(report.status)">{{ statusName(report.status) }}</span>
        </td>
        <td></td>
      </tr>
      </tbody>
    </table>
    <div>
      <b-pagination
          v-if="pager.pages !== 1"
          v-model="currentPage"
          :total-rows="pager.total"
          :per-page="pager.pageSize"
          :hide-goto-end-buttons="pager.pages < 10"
          class="mt-3 float-right"
      ></b-pagination>
    </div>
  </layout-main>
</template>

<script>

import Services from '../../../../scripts/services/services';
import withQuery from 'with-query';

import FMultiSelect from '../../../components/filter/f-multi-select.vue';
import FInput from '../../../components/filter/f-input.vue';
import FDate from '../../../components/filter/f-date.vue';

const cleanFilter = {
  id: '',
  merchant_id: [],
  status: [],
  created_at: []
};

export default {
  name: 'page-index',
  components: {FDate, FMultiSelect, FInput},
  props: [
    'iBillingReports',
    'iPager',
    'iFilter',
    'iCurrentPage',

    'merchants',

    'iPayRegisters',
    'iPayRegistersPager',
    'iPayRegistersCurrentPage'
  ],
  data() {
    let filter = Object.assign({}, cleanFilter, this.iFilter);
    filter.merchant_id = filter.merchant_id.map(value => parseInt(value));

    return {
      billingReports: this.iBillingReports,
      pager: this.iPager,
      currentPage: this.iCurrentPage || 1,

      filter: filter,
      selectedReports: [],
      selectedReportsIds: [],
      statuses: [],

      payRegisters : this.iPayRegisters,
      payRegistersPager : this.iPayRegistersPager,
      payRegistersCurrentPage : this.iPayRegistersCurrentPage,

      isSelectAllPageReports: false,

    };
  },
  methods: {
    selectAllPageReports() {
      let checkboxes = document.getElementsByClassName('report-select');
      for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = this.isSelectAllPageReports ? '' : 'checked';

      }
      if (this.selectedReportsIds.length < this.billingReports.length) {
        this.selectedReports = [];
        this.selectedReportsIds = [];
        for (let r = 0; r < this.billingReports.length; r++) {
          this.selectedReportsIds.push(this.billingReports[r].id);
          this.selectedReports.push(this.billingReports[r]);
        }
        this.isSelectAllPageReports = true;
      } else {
        this.selectedReports = [];
        this.selectedReportsIds = [];
        this.isSelectAllPageReports = false;
      }
    },
    pushRoute() {
      history.pushState(null, null, location.origin + location.pathname + withQuery('', {
        page: this.currentPage,
        filter: this.filter,
      }));
    },
    loadPage() {
      Services.showLoader();

      Services.net().get(this.route('merchant.settlements.page'), {
        page: this.currentPage,
        filter: this.filter,
      }).then(data => {
        this.billingReports = data.items;
        if (data.pager) {
          this.pager = data.pager
        }
        this.selectedReportsIds = [];
        this.selectedReports = [];
        this.pushRoute(this.currentPage);
      }).finally(() => {
        Services.hideLoader();
      });

      Services.net().get(this.route('merchant.settlements.payRegistry.page'), {
        payRegistersPage: this.payRegistersPage,
      }).then(data => {
        this.payRegisters = data.items;
        if (data.payRegistersPager) {
          this.payRegistersPager = data.payRegistersPager
        }
        this.pushRoute(this.payRegistersCurrentPage);
      }).finally(() => {
        Services.hideLoader();
      });

    },
    removeRegistry(registryId) {
      Services.showLoader();
      Services.net()
          .delete(this.getRoute('merchant.settlements.deletePayRegistry', {payRegistryId: registryId}))
          .catch(() => {
        Services.hideLoader();
      }).then(data => {
        this.loadPage();
      });
    },
    toPay() {
      Services.showLoader();
      Services.net().post(this.route('merchant.settlements.createPayRegistry'), {
        ids: this.selectedReportsIds,
      }).catch(() => {
        Services.hideLoader();
      }).then(data => {
        this.loadPage();
      });
    },
    clearFilter() {
      this.$set(this, 'filter', JSON.parse(JSON.stringify(cleanFilter)));
      this.loadPage();
    },
    statusName(id) {
      let status = this.statuses.find(status => status.id === id);
      return status ? status.name : 'N/A';
    },
    statusClass(id) {
        let status = this.statuses.find(status => status.id === id);
        return status ? 'badge-' + status.badge : '';
    },
      merchantName(id) {
          let merchant = Object.values(this.merchants).find(merchant => merchant.id === id);
          return merchant ? merchant.legal_name : 'N/A';
      },
    reportSelected(id) {
      return this.selectedReportsIds.indexOf(id) !== -1;
    },
    selectReport(e, merchant) {
      if (e.target.checked) {
        this.selectedReportsIds.push(merchant.id);
        this.selectedReports.push(merchant);
      } else {
        let index = this.selectedReportsIds.indexOf(merchant.id);
        if (index !== -1) {
          this.selectedReportsIds.splice(index, 1);
          this.selectedReports.splice(index, 1);
          this.isSelectAllPageReports = false;
        }
      }
    },
  },
  watch: {
    currentPage() {
      this.loadPage();
    }
  },
  computed: {
    statusOptions() {
      return Object.values(this.statuses).map(status => ({value: status.id, text: status.name}));
    },
    merchantOptions() {
      return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.legal_name}));
    },
  },
  created() {
      this.statuses = [
          {id: this.billingReportStatuses.new, name: 'модерация', badge: 'light'},
          {id: this.billingReportStatuses.viewed, name: 'просмотрен', badge: 'info'},
          {id: this.billingReportStatuses.accepted, name: 'подтвержден', badge: 'outline-success'},
          {id: this.billingReportStatuses.rejected, name: 'отклонен', badge: 'danger'},
          {id: this.billingReportStatuses.waiting, name: 'отправлен', badge: 'warning'},
          {id: this.billingReportStatuses.payed, name: 'оплачен', badge: 'success'},
      ];
      console.log(this.statuses);
  }
};
</script>
