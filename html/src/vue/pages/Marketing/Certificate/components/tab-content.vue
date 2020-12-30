<template>
  <div class="mt-4 ml-2 gs-content-tab">

    <div class="form-group required">
      <label for="rules">Правила использования</label>
      <textarea rows="8" class="form-control" v-model="form.rules" id="rules"/>
    </div>

    <div class="form-group required">
      <label for="desc">Описание</label>
      <textarea rows="8" class="form-control" v-model="form.description" id="desc"/>
    </div>

    <button type="button" class="btn btn-success" @click="save">Сохранить изменения</button>

  </div>
</template>

<script>
import Services from '../../../../../scripts/services/services';

export default {
  props: ['content'],
  data() {
    return {
      form: {
        rules: '',
        description: ''
      }
    }
  },
  methods: {
    loadPage() {
      Services.showLoader();
      Services.net().get(this.getRoute('certificate.tab', {tab: 'content'})).then(response => {
        this.$emit('data', response.content)
        this.form.rules = response.content.hasOwnProperty('rules') ? response.content.rules : '';
        this.form.description = response.content.hasOwnProperty('description') ? response.content.description : '';
        Services.hideLoader();
      })
    },
    save() {
      Services.showLoader();
      Services.net().put(this.getRoute('certificate.content_save'), {}, this.form)
          .then(() => {
            Services.hideLoader();
            Services.msg("Изменения сохранены")
          })
    }
  },
  mounted() {
    if (this.content === null) {
      this.loadPage();
    } else {
      this.form.rules = this.content.hasOwnProperty('rules') ? this.content.rules : '';
      this.form.description = this.content.hasOwnProperty('description') ? this.content.description : '';
    }

  },
}
</script>

<style>
.gs-content-tab .form-group {
  margin-bottom: 20px;
}
.gs-content-tab label {
  font-weight: 500;
  line-height: 1;
  font-size: 1.25rem;
}

</style>
