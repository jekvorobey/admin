<template>
    <transition name="modal" v-if="canUpdate(blocks.settings)">
        <modal :close="closeModal" v-if="isModalOpen('userAdd')">
            <div slot="header">
                {{ source ? 'Редактирование пользователя' : 'Добавление пользователя' }}
            </div>
            <div slot="body">
                <v-input v-model="$v.form.login.$model" :error="errorEmail" autocomplete="no">
                    Логин
                </v-input>
                <div slot="header">
                    Система
                </div>
                <div slot="body">
                    <template v-for="front in frontOptions">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   :id="`front-${front.id}`"
                                   @change="e => frontsCheckbox(e, front.id)"
                                   :value="front.id"
                                   :checked="source.fronts.includes(parseInt(front.id))"
                            >
                            <label class="form-check-label" :for="`front-${front.id}`">
                                {{ front.name }}
                            </label>
                        </div>
                    </template>
                </div>
                <div slot="header">
                    Роли
                </div>
                <div slot="body">
                    <template v-for="role in roleOptions">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   :id="`role-${role.id}`"
                                   @change="e => rolesCheckbox(e, role.id)"
                                   :value="role.id"
                                   :checked="source.roles.includes(parseInt(role.id))"
                            >
                            <label class="form-check-label" :for="`role-${role.id}`">
                                {{ role.name }}
                            </label>
                        </div>
                    </template>
                </div>
                <v-input v-if="source" v-model="$v.form.infinity_sip_extension.$model">
                    Infinity SIP Extension
                </v-input>
                <div v-if="source" class="row">
                    <v-input v-model="$v.form.password.$model" class="col" :error="errorPassword" type="password"
                             autocomplete="new-password">
                        Пароль
                    </v-input>
                    <v-input v-model="$v.form.repeat.$model" class="col" :error="errorRepeat" type="password"
                             autocomplete="new-password">
                        Повтор пароля
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
import {minLength, required} from 'vuelidate/lib/validators';
import Services from '../../../../scripts/services/services';

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
                login: '',
                fronts: '',
                roles: '',
                password: '',
                repeat: '',
                infinity_sip_extension: '',
            },
            rolesSelect: [],
            frontsSelect: [],
        };
    },
    validations() {
        let validations = {
            form: {
                login: {required},
                fronts: {required},
                roles: {required},
                password: {},
                repeat: {},
                infinity_sip_extension: {},
            }
        };
        if (!this.source) {
            validations.form.password[required] = required;
            validations.form.password[minLength] = minLength(8);
        }
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
                login: this.form.login,
                front: this.form.front,
            };
            if (this.source) {
                formData.id = this.source.id;
            }
            if (this.form.password) {
                formData.password = this.form.password;
            }
            if (this.form.infinity_sip_extension) {
                formData.infinity_sip_extension = this.form.infinity_sip_extension;
            }
            Services.net().post(this.getRoute('settings.createUser'), {}, formData).then(data => {
                this.$emit('onSave', {
                    login: this.form.login,
                    front: this.form.front,
                    infinity_sip_extension: this.form.infinity_sip_extension
                });
            });
        },
        rolesCheckbox(e, id) {
            id = parseInt(id);
            if (e.target.checked) {
                this.rolesSelect.push(id);
            } else {
                this.rolesSelect = this.rolesSelect.filter((roleId) => {
                    return roleId !== id;
                });
            }
            this.form.roles = this.rolesSelect;
        },
        frontsCheckbox(e, id) {
            id = parseInt(id);
            if (e.target.checked) {
                this.frontsSelect.push(id);
            } else {
                this.frontsSelect = this.frontsSelect.filter((frontId) => {
                    return frontId !== id;
                });
            }
            this.form.fronts = this.frontsSelect;
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
        errorEmail() {
            if (this.$v.form.login.$dirty) {
                if (!this.$v.form.login.required) return 'Обязательное поле!';
            }
        },
        errorFront() {
            if (this.$v.form.fronts.$dirty) {
                if (!this.$v.form.fronts.required) return 'Обязательное поле!';
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
        '$store.state.modal.currentModal': function (newValue) {
            if (newValue === 'userAdd' && this.source) {
                this.form.login = this.source.login;
                this.form.fronts = this.source.fronts;
                this.form.infinity_sip_extension = this.source.infinity_sip_extension;
            }
        }
    }
}
</script>
