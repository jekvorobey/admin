<template>
  <layout-main>
    <div class="mt-3 mb-3 shadow p-3">
      <div class="row">
        <f-input v-model="filter.from" class="col-lg-3 col-md-4 col-sm-12">Источник</f-input>
        <f-input v-model="filter.to" class="col-lg-3 col-md-4 col-sm-12">Результат</f-input>
      </div>
      <button @click="applyFilter" class="btn btn-dark">Применить</button>
      <button @click="clearFilter" class="btn btn-secondary">Очистить</button>
    </div>

    <div class="row mb-3">
      <file-input v-if="canUpdate(blocks.content)" @uploaded="(data) => onFileUpload(data)"
                  class="col-lg-4 col-md-6 col-sm-12"
                  destination="redirects"
                  label="Загрузить список редиректов"
      ></file-input>
    </div>
    
    <div class="mb-3" v-if="canUpdate(blocks.content)">
      <button @click="openModal()" class="btn btn-success">Создать</button>
    </div>
    <div class="mb-3">
      Всего редиректов: {{ pager.total }}.
    </div>
    <table class="table">
      <thead>
      <tr>
        <th>ID</th>
        <th>Источник</th>
        <th>Результат</th>
        <th v-if="canUpdate(blocks.content)"><!-- Кнопки --></th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="redirect in redirects">
        <td>
          {{redirect.id}}
        </td>
        <td>
          {{redirect.from}}
        </td>
        <td>
          {{redirect.to}}
        </td>

        <td v-if="canUpdate(blocks.content)">
          <b-button class="btn btn-warning btn-sm">
            <fa-icon icon="edit"
                     @click="openModal(redirect)"/>
          </b-button>
          <b-button class="btn btn-danger btn-sm">
            <fa-icon icon="trash-alt"
                     @click="removeItem(redirect.id)"/>
          </b-button>
        </td>
      </tr>
      </tbody>
    </table>
    <div>
      <b-pagination
          v-if="pager.pages !== 1"
          v-model="currentPage"
          :total-rows="pager.total"
          :per-page="pager.pageSize"
          @change="changePage"
          :hide-goto-end-buttons="pager.pages < 10"
          class="mt-3 float-right"
      ></b-pagination>
    </div>
    <redirect-edit-modal
        :redirect="currentRedirect"
        @saved="loadPage"
    />
  </layout-main>
</template>

<script>
import FInput from '../../../components/filter/f-input.vue';
import FileInput from '../../../components/controls/FileInput/FileInput.vue';
import RedirectEditModal from './components/redirect-edit-modal.vue';
import {mapActions} from "vuex";
import withQuery from "with-query";
import Services from "../../../../scripts/services/services";
import Helpers from '../../../../scripts/helpers';
import axios from 'axios';
const cleanFilter = {
  uri: ''
}

export default {
  components: {
    FInput,
    RedirectEditModal,
    FileInput
  },
  props: {
    iRedirects: {},
    iPager: {},
    iCurrentPage: {},
    iFilter: {},
    options: {}
  },
  data() {
    let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);

    return {
      redirects: this.iRedirects,
      pager: this.iPager,
      currentPage: this.iCurrentPage || 1,
      currentRedirect: null,
      filter,
    };
  },
  methods: {
    ...mapActions({
      showMessageBox: 'modal/showMessageBox',
    }),
    goToCreatePage() {
      window.location.href = this.route('redirect.createPage');
    },
    changePage(newPage) {
      let cleanFilter = {};
      for (let [key, value] of Object.entries(this.filter)) {
        if (value) {
          cleanFilter[key] = value;
        }
      }
      history.pushState(null, null, location.origin + location.pathname + withQuery('', {
        page: newPage,
        filter: cleanFilter,
        //sort: this.sort
      }));
    },
    loadPage() {

      ['from', 'to'].forEach(key => {
        if(this.filter.hasOwnProperty(key)) {
          this.filter[key] = Helpers.addSlash(this.filter[key])
        }
      })
      Services.net().get(this.route('redirect.page'), {
        page: this.currentPage,
        filter: this.filter,
        //sort: this.sort,
      }).then(data => {
        this.redirects = data.redirects;
        if (data.pager) {
          this.pager = data.pager
        }
      });
    },
    onFileUpload(data) {
      Services.showLoader();
      Services.net().post(this.getRoute('redirect.import'), {}, {file_id: data.id})
        .then(() => Services.msg('Импорт выполнен успешно'))
          .finally(() => {
            Services.hideLoader();
            this.loadPage();
          })
    },
    applyFilter() {
      this.changePage(1);
      this.loadPage();
    },
    clearFilter() {
      this.$set(this, 'filter', JSON.parse(JSON.stringify(cleanFilter)));
      this.applyFilter();
    },
    removeItem(id) {
      Services.net()
          .delete(this.getRoute('redirect.delete', {id: id,}))
          .then((data) => {
            this.loadPage();
            this.showMessageBox({title: 'Элемент удалён'});
          })
          .catch(() => {
            this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
          });
    },
    openModal(redirect = null) {
      this.currentRedirect = redirect
      this.$bvModal.show('redirect-edit-modal');
    },
  },
  watch: {
    currentPage() {
      this.loadPage();
    }
  }
};
</script>