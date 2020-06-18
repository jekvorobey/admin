import Vue from 'vue'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
import VueCkeditor from 'vue-ckeditor5'

const options = {
  editors: {
    classic: ClassicEditor,
  },
  name: 'ckeditor',
  toolbar: [ 'bold', 'italic', '|', 'undo', 'redo' ]
}

Vue.use(VueCkeditor.plugin, options);

export default {
  VueCkeditor
}
