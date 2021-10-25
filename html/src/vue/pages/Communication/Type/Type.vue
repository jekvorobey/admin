<template>
    <layout-main>
        <b-row class="mb-2" v-if="canUpdate(blocks.communications)">
            <b-col>
                <button class="btn btn-success btn-sm" @click="createType()"><fa-icon icon="plus"/></button>
            </b-col>
        </b-row>
        <table class="table">
            <thead>
            <tr>
                <th>Название</th>
                <th>Активность</th>
                <th>Канал</th>
                <th v-if="canUpdate(blocks.communications)">Действия</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="type in types">
                <td>{{ type.name }}</td>
                <td>{{ type.active ? 'да' : 'нет' }}</td>
                <td>{{ type.channel_id ? channels[type.channel_id].name : '-' }}</td>
                <td v-if="canUpdate(blocks.communications)">
                    <button class="btn btn-warning btn-sm" @click="editType(type)"><fa-icon icon="edit"/></button>
                    <v-delete-button @delete="deleteType(type.id)" btn-class="btn-danger btn-sm"/>
                </td>
            </tr>
            </tbody>
        </table>
        <form-modal modal-name="FormType" @accept="saveType" :model.sync="type" :channels="channels"/>
    </layout-main>
</template>

<script>
import modalMixin from '../../../mixins/modal';
import FormModal from './components/form-modal.vue';
import Services from '../../../../scripts/services/services.js';
import VDeleteButton from '../../../components/controls/VDeleteButton/VDeleteButton.vue';

export default {
    mixins: [modalMixin],
    props: ['iTypes', 'channels'],
    components: {VDeleteButton, FormModal},
    data() {
        return {
            types: this.iTypes,
            type: null,
        };
    },
    methods: {
        fillType(types) {
            return {
                id: types ? types.id : false,
                name: types ? types.name : '',
                active: types ? types.active : 1,
                channel_id: types ? types.channel_id : null,
            }
        },
        deleteType(type_id) {
            Services.net().delete(this.getRoute('communications.types.delete', {id: type_id})).then((data)=> {
                this.types = data.types;
                this.$bvToast.toast('Тип удалён', {
                    title: 'Успех',
                    variant: 'success',
                });
            }).catch(() => {
                this.$bvToast.toast('Тип не была удалён', {
                    title: 'Ошибка',
                    variant: 'danger',
                });
            });
        },
        createType() {
            this.type = this.fillType();
            this.openModal('FormType');
        },
        editType(type) {
            this.type = this.fillType(type);
            this.openModal('FormType');
        },
        saveType() {
            Services.net().post(this.getRoute('communications.types.save'), {}, {type: this.type}).then((data)=> {
                this.types = data.types;
                this.closeModal('FormType');
                this.$bvToast.toast(`Тип ${this.type.name} сохранён`, {
                    title: 'Успех',
                    variant: 'success',
                });
                this.type = null;
            });
        },
    },
};
</script>
