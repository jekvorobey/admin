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
                        <th>Название</th>
                        <td>
                            <v-input v-model="badge.text"

                                     aria-required="true"/>
                        </td>
                    </tr>
                    <tr>
                        <th>Тип шильдика</th>
                        <td>
                            <v-select v-model="badge.type"
                                      :options="availableTypes">
                            </v-select>
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
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import VSelect from "../../../../components/controls/VSelect/VSelect.vue";
    import {validationMixin} from 'vuelidate';
    import {required, integer} from 'vuelidate/lib/validators';

    export default {
        name: "modal-edit-form",
        components: {
            VInput,
            VSelect,
            modal,
            VDeleteButton,
        },
        mixins: [
            modalMixin,
            validationMixin,
        ],
        props: {
            modalName: String,
            types: Object
        },

        data () {
            return {
                badge: {
                    id: null,
                    name: '',
                    has_value: false,
                    value: ''
                },
            };
        },
        /*validations: {
            contact: {
                name: {required},
                type: {required, integer},
            },
        },*/
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
            availableTypes() {
                return Object.entries(this.types).map(type => ({
                    value: type[0],
                    text: type[1],
                }),);
            },/*
            errName() {
                if (this.$v.contact.name.$dirty) {
                    if (!this.$v.contact.name.required) {
                        return "Обязательное поле!";
                    }
                }
            },
            errType() {
                if (this.$v.contact.name.$dirty) {
                    if (!this.$v.contact.name.required) {
                        return "Выберите тип!";
                    }
                }
            },
        },
        watch: {
            'editingContact': {
                handler() {
                    if (this.editingContact) {
                        this.contact = this.editingContact
                    }
                }
            },
        */},
    }
</script>

<style scoped>

</style>