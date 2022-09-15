<template>
  <layout-main>
    <div class="mt-3 mb-3 shadow p-3">
      <div class="row">
        <f-input v-model="filter.from" class="col-lg-3 col-md-4 col-sm-12">Ссылка</f-input>
        <f-input v-model="filter.to" class="col-lg-3 col-md-4 col-sm-12">Короткая ссылка</f-input>
        <f-input v-model="filter.product_id" class="col-lg-3 col-md-4 col-sm-12">ID Продукта</f-input>
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
        <th>Короткая ссылка</th>
        <th>Ссылка</th>
        <th>ID Продукта</th>
        <th v-if="canUpdate(blocks.content)"><!-- Кнопки --></th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="redirect in redirects">
        <td>
          {{ redirect.id }}
        </td>
        <td>
          {{ redirect.from }}
          <b-button size="sm" class="d-inline-block ml-3" @click="copyFrom(redirect.from)">Скопировать</b-button>
        </td>
        <td>
          {{ redirect.to }}
        </td>
        <td>
          <a :href="getRoute('products.detail', {id: redirect.product_id})" target="_blank">{{ redirect.product_id }}</a>
        </td>

        <td v-if="canUpdate(blocks.content)">
          <b-button class="btn btn-warning btn-sm">
            <fa-icon icon="edit"
                     @click="openModal(redirect)"/>
          </b-button>
          <v-delete-button
                btnClass="btn btn-danger btn-sm"
                @delete="removeItem(redirect.id)"
          />
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

    <v-input ref="copyInputComponent" class="hidden-copy-component" v-model="copyValue" />

    <redirect-edit-modal
        :redirect="currentRedirect"
        :options="options"
        @saved="loadPage"
    />

    <b-modal id="import-modal" hide-footer ref="modal" size="md">

      <div slot="modal-title">Ошибки импорта</div>
      <div class="modal-body import-modal-body">
        <div v-for="error in importErrors">
          {{ error.row }}.{{ error.col }} ('{{ error.value }}'): {{ error.msg }}
        </div>
      </div>
    </b-modal>
  </layout-main>
</template>

<script>
import FInput from '../../../components/filter/f-input.vue';
import VInput from '../../../components/controls/VInput/VInput.vue';
import FileInput from '../../../components/controls/FileInput/FileInput.vue';
import RedirectEditModal from './components/redirect-edit-modal.vue';
import VDeleteButton from "../../../components/controls/VDeleteButton/VDeleteButton.vue";
import {mapActions} from "vuex";
import withQuery from "with-query";
import Services from "../../../../scripts/services/services";
import Helpers from '../../../../scripts/helpers';

const cleanFilter = {
  from: '',
  to: ''
}

export default {
  components: {
    FInput,
    RedirectEditModal,
    FileInput,
    VInput,
    VDeleteButton
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
      importErrors: [],
      copyValue: '',
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
        if (this.filter.hasOwnProperty(key) && this.filter[key] !== '') {
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
      this.importErrors = []
      Services.net().post(this.getRoute('redirect.import'), {}, {file_id: data.id})
          .then(data => {
            if (data.rows > 0) {
              Services.msg(`Импорт редиректов (${data.rows}) выполнен успешно`)
            }
            if (data.errors) {
              this.importErrors = data.errors
            }
          })
          .finally(() => {
            Services.hideLoader();
            this.loadPage();
            if (this.importErrors.length) {
              this.$bvModal.show('import-modal')
            }
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
    async copyFrom(value) {
      this.copyValue = this.options.host + value;
      await this.$nextTick();
      this.$refs.copyInputComponent.copy();
    }
  },
  watch: {
    currentPage() {
      this.loadPage();
    }
  }
};
</script>
<style scoped>
  .import-modal-body {
    max-height: 400px;
    overflow: auto;
  }

  .hidden-copy-component {
      position: absolute;
      left: -9999px;
  }
</style>
