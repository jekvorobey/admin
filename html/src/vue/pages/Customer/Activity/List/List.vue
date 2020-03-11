<template>
    <layout-main>
        <b-row class="mb-2">
            <b-col>
                <button class="btn btn-success btn-sm" @click="openActivity()"><fa-icon icon="plus"/></button>
            </b-col>
        </b-row>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Активность</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="activity in activities">
                    <td>{{ activity.id }}</td>
                    <td>{{ activity.name }}</td>
                    <td>{{ activity.active ? 'Да' : 'Нет' }}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" @click="openActivity(activity)"><fa-icon icon="edit"/></button>
                    </td>
                </tr>
            </tbody>
        </table>
        <form-modal modal-name="FormActivity" @accept="saveActivity" :model.sync="activity"/>
    </layout-main>
</template>

<script>

import FormModal from './components/form-modal.vue';
import modalMixin from '../../../../mixins/modal';
import Services from '../../../../../scripts/services/services.js';

export default {
    mixins: [modalMixin],
    props: ['iActivities'],
    components: {FormModal},
    data() {
        return {
            activities: this.iActivities,
            activity: {}
        };
    },
    methods: {
        fillActivity(activity) {
            return {
                id: activity ? activity.id : false,
                name: activity ? activity.name : '',
                active: activity ? activity.active : 1,
            }
        },
        openActivity(activity) {
            this.activity = this.fillActivity(activity);
            this.openModal('FormActivity');
        },
        saveActivity() {
            Services.net().post(this.getRoute('customers.activities.save'), {}, this.activity).then((data)=> {
                this.activities = data.activities;
                this.closeModal('FormActivity');
                this.$bvToast.toast(`Вид деятельности ${this.activity.name} сохранён`, {
                    title: 'Успех',
                    variant: 'success',
                });
                this.status = null;
            });
        }
    },
};
</script>
