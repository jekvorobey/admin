<template>
    <div>
        <span>Спринт</span>
        <b-form-select v-model="sprintIdModel" text-field="interval" value-field="id" :options="sprints" @change="onChangeSprint(sprintId)" />

        <span>Этап</span>
        <b-form-select v-model="sprintStageId" text-field="name" value-field="id" :options="sprintStages" @change="onChangeSprintStage(sprintStageId)" />

        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" :disabled="sprints.length == 0 || sprintStages.length == 0" @click="openModal('eventSpeakersModalForm')">Добавить спикера</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Отчество</th>
                    <th>Фамилия</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Описание</th>
                    <th>Instagram</th>
                    <th>Facebook</th>
                    <th>LinkedIn</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="speaker in speakers" :key="speaker.id">
                    <td>{{speaker.id}}</td>
                    <td>{{speaker.first_name}}</td>
                    <td>{{speaker.middle_name}}</td>
                    <td>{{speaker.last_name}}</td>
                    <td>{{speaker.phone}}</td>
                    <td>{{speaker.email}}</td>
                    <td>{{speaker.description}}</td>
                    <td>{{speaker.instagram}}</td>
                    <td>{{speaker.facebook}}</td>
                    <td>{{speaker.linkedin}}</td>
                    <td>
                        <v-delete-button @delete="() => onDeleteSpeaker(speaker.id)" class="float-right ml-1"/>
                    </td>
                </tr>
            </tbody>
        </table>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('eventSpeakersModalForm')">
                <div slot="header">
                    Спикеры
                </div>
                <div slot="body">
                    <speaker-form :sprintStageId="sprintStageId" @onSave="onSave"/>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {
        ACT_LOAD_SPRINTS,
        NAMESPACE,
        ACT_LOAD_SPRINT_STAGES,
        ACT_LOAD_EVENT_SPEAKERS,
        ACT_DELETE_EVENT_SPEAKER
    } from '../../../../store/modules/public-events';
    
    import Helpers from '../../../../../scripts/helpers';
    import modalMixin from '../../../../mixins/modal';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../../scripts/services/services";
    import SpeakerForm from './forms/speaker-form.vue';

    export default {
        mixins: [
            modalMixin
        ],
        components: {
            Modal,
            VInput,
            VDeleteButton,
            SpeakerForm
        },
        props: {
            publicEvent: {},
            sprintId: null,
        },
        data() {
            return {
                sprints: [],
                sprintStages: [],
                sprintStageId: null,
                speakers: []
            };
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadSprints: ACT_LOAD_SPRINTS,
                loadSprintStages: ACT_LOAD_SPRINT_STAGES,
                loadEventSpeakers: ACT_LOAD_EVENT_SPEAKERS,
                deleteEventSpeaker: ACT_DELETE_EVENT_SPEAKER
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
                this.loadSprintStages({sprintId})
                    .then(response => {
                        this.sprintStages = response.sprintStages;
                        if (this.sprintStages.length) {
                            this.sprintStageId = this.sprintStages[0].id;
                            this.onChangeSprintStage(this.sprintStageId);
                        }
                    });
            },
            onChangeSprintStage(sprintStageId) {
                this.loadEventSpeakers({sprintStageId})
                    .then(response => {
                        this.speakers = response.speakers;
                    });
            },
            onDeleteSpeaker(id) {
                Services.showLoader();
                this.deleteEventSpeaker({
                    stageId: this.sprintStageId,
                    id: id
                }).then(() => {
                    this.reload();
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            onSave() {
                this.closeModal();
                this.$emit('onChange');
                this.reload();
            },
            onCancel() {
                this.closeModal();
            },
        },
        computed: {
            sprintIdModel: {
                get () { return this.sprintId },
                set (value) { this.$emit('updateSprintId', value) },
            },
        },
        created() {
            this.reload();
        }
    }
</script>

<style scoped>

</style>