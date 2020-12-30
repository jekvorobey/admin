<template>
  <layout-main back :back-url="backUrl">
    <edit-form :design="design" @save="save" @cancel="goBack"/>
  </layout-main>
</template>

<script>
import EditForm from '../../components/edit-form-design.vue';
import Services from '../../../../../../scripts/services/services';

export default {
  components: {EditForm},
  props: ['design'],
  methods: {
    save(data) {
      Services.net().post(this.getRoute('certificate.designs_save'), {}, data)
          .then(() => {
            Services.msg("Дизайн создан")
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
