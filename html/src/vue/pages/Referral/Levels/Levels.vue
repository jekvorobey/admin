<template>
  <layout-main>
      <div class="form-group">
        <label for="level">Уровень</label>
        <select v-model="level_id" @change="loadLevel" class="form-control" id="level">
          <option :value="null">Выберите уровень</option>
          <option v-for="level in levels" :value="level.id">{{ level.name }}</option>
        </select>
      </div>
      <div v-if="level">
        <hr/>
        <div class="form-row">
          <div class="form-group col-md-4">
            <label for="name">Название</label>
            <input class="form-control" id="name" v-model="level.name">
          </div>
          <div class="form-group col-md-2">
            <label for="sort">Сортировка</label>
            <input class="form-control" id="sort" v-model="level.sort">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-3">
            <label for="referral_count">Кол-во рефералов для перехода на уровень</label>
            <input class="form-control" id="referral_count" v-model="level.referral_count">
          </div>
          <div class="form-group col-md-3">
            <label for="order_personal_sum">Сумма заказов (личных) для перехода на уровень</label>
            <input class="form-control" id="order_personal_sum" v-model="level.order_personal_sum">
          </div>
          <div class="form-group col-md-3">
            <label for="order_personal_count">Кол-во заказов (личных) для перехода на уровень</label>
            <input class="form-control" id="order_personal_count" v-model="level.order_personal_count">
          </div>
          <div class="form-group col-md-3">
            <label for="order_referral_sum">Сумма заказов (реферальных) для перехода на уровень</label>
            <input class="form-control" id="order_referral_sum" v-model="level.order_referral_sum">
          </div>
        </div>
        <button class="btn btn-success" @click="putLevel" v-if="canUpdate(blocks.referrals)">Сохранить</button>

        <hr/>

        <table class="table">
          <thead>
          <tr>
            <th>РП</th>
            <th>Acquisition business</th>
            <th>Ongoing business</th>
            <th>Promo-business - 1</th>
            <th>Promo-business - 2</th>
            <th>Promo-business - 3</th>
            <th v-if="canUpdate(blocks.referrals)"></th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="commission in level.commissions">
            <td>
              {{ commission.customer_id ? customerTitle(commission.customer_id) : '-' }}
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="commission.percent_x">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="commission.percent_y">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="commission.percent_t">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="commission.percent_p">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="commission.percent_z">
            </td>
            <td v-if="canUpdate(blocks.referrals)">
              <button class="btn btn-success btn-sm" @click="putCommission(commission)">
                <fa-icon icon="save"/>
              </button>
              <v-delete-button btn-class="btn-danger btn-sm" @delete="removeCommission(commission)"
                               v-if="commission.customer_id"/>
            </td>
          </tr>
          <tr v-if="canUpdate(blocks.referrals)">
            <td>
              <v-select2 v-model.number="newCommission.customer_id" class="form-control form-control-sm">
                <option v-for="customer in customers" :value="customer.id">{{ customer.title }}</option>
              </v-select2>
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="newCommission.percent_x">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="newCommission.percent_y">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="newCommission.percent_t">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="newCommission.percent_p">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="newCommission.percent_z">
            </td>
            <td>
              <button class="btn btn-outline-success btn-sm" @click="putCommission(newCommission)">
                <fa-icon icon="plus"/>
              </button>
            </td>
          </tr>
          </tbody>
        </table>

        <hr/>

        <table class="table">
          <thead>
          <tr>
            <th>Суммировать с другими типами</th>
            <th>Коэффициент</th>
            <th>Товар</th>
            <th>Бренд</th>
            <th v-if="canUpdate(blocks.referrals)"></th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="commission in level.special_commissions">
            <td>
              <input type="checkbox" v-model="commission.is_sum">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="commission.coefficient">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="commission.product_id">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="commission.brand_id">
            </td>
            <td v-if="canUpdate(blocks.referrals)">
              <button class="btn btn-success btn-sm" @click="putSpecialCommission(commission)">
                <fa-icon icon="save"/>
              </button>
              <v-delete-button btn-class="btn-danger btn-sm" @delete="removeSpecialCommission(commission)"/>
            </td>
          </tr>
          <tr v-if="canUpdate(blocks.referrals)">
            <td>
              <input type="checkbox" v-model="newSpecialCommission.is_sum">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="newSpecialCommission.coefficient">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="newSpecialCommission.product_id">
            </td>
            <td>
              <input class="form-control form-control-sm" v-model="newSpecialCommission.brand_id">
            </td>
            <td>
              <button class="btn btn-outline-success btn-sm" @click="putSpecialCommission(newSpecialCommission)">
                <fa-icon icon="plus"/>
              </button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
  </layout-main>
