<template>
    <b-modal :id="id" title="Создание пользователя" hide-footer ref="modal">
        <template v-slot:default="{close}">
            <div>
                <label>Телефон</label>
                <input class="form-control form-control-sm" v-model="form.phone"/>
            </div>
            <div>
                <label>Пароль</label>
                <input class="form-control form-control-sm" v-model="form.password" type="password"/>
            </div>
            <div>
                <label>Подтверждение пароля</label>
                <input class="form-control form-control-sm" v-model="form.password_confirmation" type="password"/>
            </div>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="save">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>

import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'modal-create-user',
    props: ['id'],
    data() {
        return {
            form: {
                phone: '',
                password: '',
                password_confirmation: '',
            }
        }
    },
    methods: {
        save() {
            Services.showLoader();
            Services.net().post(this.getRoute('customers.create'), {}, this.form).then(data => {
                window.location = data.redirect;
            }).finally(() => {
                Services.hideLoader();
            });
        }
    }
};
</script>

<style scoped>

</style>
