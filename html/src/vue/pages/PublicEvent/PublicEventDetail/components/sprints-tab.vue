<template>
    <div>
        <button class="btn btn-success">Создать спринт</button>
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Дата начала</th>
                    <th>Дата окончания</th>
                    <th>Статус</th>
                    <th>Место</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="sprint in sprints">
                    <td>{{ date(sprint.date_start) }}</td>
                    <td>{{ date(sprint.date_end) }}</td>
                    <td>{{ statusName(sprint.status_id) }}</td>
                    <td>{{ sprint.place ? sprint.place.name : '---' }}</td>
                    <td>
                        <button class="btn btn-outline-dark">
                            <fa-icon icon="copy"/>
                        </button>
                        <button class="btn btn-warning">
                            <fa-icon icon="dollar-sign"/>
                        </button>
                        <v-delete-button/>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {ACT_LOAD_SPRINTS, NAMESPACE} from '../../../../store/modules/public-events';
    import Helpers from '../../../../../scripts/helpers';

    import modalMixin from '../../../../mixins/modal.js';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';

    const $const = {
        editSprint: 'editSprint',
    };

    export default {
        mixins: [modalMixin],
        props: {
            publicEvent: {},
            sprintStatuses: {}
        },
        components: {
            Modal,
            VDeleteButton
        },
        data() {
            return {
                sprints: []
            };
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadSprints: ACT_LOAD_SPRINTS
            }),
            date(dateString) {
                return dateString ? Helpers.onlyDate(dateString) : '---';
            },
            statusName(statusId) {
                return this.sprintStatuses[statusId] ? this.sprintStatuses[statusId].name : 'N/A';
            }
        },
        computed: {
            $const() {
                return $const;
            }
        },
        created() {
            this.loadSprints({publicEventId: this.publicEvent.id})
                .then(sprints => {
                    this.sprints = sprints;
                });
        }
    }
</script>

<style scoped>

</style>