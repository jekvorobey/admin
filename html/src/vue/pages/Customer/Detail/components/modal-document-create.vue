<template>
    <transition name="modal">
        <modal :close="cancel" v-if="isModalOpen(modalName)">
            <div slot="header">
                Добавление документа пользователю
            </div>
            <div slot="body">
                <table class="table table-sm">
                    <tfoot>
                    <tr align="right">
                        <th colspan="2">
                            <button  class="btn btn-success" @click="addDocument">Сохранить</button>
                            <button  class="btn btn-outline-danger" @click="cancel">Отмена</button>
                        </th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <tr>
                        <th>Тип документа</th>
                        <td>
                            <v-select v-model="newDocument.type" :options="availableTypes" :error="errType">
                            </v-select>
                        </td>
                    </tr>
                    <tr v-if="newDocument.type !== '1'">
                        <th>Начало периода подсчета:</th>
                        <td>
                            <v-date v-model="newDocument.period_since" :error="errPeriodSince" aria-required="true"/>
                        </td>
                    </tr>
                    <tr v-if="newDocument.type !== '1'">
                        <th>Конец периода подсчета:</th>
                        <td>
                            <v-date v-model="newDocument.period_to" :error="errPeriodTo" aria-required="true"/>
                        </td>
                    </tr>
                    <tr>
                        <th>Название документа</th>
                        <td>
                            <v-input v-model="newDocument.title" :error="errTitle" aria-required="true"/>
                        </td>
                    </tr>
                    <tr v-if="newDocument.type !== '1'">
                        <th>Сумма вознаграждения, руб.</th>
                        <td>
                            <v-input v-model="newDocument.amount_reward" :error="errAmountReward" aria-required="true" aria-valuemin="0"/>
                        </td>
                    </tr>
                    <tr v-if="newDocument.type !== '1'">
                        <th>Статус</th>
                        <td>
                            <v-select v-model="newDocument.status" :options="availableStatuses" :error="errStatus" aria-required="true">
                            </v-select>
                        </td>
                    </tr>
                    <tr>
                        <th>Добавить файл</th>
                        <td>
                            <div>
                                <file-input v-if="!newDocument.file" :destination="'document'" @uploaded="(data) => newDocument.file = data" class="mb-3" :error="errFile"></file-input>
                                <div v-else class="alert alert-success py-1 px-3" role="alert">
                                    Файл <a :href="newDocument.file.url" target="_blank" class="alert-link">{{ newDocument.file.name }}</a> загружен
                                    <button class="btn btn-danger btn-sm" @click="nullifyUploaded"><fa-icon icon="trash-alt"/>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </modal>
    </transition>
</template>

<script>
    import moment from 'moment';
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../../mixins/modal.js';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
    import VDate from "../../../../components/controls/VDate/VDate.vue";
    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import VSelect from "../../../../components/controls/VSelect/VSelect.vue";

    import {validationMixin} from 'vuelidate';
    import {required, integer, decimal, minValue} from 'vuelidate/lib/validators';

    export default {
        components: {
            VSelect,
            VInput,
            VDate,
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
            types: Object,
            statuses: Object,
        },
        data () {
            return {
                newDocument: {
                    type: '',
                    period_since: '',
                    period_to: '',
                    amount_reward: '',
                    status: '',
                    file: null,
                    title: '',
                },
            };
        },
        validations: {
            newDocument: {
                type: {required, integer},
                file: {required},
                period_to: {required},
                period_since: {required},
                amount_reward: {required, decimal, minValue: minValue(0)},
                status: {required, integer},
                title: {required},
            },
        },
        methods: {
            addDocument: async function() {
                this.$v.$touch();
                if (this.newDocument.type !== '1') {
                    if (this.$v.$invalid) {
                        return;
                    }
                } else {
                    if (this.$v.newDocument.file.$invalid) {
                        return;
                    }
                    this.clearFields();
                    await this.$nextTick();
                    this.newDocument.type = 1;
                }
                this.$emit('add', this.newDocument);
                await this.$nextTick();
                this.$v.$reset();
                this.cancel();
            },
            clearFields() {
                this.newDocument.type = '';
                this.newDocument.period_since = '';
                this.newDocument.period_to = '';
                this.newDocument.amount_reward = '';
                this.newDocument.status = '';
                this.newDocument.title = '';
            },
            nullifyUploaded() {
                this.newDocument.file = null;
            },
            cancel() {
                this.clearFields();
                this.nullifyUploaded();
                this.$v.$reset();
                this.closeModal();
            },
        },
        computed: {
            availableTypes() {
                return Object.entries(this.types).map(type => ({
                    value: type[0],
                    text: type.slice(1,2),
                }),);
            },
            availableStatuses() {
                return Object.entries(this.statuses).map(status => ({
                    value: status[0],
                    text: status.slice(1,2),
                }),);
            },
            errType() {
                if (this.$v.newDocument.type.$dirty) {
                    if (!this.$v.newDocument.type.required) {
                        return "Обязательное поле!";
                    }
                }
            },
            errPeriodSince() {
                let start = this.newDocument.period_since;
                let end = this.newDocument.period_to;
                if ((start && end) && !(moment(start).unix() <= moment(end).unix())) {
                    return "Дата начала не может быть позже даты окончания";
                }
                if (this.$v.newDocument.period_since.$dirty) {
                    if (!this.$v.newDocument.period_since.required) {
                        return "Обязательное поле!";
                    }
                }
            },
            errPeriodTo() {
                if (this.$v.newDocument.period_to.$dirty) {
                    if (!this.$v.newDocument.period_to.required) {
                        return "Обязательное поле!";
                    }
                }
            },
            errAmountReward() {
                if (this.$v.newDocument.amount_reward.$dirty) {
                    return 'Сумма должна быть числом, не меньше 0';
                }
            },
            errStatus() {
                if (this.$v.newDocument.status.$dirty) {
                    if (!this.$v.newDocument.status.required) {
                        return "Обязательное поле!";
                    }
                }
            },
            errFile() {
                if (this.$v.newDocument.file.$dirty) {
                    if (!this.$v.newDocument.file.required) {
                        return "Обязательное поле!";
                    }
                }
            },
            errTitle() {
                if (this.$v.newDocument.title.$dirty) {
                    if (!this.$v.newDocument.title.required) {
                        return "Обязательное поле!";
                    }
                }
            },
        }
    };
</script>