<template>
    <transition name="modal">
        <modal :close="closeModal" type="wide" v-if="isModalOpen(modalName)">
            <div slot="header">
                {{ title }}
            </div>
            <div slot="body">
                <p v-if="this.text_field === 'how_to'">
                    Используйте разделитель <b>|</b> для описания пунктов
                    Способов применения.<br><br>
                    Пример: <mark>Нанести|Подождать 5 минут|Смыть</mark>
                </p>

                <ckeditor
                    v-if="this.text_field === 'description'"
                    class="custom-width"
                    id="description"
                    type="classic"
                    v-model="v.form[text_field].$model" />

                <textarea v-else v-model="$v.form[text_field].$model"
                          class="form-control" rows="14">
                </textarea>
                <button @click="save"
                        class="btn btn-dark mt-3"
                        :disabled="!$v.form.$anyDirty || this.form.description === ''">
                    Сохранить
                </button>
            </div>
        </modal>
    </transition>
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
    },
    mixins: [modalMixin, validationMixin],
    props: {
        modalName: String,
        text_field: String,
        title: String,
        source: Object,
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