<template>
    <div>
        <button class="btn btn-success mt-3 mb-3" @click="onCreateSprint">Создать спринт</button>

        <div role="tablist">
            <b-card v-for="sprint in sprints" :key="sprint.id" no-body style="margin-top:-2px">
                <b-card-header
                        header-tag="header"
                        role="tab"
                        v-b-toggle="'collapse' + sprint.id"
                        class="d-flex justify-content-between flex-row"
                >
                    <div class="w-25">{{ date(sprint.date_start) }}</div>
                    <div class="w-25">{{ date(sprint.date_end) }}</div>
                    <div class="w-25">{{ statusName(sprint.status_id) }}</div>
                    <div class="w-25">{{ sprint.place ? sprint.place.name : '---' }}</div>
                </b-card-header>
                <b-collapse :id="'collapse' + sprint.id" accordion="sprints" role="tabpanel">
                    <b-card-body>
                        <button class="btn btn-outline-dark">
                            <fa-icon icon="copy"/>
                        </button>
                        <button class="btn btn-warning">
                            <fa-icon icon="dollar-sign"/>
                        </button>
                        <v-delete-button @delete="onDeleteSprint(sprint.id)"/>
                    </b-card-body>
                </b-collapse>
            </b-card>
        </div>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {
        ACT_CREATE_SPRINT,
        ACT_DELETE_SPRINT,
        ACT_LOAD_SPRINTS,
        NAMESPACE
    } from '../../../../store/modules/public-events';
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
                loadSprints: ACT_LOAD_SPRINTS,
                createSprint: ACT_CREATE_SPRINT,
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
                    .then(sprints => {
                        this.sprints = sprints;
                    });
            },
            onCreateSprint() {
                this.createSprint({publicEventId: this.publicEvent.id})
                    .then(() => {
                        this.reload();
                        this.$emit('onChanged');
                    });
            },
            onDeleteSprint(sprintId) {
                this.deleteSprint({
                    publicEventId: this.publicEvent.id,
                    sprintId
                })
                    .then(() => {
                        this.reload();
                        this.$emit('onChange');
                    });
            }
        },
        computed: {
            $const() {
                return $const;
            }
        },
        created() {
            this.reload();
        }
    }
</script>

<style scoped>

</style>