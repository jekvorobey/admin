<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                Архив
            </div>
            <div slot="body">
                <v-select v-model="$v.form.archive.$model" :options="booleanOptions">
                    В архиве
                </v-select>
                <v-input v-if="form.archive" v-model="$v.form.archive_comment.$model" :error="errorComment" tag="textarea">
                    Комментарий
                </v-input>
                <button @click="save" class="btn btn-dark" :disabled="!$v.form.$anyDirty">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {requiredIf} from 'vuelidate/lib/validators';

    import Helpers from '../../../../../scripts/helpers';
    import Services from "../../../../../scripts/services/services";
    import {mapGetters} from "vuex";

    import modal from '../../../../components/controls/modal/modal.vue';

    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import VSelect from "../../../../components/controls/VSelect/VSelect.vue";

    import modalMixin from '../../../../mixins/modal.js';


    const formFields = ['archive', 'archive_comment'];
    export default {
        components: {
            modal,
            VInput,
            VSelect,
        },
        mixins: [modalMixin, validationMixin],
        props: {
            modalName: String,
            source: Object,
        },
        data () {
            return {
                form: Object.assign({}, this.source),
            };
        },
        validations: {
            form: {
                archive: {},
                archive_comment: {
                    required: requiredIf(data => data.archive)
                },
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                let data = Helpers.filterObject(this.form, formFields);
                // todo добавлять дату
                Services.net().post(this.getRoute('products.saveProduct', {id: this.source.id}), {}, data)
                    .then(()=> {
                        this.$emit('onSave');
                        this.closeModal();
                    });
            }
        },
        computed: {
            ...mapGetters(['getRoute']),

            booleanOptions() {
                return [{value: 0, text: 'Нет'}, {value: 1, text: 'Да'}];
            },

            errorComment() {
                if (this.$v.form.archive_comment.$dirty) {
                    if (!this.$v.form.archive_comment.required) return "Обязательное поле!";
                }
            },
        }
    }
</script>

<style scoped>

</style>