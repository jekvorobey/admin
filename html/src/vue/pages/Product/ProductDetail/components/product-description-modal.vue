<template>
    <modal :close="closeModal" v-if="isModalOpen(modalName)">
        <div slot="header">
            {{ title }}
        </div>
        <div slot="body">
            <p v-if="this.text_field === 'how_to'">
                Используйте разделитель <b>|</b> для описания пунктов
                Способов применения.<br><br>
                Пример:
                <mark>Нанести|Подождать 5 минут|Смыть</mark>
            </p>

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
                v-model="$v.form[text_field].$model"
                class="custom-width"
                type="classic"
            />

            <textarea
                v-else
                v-model="$v.form[text_field].$model"
                class="form-control"
                rows="14"
            />

            <button
                @click="save"
                class="btn btn-dark mt-3"
                :disabled="!$v.form.$anyDirty || this.form.description === ''"
            >
                Сохранить
            </button>
        </div>
    </modal>
</template>

<script>
import { validationMixin } from 'vuelidate';
import { required } from 'vuelidate/lib/validators';

import Helpers from '../../../../../scripts/helpers';
import Services from '../../../../../scripts/services/services';

import modal from '../../../../components/controls/modal/modal.vue';

import VInput from '../../../../components/controls/VInput/VInput.vue';

import modalMixin from '../../../../mixins/modal.js';
import VueCkeditor from '../../../../plugins/VueCkeditor';


export default {
    components: {
        modal,
        VInput,
        VueCkeditor
    },
    mixins: [modalMixin, validationMixin],
    props: {
        modalName: String,
        text_field: String,
        title: String,
        source: Object,
    },

    data() {
        return {
            editorMode: 'wysiwyg',
        };
    },

    computed: {
        form: {
            get() {return this.source}
        }
    },
    validations() {
        return {
            form: {
                [this.text_field]: {},
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
            Services.showLoader();
            Services.net().post(this.getRoute('products.saveProduct', {id: this.source.id}), {}, data).catch(() => {
                Services.hideLoader();
            }).then(()=> {
                this.$emit('onSave');
                this.closeModal();
            });
        }
    },
}
</script>

<style>
    .ck-editor__editable_inline {
        min-height: 500px;
    }
</style>
