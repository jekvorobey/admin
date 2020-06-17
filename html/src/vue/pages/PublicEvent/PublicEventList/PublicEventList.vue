<template>
    <layout-main>
        <h2>Мероприятия</h2>
        <button class="btn btn-sm btn-success" @click="createEvent">Создать</button>
        <table class="table mt-3">
            <thead>
                <tr>
                    <td></td>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Площадка</th>
                    <th>Билеты</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
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
                        <td>
                            <button class="btn btn-warning float-right" @click="editEvent(publicEvent)">
                                <fa-icon icon="edit"></fa-icon>
                            </button>
                        </td>
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
        <div>
            <b-pagination
                    v-if="numPages !== 1"
                    v-model="page"
                    :total-rows="total"
                    :per-page="pageSize"
                    :hide-goto-end-buttons="numPages < 10"
                    class="mt-3 float-right"
            ></b-pagination>
        </div>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('EventFormModal')">
                <div slot="header">
                    Событие
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-input v-model="$v.form.name.$model" :error="errorName">Название</v-input>
                        <v-select v-model="$v.form.status_id.$model" text-field="name" value-field="id" :options="eventStatuses" :error="errorStatusId">Статус</v-select>
                        <button @click="onSave" type="button" class="btn btn-primary">Сохранить</button>
                        <button @click="onCancel" type="button" class="btn btn-secondary">Отмена</button>
                    </div>
                </div>
            </modal>
        </transition>
    </layout-main>
</template>

<script>
    import withQuery from 'with-query';

    import {
        NAMESPACE,
        GET_PAGE_NUMBER,
        GET_TOTAL,
        GET_PAGE_SIZE,
        GET_NUM_PAGES,
        ACT_LOAD_PAGE,
        SET_PAGE,
        GET_LIST,
        ACT_SAVE_PUBLIC_EVENT,
        ACT_LOAD_EVENT_STATUSES
    } from "../../../store/modules/public-events";

    import {mapGetters, mapActions} from "vuex";
    import Helpers from "../../../../scripts/helpers";
    import modalMixin from '../../../mixins/modal';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';
    import VInput from '../../../components/controls/VInput/VInput.vue';
    import Modal from '../../../components/controls/modal/modal.vue';
    import Services from "../../../../scripts/services/services";
    import VSelect from '../../../components/controls/VSelect/VSelect.vue';

    export default {
    mixins: [
        modalMixin,
        validationMixin
    ],
    components: {
        Modal,
        VInput,
        VSelect
    },
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
            editEventId: null,
            eventStatuses: [],
            form: {
                name: null,
                status_id: null,
            },
        };
    },
    validations: {
        form: {
            name: {required},
            status_id: {required},
        }
    },
    methods: {
        ...mapActions(NAMESPACE, {
            savePublicEvent: ACT_SAVE_PUBLIC_EVENT,
            loadEventStatuses: ACT_LOAD_EVENT_STATUSES,
            actLoadPage: ACT_LOAD_PAGE
        }),
        loadPage(page) {
            console.log(page);
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: page,
            }));
            return this.actLoadPage({page});
        },
        reload() {
            location.reload();
        },
        createEvent() {
                this.$v.form.$reset();
                this.editEventId = null;
                this.form.name = null;
                this.form.status_id = null;
                this.openModal('EventFormModal');
        },
        editEvent(event) {
                this.$v.form.$reset();
                this.editEventId = event.id;
                this.form.name = event.name;
                this.form.status_id = event.status_id;
                this.openModal('EventFormModal');
        },
        onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this.savePublicEvent({
                    id: this.editEventId,
                    data: this.form
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
            GET_PAGE_NUMBER,
            total: GET_TOTAL,
            pageSize: GET_PAGE_SIZE,
            numPages: GET_NUM_PAGES,
            publicEvents: GET_LIST
        }),
        page: {
            get: function () {
                return this.GET_PAGE_NUMBER;
            },
            set: function (page) {
                this.loadPage(page);
            }
        },
        errorName() {
            if (this.$v.form.name.$dirty) {
                if (!this.$v.form.name.required) return "Обязательное поле!";
            }
        },
        errorStatusId() {
            if (this.$v.form.status_id.$dirty) {
                if (!this.$v.form.status_id.required) return "Обязательное поле!";
            }
        },
    },
    created() {
        window.onpopstate = () => {
                let query = qs.parse(document.location.search.substr(1));
                if (query.page) {
                    this.page = query.page;
                }
            };
        this.loadEventStatuses()
            .then(response => {
                this.eventStatuses = response.statuses;
            });
    }
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
