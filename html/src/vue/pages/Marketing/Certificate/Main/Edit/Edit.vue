<template>
  <layout-main back :back-url="backUrl">
    <edit-form :card="card" @save="save" @cancel="goBack"/>
  </layout-main>
</template>

<script>
import EditForm from '../components/edit-form-card.vue';
import Services from "../../../../../scripts/services/services.js";
export default {
  components: {EditForm},
  props: ['card'],
  methods: {
    save(data) {
      const id = this.card.id
      Services.net().put(this.getRoute('certificate.card_update', {id}), {}, data)
          .then(() => {
            Services.msg("Изменения сохранены")
            this.goBack()
          })
    },
    goBack() {
      window.location.href = this.backUrl;
    },
  },
  computed: {
    backUrl() {
      return this.getRoute('certificate.index') + '?tab=card&allTab=0'
    }
  }
}
</script>

