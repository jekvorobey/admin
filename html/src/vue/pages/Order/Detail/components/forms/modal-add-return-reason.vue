<template>
    <b-modal :id="'modal-add-return-reason-' + type" hide-footer ref="modal" size="lg" @hidden="resetModal">
        <template v-slot:default="{close}">
            <b-form-row>
                <div class="col-sm-4">
                    <v-select
                        v-model="$v.returnReason.$model"
                        :options="orderReturnReasonsOptions"
                        :nullable-value="0"
                    >
                        Причина отмены*
                    </v-select>
                </div>
            </b-form-row>
            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="save" :disabled="!$v.returnReason.$anyDirty">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';

import {required} from 'vuelidate/lib/validators';
import {validationMixin} from "vuelidate";

export default {
    name: 'modal-add-return-reason',
    components: {
        VSelect,
    },
    props: [
        'returnReasons',
        'type'
    ],
    mixins: [
        validationMixin,
    ],
    data() {
        return {
            returnReason: this.returnReasons.returnReason ? this.returnReasons.returnReason.text : 0,
        }
    },
    validations() {
        return {
            returnReason: {required}
        };
    },
    methods: {
        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            this.$emit('update:modelElement', this.returnReason)
            this.$bvModal.hide('modal-add-return-reason-' + this.type);
        },
        resetModal() {
            this.returnReason = 0;
        },
    },
    computed: {
        orderReturnReasonsOptions() {
            return Object.values(this.returnReasons).map(returnReason => ({
                value: returnReason.id,
                text: returnReason.text
            }));
        },
    },
    created() {
        this.resetModal();
    }
}
</script>
