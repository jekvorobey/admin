<template>
  <div>
    <div class="row mb-3">
      <div class="col-12 mt-3">
        <a href="#" class="btn btn-success" @click.prevent="createReport">Сформировать отчет</a>
      </div>
    </div>

    <table class="table table-condensed">
      <thead>
      <tr>
        <th>id</th>
        <th>Дата формирования</th>
        <th>Ссылка</th>
        <th>ID пользователя</th>
      </tr>
      </thead>

      <tbody v-if="items.length">
        <row-report v-for="item in items" :item="item" :key="'report' + item.id"/>
      </tbody>

      <tbody v-else-if="loading">
      <tr>
        <td colspan="4">Загрузка данных ...</td>
      </tr>
      </tbody>

      <tbody v-else>
      <tr>
        <td colspan="4">Отчеты не найдены</td>
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
import Services from '../../../../../scripts/services/services';
import RowReport from './row-report.vue'
import TabList from '../mixins/TabList.js'


export default {
  components: {RowReport},
  mixins: [TabList],
  data() {
    return {
      tabName: 'reports'
    };
  },
  methods: {
    createReport() {
      Services.net().post(this.getRoute('certificate.report_create')).then((response) => {
        this.loadPage().then(() => {
          const id = response.id;
          if (id) {
            window.location = this.getRoute('certificate.report_download', {id})
          }
        })
      })
    }
  }
}
</script>
