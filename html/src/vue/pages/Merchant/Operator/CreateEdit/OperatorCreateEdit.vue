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
            <v-input v-model="operator.middle_name" :error="errorMiddleName" class="col-md-4 col-12">Отчество</v-input>
        </div>

        <div class="row">
            <v-input v-model="operator.email" :error="errorEmail" class="col-md-6 col-12">E-mail</v-input>
            <v-input v-model="operator.phone" :error="errorPhone" v-mask="telMask" class="col-md-6 col-12">Телефон</v-input>
        </div>

        <div class="row">
            <v-input v-model="operator.login" :error="errorLogin" class="col-md-6 col-12">Логин</v-input>
            <v-input v-if="!showPass"
                     class="col-md-6 col-12"
                     disabled
            >
                Пароль
                <button class="btn btn-outline-info"
                        @click="showHidePass"
                >
                    Изменить
                </button>
            </v-input>
            <v-input v-else
                     v-model="operator.password"
                     :error="errorPassword"
                     type="password"
                     class="col-md-6 col-12"
            >
                <button v-if="operatorProp" class="btn btn-outline-secondary" @click="showHidePass">Отменить</button>
                <template v-else>Пароль</template>
            </v-input>
        </div>

        <v-input v-model="operator.position">Должность</v-input>


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
        <template v-for="(roleName, roleId) in roles">
            <div class="form-check">
                <input class="form-check-input"
                       type="checkbox"
                       :id="`role-${roleId}`"
                       @change="e => rolesCheckbox(e, roleId)"
                       :value="roleId"
                       :checked="operator.roles.includes(parseInt(roleId))"
                >
                <label class="form-check-label" :for="`role-${roleId}`">
                    {{ roleName }}
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
    import {email, required, requiredIf, minLength} from 'vuelidate/lib/validators';
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
            let showPass = Boolean();
            if (!this.operatorProp) {
                title = 'Создание менеджера';
                operator = {
                    merchant_id: this.merchantId,
                    roles: [],
                    active: false,
                };
                showPass = true;
            } else {
                title = 'Редактирование менеджера: ' +
                    this.operatorProp.last_name + ' ' + this.operatorProp.first_name + ' ' + this.operatorProp.middle_name;
                operator = {
                    merchant_id: this.operatorProp.merchant_id,
                    last_name: this.operatorProp.last_name,
                    first_name: this.operatorProp.first_name,
                    middle_name: this.operatorProp.middle_name,
                    email: this.operatorProp.email,
                    phone: this.operatorProp.phone,
                    login: this.operatorProp.login,
                    password: null,
                    position: this.operatorProp.position,
                    communication_method: this.operatorProp.communication_method,
                    roles: [...this.operatorProp.roles],
                    active: this.operatorProp.active,
                };
                showPass = false;
            }

            return {
                title: title,
                operator: operator,
                showPass: showPass,
            };
        },
        validations: {
            operator: {
                merchant_id: {required},
                last_name: {required},
                first_name: {required},
                middle_name: {required},
                email: {email, required},
                phone: {required},
                login: {required},
                password: {
                    required: requiredIf(function() {
                        return this.showPass;
                    }),
                    minLength: minLength(8),
                },
                communication_method: {required},
                roles: {required},
            }
        },
        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            create() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                } else {
                    Services.showLoader();
                    Services.net().post(
                        this.getRoute('merchant.operator.save'),
                        {},
                        this.operator
                    ).then(() => {
                        Services.msg('Менеджер успешно создан.');
                    }, () => {
                        Services.msg('Произошла ошибка при добавлении менеджера.', 'danger');
                    }).finally(() => {
                        Services.hideLoader();
                    });
                }
            },
            edit() {
                this.$v.$touch();
                if (!this.$v.$invalid) {
                    let operatorEdit = {};
                    for (let [key, value] of Object.entries(this.operator)) {
                        if (key === 'phone') {
                            value = value.replace(/[()]|\s|-/g, '');
                        }
                        if (
                            (value !== this.operatorProp[key]) &&
                            ((key !== 'merchant_id') || (parseInt(value) !== this.operatorProp[key])) &&
                            ((key !== 'password') || value) &&
                            ((key !== 'roles') || (JSON.stringify(value) !== JSON.stringify(this.operatorProp[key]))) &&
                            ((key !== 'active') || (+value !== this.operatorProp[key]))
                        ) {
                            if (key === 'roles') {
                                let cross = this.operatorProp['roles'].filter(role => value.includes(role));
                                operatorEdit[key] = {};
                                operatorEdit[key]['add'] = value.filter(role => !cross.includes(role));
                                operatorEdit[key]['delete'] = this.operatorProp['roles'].filter(role => !cross.includes(role));
                            } else {
                                if (value !== '') {
                                    operatorEdit[key] = value;
                                }
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
                                if (key === 'roles') {
                                    if (value['add']) {
                                        this.operatorProp[key] = this.operatorProp[key].concat(value['add']);
                                    }
                                    if (value['delete']) {
                                        this.operatorProp[key] = this.operatorProp[key].filter( (role) => !value['delete'].includes(role));
                                    }
                                } else {
                                    this.operatorProp[key] = value;
                                }
                            }
                            Services.msg('Данные о менеджере успешно обновлены.');
                        }, () => {
                            Services.msg('Произошла ошибка при обновлении данных о менеджере.', 'danger');
                        }).finally(() => {
                            Services.hideLoader();
                        });
                    } else {
                        Services.msg('Данные о менеджере успешно обновлены.');
                    }
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
            errorMiddleName() {
                if (this.$v.operator.middle_name.$dirty) {
                    if (!this.$v.operator.middle_name.required) return "Обязательное поле!";
                }
            },
            errorEmail() {
                if (this.$v.operator.email.$dirty) {
                    if (!this.$v.operator.email.required) return "Обязательное поле!";
                    if (!this.$v.operator.email.email) return "Формат E-mail не соответствует требованиям!";
                }
            },
            telMask() {
                return telMask;
            },
            errorPhone() {
                if (this.$v.operator.phone.$dirty) {
                    if (!this.$v.operator.phone.required) return "Обязательное поле!";
                }
            },
            errorLogin() {
                if (this.$v.operator.login.$dirty) {
                    if (!this.$v.operator.login.required) return "Обязательное поле!";
                }
            },
            errorPassword() {
                if (this.$v.operator.password.$dirty) {
                    if (!this.$v.operator.password.required) return "Обязательное поле!";
                    if (!this.$v.operator.password.minLength) return "Не меньше 8 символов!";
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
