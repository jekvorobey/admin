<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('userAdd') && (canUpdate(blocks.settings) || canUpdate(blocks.users))">
            <div slot="header">
                <h4>{{ source ? 'Редактирование пользователя' : 'Добавление пользователя' }}</h4>
            </div>
            <div slot="body">
                <div class="row">
                    <v-input v-model="$v.form.last_name.$model" :error="errorLastName" class="col-md-4 col-12"><h5>Фамилия*</h5></v-input>
                    <v-input v-model="$v.form.first_name.$model" :error="errorFirstName" class="col-md-4 col-12"><h5>Имя*</h5></v-input>
                    <v-input v-model="$v.form.middle_name.$model" class="col-md-4 col-12"><h5>Отчество</h5></v-input>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="text-left">
                                <h5>Система</h5>
                                <template v-for="front in frontOptions">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               :id="`front-${front.id}`"
                                               @change="e => frontsCheckbox(e, front.id)"
                                               :value="front.id"
                                               :checked="selectedFronts(front.id)"
                                        >
                                        <label class="form-check-label" :for="`front-${front.id}`">
                                            {{ front.name }}
                                        </label>
                                    </div>
                                </template>
                                <div class="error">
                                    {{ errorFronts }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="text-left">
                                <h5>Роли</h5>
                                <h6 v-if="checkFront" class="font-weight-bold">Выберите систему для отображения ролей</h6>
                                <div v-for="userFront in userFrontsValues">
                                    <h6 class="mt-2 font-weight-bold">{{ userFront.name }}</h6>
                                    <template v-for="role in roleOptions">
                                        <div v-if="role.front === userFront.id" class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   :id="`role-${role.id}`"
                                                   @change="e => rolesCheckbox(e, role.id)"
                                                   :value="role.id"
                                                   :checked="selectedRoles(role.id) "
                                            >
                                            <label class="form-check-label" :for="`role-${role.id}`">
                                                {{ role.name }}
                                            </label>
                                        </div>
                                    </template>
                                    <div class="error">
                                        {{ errorRoles }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <v-select v-if="checkMas" v-model="$v.form.merchant_id.$model" :options="merchantOptions" :error="errorMerchant">
                    Мерчант
                </v-select>
                <div class="row mt-3">
                    <v-input v-model="$v.form.email.$model" :error="errorEmail" class="col-md-6 col-12"><h5>E-mail*</h5></v-input>
                    <v-input
                            v-model="$v.form.phone.$model"
                            :error="errorPhone"
                            v-mask="telMask"
                            validation="phone"
                            class="col-md-6 col-12"
                    >
                        <h5>Телефон*</h5>
                    </v-input>
                </div>
                <div class="row mb-3">
                    <span class="col-md-6 col-12">*Обязательное поле, если система Admin или MAS</span>
                    <span class="col-md-6 col-12">*Обязательное поле, если система Витрина</span>
                </div>
                <v-input v-if="source" v-model="$v.form.infinity_sip_extension.$model">
                    <h5>Infinity SIP Extension</h5>
                </v-input>
                <div v-if="source" class="row">
                    <v-input v-model="$v.form.password.$model" class="col" :error="errorPassword" type="password">
                        <h5>Пароль</h5>
                    </v-input>
                    <v-input v-model="$v.form.repeatPassword.$model" class="col" :error="errorRepeat" type="password">
                        <h5>Повтор пароля</h5>
                    </v-input>
                </div>
                <button @click="save" class="btn btn-dark">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
import modal from '../../../components/controls/modal/modal.vue';
import VInput from '../../../components/controls/VInput/VInput.vue';
import VSelect from '../../../components/controls/VSelect/VSelect.vue';

import modalMixin from '../../../mixins/modal.js';
import {validationMixin} from 'vuelidate';
import {email, minLength, required, requiredIf, sameAs} from 'vuelidate/lib/validators';
import Services from '../../../../scripts/services/services';
import {telMask} from '../../../../scripts/mask.js';

export default {
    mixins: [modalMixin, validationMixin],
    components: {
        VSelect,
        VInput,
        modal
    },
    props: {
        source: Object,
        fronts: {},
        roles: {},
        merchants: {},
        merchantId: null,
    },
    data() {
        return {
            form: {
                first_name: '',
                last_name: '',
                middle_name: '',
                email: '',
                phone: '',
                login: '',
                login_email: '',
                fronts: [],
                roles: [],
                merchant_id: null,
                password: '',
                repeatPassword: '',
                infinity_sip_extension: '',
            },
            rolesModalSelect: [],
            frontsModalSelect: [],
        };
    },
    validations() {
        return {
            form: {
                last_name: {required},
                first_name: {required},
                middle_name: {},
                email: {
                    required: requiredIf(function(form){
                        return form.fronts.includes(this.userFronts.admin) || form.fronts.includes(this.userFronts.mas)
                    }),
                    email,
                    isUnique() {
                        return this.isFieldUnique(this.form.email, 'email');
                    },
                    $lazy: true
                },
                phone: {
                    required: requiredIf(function(form){
                        return form.fronts.includes(this.userFronts.showcase)
                    }),
                    isUnique() {
                        let phoneNumber = this.form.phone ?
                            this.form.phone.replace(/[()]|\s|-/g, '') : null;
                        return this.isFieldUnique(phoneNumber, 'phone');
                    },
                    $lazy: true
                },
                login: {},
                login_email: {},
                fronts: {required},
                roles: {required},
                merchant_id: {
                    required: requiredIf(function(form){
                        return form.fronts.includes(this.userFronts.mas)
                    }),
                    $lazy: true
                },
                password: {
                    minLength: minLength(8),
                    valid: function (value) {
                        if (value === '') {
                            return true
                        }
                        const containsUppercase = /[A-Z]/.test(value)
                        const containsLowercase = /[a-z]/.test(value)
                        const containsNumber = /[0-9]/.test(value)
                        //const containsSpecial = /[#?!@$%^&*-]/.test(value)

                        return containsUppercase && containsLowercase && containsNumber;
                    }
                },
                repeatPassword: {sameAsPassword: sameAs('password')},
                infinity_sip_extension: {},
            }
        };
    },
    methods: {
        waitForValidation () {
            return new Promise((resolve) => {
                const unwatch = this.$watch(() => !this.$v.$pending, (isNotPending) => {
                    if (isNotPending) {
                        resolve(!this.$v.$invalid)
                    }
                }, {immediate: true})
            })
        },
        async save() {
            await this.$v.$touch();
            const isValid = await this.waitForValidation();
            if (!isValid) {
                return;
            }
            let formData = {
                first_name: this.form.first_name,
                last_name: this.form.last_name,
                fronts: this.form.fronts,
                roles: this.form.roles,
            };
            if (this.source) {
                formData.id = this.source.id;
            }
            if (this.form.email) {
                formData.login_email = this.form.email;
                formData.email = this.form.email;
            }
            if (this.form.phone) {
                let phone = this.form.phone.replace(/[()]|\s|-/g, '');
                formData.login = phone;
                formData.phone = phone;
            }
            if (this.form.password) {
                formData.password = this.form.password;
            }
            if (this.form.middle_name) {
                formData.middle_name = this.form.middle_name;
            }
            if (this.form.infinity_sip_extension) {
                formData.infinity_sip_extension = this.form.infinity_sip_extension;
            }
            if (this.form.merchant_id) {
                formData.merchant_id = this.form.merchant_id;
            }
            Services.net().post(this.getRoute('settings.saveUser'), {}, formData).then(data => {
                this.$emit('onSave', {
                    full_name: this.form.last_name + ' ' + this.form.first_name + ' ' + this.form.middle_name,
                    last_name: this.form.last_name,
                    first_name: this.form.first_name,
                    middle_name: this.form.middle_name,
                    email: this.form.email,
                    phone: this.form.phone,
                    login: this.form.email ? this.form.email : this.form.phone,
                    login_email: this.form.email,
                    infinity_sip_extension: this.form.infinity_sip_extension,
                    fronts: this.form.fronts,
                    roles: this.form.roles,
                }).finally(() => {
                    this.closeModal();
                });
            });
        },
        isFieldUnique(data, field) {
            Services.showLoader();
            let userId = this.source ? this.source.id : null;
            return Services.net().get(this.getRoute('user.isUnique'), {data: data, field: field, id: userId})
                .then(data => data.isUnique)
                .finally(() => {
                    Services.hideLoader();
                });
        },
        rolesCheckbox(e, id) {
            id = parseInt(id);
            if (e.target.checked) {
                this.rolesModalSelect.push(id);
            } else {
                this.rolesModalSelect = this.rolesModalSelect.filter((roleId) => {
                    return roleId !== id;
                });
            }
            this.form.roles = this.rolesModalSelect;
        },
        frontsCheckbox(e, id) {
            id = parseInt(id);
            if (e.target.checked) {
                this.frontsModalSelect.push(id);
            } else {
                this.frontsModalSelect = this.frontsModalSelect.filter((frontId) => {
                    return frontId !== id;
                });
                this.rolesModalSelect = Object.values(this.roles)
                    .filter(role => role.front !== id)
                    .filter(role => this.rolesModalSelect.includes(role.id))
                    .map(role => role.id);
            }
            this.form.roles = this.rolesModalSelect;
            this.form.fronts = this.frontsModalSelect;
        },
        selectedRoles(id) {
            return this.rolesModalSelect.includes(id);
        },
        selectedFronts(id) {
            return this.frontsModalSelect.includes(id);
        },
    },
    computed: {
        userFrontsValues() {
            return Object.values(this.fronts).filter(front => this.form.fronts.includes(front.id)).map(front => ({id: front.id, name: front.name}));
        },
        frontOptions() {
            return Object.values(this.fronts).map(front => ({id: front.id, name: front.name}));
        },
        roleOptions() {
            return Object.values(this.roles).map(role => ({id: role.id, name: role.name, front: role.front}));
        },
        merchantOptions() {
            return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.name}));
        },
        // =========================================================================================================
        telMask() {
            return telMask;
        },
        errorLastName() {
            if (this.$v.form.last_name.$dirty) {
                if (!this.$v.form.last_name.required) return "Обязательное поле!";
            }
        },
        errorFirstName() {
            if (this.$v.form.first_name.$dirty) {
                if (!this.$v.form.first_name.required) return "Обязательное поле!";
            }
        },
        errorFronts() {
            if (this.$v.form.fronts.$dirty) {
                if (!this.$v.form.fronts.required) return 'Выберите хотя бы один из пунктов!';
            }
        },
        errorPhone() {
            if (this.$v.form.phone.$dirty) {
                if (!this.$v.form.phone.required) return "Обязательное поле!";
                if (!this.$v.form.phone.isUnique) return "Телефон должен быть уникальным!";
            }
        },
        errorEmail() {
            if (this.$v.form.email.$dirty) {
                if (!this.$v.form.email.required) return "Обязательное поле!";
                if (!this.$v.form.email.email) return "Формат E-mail не соответствует требованиям!";
                if (!this.$v.form.email.isUnique) return "E-mail должен быть уникальным!";
            }
        },
        errorPassword() {
            if (this.$v.form.password.$dirty) {
                if (!this.$v.form.password.minLength) return 'Минимум 8 символов!';
                if (!this.$v.form.password.valid) return 'Пароль должен содержать буквы верхнего и нижнего регистров, а также цифры';
            }
        },
        errorRepeat() {
            if (this.$v.form.repeatPassword.$dirty) {
                if (!this.$v.form.repeatPassword.sameAsPassword) return 'Введите такой же пароль!';
            }
        },
        errorRoles() {
            if (this.$v.form.roles.$dirty) {
                if (!this.$v.form.roles.required) return "Выберите хотя бы один из пунктов!";
            }
        },
        errorMerchant() {
            if (this.$v.form.merchant_id.$dirty) {
                if (!this.$v.form.merchant_id.required) return "Выберите хотя бы один из пунктов!";
            }
        },
        checkFront() {
            return this.form.fronts.length < 1;
        },
        checkMas() {
            return this.form.fronts.includes(this.userFronts.mas);
        },
    },
    watch: {
        '$store.state.modal.currentModal': function (value) {
            if (value === 'userAdd' && this.source) {
                this.form.first_name = this.source.first_name;
                this.form.last_name = this.source.last_name;
                this.form.middle_name = this.source.middle_name;
                this.form.email = this.source.email;
                this.form.phone = this.source.phone;
                this.form.fronts = this.source.fronts;
                this.form.roles = this.source.roles;
                this.form.infinity_sip_extension = this.source.infinity_sip_extension;
                this.frontsModalSelect = this.source.fronts;
                this.rolesModalSelect = this.source.roles;
                this.form.merchant_id = this.merchantId;
            }
        }
    }
}
</script>
<style>
.error {
    color: red;
}
</style>