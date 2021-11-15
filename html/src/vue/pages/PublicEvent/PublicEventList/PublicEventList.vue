<template>
    <layout-main>
        <b-card>
            <template v-slot:header>
                Фильтр
            </template>
            <div class="row">
                <f-input v-model="filter.name" class="col-sm-12 col-md-3 col-xl-2">
                    Название
                </f-input>
                <f-select v-model="filter.status_id" :options="statusOptions" class="col-sm-12 col-md-3 col-xl-2">
                    Статус
                </f-select>
            </div>
            <template v-slot:footer>
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего мероприятий: {{ pager.total }} </span>
            </template>
        </b-card>
        <div class="d-flex justify-content-between mt-3 mb-3" v-if="canUpdate(blocks.orders)">
            <button class="btn btn-sm btn-success" @click="createEvent">Создать</button>
        </div>
        <table class="table mt-3">
            <thead>
            <tr>
                <td></td>
                <th>ID</th>
                <th>Название</th>
                <th>Площадка</th>
                <th>Активных</th>
                <th>Возвратов</th>
                <th>Куплено</th>
                <th>Билеты</th>
                <th>Статус</th>
                <th>Отгружен в Shoppilot</th>
                <th v-if="canUpdate(blocks.events)">Действия</th>
            </tr>
            </thead>
            <tbody>
            <template v-for="publicEvent in publicEvents">
                <tr>
                    <td></td>
                    <td>{{ publicEvent.id }}</td>
                    <td class="with-small">
                        <a :href="getRoute('public-event.detail', {event_id: publicEvent.id})">{{
                                publicEvent.name
                            }}</a>
                        <small>{{ dates(publicEvent) }}</small>
                    </td>
                    <td>{{ placeName(publicEvent) }}</td>
                    <td>{{
                            publicEvent.actualSprint ? publicEvent.actualSprint.totalActiveTicketPrice + ' ₽' : '---'
                        }}
                    </td>
                    <td>{{
                            publicEvent.actualSprint ? publicEvent.actualSprint.totalReturnedTicketPrice + ' ₽' : '---'
                        }}
                    </td>
                    <td>{{ publicEvent.actualSprint ? publicEvent.actualSprint.totalTicketPrice + ' ₽' : '---' }}</td>
                    <td v-html="ticketsCount(publicEvent)"></td>
                    <td v-html="statusIndicator(publicEvent)"></td>
                    <td>
                        <span v-if="'shoppilotExist' in publicEvent" class="badge"
                              :class="{'badge-success': publicEvent.shoppilotExist,'badge-danger':!publicEvent.shoppilotExist}">
                            {{ (publicEvent.shoppilotExist) ? 'Да' : 'Нет' }}
                        </span>
                        <template v-else>Информация временно недоступна</template>
                    </td>
                    <td v-if="canUpdate(blocks.events)">
                        <button class="btn btn-warning float-right" @click="editEvent(publicEvent)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>
        <div>
            <b-pagination
                v-if="pager.pages > 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                @change="changePage"
                :hide-goto-end-buttons="pager.pages < 10"
                class="float-right"
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
                        <v-select v-model="$v.form.status_id.$model" :options="statusOptions"
                                  :error="errorStatusId">Статус
                        </v-select>
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
    GET_PAGE_SIZE,
    GET_NUM_PAGES,
    ACT_SAVE_PUBLIC_EVENT,
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
import FInput from '../../../components/filter/f-input.vue';
import FSelect from '../../../components/filter/f-select.vue';
import Dropdown from '../../../components/dropdown/dropdown.vue';
import qs from "qs";

const cleanFilter = {
    name: '',
    status_id: [],
};

const serverKeys = [
    'name',
    'status_id',
];

export default {
    mixins: [
        modalMixin,
        validationMixin
    ],
    components: {
        FInput,
        FSelect,
        Modal,
        VInput,
        Dropdown,
        VSelect
    },
    props: {
        iPublicEvents: {},
        iTotal: {},
        iCurrentPage: {},
        iFilter: {},
        iPager: {},
        options: {},
        eventStatuses: Object,
    },
    data() {
        let filter = Object.assign({}, cleanFilter, this.iFilter);
        return {
            editEventId: null,
            filter,
            publicEvents: this.iPublicEvents,
            currentPage: this.iCurrentPage,
            total: this.iTotal,
            appliedFilter: {},
            pager: this.iPager,
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
        }),
        changePage(newPage) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                currentPage: newPage,
                filter: this.appliedFilter,
            }));
        },
        loadPage() {
            Services.showLoader();
            Services.net().get(this.route('public-event.list.page'), {
                currentPage: this.currentPage,
                filter: this.appliedFilter,
            }).then(data => {
                this.publicEvents = data.publicEvents;
                if (data.pager) {
                    this.pager = data.pager
                    this.total = data.pager.total
                }
            }).finally(() => {
                Services.hideLoader();
            });
        },
        reload() {
            location.reload();
        },
        applyFilter() {
            let tmpFilter = {};
            for (let [key, value] of Object.entries(this.filter)) {
                if (value && serverKeys.indexOf(key) !== -1) {
                    tmpFilter[key] = value;
                }
            }
            this.appliedFilter = tmpFilter;
            this.currentPage = 1;
            this.changePage(1);
            this.loadPage();
        },
        clearFilter() {
            for (let entry of Object.entries(cleanFilter)) {
                this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
            }
            this.applyFilter();
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
            return `<span class="text-success" title="Продано билетов">${publicEvent.actualSprint.ticketsSoldCount}</span> /
                <span class="text-danger" title="Возвращено билетов">${publicEvent.actualSprint.ticketsReturnedCount}</span> /
                <span title="Всего билетов">${publicEvent.actualSprint.totalTicketsCount}</span>`;
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
            pageSize: GET_PAGE_SIZE,
            numPages: GET_NUM_PAGES,
        }),
        statusOptions() {
            return Object.values(this.options.eventStatuses).map(status => ({
                value: status.id,
                text: status.name
            }));
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
                this.currentPage = query.page;
            }
        };
    },
    watch: {
        currentPage() {
            this.loadPage();
        }
    }
};
</script>
<style scoped>
.td-collapse-wrapper {
    padding: 0 !important;
    border: none !important;
}

.with-small small {
    display: block;
    color: gray;
    line-height: 1rem;
    overflow: hidden;
}
</style>
