<template>
  <div>
    <h4>Настройки</h4>
    <div class="row">
      <v-input
          v-model="form.billing_cycle"
          class="col-4"
          type="number"
          min="1"
          step="1"
          help="период указывается в днях">Биллинговый период
      </v-input>

      <div class="col-12">
        <button class="btn btn-sm btn-success" :disabled="form.billing_cycle <= 0" @click="saveBillingCycle">Сохранить
        </button>
      </div>
    </div>

    <hr>
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
            @click="createReport">Сделать внеочередной биллинг
          </button>
        </td>
      </tr>
    </table>

    <hr>
    <h4 class="mt-4">Отчеты комиссионера</h4>
    <table class="table mt-2">
      <tr>
        <td>Период</td>
        <td>Статус</td>
        <td>Документ</td>
        <td>Изменен</td>
        <td>Действия</td>
      </tr>
      <tr v-for="report in billingReports">
        <td>{{ report.date_from }} &ndash; {{ report.date_to }}</td>
        <td>
          <b-badge :variant="getBadge(report.status)">
            {{ getStatus(report.status) }}
          </b-badge>
        </td>
        <td>
          <a target="_blank" :href="$store.getters.getRoute('merchant.detail.billingReport.download',
          {id:getMerchantId, reportId:report.id})">Скачать</a>
        </td>
        <td>{{ report.updated_at }}</td>
        <td>
          <b-button class="btn btn-success btn-sm" @click="updateStatus(report.id)">
            Подтвердить <fa-icon icon="check"/>
          </b-button>
          <b-button class="btn btn-danger btn-sm" @click="removeItem(report.id)">
            Удалить <fa-icon icon="trash-alt"/>
          </b-button>
        </td>
      </tr>
    </table>

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
    <div class="table-responsive">
      <table class="table table-condensed">
      <thead>
      <tr>
        <th>Продажа</th>
        <th>Название</th>
        <th>Цена, р</th>
        <th>Кол-во</th>
        <th>Скидка, р</th>
        <th>Бонусы</th>
        <th>Цена на витрине, р</th>
        <th>Комиссия Оператора, %</th>
        <th>Вознаграждение Оператора, руб</th>
        <th>Выплата Мерчанту, руб</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="offer in billingList">
        <td><a :href="$store.getters.getRoute('orders.detail', {id:offer.order_id})">{{ offer.status_at }}</a></td>
        <td><a :href="$store.getters.getRoute('offers.detail', {id:offer.offer_id})">{{ offer.name }}</a></td>
        <td>{{ offer.cost }}</td>
        <td>{{ offer.qty }}</td>
        <td v-if="offer.discounts.hasOwnProperty('marketplace') || offer.discounts.hasOwnProperty('merchant')">
           {{ offer.discounts.hasOwnProperty('marketplace') && offer.discounts.marketplace.sum > 0 ? 'М-с:' + offer.discounts.marketplace.sum : '' }}
           {{ offer.discounts.hasOwnProperty('merchant') && offer.discounts.merchant.sum > 0 ? 'М-т:' + offer.discounts.merchant.sum : ''  }}
        </td>
        <td v-else>0</td>
        <td>{{ offer.bonuses.hasOwnProperty('bonus_discount') ? offer.bonuses.bonus_discount : 0 }}</td>
        <td>{{ offer.price }}</td>
        <td>{{ offer.percent }}</td>
        <td>{{ parseInt(offer.commission.toFixed()) }}</td>
        <td>{{ parseInt((offer.price - offer.commission).toFixed()) }}</td>
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
import FInput from '../../../../components/filter/f-input.vue';
import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
import FDate from '../../../../components/filter/f-date.vue';
import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
import VInput from "../../../../components/controls/VInput/VInput.vue";
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import 'vue2-datepicker/locale/ru.js';
import Services from "../../../../../scripts/services/services";
import {mapActions} from "vuex";

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
  components: {
    FInput,
    FMultiSelect,
    FDate,
    VInput,
    VSelect,
    DatePicker
  },
  data() {
    //let filter = Object.assign({}, cleanFilter);
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
      statuses: [
        {text:'новый', badge:'light'},
        {text:'просмотрен', badge:'warning'},
        {text:'подтвержден', badge:'success'},
        {text:'отклонен', badge:'danger'},
      ],
      newReportDates: {
        dateFrom:null,
        dateTo:null,
      },
    }
  },
  methods: {
    ...mapActions({
      showMessageBox: 'modal/showMessageBox',
    }),
    getMerchantId() {
      return this.model.id;
    },
    getStatus(id) {
      return this.statuses[id].text;
    },
    getBadge(id) {
      return this.statuses[id].badge;
    },
    removeItem(id) {
      Services.showLoader();
      Services.net()
        .delete(this.getRoute('merchant.detail.billingReport.delete', {id: this.model.id, reportId: id,}))
        .then((data) => {
          this.loadReports();

        })
        .catch(() => {
          this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
        }).finally(() => {
          Services.hideLoader();
          this.showMessageBox({title: 'Отчет удалён'});
        });
    },
    updateStatus(id) {
      Services.showLoader();
      Services.net()
        .put(this.getRoute('merchant.detail.billingReport.updateStatus', {id: this.model.id, reportId: id,}), {},{status: 2})
        .then((data) => {
          this.loadReports();
        })
        .catch(() => {
          this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
        }).finally(() => {
          Services.hideLoader();
          this.showMessageBox({title: 'Статус Обновлен'});
        });
    },
    createReport() {
      if (!this.newReportDates) { return false; }
      Services.showLoader();
      Services.net()
        .post(this.getRoute('merchant.detail.billingReport.create', {id: this.model.id,}), {},
            { date_from: this.newReportDates.dateFrom,  date_to: this.newReportDates.dateTo})
        .then((data) => {
          this.loadReports();
          this.newReportDates = {dateFrom: null, dateTo: null};
          this.billing.period = null;
        })
        .catch(() => {
          this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
        }).finally(() => {
          Services.hideLoader();
          this.showMessageBox({title: 'Отчет создан'});
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
    loadReports() {
      Services.showLoader();
      Services.net().get(this.getRoute('merchant.detail.billingReport', {id: this.model.id}))
          .then(data => {
            this.billingReports = data.billing_reports;
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
    saveBillingCycle() {
      Services.showLoader();
      Services.net().put(this.getRoute('merchant.detail.billing.billing_cycle',
          {id: this.model.id}),
          {
            billing_cycle: this.form.billing_cycle
          }).then(data => {
        if (!data) {
          Services.msg('Изменения успешно сохранены')
        }
      }, () => {
        Services.msg('Не удалось сохранить изменения', 'danger')
      }).finally(() => {
        Services.hideLoader();
      })
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
    Services.net().get(this.getRoute('merchant.detail.billingList', {id: this.model.id}), {
      page: this.currentPage,
    })
    .then(data => {
      this.billingList = data.billingList.items;
      this.setPager(data);
    });

    Services.net().get(this.getRoute('merchant.detail.billing', {id: this.model.id}))
    .then(data => {
      this.form.billing_cycle = data.billing_cycle;
    })
    .finally(() => {
      Services.hideLoader();
    });
    this.loadReports();
  },
  watch: {
    currentPage() {
      this.loadPage();
    }
  }
};
</script>
