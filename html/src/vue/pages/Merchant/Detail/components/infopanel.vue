<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="4">
                Инфопанель
                <button class="btn btn-success btn-sm" @click="saveMerchant" :disabled="!showBtn">
                    Сохранить
                </button>
                <button @click="cancel" class="btn btn-outline-danger btn-sm" :disabled="!showBtn">Отмена</button>
                <button class="btn btn-outline-success btn-sm" @click="activateMerchant" v-if="isRequest">
                    Активировать
                </button>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Название организации</th>
            <td colspan="3">
                <input class="form-control form-control-sm" v-model="form.legal_name"/>
            </td>
        </tr>
        <tr>
            <th>Город</th>
            <td colspan="3">
                <input class="form-control form-control-sm" v-model="form.city"/>
            </td>
        </tr>
        <tr>
            <th>ФИО</th>
            <td colspan="3">
                {{ merchant.main_operator.last_name }}
                {{ merchant.main_operator.first_name }}
                {{ merchant.main_operator.middle_name }}
            </td>
        </tr>
        <tr>
            <th>E-mail</th>
            <td>{{ merchant.main_operator.email }}</td>
            <th>Телефон</th>
            <td>{{ merchant.main_operator.phone }}</td>
        </tr>
        <tr>
            <th>Статус</th>
            <td>
                <select class="form-control form-control-sm" v-model="form.status">
                    <option v-for="status in statuses" :value="status.id">{{ status.name }}</option>
                </select>
            </td>
            <th>Дата получения статуса</th>
            <td>{{ merchant.status_at }}</td>
        </tr>
        <tr>
            <th>Рейтинг</th>
            <td>
                <select class="form-control form-control-sm" v-model="form.rating_id">
                    <option :value="null">Выбрать</option>
                    <option v-for="rating in ratings" :value="rating.id">{{ rating.name }}</option>
                </select>
            </td>
            <th>Менеджер</th>
            <td>
                <select class="form-control form-control-sm" v-model="form.manager_id">
                    <option :value="null">Выбрать</option>
                    <option v-for="manager in managers" :value="manager.id">{{ manager.name }}</option>
                </select>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'infopanel',
    props: ['model', 'statuses', 'ratings', 'managers', 'isRequest'],
    data() {
        return {
            form: {
                legal_name: this.model.legal_name,
                status: this.model.status,
                city: this.model.city,
                rating_id: this.model.rating_id,
                manager_id: this.model.manager_id,
            }
        }
    },
    methods: {
        saveMerchant() {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.edit', {id: this.merchant.id}), {
                merchant: this.form
            }).then(() => {
                this.merchant.legal_name = this.form.legal_name;
                this.merchant.status = this.form.status;
                this.merchant.city = this.form.city;
                this.merchant.rating_id = this.form.rating_id;
                this.merchant.manager_id = this.form.manager_id;
                this.$store.commit('title', this.form.legal_name);
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
        activateMerchant() {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.edit', {id: this.merchant.id}), {
                merchant: {
                    status: this.merchantStatuses.activation
                },
            }).then(() => {
                window.location.reload();
            });
        },
        cancel() {
            this.form.legal_name = this.merchant.legal_name;
            this.form.status = this.merchant.status;
            this.form.city = this.merchant.city;
            this.form.rating_id = this.merchant.rating_id;
            this.form.manager_id = this.merchant.manager_id;
        }
    },
    computed: {
        merchant: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        showBtn() {
            return this.merchant.legal_name !== this.form.legal_name ||
                this.merchant.status !== this.form.status ||
                (this.merchant.city || '') !== (this.form.city || '') ||
                (this.merchant.rating_id || '') !== (this.form.rating_id || '') ||
                (this.merchant.manager_id || '') !== (this.form.manager_id || '');
        },
    },
};
</script>

<style scoped>

</style>