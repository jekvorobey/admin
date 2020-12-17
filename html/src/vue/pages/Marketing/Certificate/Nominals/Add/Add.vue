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
      Services.net().post(this.getRoute('certificate.nominals_save'), {}, data)
          .then(() => {
            Services.msg("Номинал создан")
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

