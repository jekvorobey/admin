<template>
    <layout-main>
        <div class="password-confirm">
            <h1>Установка нового пароля</h1>
            <form v-on:submit.prevent.stop="passwordConfirm">
                <div class="form-group">
                    <label for="password" class="control-label">Пароль</label>
                    <input v-model="form_data.password" v-validate="'required'" id="password" name="password" type="password" class="form-control" :class="{'is-danger': errors.has('password')}" placeholder="Пароль" ref="password">
                    <span v-show="errors.has('password')" class="validation-error help is-danger">{{ errors.first('password') }}</span>
                </div>
                <div class="form-group">
                    <label for="passwordRepeat" class="control-label">Повтор пароля</label>
                    <input v-model="form_data.password_confirmation" type="password" class="form-control" :class="{'is-danger': errors.has('password_confirmation')}" id="passwordRepeat" name="password_confirmation"
                           v-validate="'required|confirmed:password'" data-vv-as="password" placeholder="Повтор пароля">
                    <span class="validation-error help is-danger" v-show="errors.has('password_confirmation')">
                        {{ errors.first('password_confirmation') }}
                    </span>
                </div>
                <div class="form-group">
                    <button @click="passwordConfirm" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </layout-main>
</template>

<script>
import Services from '../../../scripts/services/services';
import modalMixin from '../../mixins/modal.js';

import './PasswordConfirm.css';

export default {
    name: 'page-password-confirm',
    components: {},
    mixins: [modalMixin],
    props: {
        id: '',
    },
    data() {
        return {
            form_data: {}
        };
    },
    methods: {
        passwordConfirm() {
            let errorMessage = 'Неверный пароль';
            let formData = {
                id: this.id,
                password: this.form_data.password,
            };
            this.$validator
            .validateAll()
            .then(result => {
                if (!result) {
                    return;
                }
                Services.net().post(this.getRoute('settings.saveUser'), null, formData).then(data => {
                    if (data.status === 'ok') {
                        window.location.href = this.route('page.login');
                    } else {
                        this.showMessageBox({title: 'Ошибка', text: errorMessage});
                    }
                });
            });
        }
    }
};
</script>
