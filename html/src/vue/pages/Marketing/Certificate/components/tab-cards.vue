<template>
  <div class="scroll-x">
    <br>
    <div v-if="showFilter" class="card">
      <div class="card-header">
        Фильтр
      </div>
      <div class="card-body">
        <form action="" name="ordersFilterForm" method="post">
          <div class="row">
            <!-- <f-input class="col-2">
                ID
            </f-input> -->

            <f-date class="col-2" confirm>
              Дата заказа
            </f-date>

            <f-input class="col-2">
              Артикул
            </f-input>

            <f-input class="col-2">
              Номинал (руб.)
            </f-input>

            <f-input class="col-1">
              Сумма (от)
            </f-input>

            <f-input class="col-1">
              Сумма (до)
            </f-input>

            <f-date class="col-2" confirm>
              Дата отгрузки
            </f-date>

            <f-input class="col-2">
              Срок активации
            </f-input>

            <f-select :options="booleanOptions" class="col-2">
              Статус заказа
            </f-select>

            <f-input class="col-2">
              ID заказа
            </f-input>

            <f-input class="col-2">
              ID ПС
            </f-input>

            <f-input class="col-2">
              ID Покупателя
            </f-input>
          </div>
        </form>
      </div>
      <div class="card-footer">
        <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
        <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
      </div>
    </div>



    <div class="form-group" v-show="this.getCustomerId()">
      <div class="input-group m-0 p-0 col-3">
        <input type="text"
               class="form-control"
               v-model="pin"
               placeholder="Введите PIN сертификата"
        />
        <button @click="activate" type="button"  class="btn btn-sm btn-success">Активировать</button>

      </div>

    </div>


    <table class="table table-condensed">
      <thead>
      <tr>
        <th>№ заказа</th>
        <th>ID ПС</th>
        <th>PIN</th>
        <th>Номинал (руб)</th>
        <th>Остаток средств</th>
        <th>Дата покупки</th>
        <th>Дата отправки</th>
        <th>Дата активации</th>
        <th>Срок активации</th>
        <th>Статус</th>
        <th>Покупатель</th>
        <th>Получатель</th>
        <th>Оплаченные заказы</th>
        <th>Сообщение</th>
        <th>Email</th>
        <th>Телефон</th>
        <th>Консоль</th>
      </tr>
      </thead>

      <tbody v-if="items.length">
        <row-card v-for="item in items" :card="item" :key="'card' + item.id" @update="loadPage"/>
      </tbody>

      <tbody v-else-if="loading">
      <tr>
        <td colspan="12">Загрузка данных ...</td>
      </tr>
      </tbody>

      <tbody v-else>
      <tr>
        <td colspan="12">Пока нет купленных сертификатов</td>
      </tr>
      </tbody>
    </table>

      <b-pagination
          v-if="pager.pages > 1"
          v-model="currentPage"
          :total-rows="pager.total"
          :per-page="pager.pageSize"
          @change="loadPage"
          :hide-goto-end-buttons="pager.pages < 10"
          class="float-right"
      ></b-pagination>
  </div>
</template>

<script>
import modalMixin from '../../../../mixins/modal';
import Modal from '../../../../components/controls/modal/modal.vue';
import ShadowCard from '../../../../components/shadow-card.vue';

import FInput from '../../../../components/filter/f-input.vue';
import FDate from '../../../../components/filter/f-date.vue';
import FSelect from '../../../../components/filter/f-select.vue';
import FMultiSelect from '../../../../components/filter/f-multi-select.vue';

import RowCard from './row-card.vue'
import TabList from '../mixins/TabList.js'
import Services from "../../../../../scripts/services/services";

export default {
  components: {
    FInput,
    Modal,
    FDate,
    FSelect,
    FMultiSelect,
    ShadowCard,
    RowCard,
  },
  mixins: [modalMixin, TabList],
  props: {
    externalFilter: {
        type: Object
    }
  },
  data() {
    return {
      pin: null,
      tabName: 'cards'
    };
  },
  methods: {
    clearFilter() {
      this.loadPage(1);
    },

    getCustomerId() {
      const filter = this.getFilter();
      return filter.customer_or_recipient_id || null;
    },

    activate() {
      if (!this.pin || this.pin.trim() === '' ){
        return;
      }

      const filter = this.getFilter();
      const customer_id = filter.customer_or_recipient_id || null;
      let route = 'card_activate';

      if (!customer_id) {
        route = 'card_activate_by_pin';
      }

      Services.showLoader();

      Services.net()
          .post(this.getRoute(`certificate.${route}`), {pin: this.pin, customer_id})
          .then(() => {
            Services.msg('Сертификат активирован', 'success');
            this.loadPage()
          })
          .finally(() => Services.hideLoader())

    }
  },
  computed: {
    booleanOptions() {
      return [{value: 0, text: 'Не активен'}, {value: 1, text: 'Активен'}];
    },
    showFilter() {
        return (this.externalFilter) ? false : false;
    }
  },
  created() {
      if (this.externalFilter) {
          this.filter = this.externalFilter
      }
  }
}
</script>
<style>
  .scroll-x {
    overflow-x: auto;
  }
</style>
