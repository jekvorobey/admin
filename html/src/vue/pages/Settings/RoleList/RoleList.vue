<template>
  <layout-main>
    <div class="mb-3" v-if="canUpdate(blocks.settings)">
      <button @click="openModal('roleAdd')" class="btn btn-success">
        <fa-icon icon="plus"/>
        Добавить роль
      </button>
      <span class="float-right">Всего ролей: {{ pager.total }}. <span
          v-if="selectedItems.length">Выбрано: {{ selectedItems.length }}</span></span>
    </div>
    <table class="table">
      <thead>
      <tr>
        <th></th>
        <th>№</th>
        <th>Наименование</th>
        <th>Система</th>
        <th v-if="canUpdate(blocks.settings)">Действия</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="role in roles">
        <td>
          <input type="checkbox" :checked="itemSelected(role.id)"
                 @change="e => selectItem(e, role.id)">
        </td>
        <td>{{ role.id }}</td>
        <td v-if="canView(blocks.settings)">
            <a :href="getRoute('settings.roleDetail', {id: role.id})">{{ role.name }}</a>
        </td>
        <td v-else>{{ role.name }}</td>
        <td>{{ frontName(role.front) }}</td>
        <td v-if="canUpdate(blocks.settings)">
          <b-button class="btn btn-danger btn-sm" v-if="role.can_edit">
            <fa-icon icon="trash-alt"
                     @click="deleteRole(role.id)"/>
          </b-button>
        </td>
      </tr>
      </tbody>
    </table>
    <div>
      <b-pagination
          v-if="pager.pages > 1"
          v-model="currentPage"
          :total-rows="pager.total"
          :per-page="pager.pageSize"
          @change="changePage"
          :hide-goto-end-buttons="pager.pages < 10"
          class="mt-3 float-right"
      ></b-pagination>
    </div>
    <role-add-modal :fronts="options.fronts" @onSave="onRoleCreated"></role-add-modal>
  </layout-main>
</template>

<script>

import Services from '../../../../scripts/services/services';
import withQuery from 'with-query';

import RoleAddModal from '../components/role-add-modal.vue';
import modalMixin from '../../../mixins/modal.js';

export default {
  mixins: [modalMixin],
  components: {
    RoleAddModal
  },
  props: {
    iRoles: {},
    iPager: {},
    iCurrentPage: {},
    options: {}
  },
  data() {
    return {
      roles: this.iRoles,
      pager: this.iPager,
      currentPage: this.iCurrentPage,
      selectedItems: []
    };
  },
  methods: {
    changePage(newPage) {
      history.pushState(null, null, location.origin + location.pathname + withQuery('', {
        page: newPage,
      }));
    },
    loadPage() {
      Services.showLoader();
      Services.net().get(this.route('settings.roleListPagination'), {
        page: this.currentPage,
        filter: this.appliedFilter,
        sort: this.sort,
      }).then(data => {
        this.roles = data.items;
        if (data.pager) {
          this.pager = data.pager
        }
      }).finally(() => {
        Services.hideLoader();
      });
    },
    itemSelected(id) {
      return this.selectedItems.indexOf(id) !== -1;
    },
    selectItem(e, id) {
      if (e.target.checked) {
        this.selectedItems.push(id);
      } else {
        let index = this.selectedItems.indexOf(id);
        if (index !== -1) {
          this.selectedItems.splice(index, 1);
        }
      }
    },
    frontName(id) {
      let fronts = Object.values(this.options.fronts).filter(front => front.id === id);
      return fronts.length > 0 ? fronts[0].name : 'N/A';
    },
    onRoleCreated(newData) {
      this.roles = newData.iRoles;
      this.pager = newData.iPager;
      this.showMessageBox({text: "Роль создана!"});
    },
    deleteRole(id) {
      Services.net().delete(this.getRoute('settings.deleteRole', {id: id}), {}, {}, {})
          .then(data => {
            this.roles = data.iRoles;
            this.pager = data.iPager;
            this.showMessageBox({text: 'Роль удалена'});
          });
    },
  },
  created() {
    window.onpopstate = () => {
      let query = qs.parse(document.location.search.substr(1));
      if (query.page) {
        this.currentPage = query.page;
      }
    };
  },
  watch: {
    currentPage() {
      this.loadPage();
    }
  },
};
</script>
