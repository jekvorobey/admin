<template>

  <div>
    <div class="row mb-3">
      <div class="col-12 mt-3">
        <a class="btn btn-success" :href="createLink">Создать номинал</a>
      </div>
    </div>

    <table class="table table-condensed">
      <thead>
      <tr>
        <th>id</th>
        <th>Номинал (руб)</th>
        <th>Статус</th>
        <th>Период активации (дни)</th>
        <th>Доступные дизайны</th>
        <th>Доступно (шт)</th>
        <th></th>
      </tr>
      </thead>

      <tbody>
      <row-nominal
          v-for="item in items"
          :key="'n' + item.id"
          :item="item"
          @deleted="onDelete"
      />
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
import RowNominal from './row-nominal.vue'
import TabList from '../mixins/TabList.js'

export default {
  components: {RowNominal},
  mixins: [TabList],
  data() {
    return {
      tabName: 'nominals'
    };
  },
  methods: {
    onDelete() {
      this.loadPage()
    },
  },
  computed: {
    createLink() {
      return this.getRoute('certificate.nominals_add')
    },
  },
}
</script>
