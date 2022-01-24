<template>
    <transition name="modal" v-if="canUpdate(blocks.settings)">
        <modal :close="closeModal" v-if="isModalOpen('userAdd')">
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
                                               :checked="source ? source.fronts.includes(parseInt(front.id)) : null"
                                        >
                                        <label class="form-check-label" :for="`front-${front.id}`">
                                            {{ front.name }}
                                        </label>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="text-left">
                                <h5>Роли</h5>
                                <template v-for="role in roleOptions">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               :id="`role-${role.id}`"
                                               @change="e => rolesCheckbox(e, role.id)"
                                               :value="role.id"
                                               :checked="source ? source.roles.includes(parseInt(role.id)) : null"
                                        >
                                        <label class="form-check-label" :for="`role-${role.id}`">
                                            {{ role.name }}
                                        </label>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <v-input v-model="$v.form.email.$model" :error="errorEmail" class="col-md-6 col-12"><h5>E-mail*</h5></v-input>
                    <v-input v-model="$v.form.phone.$model" :error="errorPhone" v-mask="telMask" class="col-md-6 col-12"><h5>Телефон*</h5></v-input>
                </div>
                <div class="row mb-3">
                    <span class="col-md-6 col-12">*Обязательное поле, если система Admin или MAS</span>
                </div>
                <v-input v-if="source" v-model="$v.form.infinity_sip_extension.$model">
                    <h5>Infinity SIP Extension</h5>
                </v-input>
                <div v-if="source" class="row">
                    <v-input v-model="$v.form.password.$model" class="col" :error="errorPassword" type="password"
                             autocomplete="new-password">
                        <h5>Пароль</h5>
                    </v-input>
                    <v-input v-model="$v.form.repeat.$model" class="col" :error="errorRepeat" type="password"
                             autocomplete="new-password">
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
import {email, minLength, required, requiredIf} from 'vuelidate/lib/validators';
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
                fronts: '',
                roles: '',
                password: '',
                repeat: '',
                infinity_sip_extension: '',
            },
            rolesModalSelect: [],
            frontsModalSelect: [],
        };
    },
    validations() {
        let validations = {
            form: {
                last_name: {required},
                first_name: {required},
                middle_name: {},
                email: {
                    required: requiredIf(function() {
                        return this.form.fronts.includes(1) || this.form.fronts.includes(2);
                    }),
                    email
                },
                phone: {required},
                login: {},
                login_email: {},
                fronts: {required},
                roles: {required},
                password: {minLength: minLength(8)},
                repeat: {},
                infinity_sip_extension: {},
            }
        };
        console.log(validations);

        return validations;
    },
    methods: {
        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
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
            Services.net().post(this.getRoute('settings.saveUser'), {}, formData).then(data => {
                this.$emit('onSave', {
                    last_name: this.form.last_name,
                    first_name: this.form.first_name,
                    middle_name: this.form.middle_name,
                    email: this.form.email,
                    phone: this.form.phone,
                    login: this.form.email ? this.form.email : this.form.phone,
                    fronts: this.form.fronts,
                    roles: this.form.roles,
                });
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
            }
            this.form.fronts = this.frontsModalSelect;
        },
    },
    computed: {
        frontOptions() {
            return Object.values(this.fronts).map(front => ({id: front.id, name: front.name}));
        },
        roleOptions() {
            return Object.values(this.roles).map(role => ({id: role.id, name: role.name}));
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
            }
        },
        errorEmail() {
            if (this.$v.form.email.$dirty) {
                if (!this.$v.form.email.required) return "Обязательное поле!";
                if (!this.$v.form.email.email) return "Формат E-mail не соответствует требованиям!";
            }
        },
        errorPassword() {
            if (this.$v.form.password.$dirty) {
                if (this.$v.form.password.minLength === false) return 'Минимум 8 символов!';
                if (!this.$v.form.password.required === false) return 'Обязательное поле!';
            }
        },
        errorRepeat() {
            if (this.$v.form.repeat.$dirty) {
                if (!this.$v.form.repeat.sameAs === false) return 'Введите такой же пароль!';
            }
        },
        errorRoles() {
            if (this.$v.form.roles.$dirty) {
                if (!this.$v.form.roles.required) return "Выберите хотя бы один из пунктов!";
            }
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
            }
        }
    }
}
</script>
