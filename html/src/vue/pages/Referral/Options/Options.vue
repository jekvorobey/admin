<template>
  <layout-main>
    <div v-if="canUpdate(blocks.referrals)">
      <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" id="rp" v-model="rp">
        <label class="custom-control-label" for="rp">Отображать уровень на странице "Реферальная программа"</label>
      </div>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" id="order" v-model="order">
        <label class="custom-control-label" for="order">Отображать уровень на странице "Мои заказы"</label>
      </div>
      <button class="btn btn-success" @click="saveOption">Сохранить</button>
    </div>
  </layout-main>
</template>

<script>

import Services from '../../../../scripts/services/services.js';

export default {
  props: ['iRP', 'iOrder'],
  data() {
    return {
      rp: this.iRP,
      order: this.iOrder,
    };
  },
  methods: {
    saveOption() {
      Services.showLoader();
      Services.net().put(this.getRoute('referral.options.save'), {}, {
        rp: this.rp,
        order: this.order,
      }).finally(() => {
        Services.hideLoader();
      });
    }
  }
};
</script>
