<template>
    <div>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" @click="createSprint()">Добавить спринт</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Начало</th>
                    <th>Конец</th>
                    <th>Статус</th>
                    <th>Площадка</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="sprint in sprints" :key="sprint.id">
                    <td>{{sprint.id}}</td>
                    <td>{{date(sprint.date_start)}}</td>
                    <td>{{date(sprint.date_end)}}</td>
                    <td>{{statusName(sprint.status_id)}}</td>
                    <td>{{sprint.place ? sprint.place.name : '---'}}</td>
                    <td>
                        <v-delete-button @delete="() => onDeleteSprint([sprint.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editSprint(sprint)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('SprintFormModal')">
                <div slot="header">
                    Спринт
                </div>
                <div slot="body">
                    <div class="form-group">
                        <div class="form-group">
                            <label for="date">Начало</label>
                            <date-picker id="date" input-class="form-control" v-model="$v.form.date_start.$model" value-type="format" format="YYYY-MM-DD" :error="errorDateStart"/>
                        </div>
                        <div class="form-group">
                            <label for="date">Конец</label>
                            <date-picker id="date" input-class="form-control" v-model="$v.form.date_end.$model" value-type="format" format="YYYY-MM-DD" :error="errorDateEnd"/>
                        </div>
                    </div>
                    <div class="form-group">
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
        ACT_SAVE_SPRINT,
        ACT_DELETE_SPRINT,
        NAMESPACE
    } from '../../../../store/modules/public-events';
    
    import Helpers from '../../../../../scripts/helpers';
    import modalMixin from '../../../../mixins/modal';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../../scripts/services/services";
    import DatePicker from 'vue2-datepicker';
    import 'vue2-datepicker/index.css';
    import 'vue2-datepicker/locale/ru.js';

    export default {
        mixins: [
            modalMixin,
            validationMixin,
        ],
        components: {
            Modal,
            VInput,
            VDeleteButton,
            DatePicker
        },
        props: {
            publicEvent: {},
            sprintStatuses: {}
        },
        data() {
            return {
                sprints: [],
                editSprintId: null,
                form: {
                    date_start: null,
                    date_end: null,
                },
            };
        },
        validations: {
            form: {
                date_start: {required},
                date_end: {required},
            }
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadSprints: ACT_LOAD_SPRINTS,
                saveSprint: ACT_SAVE_SPRINT,
                deleteSprint: ACT_DELETE_SPRINT,
            }),
            date(dateString) {
                return dateString ? Helpers.onlyDate(dateString) : '---';
            },
            statusName(statusId) {
                return this.sprintStatuses[statusId] ? this.sprintStatuses[statusId].name : 'N/A';
            },
            reload() {
                this.loadSprints({publicEventId: this.publicEvent.id})
                    .then(response => {
                        this.sprints = response.sprints;
                    });
            },
            createSprint() {
                this.$v.form.$reset();
                this.editSprintId = null;
                this.form.public_event_id = this.publicEvent.id;
                this.form.date_start = null;
                this.form.date_end = null;
                this.form.status_id = 1;
                this.openModal('SprintFormModal');
            },
            editSprint(sprint) {
                this.$v.form.$reset();
                this.editSprintId = sprint.id;
                this.form.public_event_id = sprint.public_event_id;
                this.form.date_start = sprint.date_start;
                this.form.date_end = sprint.date_end;
                this.status_id = sprint.status_id;
                this.openModal('SprintFormModal');
            },
            onDeleteSprint(ids) {
                Services.showLoader();
                this.deleteSprint({
                    ids
                }).then(() => {
                    this.reload();
                    this.$emit('onChanged');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this.saveSprint({
                    id: this.editSprintId,
                    sprint: this.form
                }).then(() => {
                    this.reload();
                    this.$emit('onChanged');
                }).finally(() => {
                    this.closeModal();
                    Services.hideLoader();
                });
            },
            onCancel() {
                this.closeModal();
            },
        },
        computed: {
            errorDateStart() {
                if (this.$v.form.date_start.$dirty) {
                    if (!this.$v.form.date_start.required) return "Обязательное поле!";
                }
            },
            errorDateEnd() {
                if (this.$v.form.date_end.$dirty) {
                    if (!this.$v.form.date_end.required) return "Обязательное поле!";
                }
            },
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
</style>