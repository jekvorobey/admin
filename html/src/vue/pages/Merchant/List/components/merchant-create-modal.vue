<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('merchantCreate')">
            <div slot="header">
                Создание мерчанта
            </div>
            <div slot="body">
                <div class="mb-3">
                    <v-input v-model="$v.form.company.$model" :error="errorCompany">Короткое название компании</v-input>
                </div>
                <div class="mb-3 row">
                    <v-input v-model="$v.form.last_name.$model" :error="errorLastName" class="col-md-4 col-12">Фамилия</v-input>
                    <v-input v-model="$v.form.first_name.$model" :error="errorFirstName" class="col-md-4 col-12">Имя</v-input>
                    <v-input v-model="$v.form.middle_name.$model" :error="errorMiddleName" class="col-md-4 col-12">Отчество</v-input>
                </div>
                <div class="mb-3 row">
                    <v-input
                            v-model="$v.form.email.$model"
                            :placeholder="emailPlaceholder"
                            :error="errorEmail"
                            class="col-md-6 col-12"
                            autocomplete="off"
                    >E-mail</v-input>
                    <v-input
                            v-model="$v.form.phone.$model"
                            :placeholder="telPlaceholder"
                            :error="errorPhone"
                            v-mask="telMask"
                            class="col-md-6 col-12"
                            autocomplete="off"
                    >Телефон</v-input>
                </div>
                <div class="mb-3 row">
                    <v-input
                            v-model="$v.form.password.$model"
                            :error="errorPassword"
                            class="col-md-6 col-12"
                            type="password"
                            autocomplete="new-password"
                    >Пароль</v-input>
                    <v-input
                            v-model="$v.form.repeat.$model"
                            :error="errorRepeat"
                            class="col-md-6 col-12"
                            type="password"
                            autocomplete="new-password"
                    >Повтор пароля</v-input>
                </div>

                <button @click="create" class="btn btn-dark">Создать мерчанта</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';

    import modalMixin from '../../../../mixins/modal.js';
    import { validationMixin } from 'vuelidate';

    import {telMask} from '../../../../../scripts/mask';
    import {emailPlaceholder, telPlaceholder} from '../../../../../scripts/placeholder';

    import {email, minLength, required, sameAs} from 'vuelidate/lib/validators';
    import Services from "../../../../../scripts/services/services";

    const cleanForm = {
        company: '',
        last_name: '',
        first_name: '',
        middle_name: '',
        email: '',
        phone: '',
        password: '',
        repeat: ''
    };

    export default {
        mixins: [modalMixin, validationMixin],
        components: {
            modal,
            VInput,
        },
        data() {
            let form = JSON.parse(JSON.stringify(cleanForm));
            return {
                form: form,
            };
        },
        validations: {
            form: {
                company: {required},
                first_name: {required},
                last_name: {required},
                middle_name: {required},
                email: {
                    required,
                    email,
                    isUnique(value) {
                        return new Promise((resolve, reject) => {
                            Services.net().get(this.route('check.userExists'), {
                                email: value
                            }).then(data => {
                                resolve(data.exists === false);
                            });
                        });
                    }
                },
                phone: {required},
                password: {
                    required,
                    minLength: minLength(8)
                },
                repeat: {
                    required,
                    sameAsPassword: sameAs('password')
                }
            }
        },
        methods: {
            create() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.net().post(this.getRoute('merchant.create'), {}, this.form)
                    .then(data => {
                        this.$emit('created');
                        this.closeModal();
                    });
            }
        },
        computed: {
            telMask() {
                return telMask;
            },
            telPlaceholder() {
                return telPlaceholder;
            },
            emailPlaceholder() {
                return emailPlaceholder;
            },
            // ===============================
            errorCompany() {
                if (this.$v.form.company.$dirty) {
                    if (!this.$v.form.company.required) return "Обязательное поле!";
                }
            },
            errorFirstName() {
                if (this.$v.form.first_name.$dirty) {
                    if (!this.$v.form.first_name.required) return "Обязательное поле!";
                }
            },
            errorLastName() {
                if (this.$v.form.last_name.$dirty) {
                    if (!this.$v.form.last_name.required) return "Обязательное поле!";
                }
            },
            errorMiddleName() {
                if (this.$v.form.middle_name.$dirty) {
                    if (!this.$v.form.middle_name.required) return "Обязательное поле!";
                }
            },
            errorEmail() {
                if (this.$v.form.email.$dirty) {
                    if (!this.$v.form.email.required) return "Обязательное поле!";
                    if (!this.$v.form.email.email) return "Введите валидный e-mail!";
                    if (!this.$v.form.email.isUnique) return "Такой e-mail уже зарегистрирован!";
                }
            },
            errorPhone() {
                if (this.$v.form.phone.$dirty) {
                    if (!this.$v.form.phone.required) return "Обязательное поле!";
                }
            },
            errorPassword() {
                if (this.$v.form.password.$dirty) {
                    if (!this.$v.form.password.required) return "Обязательное поле!";
                    if (!this.$v.form.password.minLength) return "Не меньше 8 символов!";
                }
            },
            errorRepeat() {
                if (this.$v.form.repeat.$dirty) {
                    if (!this.$v.form.repeat.required) return "Обязательное поле!";
                    if (!this.$v.form.repeat.sameAsPassword) return "Введённые пароли не совпадают!";
                }
            }
        }
    }
</script>