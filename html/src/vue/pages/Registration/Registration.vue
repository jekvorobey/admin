<template>
    <layout-main>
        <form id="register_form" v-on:submit.prevent.stop="register">
            <h4 class="mb-3">Данные компании</h4>
            <div class="mb-3">
                <div class="form-group">
                    <label for="company_legal_name" class="control-label">Юридическое наименование организации</label>
                    <input type="text" class="form-control" id="company_legal_name" name="company_legal_name"
                            v-model="form_data.company_legal_name" v-validate="'required'">
                    <span class="validation-error" v-if="errors.has('company_legal_name')">{{
                        errors.first('company_legal_name') }}</span>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <label for="company_legal_address" class="control-label">Юридический адрес</label>
                    <input type="text" class="form-control" id="company_legal_address"
                            v-model="form_data.company_legal_address" v-validate="'required'">
                    <div class="invalid-feedback">
                        Заполните поле!
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="company_inn" class="control-label">ИНН</label>
                        <input type="number" min="100000000" max="999999999999" class="form-control" id="company_inn"
                                v-model="form_data.company_inn" v-validate="'required|inn'">
                        <div class="invalid-feedback">
                        Заполните поле!
                    </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="company_kpp" class="control-label">КПП</label>
                        <input type="text" maxlength="9" class="form-control" id="company_kpp"
                                v-model="form_data.company_kpp" v-validate="'required|kpp'">
                        <div class="invalid-feedback">
                        Заполните поле!
                    </div>
                    </div>
                </div>
            </div>

            <h4 class="mb-3">Данные пользователя</h4>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="user_name" class="control-label">ФИО</label>
                        <input type="text" class="form-control" id="user_name" v-model="form_data.user_name" v-validate="'required'">
                        <div class="invalid-feedback">
                        Заполните поле!
                    </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="user_email" class="control-label">E-mail</label>
                        <input type="email" class="form-control" id="user_email" name="user_email" v-model="form_data.user_email"
                                v-validate="'required|email'">
                        <span class="validation-error" v-if="errors.has('user_email')">{{ errors.first('user_email') }}</span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="user_phone" class="control-label">Телефон</label>
                        <input type="tel" class="form-control" id="user_phone"
                                v-model="form_data.user_phone" v-validate="'phone'">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
            <button type="button" class="btn btn-secondary" @click="onCancel">Отмена</button>
        </form>
    </layout-main>
</template>

<script>
import axios from 'axios';

export default {
    name: 'page-registration',
    components: {},
    props: [],
    data() {
        return {
            form_data: {},
        };
    },
    methods: {
        register() {
            if (this.$validator.validateAll()) {
                /*this.form_data.status = STATUS_SEND;
                let query = RegistrationRequest.query();
                let promise = query.create(this.form_data);

                let successMessage = 'Заявка на регистрацию успешно отправлена.';
                let errorMessage = 'Произошла ошибка при регистрации.';
                promise.then(
                    result => {
                        let variant = 'danger';
                        let message = errorMessage;
                        if (result) {
                            variant = 'success';
                            message = successMessage;
                            clearForm(this.form);
                            this.form_data = {};
                        }

                        this.$store.commit('alert', {
                            variant: variant,
                            seconds: variant,
                            message: message,
                        });
                    },
                    () => {
                        this.$store.commit('alert', {
                            variant: 'danger',
                            message: errorMessage,
                        });
                    });*/
            }
        },
        onCancel() {
            /*this.$router.back();*/
        }
    }
};
</script>
