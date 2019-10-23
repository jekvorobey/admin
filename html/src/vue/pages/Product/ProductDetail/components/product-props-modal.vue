<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)" type="wide">
            <div slot="header">
                Редактирование харакетристик товара
            </div>
            <div slot="body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="prop-name">Характеристика</th>
                            <th>Значения</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr v-for="prop in availableProperties">
                        <td :class="{'text-danger': errorProperty(prop.id)}" class="prop-name">
                            {{prop.name}}
                            <button
                                    v-if="prop.is_multiple"
                                    @click="addField(prop.id)"
                                    class="btn btn-outline-info float-right">
                                <fa-icon icon="plus"></fa-icon>
                            </button>
                        </td>
                        <td>
                            <div v-for="(_, index) in form[prop.id]" class="input-group mb-3">
                                <b-form-select v-if="prop.type === 'directory'"
                                               v-model="form[prop.id][index]"
                                               :options="directoryOptions(prop.id)"
                                               :class="{ 'is-invalid': errorField(prop.id, index) }"
                                ></b-form-select>
                                <input v-else-if="prop.type === 'datetime'"
                                       :value="getDate(prop.id, index)"
                                       @input="v => setDate(prop.id, index, v)"
                                       :class="{ 'is-invalid': errorField(prop.id, index) }"
                                       type="date"
                                       class="form-control">
                                <input v-else
                                       v-model="form[prop.id][index]"
                                       :class="{ 'is-invalid': errorField(prop.id, index) }"
                                       type="text"
                                       class="form-control">
                                <div v-if="prop.is_multiple" class="input-group-append">
                                    <button @click="deleteField(prop.id, index)"
                                            class="btn btn-outline-secondary"
                                            type="button">
                                        <fa-icon icon="trash-alt"></fa-icon>
                                    </button>
                                </div>
                                <span class="invalid-feedback" role="alert">
                                    {{ errorField(prop.id, index) }}
                                </span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <button @click="save" class="btn btn-dark">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {integer} from 'vuelidate/lib/validators';

    import Helpers from '../../../../../scripts/helpers';
    import Services from "../../../../../scripts/services/services";
    import {mapGetters} from "vuex";

    import modal from '../../../../components/controls/modal/modal.vue';

    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import VSelect from "../../../../components/controls/VSelect/VSelect.vue";

    import modalMixin from '../../../../mixins/modal.js';

    export default {
        components: {
            modal,
            VInput,
            VSelect,
        },
        mixins: [modalMixin, validationMixin],
        props: {
            modalName: String,
            properties: {},
            availableProperties: {},
            directoryValues: {}
        },
        data () {
            let form = {};
            this.availableProperties.forEach(property => {
                form[property.id] = this.properties[property.id] || [null];
            });
            return {
                form: form,
            };
        },
        validations() {
            let form = {};
            this.availableProperties.forEach(property => {
                let rules = {
                    $each: {}
                };
                switch (property.type) {
                    case 'integer': rules.$each['integer'] = integer; break;
                    case 'double': rules.$each['numeric'] = decimal; break;
                }
                form[property.id] = rules;
            });
            return {
                form
            };
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                // let data = Helpers.filterObject(this.form, formFields);
                // Services.net().post(this.getRoute('products.saveProduct', {id: this.source.id}), {}, data)
                //     .then(()=> {
                //         this.$emit('onSave');
                //         this.closeModal();
                //     });
            },
            errorProperty(propertyId) {
                return this.$v.form[propertyId].$invalid;
            },
            errorField(propertyId, index) {
                if (!this.$v.form[propertyId]) return;
                if (!this.$v.form[propertyId].$each[index]) return;
                if (this.$v.form[propertyId].$each[index].$dirty) {
                    if (this.$v.form[propertyId].$each[index].required === false) return "Обязательное поле!";
                    if (this.$v.form[propertyId].$each[index].integer === false) return "Только целые числа!";
                    if (this.$v.form[propertyId].$each[index].numeric === false) return "Только цифры и точка!";
                }
            },
            directoryOptions(propertyId) {
                return this.directoryValues[propertyId] ? this.directoryValues[propertyId].map(option => ({
                    text: option.name,
                    value: option.id
                })) : [];
            },
            getDate(propertyId, index) {
                console.log(this.form[propertyId][index]);
                if (!this.form[propertyId][index]) {
                    return '';
                }

                return this.form[propertyId][index].split(' ')[0];
            },
            setDate(propertyId, index, dateValue) {
                this.form[propertyId][index] = dateValue;
            },
            addField(propertyId) {
                this.form[propertyId].push('');
            },
            deleteField(propertyId, index) {
                this.form[propertyId] = this.form[propertyId].filter((v, i) => {
                    return i !== index;
                });
            }
        },
        computed: {
            ...mapGetters(['getRoute']),
        }
    }
</script>

<style scoped>
    .prop-name {
        width: 400px;
    }
</style>