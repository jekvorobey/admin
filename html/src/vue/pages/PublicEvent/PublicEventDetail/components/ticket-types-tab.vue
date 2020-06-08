<template>
    <div>
        <span>Спринт</span>
        <b-form-select v-model="sprintId" text-field="interval" value-field="id" :options="sprints" @change="onChangeSprint(sprintId)" />

        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" @click="createTicketType">Добавить тип билета</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Количество</th>
                    <th>Цена</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="ticketType in ticketTypes" :key="ticketType.id">
                    <td>{{ticketType.id}}</td>
                    <td>{{ticketType.name}}</td>
                    <td>{{ticketType.description}}</td>
                    <td>{{ticketType.qty}}</td>
                    <td>{{ticketType.price}}</td>
                    <td>
                        <v-delete-button @delete="() => onDeleteTicketType([ticketType.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editTicketType(ticketType)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('TicketTypeFormModal')">
                <div slot="header">
                    Тип билета
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-input v-model="$v.form.name.$model" :error="errorName">Название</v-input>
                        <v-input v-model="$v.form.description.$model" :error="errorDescription">Описание</v-input>
                        <v-input v-model="$v.form.qty.$model" type="number" :error="errorQty">Количество</v-input>
                        <v-input v-model="$v.form.price.$model" type="number" :error="errorPrice">Цена</v-input>
                    </div>
                    <div class="form-group">
                        <button @click="onSave" type="button" class="btn btn-primary">Сохранить</button>
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
        ACT_SAVE_TICKET_TYPE,
        ACT_DELETE_TICKET_TYPE,
        ACT_LOAD_TICKET_TYPES,
        NAMESPACE
    } from '../../../../store/modules/public-events';
    
    import Helpers from '../../../../../scripts/helpers';
    import modalMixin from '../../../../mixins/modal';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../../scripts/services/services";

    export default {
        mixins: [
            modalMixin,
            validationMixin,
        ],
        components: {
            Modal,
            VInput,
            VDeleteButton
        },
        props: {
            publicEvent: {},
        },
        data() {
            return {
                sprints: [],
                sprintId: null,
                ticketTypes: [],
                editTicketTypeId: null,
                form: {
                    name: null,
                    description: null,
                    qty: null,
                    price: null,
                },
            };
        },
        validations: {
            form: {
                name: {required},
                description: {required},
                qty: {required},
                price: {required},
            }
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadSprints: ACT_LOAD_SPRINTS,
                loadTicketTypes: ACT_LOAD_TICKET_TYPES,
                saveTicketType: ACT_SAVE_TICKET_TYPE,
                deleteTicketType: ACT_DELETE_TICKET_TYPE
            }),
            reload() {
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
            onChangeSprint(sprintId) {
                this.loadTicketTypes({sprintId: sprintId})
                    .then(response => {
                        this.ticketTypes = response.ticketTypes;
                    });
            },
            createTicketType() {
                this.$v.form.$reset();
                this.editTicketTypeId = null;
                this.form.sprint_id = this.sprintId;
                this.form.name = null;
                this.form.description = null;
                this.form.qty = null;
                this.form.price = null;
                this.openModal('TicketTypeFormModal');
            },
            editTicketType(ticketType) {
                this.$v.form.$reset();
                this.editTicketTypeId = ticketType.id;
                this.form.sprint_id = this.sprintId;
                this.form.name = ticketType.name;
                this.form.description = ticketType.description;
                this.form.qty = ticketType.qty;
                this.form.price = ticketType.price;
                this.openModal('TicketTypeFormModal');
            },
            onDeleteTicketType(ids) {
                Services.showLoader();
                this.deleteTicketType({
                    ids
                }).then(() => {
                    this.reload();
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this.saveTicketType({
                    id: this.editTicketTypeId,
                    ticketType: this.form
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
            errorQty() {
                if (this.$v.form.qty.$dirty) {
                    if (!this.$v.form.qty.required) return "Обязательное поле!";
                }
            },
            errorPrice() {
                if (this.$v.form.price.$dirty) {
                    if (!this.$v.form.price.required) return "Обязательное поле!";
                }
            },
        },
        created() {
            this.reload();
        }
    }
</script>

<style scoped>

</style>