<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="4">Инфопанель <button class="btn btn-success btn-sm" @click="saveCustomer">Сохранить</button></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Роль</th>
            <td colspan="3">
                {{ customer.referral ? 'Реферальный Партнер' : 'Профессионал' }}
                ({{ customer.role_date }})
            </td>
        </tr>
        <tr>
            <th>Статус</th>
            <td>
                <select class="form-control form-control-sm" v-model="form.status">
                    <option v-for="(title, id) in statuses" :value="id">{{ title }}</option>
                </select>
            </td>
            <th>Сегмент</th>
            <td></td>
        </tr>
        <tr>
            <th>ФИО</th>
            <td>
                <input class="form-control form-control-sm" v-model="form.last_name" placeholder="Фамилия"/>
            </td>
            <td>
                <input class="form-control form-control-sm" v-model="form.first_name" placeholder="Имя"/>
            </td>
            <td>
                <input class="form-control form-control-sm" v-model="form.middle_name" placeholder="Отчество"/>
            </td>
        </tr>
        <tr>
            <th>ID</th>
            <td>{{ customer.id }}</td>
            <th>Фото</th>
            <td></td>
        </tr>
        <tr>
            <th>E-mail</th>
            <td>
                <input class="form-control form-control-sm" v-model="form.email" :disabled="!user.isSuper"/>
            </td>
            <th>Телефон</th>
            <td>
                <input class="form-control form-control-sm" v-model="form.phone" :disabled="!user.isSuper"/>
            </td>
        </tr>
        <tr>
            <th>Соцсети</th>
            <td>
                <div v-for="social in customer.socials">
                    {{ social.driver }}: {{ social.name }}
                </div>
                <div v-if="!customer.socials.length">-</div>
            </td>
            <th>Ссылка на портфолио</th>
            <td>
                <div v-for="portfolio in customer.portfolios">
                    <a :href="portfolio.link" target="_blank">{{ portfolio.name }}</a>
                </div>
                <div v-if="!customer.portfolios.length">-</div>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import { mapGetters } from 'vuex';

export default {
    name: 'infopanel',
    props: ['model', 'statuses'],
    data() {
        return {
            editStatus: false,
            form: {
                status: this.model.status,
                last_name: this.model.last_name,
                first_name: this.model.first_name,
                middle_name: this.model.middle_name,
                email: this.model.email,
                phone: this.model.phone,
            }
        };
    },
    computed: {
        ...mapGetters(['getRoute']),
        customer: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
    },
    methods: {
        saveCustomer() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.save', {id: this.customer.id}), {}, {
                customer: {
                    status: this.form.status,
                },
                user: {
                    id: this.customer.user_id,
                    last_name: this.form.last_name,
                    first_name: this.form.first_name,
                    middle_name: this.form.middle_name,
                    email: this.form.email,
                    phone: this.form.phone,
                }
            }).then(data => {
                this.customer.status = this.form.status;
                this.customer.last_name = this.form.last_name;
                this.customer.first_name = this.form.first_name;
                this.customer.middle_name = this.form.middle_name;
                this.customer.email = this.form.email;
                this.customer.phone = this.form.phone;
            }).catch(errorMsg => {
                Services.msg(errorMsg || "Ошибка сохранения", 'danger');
            }).then(data => {
                Services.hideLoader();
            })
        },
    }
};
</script>

<style scoped>

</style>