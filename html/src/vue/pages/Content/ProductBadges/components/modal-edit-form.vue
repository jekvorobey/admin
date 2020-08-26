<template>
    <transition name="modal">
        <modal :close="cancel" v-if="isModalOpen(modalName)">
            <h2 v-if="badge.id" slot="header">
                Редактирование шильдика
            </h2>
            <h2 v-else slot="header">
                Добавление шильдика
            </h2>
            <div slot="body">
                <table class="table table-sm">
                    <tbody>
                    <tr>
                        <th>Текст шильдика</th>
                        <td>
                            <v-input v-model="badge.text"
                                     :error="errText"
                                     aria-required="true"/>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr align="right">
                        <th colspan="2">
                            <button class="btn btn-success"
                                     @click="save">
                                Сохранить
                            </button>
                            <button class="btn btn-outline-danger"
                                    @click="cancel">
                                Отмена
                            </button>
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </modal>
    </transition>
</template>

<script>

    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../../mixins/modal.js';
    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import VSelect from "../../../../components/controls/VSelect/VSelect.vue";
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    export default {
        name: "modal-edit-form",
        components: {
            VInput,
            VSelect,
            modal,
        },
        mixins: [
            modalMixin,
            validationMixin,
        ],
        props: {
            modalName: String,
            editingBadge: Object,
        },

        data () {
            return {
                badge_null: {
                    id: null,
                    text: '',
                }
            };
        },
        validations: {
            badge: {
                text: {required},
            },
        },
        methods: {
            save: async function() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                this.$emit('saved', this.badge);
                await this.$nextTick();
                this.cancel();
            },
            cancel() {
                this.$v.$reset();
                this.closeModal();
            },
        },
        computed: {
            badge: {
                get() {
                    return this.editingBadge || this.badge_null
                }
            },
            errText() {
                if (this.$v.badge.text.$dirty) {
                    if (!this.$v.badge.text.required) {
                        return "Обязательное поле!";
                    }
                }
            },
        },
    }
</script>

<style scoped>

</style>