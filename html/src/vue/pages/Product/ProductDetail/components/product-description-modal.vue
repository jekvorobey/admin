<template>
    <transition name="modal">
        <modal :close="closeModal" type="wide" v-if="isModalOpen(modalName)">
            <div slot="header">
                {{ title }}
            </div>
            <div slot="body">
                <textarea v-model="$v.form[text_field].$model" class="form-control" rows="20"></textarea>
                <button @click="save" class="btn btn-dark mt-3" :disabled="!$v.form.$anyDirty">Сохранить</button>
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
    data () {
        return {
            form: Object.assign({}, this.source),
        };
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