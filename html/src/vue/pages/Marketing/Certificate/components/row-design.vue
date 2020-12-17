<template>
  <tr>
    <td>{{ item.id }}</td>
    <td>{{ item.name }}</td>
    <td>

      <a :href="item.preview" target="_blank" class="gift-cards-design-list-preview shadow-sm"
         :style="{ backgroundImage: 'url(' + item.preview + ')'}">
      </a>
    </td>
    <td>{{ !!item.status ? 'Активен' : 'Не активен' }}</td>
    <td>{{ item.created_at }}</td>
    <td>
      <a :href="editLink" class="btn btn-info btn-sm" style="height: 31px; padding-top: 7px;">
        <fa-icon icon="pencil-alt" class="float-right media-btn"></fa-icon>
      </a>

      <v-delete-button @delete="deleteDesign" btn-class="btn btn-danger btn-sm"/>
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
      return this.getRoute('certificate.designs_edit', {id: this.item.id})
    }
  },
  methods: {
    deleteDesign() {
      Services.showLoader();
      Services.net().delete(this.getRoute('certificate.designs_delete', { id: this.item.id }))
          .then(() => {
            Services.hideLoader();
            this.$emit('deleted')
          })
    },
  }
}
</script>
