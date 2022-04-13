<template>
    <layout-main back :custom-title="title">
        <template v-if="!merchantId">
            <label for="merchant">Мерчант</label>
            <v-select2 id="merchant"
                       v-model="operator.merchant_id"
                       :error="errorMerchantId"
                       class="form-control form-control-sm"
            >
                <option v-for="merchant in merchants" :value="merchant.id">{{ merchant.legal_name }}</option>
            </v-select2>
        </template>

        <div class="row">
            <v-input v-model="operator.last_name" :error="errorLastName" class="col-md-4 col-12">Фамилия</v-input>
            <v-input v-model="operator.first_name" :error="errorFirstName" class="col-md-4 col-12">Имя</v-input>
            <v-input v-model="operator.middle_name" class="col-md-4 col-12">Отчество</v-input>
        </div>

        <div class="row">
            <v-input v-model="operator.email" :error="errorEmail" class="col-md-4 col-12">E-mail</v-input>
            <v-input v-model="operator.phone" :error="errorPhone" v-mask="telMask" class="col-md-4 col-12">Телефон</v-input>
            <v-input class="col-md-4 col-12" v-model="operator.position">Должность</v-input>
        </div>

        <div v-if="operatorProp" class="row">
            <v-input v-model="operator.password" class="col-md-6 col-12" :error="errorPassword" type="password">
                Пароль
            </v-input>
        </div>

        Способ связи*:
        <template v-for="method in communicationMethods">
            <div class="form-check">
                <input class="form-check-input"
                       type="radio"
                       :id="`communication-method-${method.id}`"
                       v-model="operator.communication_method"
                       :value="method.id"
                >
                <label class="form-check-label" :for="`communication-method-${method.id}`">
                    {{ method.name }}
                </label>
            </div>
        </template>
        <div class="error mb-3">
            {{ errorCommunicationMethod }}
        </div>

        Роль пользователя*:
        <template v-for="role in roles">
            <div class="form-check">
                <input class="form-check-input"
                       type="checkbox"
                       :id="`role-${role.value}`"
                       @change="e => rolesCheckbox(e, role.value)"
                       :value="role.value"
                       :checked="operator.roles.includes(role.value)"
                >
                <label class="form-check-label" :for="`role-${role.value}`">
                    {{ role.text }}
                </label>
            </div>
        </template>
        <div class="error">
            {{ errorRoles }}
        </div>

        <div class="form-check mt-3 mb-3">
            <input class="form-check-input"
                   type="checkbox"
                   id="active"
                   v-model="operator.active"
            >
            <label class="form-check-label" for="active">
                Активен
            </label>
        </div>

        <button v-if="!operatorProp" class="btn btn-success" @click="create">Создать</button>
        <button v-else class="btn btn-warning" @click="edit">Сохранить изменения</button>
    </layout-main>
</template>

<script>
import VInput from '../../../../components/controls/VInput/VInput.vue';
import VSelect2 from '../../../../components/controls/VSelect2/v-select2.vue';

import {validationMixin} from 'vuelidate';
import {email, minLength, required, requiredIf} from 'vuelidate/lib/validators';
import {telMask} from '../../../../../scripts/mask';

import Services from "../../../../../scripts/services/services.js";
import {mapActions} from 'vuex';

