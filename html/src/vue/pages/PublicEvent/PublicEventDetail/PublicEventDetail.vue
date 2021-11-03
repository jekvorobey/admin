<template>
    <layout-main back>
        <div class="d-flex flex-wrap align-items-stretch justify-content-start product-header">
            <div class="shadow flex-grow-3 mr-3 mt-3">
                <h2>{{ publicEvent.name }}</h2>
                <p class="text-secondary">ID: <span class="float-right">{{ publicEvent.id }}</span></p>
                <p v-if="publicEvent.organizer" class="text-secondary">Организатор: <span class="float-right">{{ publicEvent.organizer.name }}</span></p>
                <template v-if="publicEvent.actualSprint">
                    <p class="text-secondary">Дата начала: <span class="float-right">{{ publicEvent.actualSprint.date_start }}</span></p>
                    <p class="text-secondary">Дата окончания: <span class="float-right">{{ publicEvent.actualSprint.date_end }}</span></p>
                    <p class="text-secondary">Статус: <span class="float-right">{{statusName(publicEvent.actualSprint.status_id)}}</span></p>
                    <p v-if="publicEvent.actualSprint.place" class="text-secondary">Место проведения: <span class="float-right">{{ publicEvent.actualSprint.place.name }}</span></p>
                    <p class="text-secondary">Билеты: <span class="float-right">{{ publicEvent.actualSprint.ticketsSoldCount }} / {{ publicEvent.actualSprint.totalTicketsCount }}</span></p>
                </template>
            </div>
        </div>
        <v-tabs :current="nav.currentTab" :items="nav.tabs" @nav="tab => nav.currentTab = tab"/>
        <main-tab
                v-if="nav.currentTab === 'main'"
                :public-event="publicEvent"
                @onChange="onTabChange"
        />
        <content-tab
                v-if="nav.currentTab === 'content'"
                :public-event="publicEvent"
                @onChange="onTabChange"
        />
        <sprints-tab
                v-if="nav.currentTab === 'sprints'"
                :public-event="publicEvent"
                :sprint-statuses="sprintStatuses"
                @onChange="onTabChange"
        />
        <sprint-stages-tab
                v-if="nav.currentTab === 'spritnStages'"
                :sprint-id.sync="sprintId"
                :public-event="publicEvent"
                @onChange="onTabChange"
                @updateSprintId="updateSprintId"
        />
        <sprint-results-tab
                v-if="nav.currentTab === 'sprintResults'"
                :sprint-id.sync="sprintId"
                :public-event="publicEvent"
                @onChange="onTabChange"
                @updateSprintId="updateSprintId"
        />
        <sprint-orders-tab
                v-if="nav.currentTab === 'orders'"
                :sprint-id.sync="sprintId"
                :public-event="publicEvent"
                @onChange="onTabChange"
        />
        <sprint-tickets-tab
                v-if="nav.currentTab === 'tickets'"
                :sprint-id.sync="sprintId"
                :public-event="publicEvent"
                @onChange="onTabChange"
        />
        <ticket-types-tab
                v-if="nav.currentTab === 'ticketTypes'"
                :sprint-id.sync="sprintId"
                :public-event="publicEvent"
                @onChange="onTabChange"
                @updateSprintId="updateSprintId"
        />
        <speakers-tab
                v-if="nav.currentTab === 'speakers'"
                :sprint-id.sync="sprintId"
                :public-event="publicEvent"
                @onChange="onTabChange"
                @updateSprintId="updateSprintId"
        />
        <professions-tab
                v-if="nav.currentTab === 'professions'"
                :public-event="publicEvent"
                @onChange="onTabChange"
        />
        <recommendations-tab
                v-if="nav.currentTab === 'recommendations'"
                :public-event="publicEvent"
                @onChange="onTabChange"
        />
    </layout-main>
</template>

<script>
    import {mapActions, mapGetters} from "vuex";

    import VTabs from '../../../components/tabs/tabs.vue';

    import MainTab from './components/main-tab.vue';
    import ContentTab from './components/content-tab.vue';
    import SprintsTab from './components/sprints-tab.vue';
    import SprintStagesTab from './components/sprint-stages-tab.vue';
    import TicketTypesTab from './components/ticket-types-tab.vue';
    import SpeakersTab from './components/speakers-tab.vue';
    import ProfessionsTab from './components/professions-tab.vue';
    import RecommendationsTab from './components/recommendations-tab.vue';
    import SprintResultsTab from './components/sprint-results-tab.vue';
    import SprintOrdersTab from './components/sprint-orders-tab.vue';
    import SprintTicketsTab from './components/sprint-tickets-tab.vue';

    import modalMixin from '../../../mixins/modal';
    import {ACT_LOAD_PUBLIC_EVENT, GET_DETAIL, NAMESPACE, SET_DETAIL,} from '../../../store/modules/public-events';

    export default {
    components: {
        VTabs,
        MainTab,
        ContentTab,
        SprintsTab,
        SprintStagesTab,
        TicketTypesTab,
        SpeakersTab,
        ProfessionsTab,
        RecommendationsTab,
        SprintResultsTab,
        SprintOrdersTab,
        SprintTicketsTab,
    },
    mixins: [modalMixin],
    props: {
        iPublicEvent: {},
        sprintStatuses: {}
    },
    data() {
        this.$store.commit(`${NAMESPACE}/${SET_DETAIL}`, {publicEvent: this.iPublicEvent});

        return {
            sprintId: null,
            nav: {
                currentTab: 'main',
                tabs: [
                    {value: 'main', text: 'Основное'},
                    {value: 'content', text: 'Контент'},
                    {value: 'sprints', text: 'Спринты'},
                    {value: 'spritnStages', text: 'Программа'},
                    {value: 'sprintResults', text: 'Результаты'},
                    {value: 'orders', text: 'Заказы'},
                    {value: 'tickets', text: 'Участники'},
                    {value: 'ticketTypes', text: 'Типы билетов'},
                    {value: 'speakers', text: 'Спикеры'},
                    {value: 'professions', text: 'Профессии'},
                    {value: 'recommendations', text: 'Рекомендации'},
                ]
            }
        };
    },

    methods: {
        ...mapActions(NAMESPACE, {
            reload: ACT_LOAD_PUBLIC_EVENT
        }),
        onTabChange() {
            this.reload({id: this.publicEvent.id});
        },
        updateSprintId(sprintId) {
            this.sprintId = sprintId;
        },
        statusName(statusId) {
            return this.sprintStatuses[statusId] ? this.sprintStatuses[statusId].name : 'N/A';
        },
    },
    computed: {
        ...mapGetters(NAMESPACE, {
            publicEvent: GET_DETAIL
        })
    },
};
</script>
<style scoped>
    .product-header {
        min-height: 200px;
    }
    .product-header > div {
        padding: 16px;
    }
    .product-header img {
        max-height: calc( 200px - 16px * 2 );
    }
    .product-header p {
        margin: 0;
        padding: 0;
    }
    .measure {
        width: 30px;
        margin-left: 10px;
    }
</style>
