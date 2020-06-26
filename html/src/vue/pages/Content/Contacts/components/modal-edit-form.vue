<template>
    <transition name="modal">
        <modal :close="cancel" v-if="isModalOpen(modalName)">
            <div v-if="contact.id" slot="header">
                Редактирование контактной информации
            </div>
            <div v-else slot="header">
                Добавление контакта
            </div>
            <div slot="body">
                <table class="table table-sm">
                    <tbody>
                    <tr>
                        <th>Название</th>
                        <td>
                            <v-input v-model="contact.name"
                                     :error="errName"
                                     aria-required="true"/>
                        </td>
                    </tr>
                    <tr>
                        <th>Описание</th>
                        <td>
                            <textarea class="form-control"
                                      v-model="contact.description"
                                      rows="5"
                                      placeholder="Краткое описание для пользователей"
                                      maxlength="500"/>
                        </td>
                    </tr>
                    <tr>
                        <th>Адрес или телефон</th>
                        <td>
                            <v-input v-model="contact.address"
                                     placeholder="mail@email.com или vk.com/iBT"/>
                        </td>
                    </tr>
                    <tr>
                        <th>Тип</th>
                        <td>
                            <v-select v-model="contact.type"
                                      :options="availableTypes"
                                      :error="errType">
                            </v-select>
                        </td>
                    </tr>
                    <tr>
                        <th>Иконка</th>
                        <td>
                            <div>
                                <file-input v-if="!contact.icon_file"
                                            :destination="'contact-icons'"
                                            @uploaded="(data) => contact.icon_file = data"
                                            class="mb-3"/>
                                <div v-else class="alert alert-success py-1 px-3" role="alert">
                                    Файл <a :href="contact.icon_file.url"
                                            target="_blank"
                                            class="alert-link">
                                    {{ contact.icon_file.name }}
                                </a> загружен
                                    <button class="btn btn-danger btn-sm"
                                            @click="contact.icon_file = null">
                                        <fa-icon icon="trash-alt"/>
                                    </button>
                                </div>
                            </div>
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
    import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import VSelect from "../../../../components/controls/VSelect/VSelect.vue";

    import {validationMixin} from 'vuelidate';
    import {required, integer} from 'vuelidate/lib/validators';

    export default {
        name: "modal-edit-form",
        components: {
            VSelect,
            VInput,
            modal,
            FileInput,
            VDeleteButton,
        },
        mixins: [
            modalMixin,
            validationMixin,
        ],
        props: {
            modalName: String,
            editingContact: Object,
            types: Object,
        },

        data () {
            return {
                contact: {
                    id: null,
                    name: '',
                    address: '',
                    icon_file: null,
                    description: '',
                    type: '',
                },
            };
        },
        validations: {
            contact: {
                name: {required},
                type: {required, integer},
            },
        },
        methods: {
            save: async function() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                this.$emit('saved', this.contact);
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
            },
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
        },
    }
</script>

<style scoped>

</style>