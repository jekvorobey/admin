<template>
  <layout-main back :back-url="backUrl">
    <edit-form :nominal="nominal" :all_designs="all_designs" @save="save" @cancel="goBack"/>
  </layout-main>
</template>

<script>
import EditForm from '../../components/edit-form-nominal.vue';
import Services from "../../../../../../scripts/services/services.js";
export default {
  components: {EditForm},
  props: ['nominal', 'all_designs'],
  methods: {
    save(data) {
      const id = this.nominal.id
      Services.net().put(this.getRoute('certificate.nominals_update', {id}), {}, data)
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
      return this.getRoute('certificate.index') + '?tab=nominals&allTab=0'
    }
  }
}
</script>

