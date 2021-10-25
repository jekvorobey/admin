<template>
  <tr>
    <td>{{ item.id }}</td>
    <td>{{ item.price }}</td>
    <td>{{ item.is_active ? 'Активный' : 'Не активный' }}</td>
    <td>{{ item.activation_period }}</td>
    <td v-if="canUpdate(blocks.marketing)">
      <span v-for="(design, index) in item.designs" :key="'nd' + design.id">
        <span v-if="index > 0">, </span>
        <a :href="designLink(design.id)" :class="'certificate_nominal_design_active_' + design.is_active">{{ design.name }}</a>
      </span>
    </td>
    <td v-else>
      <span v-for="(design, index) in item.designs" :key="'nd' + design.id">
        <span v-if="index > 0">, </span>
          {{ design.name }}
      </span>
    </td>
    <td>{{ item.qty }}</td>

    <td v-if="canUpdate(blocks.marketing)" style="white-space: nowrap">
      <a :href="editLink" class="btn btn-info btn-sm" style="height: 31px; padding-top: 7px;">
        <fa-icon icon="pencil-alt" class="float-right media-btn"></fa-icon>
      </a>
      <v-delete-button @delete="deleteNominal" btn-class="btn btn-danger btn-sm"/>
    </td>

  </tr>
</template>

<script>
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import Services from "../../../../../scripts/services/services.js";

export default {
  props: ['item'],
  components: {VDeleteButton},
  computed: {
    editLink() {
      return this.getRoute('certificate.nominals_edit', {id: this.item.id})
    }
  },
  methods: {
    deleteNominal() {
      Services.showLoader();
      Services.net().delete(this.getRoute('certificate.nominals_delete', {id: this.item.id}))
          .then(() => {
            Services.hideLoader();
            this.$emit('deleted')
          })
    },
    designLink(id) {
      return this.getRoute('certificate.designs_edit', {id})
    }
  }
}
</script>
<style scoped>
.certificate_nominal_design_active_0 {
    color: #999;
}
.certificate_nominal_design_active_1 {
    /*color: red;*/
}
</style>
