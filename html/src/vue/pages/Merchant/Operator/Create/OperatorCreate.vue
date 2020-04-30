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
            <v-input v-model="operator.last_name" :error="errorLastName" class="col-md-4 col-12">Фамилия*</v-input>
            <v-input v-model="operator.first_name" :error="errorFirstName" class="col-md-4 col-12">Имя*</v-input>
            <v-input v-model="operator.middle_name" :error="errorMiddleName" class="col-md-4 col-12">Отчество*</v-input>
        </div>

        <div class="row">
            <v-input v-model="operator.email" :error="errorEmail" class="col-md-6 col-12">E-mail*</v-input>
            <v-input v-model="operator.phone" :error="errorPhone" v-mask="telMask" class="col-md-6 col-12">Телефон*</v-input>
        </div>

        <div class="row">
            <v-input v-model="operator.login" :error="errorLogin" class="col-md-6 col-12">Логин*</v-input>
            <v-input v-model="operator.password" :error="errorPassword" type="password" class="col-md-6 col-12">Пароль*</v-input>
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
        <button v-else class="btn btn-success" @click="edit">Сохранить изменения</button>
    </layout-main>
</template>

<script>
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect2 from '../../../../components/controls/VSelect2/v-select2.vue';

    import {validationMixin} from 'vuelidate';
    import {email, required} from 'vuelidate/lib/validators';
    import {telMask} from '../../../../../scripts/mask';

    import Services from "../../../../../scripts/services/services.js";
    import {mapActions} from 'vuex';

    export default {
        name: 'operator-create',
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
                    active: false,
                };
            } else {
                title = 'Редактирование менеджера: ' + this.operatorProp.full_name;
                operator = {
                    merchant_id: this.operatorProp.merchant_id,
                    last_name: this.operatorProp.merchant_id,
                    first_name: this.operatorProp.merchant_id,
                    middle_name: this.operatorProp.merchant_id,
                    email: this.operatorProp.merchant_id,
                    phone: this.operatorProp.merchant_id,
                    login: this.operatorProp.merchant_id,
                    password: this.operatorProp.merchant_id,
                    position: this.operatorProp.position,
                    communication_method: this.operatorProp.merchant_id,
                    roles: this.operatorProp.merchant_id,
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
                middle_name: {required},
                email: {email, required},
                phone: {required},
                login: {required},
                password: {required},
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
