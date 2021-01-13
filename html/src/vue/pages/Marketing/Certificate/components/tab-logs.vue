<template>
  <div>
    <br>
    <div class="card">
      <form @submit.prevent="applyFilter">
        <div class="card-header">
          Фильтр
        </div>
        <div class="card-body">
          <div class="row">
            <f-date class="col-2" confirm v-model="filter.date_from">
              Дата от
            </f-date>
            <f-date class="col-2" confirm v-model="filter.date_to">
              Дата до
            </f-date>

            <f-input class="col-2" v-model="filter.certificate_id" placeholder="через пробелы">
              ID сертификата
            </f-input>

            <f-input class="col-2" v-model="filter.transaction_id" placeholder="через пробелы">
              ID транзакции
            </f-input>
          </div>

        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-sm btn-dark">Применить</button>
          <button type="button" @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
          <span class="float-right">Всего записей: {{ recordsAmount }}.</span>
        </div>
      </form>
    </div>
    <br>

    <div class="mt-2 ml-2" v-if="!loading && !items.length">
      Записей в логе не найдено
    </div>

    <table class="table table-condensed">
      <thead>
      <tr>
        <th>ID транзакции</th>
        <th>№ заказа</th>
        <th>ID ПС</th>
        <th>Действие</th>
        <th>Изменение баланса</th>
        <th>Статус до</th>
        <th>Статус после</th>
        <th>Владелец ПС</th>
        <th>Пользователь</th>
        <th>Дата и время</th>
      </tr>
      </thead>

      <tbody v-if="items.length">
        <row-log v-for="item in items" :item="item" :key="'log' + item.id"/>
      </tbody>

      <tbody v-else-if="loading">
      <tr>
        <td colspan="5">Загрузка данных ...</td>
      </tr>
      </tbody>

      <tbody v-else>
      <tr>
        <td colspan="5">Записей в логе не найдено</td>
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
import RowLog from './row-log.vue'

import FInput from '../../../../components/filter/f-input.vue';
import FDate from '../../../../components/filter/f-date.vue';
import FSelect from '../../../../components/filter/f-select.vue';
import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
import TabList from '../mixins/TabList.js'

export default {
  components: {
    FInput,
    FDate,
    FSelect,
    FMultiSelect,
    RowLog
  },
  mixins: [TabList],
  data() {
    return {
      tabName: 'logs',
      filter: {
        date_from: '',
        date_to: '',
        certificate_id: '',
        transaction_id: '',
      }
    };
  },
  methods: {
    clearFilter() {
      this.filter.date_from = '';
      this.filter.date_to = '';
      this.filter.certificate_id = '';
      this.filter.transaction_id = '';
      this.loadPage(1);
    },
  },
}
</script>

<style scoped>

</style>
