<template>
    <div>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" :disabled="sprints.length === 0" @click="getTicketsCsv">Получить файл
            </button>
        </div>
        <br/>
        <span>Спринт</span>
        <b-form-select v-model="sprintIdModel" text-field="interval" value-field="id" :options="sprints">
            <template #first>
                <b-form-select-option :value="null">-- Выберите спринт --</b-form-select-option>
            </template>
        </b-form-select>
        <br/>
        <table class="table">
            <thead>
            <tr>
                <th>ID билета</th>
                <th>ID заказа</th>
                <th>ФИО</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Профессия</th>
                <th>Тип билета</th>
                <th>Кол-во билетов в заказе</th>
                <th>Уникальный код</th>
                <th>Статус</th>
                <th>Комментарий</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="ticket in tickets" :key="ticket.id">
                <td>{{ ticket.id }}</td>
                <td>
                    <a :href="getRoute('orders.detail', {id: ticket.order.id})">{{ ticket.order.number }}</a>
                </td>
                <td>{{ fullName(ticket) }}</td>
                <td>{{ ticket.phone }}</td>
                <td>{{ ticket.email }}</td>
                <td>{{ ticket.profession }}</td>
                <td>{{ ticket.type.name }}</td>
                <td>{{ ticket.order.count_tickets }}</td>
                <td>{{ ticket.code }}</td>
                <td><span class="badge" :class="statusClass(ticket.status.id)">{{ ticket.status.name }}</span></td>
                <td>
                    <button v-if="ticket.comment" class="btn" @click="editComment(ticket)">
                        <fa-icon icon="comment-dots"></fa-icon>
                    </button>
                </td>
                <td>
                    <button class="btn" @click="editComment(ticket)">
                        <fa-icon icon="cog"></fa-icon>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('TicketCommentFormModal')">
                <div slot="body">
                    <div class="form-group">
                        <div class="form-group">
                            <v-input v-model="$v.form.comment.$model">
                                Комментарий
                            </v-input>
                        </div>
                        <button v-if="canUpdate(blocks.events)" @click="onSave" type="button" class="btn btn-primary">Сохранить</button>
                        <button @click="onCancel" type="button" class="btn btn-secondary">Отмена</button>
                    </div>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
import {mapActions} from "vuex";
import {
    ACT_LOAD_SPRINTS,
    ACT_LOAD_SPRINT_TICKETS,
    NAMESPACE
} from '../../../../store/modules/public-events';

import modalMixin from '../../../../mixins/modal';
import mediaMixin from '../../../../mixins/media';
import {validationMixin} from 'vuelidate';
import Helpers from "../../../../../scripts/helpers";
import Services from "../../../../../scripts/services/services";
import Modal from '../../../../components/controls/modal/modal.vue';
import VInput from '../../../../components/controls/VInput/VInput.vue';

export default {
    mixins: [
        modalMixin,
        mediaMixin,
        validationMixin,
    ],
    components: {
        VInput,
        Modal,
    },
    props: {
        publicEvent: {},
        sprintId: null,
    },
    data() {
        return {
            sprintIdModel: this.sprintId,
            sprints: [],
            tickets: [],
            form: {
                comment: null,
            },
        };
    },
    validations: {
        form: {
            comment: {},
        }
    },
    methods: {
        ...mapActions(NAMESPACE, {
            loadSprints: ACT_LOAD_SPRINTS,
            loadTickets: ACT_LOAD_SPRINT_TICKETS,
        }),
        reload() {
            this.loadTickets({publicEventId: this.publicEvent.id, sprintId: this.sprintIdModel})
                .then(response => {
                    this.tickets = response.tickets;
                });
            this.loadSprints({publicEventId: this.publicEvent.id})
                .then(response => {
                    this.sprints = response.sprints;
                    if (this.sprints.length) {
                        this.sprints.forEach(sprint => {
                            sprint.interval = this.interval(sprint.date_start, sprint.date_end);
                        });
                    }
                });
        },
        interval(dateStartString, dateEndString) {
            return Helpers.onlyDate(dateStartString) + ' - ' + Helpers.onlyDate(dateEndString);
        },
        fullName(ticket) {
            return ticket.last_name + " " + ticket.first_name + " " + ticket.middle_name;
        },
        statusClass(statusId) {
            switch (statusId) {
                case 1:
                    return 'badge-success';
                case 2:
                    return 'badge-danger';
                default:
                    return 'badge-dark';
            }
        },
        getTicketsCsv() {
            const href = this.getRoute('public-event.tickets.file', {
                event_id: this.publicEvent.id,
                sprint_id: this.sprintIdModel
            })
            window.open(href);
        },
        editComment(ticket) {
            this.$v.form.$reset();
            this.ticketId = ticket.id;
            this.form.comment = ticket.comment;
            this.openModal('TicketCommentFormModal');
        },
        onSave() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }
            Services.showLoader();
            Services.net().post(this.getRoute('public-event.tickets.editComment', {event_id: this.publicEvent.id}), {}, {
                ticketId: this.ticketId,
                comment: this.form.comment,
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
    created() {
        this.reload();
    },
    watch: {
        'sprintIdModel': 'reload',
    },
}
</script>

<style lang="css">
.mx-datepicker-popup {
    overflow: visible !important;
    z-index: 9999;
}

.preview {
    width: 550px;
}
</style>