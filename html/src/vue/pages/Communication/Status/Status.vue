<template>
    <layout-main>
        <b-row class="mb-2" v-if="canUpdate(blocks.communications)">
            <b-col>
                <button class="btn btn-success btn-sm" @click="createStatus()"><fa-icon icon="plus"/></button>
            </b-col>
        </b-row>
        <table class="table">
            <thead>
            <tr>
                <th>Название</th>
                <th>Активность</th>
                <th>По умолчанию</th>
                <th>Канал</th>
                <th v-if="canUpdate(blocks.communications)">Действия</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="status in statuses">
                <td>{{ status.name }}</td>
                <td>{{ status.active ? 'да' : 'нет' }}</td>
                <td>{{ status.default ? 'да' : 'нет' }}</td>
                <td>{{ status.channel_id ? channels[status.channel_id].name : '-' }}</td>
                <td v-if="canUpdate(blocks.communications)">
                    <button class="btn btn-warning btn-sm" @click="editStatus(status)"><fa-icon icon="edit"/></button>
                    <v-delete-button @delete="deleteStatus(status.id)" btn-class="btn-danger btn-sm"/>
                </td>
            </tr>
            </tbody>
        </table>
        <form-modal modal-name="FormStatus" @accept="saveStatus" :model.sync="status" :channels="channels"/>
    </layout-main>
</template>

<script>
import modalMixin from '../../../mixins/modal';
import FormModal from './components/form-modal.vue';
import Services from '../../../../scripts/services/services.js';
import VDeleteButton from '../../../components/controls/VDeleteButton/VDeleteButton.vue';

export default {
    mixins: [modalMixin],
    props: ['iStatuses', 'channels'],
    components: {VDeleteButton, FormModal},
    data() {
        return {
            statuses: this.iStatuses,
            status: null,
        };
    },
    methods: {
        fillStatus(status) {
            return {
                id: status ? status.id : false,
                name: status ? status.name : '',
                active: status ? status.active : 1,
                default: status ? status.default : 0,
                channel_id: status ? status.channel_id : null,
            }
        },
        deleteStatus(status_id) {
            Services.net().delete(this.getRoute('communications.statuses.delete', {id: status_id})).then((data)=> {
                this.statuses = data.statuses;
                this.$bvToast.toast('Статус удалён', {
                    title: 'Успех',
                    variant: 'success',
                });
            }).catch(() => {
                this.$bvToast.toast('Статус не был удалён', {
                    title: 'Ошибка',
                    variant: 'danger',
                });
            });
        },
        createStatus() {
            this.status = this.fillStatus();
            this.openModal('FormStatus');
        },
        editStatus(status) {
            this.status = this.fillStatus(status);
            this.openModal('FormStatus');
        },
        saveStatus() {
            Services.net().post(this.getRoute('communications.statuses.save'), {}, {status: this.status}).then((data)=> {
                this.statuses = data.statuses;
                this.closeModal('FormStatus');
                this.$bvToast.toast(`Статус ${this.status.name} сохранён`, {
                    title: 'Успех',
                    variant: 'success',
                });
                this.status = null;
            });
        },
    },
};
</script>
