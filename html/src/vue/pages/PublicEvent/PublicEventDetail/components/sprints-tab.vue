<template>
    <div>
        <div class="d-flex justify-content-between mt-3 mb-3" v-if="canUpdate(blocks.events)">
            <button class="btn btn-success" @click="createSprint()">Добавить спринт</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Начало</th>
                    <th>Конец</th>
                    <th>Статус</th>
                    <th>Скрывать количество мест</th>
                    <th>Площадка</th>
                    <th>Что взять с собой</th>
                    <th>Статусы продажи</th>
                    <th class="text-right" v-if="canUpdate(blocks.events)">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="sprint in sprints" :key="sprint.id">
                    <td>{{sprint.id}}</td>
                    <td>{{sprint.name ? sprint.name : '---'}}</td>
                    <td>{{date(sprint.date_start)}}</td>
                    <td>{{date(sprint.date_end)}}</td>
                    <td>{{statusName(sprint.status_id)}}</td>
                    <td>{{sprint.hide_ticket_count ? 'V' : 'X'}}</td>
                    <td>{{places(sprint.places)}}</td>
                    <td>{{sprint.raider}}</td>
                    <td>{{statusNames(sprint.sellStatuses)}}</td>
                    <td v-if="canUpdate(blocks.events)">
                        <v-delete-button @delete="() => onDeleteSprint([sprint.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editSprint(sprint)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <modal :close="closeModal" v-if="isModalOpen('SprintFormModal')">
            <div slot="header">
                Спринт
            </div>
            <div slot="body">
                <div class="form-group">
                    <v-input v-model="$v.form.name.$model" :error="errorName">Название</v-input>
                    <div class="form-group">
                        <label for="date">Начало</label>
                        <date-picker id="date" input-class="form-control" v-model="$v.form.date_start.$model" value-type="format" format="YYYY-MM-DD" :error="errorDateStart"/>
                    </div>
                    <div class="form-group">
                        <label for="date">Конец</label>
                        <date-picker id="date" input-class="form-control" v-model="$v.form.date_end.$model" value-type="format" format="YYYY-MM-DD" :error="errorDateEnd"/>
                    </div>
                    <v-select v-model="$v.form.status_id.$model" text-field="name" value-field="id" :options="statuses">Статус</v-select>
                    <b-form-checkbox
                        v-model="$v.form.hide_ticket_count.$model"
                        :error="errorHideTicketCount"
                    >
                        Скрывать количество оставшихся билетов
                    </b-form-checkbox>
                    <div class="form-group">
                        <label for="description">Что взять с собой</label>
                        <ckeditor type="classic" v-model="$v.form.raider.$model" />
                    </div>
                    <b-form-group label="Билеты продаются при статусах" :disabled="editSprintId === null">
                        <b-form-checkbox-group
                            v-model="selectedSprintStatusTypes"
                            :options="statuses"
                            value-field="id"
                            text-field="name"
                            @change="toggleSprintStatus"
                        ></b-form-checkbox-group>
                    </b-form-group>
                </div>
                <div class="form-group">
                    <button @click="onSave" type="button" class="btn btn-primary">Сохранить</button>
                    <button @click="onCancel" type="button" class="btn btn-secondary">Отмена</button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {
        ACT_LOAD_SPRINTS,
        ACT_SAVE_SPRINT,
        ACT_DELETE_SPRINT,
        ACT_LOAD_STATUSES,
        NAMESPACE,
        ACT_SAVE_SPRINT_STATUS,
        ACT_DELETE_SPRINT_STATUS
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
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import VueCkeditor from '../../../../plugins/VueCkeditor';

    export default {
        mixins: [
            modalMixin,
            validationMixin,
        ],
        components: {
            Modal,
            VInput,
            VDeleteButton,
            DatePicker,
            VSelect,
            VueCkeditor
        },
        props: {
            publicEvent: {},
            sprintStatuses: {}
        },
        data() {
            return {
                sprints: [],
                statuses: [],
                editSprintId: null,
                selectedSprintStatusTypes: [],
                form: {
                    name: null,
                    date_start: null,
                    date_end: null,
                    status_id: null,
                    hide_ticket_count: null,
                    raider: null,
                },
            };
        },
        validations: {
            form: {
                name: {required},
                date_start: {required},
                date_end: {required},
                status_id: {required},
                hide_ticket_count: {required},
                raider: {},
            }
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadSprints: ACT_LOAD_SPRINTS,
                saveSprint: ACT_SAVE_SPRINT,
                deleteSprint: ACT_DELETE_SPRINT,
                loadStatuses: ACT_LOAD_STATUSES,
                saveStatus: ACT_SAVE_SPRINT_STATUS,
                deleteStatus: ACT_DELETE_SPRINT_STATUS
            }),
            date(dateString) {
                return dateString ? Helpers.onlyDate(dateString) : '---';
            },
            statusName(statusId) {
                return this.sprintStatuses[statusId] ? this.sprintStatuses[statusId].name : 'N/A';
            },
            places(items) {
                let result = [];
                items.forEach(item => {
                    result.push(item.name);
                })

                return result.join(', ');
            },
            statusNames(items) {
                let result = [];
                items.forEach(item => {
                    result.push(this.statusName(item.status_id));
                })

                return result.join(', ');
            },
            toggleSprintStatus(sprintStatus) {
                const difference = sprintStatus
                    .filter(x => !this.selectedSprintStatusTypes.includes(x))
                    .concat(this.selectedSprintStatusTypes.filter(x => !sprintStatus.includes(x)));

                const found = this.selectedSprintStatusTypes.filter(x => difference.includes(x));

                if (found.length) {
                    this.deleteStatus({
                        sprint_id: this.editSprintId,
                        status_id: difference[0]
                    });
                } else {
                    this.saveStatus({
                        sprint_id: this.editSprintId,
                        status_id: difference[0]
                    });
                }
            },
            reload() {
                this.loadStatuses()
                    .then(response => {
                        this.statuses = response.statuses;
                    });
                this.loadSprints({publicEventId: this.publicEvent.id})
                    .then(response => {
                        this.sprints = response.sprints;
                    });
            },
            checkActiveStatus(sprintStatuses) {
                let result = [];

                sprintStatuses.forEach(sprintStatuse => {
                    result.push(sprintStatuse.status_id);
                });

                return result;
            },
            createSprint() {
                this.$v.form.$reset();
                this.editSprintId = null;
                this.form.public_event_id = this.publicEvent.id;
                this.form.name = null;
                this.form.date_start = null;
                this.form.date_end = null;
                this.form.status_id = null;
                this.form.hide_ticket_count = false;
                this.form.raider = null;
                this.selectedSprintStatusTypes = [];
                this.openModal('SprintFormModal');
            },
            editSprint(sprint) {
                this.$v.form.$reset();
                this.editSprintId = sprint.id;
                this.form.public_event_id = sprint.public_event_id;
                this.form.name = sprint.name;
                this.form.date_start = sprint.date_start;
                this.form.date_end = sprint.date_end;
                this.form.status_id = sprint.status_id;
                this.form.hide_ticket_count = sprint.hide_ticket_count ? true : false;
                this.form.raider = sprint.raider;
                this.selectedSprintStatusTypes = this.checkActiveStatus(sprint.sellStatuses);
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
            errorName() {
                if (this.$v.form.name.$dirty) {
                    if (!this.$v.form.name.required) return "Обязательное поле!";
                }
            },
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
            errorHideTicketCount() {
                if (this.$v.form.hide_ticket_count.$dirty) {
                    if (!this.$v.form.hide_ticket_count.required) return "Обязательное поле!";
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
