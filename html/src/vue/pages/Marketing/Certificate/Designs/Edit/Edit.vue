<template>
  <layout-main back :back-url="backUrl">
    <edit-form :design="design" @save="save" @cancel="goBack"/>
  </layout-main>
</template>

<script>
import EditForm from '../../components/edit-form-design.vue';
import Services from "../../../../../../scripts/services/services.js";

export default {
  components: {EditForm},
  props: ['design'],
  methods: {
    save(data) {
      const id = this.design.id
      Services.net().put(this.getRoute('certificate.designs_update', {id}), {}, data)
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
      return this.getRoute('certificate.index') + '?tab=designs&allTab=0'
    }
  }
}
</script>
