<template>
    <layout-main>
        <div class="page-login">
            <h1>Авторизация</h1>
            <form v-on:submit.prevent.stop="login">
                <div class="form-group">
                    <label for="email" class="control-label">E-mail</label>
                    <input v-model="form_data.email" type="text" id="email" name="email" class="form-control"
                            v-validate="'required|email'">
                    <span class="validation-error" v-if="errors.has('email')">
                        {{ errors.first('email') }}
                    </span>
                </div>
                <div class="form-group">
                    <label for="password" class="control-label">Пароль</label>
                    <input v-model="form_data.password" type="password" id="password" name="password" class="form-control"
                            v-validate="'required'">
                    <span class="validation-error" v-if="errors.has('password')">
                        {{ errors.first('password') }}
                    </span>
                </div>
                <div class="form-group">
                    <button @click="login" class="btn btn-primary">Войти</button>
                </div>
            </form>
        </div>
    </layout-main>
</template>

<script>
import Services from '../../../scripts/services/services';
import modalMixin from '../../mixins/modal.js';

import './Login.css';

export default {
    name: 'page-login',
    components: {},
    mixins: [modalMixin],
    props: [],
    data() {
        return {
            form_data: {}
        };
    },
    methods: {
        login() {
            let errorMessage = 'Неверный e-mail и/или пароль.';

            this.$validator
            .validateAll()
            .then(result => {
                if (!result) {
                    return;
                }
                Services.net().post(this.route('login'), null, this.form_data).then(data => {
                    if (data.status === 'ok') {
                        window.location.href = this.route('home');
                    } else {
                        this.showMessageBox({title: 'Ошибка', text: errorMessage});
                    }
                }, () => {
                    this.showMessageBox({title: 'Ошибка', text: errorMessage});
                });
            });
        }
    }
};
</script>
