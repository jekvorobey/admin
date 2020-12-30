<template>
  <div>
    <form @submit.prevent="onSubmit">
      <div class="form-group required">
        <label class="control-label" for="design_name">
          Название дизайна*
        </label>

        <input type="text"
               class="form-control"
               id="design_name"
               v-model="name"
               required
               placeholder="Введите название дизайна">
      </div>

      <div class="form-group required">

        <label class="control-label">
          Превью дизайна
        </label>

        <a href="#" @click.prevent="openModal('ImageUpload')" class="gift-certificate-design-preview shadow-sm"
           :style="{ backgroundImage: 'url(' + preview + ')'}">
          <svg v-if="!preview" style="height: 50px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </a>

        <small style="color: #666;">
          Рекомендуемый размер изображения 500x300. Формат - JPG или PNG.
        </small>
      </div>

      <div class="form-group required">
        <label class="control-label" for="design_status">Активность</label><br>

        <div>
          <input type="checkbox"
                 :checked="!!status"
                 @change="status=!status"
                 id="design_status">
        </div>
      </div>

      <button type="submit" class="btn btn-success">Сохранить</button>
      <button type="button" class="btn" @click="cancel">Отменить</button>

    </form>

    <file-upload-modal
        @accept="onAcceptImage"
        modal-name="ImageUpload"/>
  </div>
</template>

<script>
import modalMixin from '../../../../mixins/modal';
import Modal from '../../../../components/controls/modal/modal.vue';
import FileUploadModal from './file-upload-modal.vue';
import Services from '../../../../../scripts/services/services';

export default {
  name: 'design-edit-form',
  components: {
    Modal,
    FileUploadModal
  },
  props: {
    design: Object,
    default: () => ({})
  },
  mixins: [modalMixin],
  data() {
    return {
      name: '',
      preview: "",
      status: 0
    };
  },
  mounted() {
    if (this.design) {
      this.name = this.design.name || ''
      this.preview = this.design.preview || ''
      this.status = this.design.status
    }
  },
  methods: {
    onSubmit() {
      let errorMessage = ""
      if (this.name === '')
        errorMessage += "\nИмя дизайна обязательно"

      if (this.preview === '')
        errorMessage += "\nPreview картинка обязательна"

      if (errorMessage !== '') {
        Services.msg(errorMessage, 'danger');
      } else {
        this.$emit('save', {
          name: this.name,
          preview: this.preview,
          status: this.status ? 1 : 0
        })
      }
    },
    onAcceptImage(img) {
      this.preview = img.url
      this.closeModal()
    },
    cancel() {
      this.$emit('cancel')
    }
  }
}
</script>
<style>
.gift-certificate-design-preview {
  width: 300px;
  height: 150px;
  background-color: #eee;
  background-size: cover;
  background-repeat: no-repeat;
  border: 1px solid #eee;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gift-certificate-design-preview svg {
  color: #ccc;
}

.gift-certificate-design-preview:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.gift-certificate-design-preview:hover svg {
  color: #b0afaf;
}
</style>
