<template>
    <transition name="modal">
        <modal :close="closeModal" type="wide" v-if="isModalOpen(modalName)">
            <div slot="header">
                {{ title }}
            </div>
            <div slot="body">
                <ckeditor :editor="editor" v-model="$v.form[text_field].$model" :config="editorConfig"></ckeditor>
                <button @click="save" class="btn btn-dark mt-3" :disabled="!$v.form.$anyDirty">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import CKEditor from '@ckeditor/ckeditor5-vue';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Helpers from '../../../../../scripts/helpers';
    import Services from "../../../../../scripts/services/services";
    import {mapGetters} from "vuex";

    import modal from '../../../../components/controls/modal/modal.vue';

    import VInput from "../../../../components/controls/VInput/VInput.vue";

    import modalMixin from '../../../../mixins/modal.js';

    export default {
        components: {
            modal,
            VInput,
            ckeditor: CKEditor.component
        },
        mixins: [modalMixin, validationMixin],
        props: {
            modalName: String,
            text_field: String,
            title: String,
            source: Object,
        },
        data () {
            return {
                editor: ClassicEditor,
                editorConfig: {
                    toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote' ],
                    height: 400
                },
                form: Object.assign({}, this.source),
            };
        },
        validations() {
            return {
                form: {
                    [this.text_field]: {required},
                }
            };
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                let data = Helpers.filterObject(this.form, [this.text_field]);
                Services.net().post(this.getRoute('products.saveProduct', {id: this.source.id}), {}, data)
                    .then(()=> {
                        this.$emit('onSave');
                        this.closeModal();
                    });
            }
        },
        computed: {
            ...mapGetters(['getRoute']),
        }
    }
</script>

<style>
    .ck-editor__editable_inline {
        min-height: 500px;
    }
</style>