</template>

<script>

import Services from '../../../../scripts/services/services.js';
import VDeleteButton from '../../../components/controls/VDeleteButton/VDeleteButton.vue';
import VSelect2 from '../../../components/controls/VSelect2/v-select2.vue';

export default {
  components: {VSelect2, VDeleteButton},
  props: ['levels', 'customers'],
  data() {
    return {
      level_id: Services.route().get('level_id'),
      level: null,
      newSpecialCommission: {
        is_sum: false,
        coefficient: '',
        product_id: '',
        brand_id: '',
      },
      newCommission: {
        customer_id: '',
        percent_x: '',
        percent_y: '',
        percent_t: '',
        percent_p: '',
        percent_z: '',
      },
    };
  },
  methods: {
    pushRoute() {
      let route = {};
      if (this.level_id) {
        route.level_id = this.level_id;
      }
      Services.route().push(route, this.getRoute('referral.levels'));
    },
    loadLevel() {
      this.pushRoute();
      this.level = null;
      if (!this.level_id) {
        return;
      }
      Services.showLoader();
      Services.net().post(this.getRoute('referral.levels.detail', {level_id: this.level_id})).then(data => {
        this.level = data.level;
        Services.hideLoader();
      });
    },
    putLevel() {
      if (!this.level_id) {
        return;
      }
      Services.showLoader();
      Services.net().put(this.getRoute('referral.levels.save', {level_id: this.level_id}), {}, {
        name: this.level.name,
        sort: this.level.sort,
        referral_count: this.level.referral_count,
        order_personal_sum: this.level.order_personal_sum,
        order_personal_count: this.level.order_personal_count,
        order_referral_sum: this.level.order_referral_sum,
      }).then(data => {
        this.level = data.level;
      }).finally(() => {
        Services.hideLoader();
      });
    },
    putCommission(commission) {
      if (!this.level_id) {
        return;
      }
      Services.showLoader();
      Services.net().put(this.getRoute('referral.levels.commission.save', {level_id: this.level_id}), {}, {
        customer_id: commission.customer_id,
        percent_x: commission.percent_x,
        percent_y: commission.percent_y,
        percent_t: commission.percent_t,
        percent_p: commission.percent_p,
        percent_z: commission.percent_z,
      }).then(data => {
        this.level = data.level;
        this.newCommission = {
          customer_id: '',
          percent_x: '',
          percent_y: '',
          percent_t: '',
          percent_p: '',
          percent_z: '',
        };
      }).finally(() => {
        Services.hideLoader();
      });
    },
    removeCommission(commission) {
      if (!this.level_id) {
        return;
      }
      Services.showLoader();
      Services.net().put(this.getRoute('referral.levels.commission.remove', {level_id: this.level_id}), {}, {
        customer_id: commission.customer_id,
      }).then(data => {
        this.level = data.level;
      }).finally(() => {
        Services.hideLoader();
      });
    },
    putSpecialCommission(commission) {
      if (!this.level_id) {
        return;
      }
      Services.showLoader();
      Services.net().put(this.getRoute('referral.levels.special-commission.save', {level_id: this.level_id}), {}, {
        is_sum: commission.is_sum,
        coefficient: commission.coefficient,
        product_id: commission.product_id,
        brand_id: commission.brand_id,
      }).then(data => {
        this.level = data.level;
        this.newSpecialCommission = {
          is_sum: false,
          coefficient: '',
          product_id: '',
          brand_id: '',
        };
      }).finally(() => {
        Services.hideLoader();
      });
    },
    removeSpecialCommission(commission) {
      if (!this.level_id) {
        return;
      }
      Services.showLoader();
      Services.net().delete(this.getRoute('referral.levels.special-commission.remove', {level_id: this.level_id}), {
        product_id: commission.product_id,
        brand_id: commission.brand_id,
      }).then(data => {
        this.level = data.level;
      }).finally(() => {
        Services.hideLoader();
      });
    },
    customerTitle(customer_id) {
      return this.customers.find(customer => customer.id === customer_id).title;
    }
  },
  mounted() {
    this.loadLevel();
  }
};
</script>
