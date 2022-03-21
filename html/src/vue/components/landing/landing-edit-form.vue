<template>
    <div>
        <b-form-group
            label="Название*"
            label-for="landing-group-name"
        >
          <b-form-input
              id="landing-group-name"
              v-model="landing.name"
              type="text"
              required
              placeholder="Введите название"
          />
        </b-form-group>

        <b-form-group
            label="Символьный код*"
            label-for="landing-group-code"
        >
            <b-form-input
                id="landing-group-code"
                v-model="landing.code"
                type="text"
                required
                placeholder="Введите символьный код"
            />
        </b-form-group>

        <b-form-group
            label="Мета заголовок"
            label-for="landing-group-meta-title"
        >
            <b-form-input
                id="landing-group-meta-title"
                v-model="landing.meta_title"
                type="text"
                placeholder="Введите мета заголовок"
            />
        </b-form-group>

        <b-form-group
            label="Мета описание"
            label-for="landing-group-meta-description"
        >
            <b-form-textarea
                id="landing-group-meta-description"
                v-model="landing.meta_description"
                type="text"
                placeholder="Введите мета описание"
            />
        </b-form-group>

        <b-form-group
            label="Активность"
            label-for="landing-group-active"
        >
            <b-form-checkbox
                id="landing-group-active"
                v-model="landing.active"
                :value="1"
                :unchecked-value="0"
            />
        </b-form-group>

        <b-form-group
            label="Контент"
            label-for="landing-group-meta-description"
        >
            <div class="text-right mb-3">
                <b-button
                    :pressed="editorMode === 'wysiwyg'"
                    size="sm"
                    variant="outline-secondary"
                    @click="editorMode = 'wysiwyg'"
                >WYSIWYG</b-button>

                <b-button
                    :pressed="editorMode === 'html'"
                    size="sm"
                    variant="outline-secondary"
                    @click="editorMode = 'html'"
                >HTML</b-button>
            </div>

            <ckeditor
                v-if="editorMode === 'wysiwyg'"
                @ready="onReady"
                v-model="landing.content"
                :config="editorSettings"
                class="custom-width"
                tag-name="textarea"
                type="classic"
            />

            <textarea
                v-else
                v-model="landing.content"
                class="form-control"
                rows="14"
            />
        </b-form-group>
    </div>
</template>

<script>
import {mapActions} from 'vuex';

import VueEditor from '../../plugins/VueCkeditor';
import CKEditorUploadAdapter from '../../plugins/CKEditorUploadAdapter';
import "@ckeditor/ckeditor5-build-classic/build/translations/ru";

  export default {
      components: {
          VueEditor,
      },
      props: {
          iLanding: {
              type: Object,
              default: {},
          },
      },

      data() {
          return {
              landing: this.normalizeLanding(this.iLanding),
              editorMode: 'wysiwyg',
              editorSettings: {
                  height: 200,
                  language: 'ru',
                  ckfinder: {
                      uploadUrl: `/upload`,
                  },
              },
          };
      },

      methods: {
          ...mapActions({
              showMessageBox: 'modal/showMessageBox',
          }),

          normalizeLanding(source) {
              return {
                  id: source.id ? source.id : null,
                  name: source.name ? source.name : null,
                  code: source.code ? source.code : null,
                  active: source.active ? source.active : false,
                  content: source.content ? source.content : null,
                  meta_title: source.meta_title ? source.meta_title : null,
                  meta_description: source.meta_description ? source.meta_description : null,
              };
          },
          onReady(editor) {
              editor.plugins.get("FileRepository").createUploadAdapter = loader => {
                  return new CKEditorUploadAdapter(loader);
              };
          }
      },
      watch: {
          landing: {
              deep: true,
              handler() {
                  this.$emit('update', this.landing);
              },
          },
      }
  };
</script>

<style>
.ck-editor__editable_inline {
    min-height: 500px;
}
</style>
