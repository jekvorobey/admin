<template>
    <layout-main>
        <h2>Мероприятия</h2>
        <button class="btn btn-sm btn-success">Создать</button>
        <table class="table mt-3">
            <thead></thead>
            <tbody>
                <template v-for="publicEvent in publicEvents">
                    <tr>
                        <td></td>
                        <td>{{ publicEvent.id }}</td>
                        <td class="with-small">
                            <a :href="getRoute('public-event.detail', {event_id: publicEvent.id})">{{ publicEvent.name }}</a>
                            <small>{{ dates(publicEvent) }}</small>
                        </td>
                        <td>{{ placeName(publicEvent) }}</td>
                        <td v-html="ticketsCount(publicEvent)"></td>
                        <td v-html="statusIndicator(publicEvent)"></td>
                        <!-- <td>
                            <button class="btn btn-outline-dark" v-b-toggle="'collapse' + publicEvent.id">Раскрыть</button>
                        </td> -->
                    </tr>
                    <!-- <tr class="table-light">
                        <td class="td-collapse-wrapper" colspan="3">
                            <b-collapse :id="'collapse' + publicEvent.id" accordion="publicEventList">
                                WIP
                            </b-collapse>
                        </td>
                    </tr> -->
                </template>
            </tbody>
        </table>
    </layout-main>
</template>

<script>
    import {
        NAMESPACE,
        SET_PAGE,
        GET_LIST
    } from "../../../store/modules/public-events";
    import {mapGetters} from "vuex";
    import Helpers from "../../../../scripts/helpers";

    export default {
    components: {},
    props: {
        iPublicEvents: {},
        iTotal: {},
        iCurrentPage: {},
        options: {},
    },
    data() {
        this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
            list: this.iPublicEvents,
            total: this.iTotal,
            page: this.iCurrentPage
        });
        return {

        };
    },

    methods: {
        dates(publicEvent) {
            if (!publicEvent.actualSprint) {
                return '';
            }
            let dates = new Set();
            if (publicEvent.actualSprint.date_start) {
                dates.add(Helpers.onlyDate(publicEvent.actualSprint.date_start));
            }
            if (publicEvent.actualSprint.date_end) {
                dates.add(Helpers.onlyDate(publicEvent.actualSprint.date_end));
            }
            return [...dates].join(' - ');
        },
        placeName(publicEvent) {
            if (!publicEvent.actualSprint || !publicEvent.actualSprint.place) {
                return '';
            }
            return publicEvent.actualSprint.place.name;
        },
        ticketsCount(publicEvent) {
            if (!publicEvent.actualSprint) {
                return '---';
            }
            return `<span class="text-success" title="Продано">${publicEvent.actualSprint.ticketsSoldCount}</span> /
                <span class="text-danger" title="Возвращено">${publicEvent.actualSprint.ticketsReturnedCount}</span> /
                <span title="Всего">${publicEvent.actualSprint.totalTicketsCount}</span>`;
        },
        statusIndicator(publicEvent) {
            let smallStatus = publicEvent.actualSprint ? this.sprintStatusName(publicEvent.actualSprint.status_id) : '';
            return `<div>${this.eventStatusName(publicEvent.status_id)}</div>${smallStatus}`
        },
        // ==
        eventStatusClass(statusId) {
            switch (statusId) {
                case this.publicEventStatus.created:
                    return 'badge-warning';
                case this.publicEventStatus.active:
                    return 'badge-success';
                case this.publicEventStatus.disabled:
                    return 'badge-danger';
                default:
                    return 'badge-light';
            }
        },
        sprintStatusClass(statusId) {
            switch (statusId) {
                case this.publicEventSprintStatus.created:
                    return 'text-warning';
                case this.publicEventSprintStatus.disabled:
                    return 'text-danger';
                case this.publicEventSprintStatus.ready:
                    return 'text-success';
                case this.publicEventSprintStatus.in_process:
                    return 'text-success';
                case this.publicEventSprintStatus.done:
                    return '';
            }
        },
        eventStatusName(statusId) {
            let status = this.options.eventStatuses[statusId] ? this.options.eventStatuses[statusId].name : 'N/A';
            return `<span class="badge ${this.eventStatusClass(statusId)}">${status}</span>`;
        },
        sprintStatusName(statusId) {
            let status = this.options.sprintStatuses[statusId] ? this.options.sprintStatuses[statusId].name : 'N/A';
            return `<small class="${this.sprintStatusClass(statusId)}">${status}</small>`;
        },
    },
    computed: {
        ...mapGetters(NAMESPACE, {
            publicEvents: GET_LIST
        })
    },
};
</script>
<style scoped>
    .td-collapse-wrapper {
        padding: 0 !important;
        border: none !important;
    }
    .with-small small{
        display: block;
        color: gray;
        line-height: 1rem;
        overflow: hidden;
    }
</style>
