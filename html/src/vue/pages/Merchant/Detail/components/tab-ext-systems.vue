<template>
    <div>
        <button v-if="!extSystem" @click="create()" class="btn btn-success btn-md">
            Создать интеграцию c 1С
        </button>

        <table v-else class="table table-sm">
            <tbody>
            <tr>
                <th width="400px">ID</th>
                <td>{{ extSystem.id }}</td>
            </tr>
            <tr>
                <th width="400px">Логин</th>
                <td>{{ extSystem.connection_params.login }}</td>
            </tr>
            <tr>
                <th width="400px">Пароль</th>
                <td>{{ extSystem.connection_params.password }}</td>
            </tr>
            <tr>
                <th width="400px">Дата создания</th>
                <td>{{ datetimePrint(extSystem.created_at) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>

import Services from "../../../../../scripts/services/services";
import {mapActions} from "vuex";

export default {
    name: 'tab-ext-systems',
    props: ['id'],
    data() {
        return {
            extSystem: null,
        }
    },
    methods: {
        ...mapActions({
            showMessageBox: 'modal/showMessageBox',
        }),
        loadExtSystem() {
            Services.showLoader();

            Services.net().get(this.getRoute('merchant.detail.extSystems', {id: this.id})).then(data => {
                this.extSystem = data.extSystem;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        create() {
            Services.showLoader();

            Services.net().post(this.getRoute('merchant.detail.extSystems.store', {id: this.id})).then(() => {
                Services.msg('Интеграция успешно создана');
                this.loadExtSystem();
            }).finally(() => {
                Services.hideLoader();
            })

        },
    },
    created() {
        this.loadExtSystem();
    }
}
</script>