function myCustomValidator () {
        return value === 'isOk' // should return Boolean
    }

    export default {
        name: 'operator-create-edit',
        props: [
            'operatorProp',
            'merchantId',
            'merchants',
            'communicationMethods',
            'roles'
        ],
        components: {
            VInput,
            VSelect2,
        },
        mixins: [validationMixin],
        data() {
            let title = '';
            let operator = {};
            if (!this.operatorProp) {
                title = 'Создание менеджера';
                operator = {
                    merchant_id: this.merchantId,
                    roles: [],
                    active: true,
                };
            } else {
                title = 'Редактирование менеджера: ' +
                    this.operatorProp.last_name + ' ' + this.operatorProp.first_name + ' ' + this.operatorProp.middle_name;
                operator = {
                    merchant_id: this.operatorProp.merchant_id,
                    user_id: this.operatorProp.user_id,
                    last_name: this.operatorProp.last_name,
                    first_name: this.operatorProp.first_name,
                    middle_name: this.operatorProp.middle_name,
                    email: this.operatorProp.email,
                    phone: this.operatorProp.phone,
                    position: this.operatorProp.position,
                    communication_method: this.operatorProp.communication_method,
                    roles: [...this.operatorProp.roles],
                    active: this.operatorProp.active,
                };
            }

            return {
                title: title,
                operator: operator,
            };
        },
        validations: {
            operator: {
                merchant_id: {required},
                last_name: {required},
                first_name: {required},
                middle_name: {},
                email: {
                    required,
                    email,
                    isUnique: function() {
                        return this.isFieldUnique(this.operator.email, 'email');
                    },
                    $lazy: true,
                },
                phone: {
                    required,
                    isUnique: function() {
                        return this.isFieldUnique(this.operator.phone, 'phone');
                    },
                    $lazy: true,
                },
                password: {
                    minLength: minLength(8),
                    valid: function (value) {
                        if (!value) {
                            return true
                        }
                        const containsUppercase = /[A-Z]/.test(value)
                        const containsLowercase = /[a-z]/.test(value)
                        const containsNumber = /[0-9]/.test(value)
                        //const containsSpecial = /[#?!@$%^&*-]/.test(value)

                        return containsUppercase && containsLowercase && containsNumber;
                    }
                },
                communication_method: {required},
                roles: {required},
            }
        },
        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            waitForValidation () {
                return new Promise((resolve) => {
                    const unwatch = this.$watch(() => !this.$v.$pending, (isNotPending) => {
                        if (isNotPending) {
                            resolve(!this.$v.$invalid)
                        }
                    }, {immediate: true})
                })
            },

            async create() {
                await this.$v.$touch();
                const isValid = await this.waitForValidation();
                if (!isValid) {
                    return;
                }
                if (this.operator.phone) {
                    let phoneNumber = this.operator.phone.replace(/[()]|\s|-/g, '');
                    this.operator.phone = phoneNumber;
                    this.operator.login = phoneNumber;
                }
                if (this.operator.email) {
                    this.operator.login_email = this.operator.email;
                }
                Services.showLoader();
                Services.net().post(
                    this.getRoute('merchant.operator.save'),
                    {},
                    this.operator
                ).then(() => {
                    Services.msg('Менеджер успешно создан.');
                    window.location.href = this.getRoute('merchant.detail', {id: this.merchantId}) + '?tab=operator&allTab=0';
                }, () => {
                    Services.msg('Произошла ошибка при добавлении менеджера.', 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            async edit() {
                await this.$v.$touch();
                const isValid = await this.waitForValidation();
                if (!isValid) {
                    return;
                }
                let operatorEdit = {};
                for (let [key, value] of Object.entries(this.operator)) {
                    if (key === 'phone') {
                        let phoneNumber = value.replace(/[()]|\s|-/g, '');
                        value = phoneNumber;
                        operatorEdit['login'] = phoneNumber;
                    }
                    if (key === 'phone') {
                        operatorEdit['login_email'] = value;
                    }
                    if (key === 'password') {
                        operatorEdit['password'] = value;
                    }
                    if (
                        (value !== this.operatorProp[key]) &&
                        ((key !== 'merchant_id') || (parseInt(value) !== this.operatorProp[key])) &&
                        ((key !== 'password') || value) &&
                        ((key !== 'roles') || (JSON.stringify(value) !== JSON.stringify(this.operatorProp[key]))) &&
                        ((key !== 'active') || (+value !== this.operatorProp[key]))
                    ) {
                        if (value !== '') {
                            operatorEdit[key] = value;
                        }
                    }
                }

                if (Object.keys(operatorEdit).length !== 0 || operatorEdit.constructor !== Object) {
                    Services.showLoader();
                    Services.net().put(
                        this.route('merchant.operator.update'),
                        {id: this.operatorProp.id},
                        operatorEdit
                    ).then(() => {
                        for (let [key, value] of Object.entries(operatorEdit)) {
                            this.operatorProp[key] = value;
                        }
                        Services.msg('Данные о менеджере успешно обновлены.');
                        window.location.href = this.getRoute('merchant.detail', {id: this.operatorProp.merchant_id}) + '?tab=operator&allTab=0';
                    }, () => {
                        Services.msg('Произошла ошибка при обновлении данных о менеджере.', 'danger');
                    }).finally(() => {
                        Services.hideLoader();
                    });
                } else {
                    Services.msg('Данные о менеджере успешно обновлены.');
                }
            },
            rolesCheckbox(e, id) {
                id = parseInt(id);
                if (e.target.checked) {
                    this.operator.roles.push(id);
                } else {
                    this.operator.roles = this.operator.roles.filter((roleId) => {
                        return roleId !== id;
                    });
                }
            },
            showHidePass() {
                this.showPass = !this.showPass;
                this.operator.password = null;
            },
            isFieldUnique(data, field) {
                let userId = this.operator.user_id ? this.operator.user_id : null;
                return Services.net().get(this.getRoute('user.isUnique'), {data: data, field: field, id: userId})
                    .then(data => data.isUnique);
            },
        },
        computed: {
            errorMerchantId() {
                if (this.$v.operator.merchant_id.$dirty) {
                    if (!this.$v.operator.merchant_id.required) return "Обязательное поле!";
                }
            },
            errorLastName() {
                if (this.$v.operator.last_name.$dirty) {
                    if (!this.$v.operator.last_name.required) return "Обязательное поле!";
                }
            },
            errorFirstName() {
                if (this.$v.operator.first_name.$dirty) {
                    if (!this.$v.operator.first_name.required) return "Обязательное поле!";
                }
            },
            errorEmail() {
                if (this.$v.operator.email.$dirty) {
                    if (!this.$v.operator.email.required) return "Обязательное поле!";
                    if (!this.$v.operator.email.email) return "Формат E-mail не соответствует требованиям!";
                    if (!this.$v.operator.email.$pending && !this.$v.operator.email.isUnique) return "Пользователь с таким E-mail уже существует";
                }
            },
            telMask() {
                return telMask;
            },
            errorPhone() {
                if (this.$v.operator.phone.$dirty) {
                    if (!this.$v.operator.phone.required) return "Обязательное поле!";
                    if (!this.$v.operator.phone.$pending && !this.$v.operator.phone.isUnique) return "Пользователь с таким телефоном уже существует";
                }
            },
            errorPassword() {
                if (this.$v.operator.password.$dirty) {
                    if (!this.$v.operator.password.minLength) return "Не меньше 8 символов!";
                    if (!this.$v.operator.password.valid) return 'Пароль должен содержать буквы верхнего и нижнего регистров, а также цифры';
                }
            },
            errorCommunicationMethod() {
                if (this.$v.operator.communication_method.$dirty) {
                    if (!this.$v.operator.communication_method.required) return "Выберите один из пунктов!";
                }
            },
            errorRoles() {
                if (this.$v.operator.roles.$dirty) {
                    if (!this.$v.operator.roles.required) return "Выберите хотя бы один из пунктов!";
                }
            },
        }
    };
</script>

<style>
    .error {
        color: red;
    }
</style>
