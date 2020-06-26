<template>
    <div>
        <span>Спринт</span>
        <b-form-select v-model="sprintId" text-field="interval" value-field="id" :options="sprints" @change="onChangeSprint(sprintId)" />

        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" @click="createSprintStage">Добавить этап программы</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Дата</th>
                    <th>Начало</th>
                    <th>Конец</th>
                    <th>Площадка</th>
                    <th>Что взять с собой</th>
                    <th>Результат</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="sprintStage in sprintStages" :key="sprintStage.id">
                    <td>{{sprintStage.id}}</td>
                    <td>{{sprintStage.name}}</td>
                    <td>{{sprintStage.description}}</td>
                    <td>{{date(sprintStage.date)}}</td>
                    <td>{{sprintStage.time_from}}</td>
                    <td>{{sprintStage.time_to}}</td>      
                    <td>{{place(sprintStage.place_id)}}</td>
                    <td>{{sprintStage.raider}}</td>
                    <td>{{sprintStage.result}}</td>
                    <td>
                        <v-delete-button @delete="() => onDeleteSprintStage([sprintStage.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editSprintStage(sprintStage)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
            <modal :close="closeModal" v-if="isModalOpen('SprintStageFormModal')">
                <div slot="header">
                    Программа
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-input v-model="$v.form.name.$model" :error="errorName">Название</v-input>
                        <div class="form-group">
                            <label for="description">Описание</label>
                            <ckeditor id="description" type="classic" v-model="$v.form.description.$model" :error="errorDescription" />
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Дата</label>
                            <date-picker id="date" input-class="form-control" v-model="$v.form.date.$model" value-type="format" format="YYYY-MM-DD"/>
                        </div>

                        <label for="timeTo">Начало</label>
                        <vue-timepicker id="timeFrom" v-model="$v.form.time_from.$model" format="HH:mm:ss" :error="errorTimeFrom" />
                    
                        <label for="timeTo">Конец</label>
                        <vue-timepicker id="timeTo"  v-model="$v.form.time_to.$model" format="HH:mm:ss" :error="errorTimeTo" />
                    
                        <v-select v-model="$v.form.place_id.$model" text-field="name" value-field="id" :options="places">Площадка</v-select>
                        
                        <div class="form-group">
                            <label for="raider">Что взять с собой</label>
                            <ckeditor id="raider" type="classic" v-model="$v.form.raider.$model" :error="errorRaider" />
                        </div>

                        <div class="form-group">
                            <label for="result">Результат</label>
                            <ckeditor id="result" type="classic" v-model="$v.form.result.$model" :error="errorResult" />
                        </div>
                        
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
        ACT_SAVE_SPRINT_STAGE,
        ACT_DELETE_SPRINT_STAGE,
        ACT_LOAD_SPRINT_STAGES,
        NAMESPACE,
        ACT_LOAD_PLACES
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
    import VueTimepicker from 'vue2-timepicker'
    import 'vue2-timepicker/dist/VueTimepicker.css'
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
            VueTimepicker,
            VSelect,
            VueCkeditor
        },
        props: {
            publicEvent: {},
        },
        data() {
            return {
                sprints: [],
                sprintId: null,
                sprintStages: [],
                editSprintStageId: null,
                places: [],
                form: {
                    name: null,
                    description: null,
                    date: null,
                    place_id: null,
                    raider: null,
                    result: null,
                    time_from: '00:00:00',
                    time_to: '00:00:00'
                },
                
            };
        },
        validations: {
            form: {
                name: {required},
                description: {required},
                date: {required},
                time_from: {required},
                time_to: {required},
                place_id: {required},
                raider: {required},
                result: {required},
            }
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadSprints: ACT_LOAD_SPRINTS,
                loadSprintStages: ACT_LOAD_SPRINT_STAGES,
                saveSprintStage: ACT_SAVE_SPRINT_STAGE,
                deleteSprintStage: ACT_DELETE_SPRINT_STAGE,
                loadPlaces: ACT_LOAD_PLACES
            }),
            reload() {
                this.loadPlaces()
                    .then(response => {
                        this.places = response.places;
                    });
                this.loadSprints({publicEventId: this.publicEvent.id})
                    .then(response => {
                        this.sprints = response.sprints;
                        if (this.sprints.length) {
                            this.sprints.forEach(sprint => {
                                sprint.interval = this.interval(sprint.date_start, sprint.date_end);
                            });
                            this.sprintId = this.sprints[0].id;
                            this.onChangeSprint(this.sprintId);
                        }
                    });
            },
            interval(dateStartString, dateEndString) {
                return Helpers.onlyDate(dateStartString) + ' - ' + Helpers.onlyDate(dateEndString);
            },
            date(dateString) {
                return dateString ? Helpers.onlyDate(dateString) : '---';
            },
            place(placeId) {
                let place = this.places.find(place => place.id === placeId);
                return place ? place.name : 'N/A';
            },
            onChangeSprint(sprintId) {
                this.loadSprintStages({sprintId: sprintId})
                    .then(response => {
                        this.sprintStages = response.sprintStages;
                    });
            },
            createSprintStage() {
                this.$v.form.$reset();
                this.editSprintStageId = null;
                this.form.sprint_id = this.sprintId;
                this.form.name = null;
                this.form.description = null;
                this.form.date = null;
                this.form.time_from = '00:00:00';
                this.form.time_to = '00:00:00';
                this.form.place_id = null;
                this.form.raider = null;
                this.form.result = null;
                this.openModal('SprintStageFormModal');
            },
            editSprintStage(sprintStage) {
                this.$v.form.$reset();
                this.editSprintStageId = sprintStage.id;
                this.form.sprint_id = this.sprintId;
                this.form.name = sprintStage.name;
                this.form.description = sprintStage.description;
                this.form.date = sprintStage.date;
                this.form.time_from = sprintStage.time_from;
                this.form.time_to = sprintStage.time_to;
                this.form.place_id = sprintStage.place_id;
                this.form.raider = sprintStage.raider;
                this.form.result = sprintStage.result;
                this.openModal('SprintStageFormModal');
            },
            onDeleteSprintStage(ids) {
                Services.showLoader();
                this.deleteSprintStage({
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
                this.form.date.toString()
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this.saveSprintStage({
                    id: this.editSprintStageId,
                    sprintStage: this.form
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
        },
        computed: {
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
            errorDate() {
                if (this.$v.form.date.$dirty) {
                    if (!this.$v.form.date.required) return "Обязательное поле!";
                }
            },
            errorTimeFrom() {
                if (this.$v.form.time_from.$dirty) {
                    if (!this.$v.form.time_from.required) return "Обязательное поле!";
                }
            },
            errorTimeTo() {
                if (this.$v.form.time_to.$dirty) {
                    if (!this.$v.form.time_from.required) return "Обязательное поле!";
                }
            },
            errorPlaceId() {
                if (this.$v.form.place_id.$dirty) {
                    if (!this.$v.form.place_id.required) return "Обязательное поле!";
                }
            },
            errorRaider() {
                if (this.$v.form.raider.$dirty) {
                    if (!this.$v.form.raider.required) return "Обязательное поле!";
                }
            },
            errorResult() {
                if (this.$v.form.result.$dirty) {
                    if (!this.$v.form.result.required) return "Обязательное поле!";
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