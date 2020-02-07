<template>
    <div>
         <table class="table table-sm">
            <thead>
            <tr>
                <th colspan="2">Основная информация</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th>Профессиональная деятельность</th>
                <td></td>
            </tr>
            <tr>
                <th>Пол</th>
                <td></td>
            </tr>
            <tr>
                <th>Дата рождения</th>
                <td></td>
            </tr>
            <tr>
                <th>Город</th>
                <td></td>
            </tr>
            <tr>
                <th>Сумма покупок накопительным итогом</th>
                <td></td>
            </tr>
            <tr>
                <th>Подписка на рассылку</th>
                <td></td>
            </tr>
            <tr>
                <th>Дата регистрации</th>
                <td>{{ customer.created_at }}</td>
            </tr>
            <tr>
                <th>Профили в социальных сетях</th>
                <td>
                    <div v-for="social in customer.socials">
                        {{ social.driver }}: {{ social.name }}
                    </div>
                    <div v-if="!customer.socials.length">-</div>
                </td>
            </tr>
            <tr>
                <th>Сертификаты</th>
                <td>
                    <div v-for="certificate in certificates">
                        <a :href="certificate.url" target="_blank">{{ certificate.name }}</a>
                    </div>
                    <div v-if="!certificates.length">-</div>
                </td>
            </tr>
            <tr>
                <th>Персональный Менеджер</th>
                <td></td>
            </tr>
            <tr>
                <th>Служебный комментарий</th>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'info-main',
    props: ['customer'],
    data() {
        return {
            certificates: [],
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.main', {id: this.customer.id}), {user_id: this.customer.user_id}).then(data => {
            this.certificates = data.certificates;
            Services.event().$emit('kpiLoad', {kpis: data.kpis});
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>