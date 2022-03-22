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
            <vue-editor
                id="landing-group-meta-description"
                v-model="landing.content"
                :editorOptions="editorSettings"
                type="text"
                tag-name="textarea"
                placeholder="Введите контент"
            > </vue-editor>
        </b-form-group>
    </div>
</template>

<script>
  import {mapActions} from 'vuex';

  import { VueEditor, Quill } from 'vue2-editor';

  import { ImageDrop } from 'quill-image-drop-module';
  import ImageResize from 'quill-image-resize-vue';

  Quill.register("modules/imageDrop", ImageDrop);
  Quill.register("modules/imageResize", ImageResize);

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
              editorSettings: {
                  modules: {
                      imageDrop: true,
                      imageResize: {
                          modules: ["Resize", "DisplaySize", "Toolbar"],
                          handleStyles: {
                              backgroundColor: "black",
                              border: "none",
                              color: "white",
                          },
                      },
                  }
              }
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
