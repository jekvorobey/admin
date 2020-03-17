<template>
    <layout-main>
        <b-row class="mb-2">
            <b-col>
                <button class="btn btn-success btn-sm" @click="createTheme()"><fa-icon icon="plus"/></button>
            </b-col>
        </b-row>
        <table class="table">
            <thead>
            <tr>
                <th>Название</th>
                <th>Активность</th>
                <th>Канал</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="theme in themes">
                <td>{{ theme.name }}</td>
                <td>{{ theme.active ? 'да' : 'нет' }}</td>
                <td>{{ theme.channel_id ? channels[theme.channel_id].name : '-' }}</td>
                <td>
                    <button class="btn btn-warning btn-sm" @click="editTheme(theme)"><fa-icon icon="edit"/></button>
                    <v-delete-button @delete="deleteTheme(theme.id)" btn-class="btn-danger btn-sm"/>
                </td>
            </tr>
            </tbody>
        </table>
        <form-modal modal-name="FormTheme" @accept="saveTheme" :model.sync="theme" :channels="channels"/>
    </layout-main>
</template>

<script>
import modalMixin from '../../../mixins/modal';
import FormModal from './components/form-modal.vue';
import Services from '../../../../scripts/services/services.js';
import VDeleteButton from '../../../components/controls/VDeleteButton/VDeleteButton.vue';

export default {
    mixins: [modalMixin],
    props: ['iThemes', 'channels'],
    components: {VDeleteButton, FormModal},
    data() {
        return {
            themes: this.iThemes,
            theme: null,
        };
    },
    methods: {
        fillTheme(themes) {
            return {
                id: themes ? themes.id : false,
                name: themes ? themes.name : '',
                active: themes ? themes.active : 1,
                channel_id: themes ? themes.channel_id : null,
            }
        },
        deleteTheme(theme_id) {
            Services.net().delete(this.getRoute('communications.themes.delete', {id: theme_id})).then((data)=> {
                this.themes = data.themes;
                this.$bvToast.toast('Тема удалена', {
                    title: 'Успех',
                    variant: 'success',
                });
            }).catch(() => {
                this.$bvToast.toast('Тема не была удалена', {
                    title: 'Ошибка',
                    variant: 'danger',
                });
            });
        },
        createTheme() {
            this.theme = this.fillTheme();
            this.openModal('FormTheme');
        },
        editTheme(theme) {
            this.theme = this.fillTheme(theme);
            this.openModal('FormTheme');
        },
        saveTheme() {
            Services.net().post(this.getRoute('communications.themes.save'), {}, {theme: this.theme}).then((data)=> {
                this.themes = data.themes;
                this.closeModal('FormTheme');
                this.$bvToast.toast(`Тема ${this.theme.name} сохранена`, {
                    title: 'Успех',
                    variant: 'success',
                });
                this.theme = null;
            });
        },
    },
};
</script>
