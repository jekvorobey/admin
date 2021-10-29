<template>
    <div class="d-flex justify-content-start align-items-start">
        <shadow-card title="Свойства события" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('eventMainEdit')">
            <table class="values-table">
                <tbody>
                    <tr>
                        <th>Название:</th>
                        <td>{{ publicEvent.name }}</td>
                    </tr>
                    <tr>
                        <th>Код:</th>
                        <td>{{ publicEvent.code }}</td>
                    </tr>
                    <tr>
                        <th>Тип:</th>
                        <td>{{ typeName }}</td>
                    </tr>
                    <tr>
                        <th>Галерея:</th>
                        <td>{{ publicEvent.gallery_top ? 'Сверху' : 'Cнизу'}}</td>
                    </tr>
                </tbody>
            </table>
        </shadow-card>
        <shadow-card title="Организатор" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('eventOrganizerEdit')">
            <table v-if="publicEvent.organizer" class="values-table">
                <tbody>
                    <tr>
                        <th>Название:</th>
                        <td>{{ publicEvent.organizer.name }}</td>
                    </tr>
                    <tr>
                        <th>Описание:</th>
                        <td>{{ publicEvent.organizer.description }}</td>
                    </tr>
                    <tr>
                        <th>Телефон:</th>
                        <td>{{ formatPhone(publicEvent.organizer.phone) }}</td>
                    </tr>
                    <tr>
                        <th>E-mail:</th>
                        <td>{{ publicEvent.organizer.email }}</td>
                    </tr>
                    <tr>
                        <th>Сайт:</th>
                        <td>{{ publicEvent.organizer.site }}</td>
                    </tr>
                </tbody>
            </table>
        </shadow-card>

        <transition name="modal" v-if="canUpdate(blocks.events)">
            <modal :close="closeModal" v-if="isModalOpen('eventMainEdit')">
                <div slot="header">
                    Редактирование события
                </div>
                <div slot="body">
                    <public-event-form :public-event="publicEvent" @onSave="onSave"/>
                </div>
            </modal>
        </transition>

        <transition name="modal" v-if="canUpdate(blocks.events)">
            <modal :close="closeModal" v-if="isModalOpen('eventOrganizerEdit')">
                <div slot="header">
                    Редактирование организатора
                </div>
                <div slot="body">
                    <organizer-form
                            :event-id="publicEvent.id"
                            :organizer="publicEvent.organizer"
                            @onSave="onSave"/>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import Helpers from '../../../../../scripts/helpers';

    import modalMixin from '../../../../mixins/modal.js';
    import ShadowCard from '../../../../components/shadow-card.vue';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import PublicEventForm from './forms/public-event-form.vue';
    import OrganizerForm from './forms/organizer-form.vue';

    export default {
        mixins: [modalMixin],
        components: {
            ShadowCard,
            Modal,
            PublicEventForm,
            OrganizerForm
        },
        props: {
            publicEvent: {}
        },
        methods: {
            onSave() {
                this.closeModal();
                this.$emit('onChange');
            },
            formatPhone(phoneString) {
                return Helpers.formatPhoneNumber(phoneString);
            }
        },
        computed: {
            typeName() {
                let type = this.publicEventTypes.find(type => type.id === this.publicEvent.type_id);
                return type ? type.name : 'N/A';
            }
        }
    }
</script>

<style scoped>
    .value {
        margin: 0;
        padding: 0;
    }
    .values-table th {
        text-align: end;
        padding-right: 8px;
    }
    .values-table td {
        padding-left: 8px;
    }
</style>