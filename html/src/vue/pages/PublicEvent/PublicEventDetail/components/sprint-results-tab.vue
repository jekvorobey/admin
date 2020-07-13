<template>
    <div>
        <span>Спринт</span>
        <b-form-select v-model="sprintIdModel" text-field="interval" value-field="id" :options="sprints" @change="onChangeSprint(sprintId)" />

        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" @click="createResult">Добавить документ</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="result in results" :key="result.id">
                    <td>{{result.id}}</td>
                    <td>{{result.name}}</td>
                    <td>{{result.description}}</td>
                    <td>
                        <v-delete-button @delete="() => onDeleteResult([result.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editResult(result)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('SprintResultFormModal')">
                <div slot="header">
                    Документ
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-input v-model="$v.form.name.$model" :error="errorName">Название</v-input>
                        <v-input v-model="$v.form.description.$model" :error="errorDescription">Описание</v-input>
                        <img v-if="$v.form.file_id.$model" :src="fileUrl($v.form.file_id.$model)" class="preview">
                        <file-input destination="result" @uploaded="onFileUpload">Файл</file-input>
                        <button @click="onSave" type="button" class="btn btn-primary">Сохранить</button>
                        <button @click="onCancel" type="button" class="btn btn-secondary">Отмена</button>
                    </div>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {
        ACT_LOAD_SPRINTS,
        ACT_SAVE_SPRINT_RESULT,
        ACT_DELETE_SPRINT_RESULT,
        ACT_LOAD_SPRINT_RESULTS,
        NAMESPACE
    } from '../../../../store/modules/public-events';
    
    import Helpers from '../../../../../scripts/helpers';
    import modalMixin from '../../../../mixins/modal';
    import mediaMixin from '../../../../mixins/media';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../../scripts/services/services";
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import FileInput from '../../../../components/controls/FileInput/FileInput.vue';

    export default {
        mixins: [
            modalMixin,
            validationMixin,
            mediaMixin
        ],
        components: {
            Modal,
            VInput,
            VDeleteButton,
            FileInput
        },
        props: {
            publicEvent: {},
            sprintId: null,
        },
        data() {
            return {
                sprints: [],
                results: [],
                editResultId: null,
                form: {
                    name: null,
                    description: null,
                    file_id: null
                },
            };
        },
        validations: {
            form: {
                name: {required},
                description: {required},
                file_id: {required}
            }
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadSprints: ACT_LOAD_SPRINTS,
                loadResults: ACT_LOAD_SPRINT_RESULTS,
                saveResult: ACT_SAVE_SPRINT_RESULT,
                deleteResult: ACT_DELETE_SPRINT_RESULT
            }),
            reload() {
                    this.loadSprints({publicEventId: this.publicEvent.id})
                        .then(response => {
                            this.sprints = response.sprints;
                            if (this.sprints.length) {
                                this.sprints.forEach(sprint => {
                                    sprint.interval = this.interval(sprint.date_start, sprint.date_end);
                                });
                                const sprintId = this.sprintId != null ? this.sprintId : this.sprints[0].id;
                                this.onChangeSprint(sprintId);
                            }
                        });
            },
            interval(dateStartString, dateEndString) {
                return Helpers.onlyDate(dateStartString) + ' - ' + Helpers.onlyDate(dateEndString);
            },
            onChangeSprint(sprintId) {
                this.sprintIdModel = sprintId;
                this.loadResults({sprintId: sprintId})
                    .then(response => {
                        this.results = response.sprintDocuments;
                    });
            },
            createResult() {
                this.$v.form.$reset();
                this.editResultId = null;
                this.form.sprint_id = this.sprintId;
                this.form.name = null;
                this.form.description = null;
                this.form.file_id = null;
                this.openModal('SprintResultFormModal');
            },
            editResult(result) {
                this.$v.form.$reset();
                this.editResultId = result.id;
                this.form.sprint_id = this.sprintId;
                this.form.name = result.name;
                this.form.description = result.description;
                this.form.file_id = result.file_id;
                this.openModal('SprintResultFormModal');
            },
            onDeleteResult(ids) {
                Services.showLoader();
                this.deleteResult({
                    ids
                }).then(() => {
                    this.reload();
                }).finally(() => {
                    Services.hideLoader();
                    this.$emit('onChange');
                });
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this.saveResult({
                    id: this.editResultId,
                    sprintDocument: this.form
                }).then(() => {
                    this.reload()
                }).finally(() => {
                    this.closeModal();
                    Services.hideLoader();
                });
            },
            onCancel() {
                this.closeModal();
            },
            onFileUpload(file) {
                this.$v.form.file_id.$model = file.id;
            },
        },
        computed: {
            sprintIdModel: {
                get () { return this.sprintId },
                set (value) { this.$emit('updateSprintId', value) },
            },
            errorName() {
                if (this.$v.form.name.$dirty) {
                    if (!this.$v.form.name.required) return "Обязательное поле!";
                }
            },
            errorDescription() {
                if (this.$v.form.description.$dirty) {
                    if (!this.$v.form.description.required) return "Обязательное поле!";
                }
            },
            errorFileId() {
                if (this.$v.form.file_id.$dirty) {
                    if (!this.$v.form.file_id.required) return "Обязательное поле!";
                }
            }
        },
        created() {
            this.reload();
        }
    }
</script>

<style lang="css">
.mx-datepicker-popup {
    overflow: visible !important;
    z-index: 9999;
}
.preview {
    width: 550px;
}
</style>