<template>
  <b-modal id="redirect-edit-modal" hide-footer ref="modal" size="lg">
    <div slot="modal-title">
      <strong v-if="mode === 'create'">Создать новый редирект</strong>
      <strong v-else-if="mode === 'edit'">Редактировать редирект</strong>
    </div>
    <div class="card">
      <div class="card-body">
        <b-row class="mb-2">
          <b-col cols="4">
            <label >Источник</label>
          </b-col>
          <b-col cols="8">
            <v-input
                     v-model="from"
                     class="mb-2"
                     :error="null"
            />
          </b-col>
        </b-row>
        <b-row class="mb-2">
          <b-col cols="4">
            <label > Результат</label>
          </b-col>
          <b-col cols="8">
            <v-input
                     v-model="to"
                     class="mb-2"
                     :error="null"
            />
          </b-col>
        </b-row>
      </div>
    </div>
    <div class="mt-3">
      <button class="btn btn-success"
              :disabled="invalid"
              @click="save">
        Сохранить
      </button>
      <button class="btn btn-outline-danger"
              @click="closeModal">
        Отмена
      </button>
    </div>
  </b-modal>
</template>

<script>
import VInput from '../../../../components/controls/VInput/VInput.vue';
import Services from "../../../../../scripts/services/services";
export default {
  name: "redirect-edit-modal.vue",
  components: {
    VInput
  },
  props: {
    redirect: null,
  },
  data() {
    return {
      id: null,
      from: '',
      to: '',
      invalid: false,
      mode: 'create'
    }
  },
  methods: {
    save() {
      Services.showLoader();
      if (this.mode === 'edit') {
        this.updateRedirect({
          from: this.from,
          to: this.to,
          id: this.id,
        })
            .then(data => {
              console.log(data)
          // if (data.order) {
          //   Services.msg("Изменения сохранены");
          // } else {
          //   Services.msg(errorMessage, 'danger');
          // }
              this.$emit('saved')
              this.closeModal();
        }, (data) => {
              console.log('or', data)
          // Services.msg(errorMessage, 'danger');
        }).finally(() => {
          Services.hideLoader();
        });
      } else if (this.mode === 'create') {
        console.log('create')
      }
    },
    closeModal() {
      this.$bvModal.hide('redirect-edit-modal');
    },
    createRedirect(data) {
      return Services.net().post(this.getRoute('redirect.create'), {}, data, {}, true);
    },
    updateRedirect(data) {
      return Services.net().put(this.getRoute('redirect.update', {id: data.id}), {}, data, {}, true);
    },
  },
  watch: {
    redirect(value) {
        console.log(value)
        this.mode = value ? 'edit' : 'create'
        if (value) {
          this.from = value.from
          this.to = value.to
          this.id = +value.id
        } else {
          this.from = ''
          this.to = ''
          this.id = null
        }
    }
  }
}
</script>
