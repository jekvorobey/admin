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
          <button class="btn btn-success btn-sm" :disabled="!isPeriod(billing.period)">Сделать внеочередной биллинг
          </button>
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

    <table class="table mt-3">
      <thead>
      <tr>
        <th>id Оффера</th>
        <th>Дата продажи</th>
        <th>Название</th>
        <th>Цена, р</th>
        <th>Скидка, %</th>
        <th>Цена на витрине, р</th>
        <th>Комиссия Оператора, %</th>
        <th>Вознаграждение Оператора, руб</th>
        <th>Выплата Мерчанту, руб</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="offer in billingList">
        <td>{{ offer.offer_id }}</td>
        <td><a :href="$store.getters.getRoute('orders.detail', {id:offer.order_id})">{{ offer.status_at }}</a></td>
        <td><a :href="$store.getters.getRoute('offers.detail', {id:offer.offer_id})">{{ offer.name }}</a></td>
        <td>{{ offer.price }}</td>
        <td>{{ 0 }}</td>
        <td>{{ offer.price }}</td>
        <td>{{ offer.percent }}</td>
        <td>{{ parseInt((offer.price - offer.commission).toFixed()) }}</td>
        <td>{{ parseInt(offer.commission.toFixed()) }}</td>
      </tr>
      <tr v-if="!billingList.length">
        <td :colspan="billingList.length + 1">Заказы отсутствуют</td>
      </tr>
      </tbody>
    </table>

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

const cleanHiddenFilter = {
  sale_status: [],
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
  sale_status: [],
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

}, cleanHiddenFilter);

const serverKeys = [
  'offer_id',
  'order_id',
  'percent_from',
  'percent_to',
  'name',
  'sale_status',
  'price_from',
  'price_to',
  'discount_from',
  'discount_to',
  'status_at',
  'commission_from',
  'commission_to'
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
      offerSaleStatuses: {},
      appliedFilter: {},
      currentPage: 1,
      pager: {},
      form: {
        billing_cycle: null,
      },
      billing: {
        period: null,
      },
      billingList: {}
    }
  },
  methods: {
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

    Services.net().get(this.getRoute('merchant.detail.billing', {id: this.model.id})).then(data => {
      this.form.billing_cycle = data.billing_cycle;
    }).finally(() => {
      Services.hideLoader();
    })
  },
  watch: {
    currentPage() {
      this.loadPage();
    }
  }
};
</script>
