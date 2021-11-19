<template>
    <div>
        <div class="d-flex justify-content-between mt-3 mb-3" v-if="canUpdate(blocks.events)">
            <button class="btn btn-success" @click="openModal('eventRecommendationModalForm')">Добавить рекомендацию</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th class="text-right" v-if="canUpdate(blocks.events)">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="eventRecommendation in eventRecommendations" :key="eventRecommendation.id">
                    <td>{{eventRecommendation.id}}</td>
                    <td>{{eventRecommendation.name}}</td>
                    <td v-if="canUpdate(blocks.events)">
                        <v-delete-button @delete="() => onDelete([eventRecommendation.id])" class="float-right ml-1"/>
                    </td>
                </tr>
            </tbody>
        </table>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('eventRecommendationModalForm')">
                <div slot="header">
                    События
                </div>
                <div slot="body">
                    <recommendation-form :publicEvent="publicEvent" :events="events" @onSave="onSave"/>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {
        NAMESPACE,
        ACT_LOAD_EVENT_RECOMMENDATIONS,
        ACT_DELETE_EVENT_RECOMMENDATION,
        ACT_LOAD_EVENTS
    } from '../../../../store/modules/public-events';

    import Helpers from '../../../../../scripts/helpers';
    import modalMixin from '../../../../mixins/modal';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../../scripts/services/services";
    import RecommendationForm from './forms/recommendation-form.vue';

    export default {
        mixins: [
            modalMixin,
            validationMixin,
        ],
        components: {
            Modal,
            VInput,
            VDeleteButton,
            RecommendationForm
        },
        props: {
            publicEvent: {},
        },
        data() {
            return {
                events: [],
                eventRecommendations: []
            };
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadEvents: ACT_LOAD_EVENTS,
                loadEventRecommendations: ACT_LOAD_EVENT_RECOMMENDATIONS,
                deleteEventRecommendation: ACT_DELETE_EVENT_RECOMMENDATION
            }),
            reload() {
                this.loadEvents()
                    .then(response => {
                        this.events = response.events;
                    });
                this.loadEventRecommendations({publicEventId: this.publicEvent.id})
                    .then(response => {
                        this.eventRecommendations = response.recommendations;
                    });
            },
            onDelete(ids) {
                Services.showLoader();
                this.deleteEventRecommendation({
                    event_id: this.publicEvent.id,
                    recommendation_id: ids
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
        },
        created() {
            this.reload();
        }
    }
</script>

<style scoped>

</style>
