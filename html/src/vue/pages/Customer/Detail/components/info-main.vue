<template>
     <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="2">Основная информация <button @click="save" class="btn btn-success">Сохранить</button></th>
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
            <td><a tabindex @click="openOrder">{{ order.price }}</a></td>
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
            <td>
                <select class="form-control form-control-sm" v-model="form.manager_id">
                    <option :value="null">-</option>
                    <option v-for="(manager, id) in managers" :value="id">{{ manager }}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="comment_internal">Служебный комментарий</label></th>
            <td>
                <textarea class="form-control" id="comment_internal" v-model="form.comment_internal"/>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
import { mapGetters } from 'vuex';
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'info-main',
    props: ['model', 'order'],
    data() {
        return {
            certificates: [],
            managers: [],

            form: {
                comment_internal: this.model.comment_internal,
                manager_id: this.model.manager_id,
            }
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        customer: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        }
    },
    methods: {
        save() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.save', {id: this.customer.id}), {}, {
                customer: {
                    comment_internal: this.form.comment_internal,
                    manager_id: this.form.manager_id,
                },
            }).then(data => {
                this.customer.comment_internal = this.form.comment_internal;
                this.customer.manager_id = this.form.manager_id;
                Services.hideLoader();
            })
        },
        openOrder() {
            Services.event().$emit('showTab', 'order');
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.main', {id: this.model.id}), {user_id: this.model.user_id}).then(data => {
            this.managers = data.managers;
            this.certificates = data.certificates;
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>