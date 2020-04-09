<template>
    <layout-main>
        <table class="table">
        <tbody>
            <tr v-for="f in form">
                <td>{{ f.name }}</td>
                <td><input class="form-control form-control-sm" v-model="f.value"/></td>
                <td>
                    <button class="btn btn-success btn-sm" @click="saveCommission(f)"><fa-icon icon="save"/></button>
                </td>
            </tr>
        </tbody>
        </table>
    </layout-main>
</template>

<script>

import Services from '../../../../scripts/services/services.js';

export default {
    props: ['iForm'],
    data() {
        return {
            form: this.iForm,
        };
    },
    methods: {
        saveCommission(f) {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.commission.save'), {}, {
                type: f.type,
                value: f.value,
                rating_id: f.rating_id,
            }).then(() => {
                Services.msg('Данные сохранены');
            }).finally(() => {
                Services.hideLoader();
            })
        }
    }
};
</script>
