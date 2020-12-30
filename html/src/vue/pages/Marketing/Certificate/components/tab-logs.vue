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
            <f-date class="col-2" confirm v-model="filter.date">
              Дата изменения
            </f-date>

            <f-multi-select class="col-4" v-model="filter.entity_type" :options="[
                        {value: 'App\\Models\\Certificate\\Design', text: 'дизайн'},
                        {value: 'App\\Models\\Certificate\\Nominal', text: 'номинал'},
                        {value: 'App\\Models\\Certificate\\Order', text: 'заказ ПС'},
                        {value: 'App\\Models\\Certificate\\Card', text: 'ПС'},
                    ]">
              Сущность
            </f-multi-select>

            <f-input class="col-2" v-model="filter.entity_id">
              ID сущности
            </f-input>

            <f-input class="col-2" v-model="filter.user_id">
              ID пользователя
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
        <th>ID лога</th>
        <th>Сущность</th>
        <th>Действие</th>
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
        v-if="pagination.last_page > 1"
        v-model="currentPage"
        :total-rows="pagination.total"
        :per-page="pagination.per_page"
        @change="loadPage"
        :hide-goto-end-buttons="pagination.last_page < 10"
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
        date: '',
        entity_type: [],
        user_id: '',
        entity_id: '',
      }
    };
  },
  methods: {
    clearFilter() {
      this.filter.date = '';
      this.filter.entity_type = [];
      this.filter.user_id = '';
      this.filter.entity_id = '';
      this.loadPage(1);
    },
  },
}
</script>

<style scoped>

</style>
