<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('userAdd')">
            <div slot="header">
                Добавление пользователя
            </div>
            <div slot="body">
                <v-input v-model="$v.email.$model" :error="errorEmail"  autocomplete="no">
                    E-mail
                </v-input>
                <v-select v-model="$v.front.$model" :options="frontOptions" :error="errorFront">
                    Система
                </v-select>
                <div class="row">
                    <v-input v-model="$v.password.$model" class="col" :error="errorPassword" type="password" autocomplete="new-password">
                        Пароль
                    </v-input>
                    <v-input v-model="$v.repeat.$model" class="col" :error="errorRepeat" type="password" autocomplete="new-password">
                        Повтор пароля
                    </v-input>
                </div>

                <button @click="save" class="btn btn-dark">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';

    import modalMixin from '../../../../mixins/modal.js';
    import {validationMixin} from 'vuelidate';
    import {required, email, minLength, sameAs} from 'vuelidate/lib/validators'
    import Services from "../../../../../scripts/services/services";
    import {mapGetters} from "vuex";

    export default {
        mixins: [modalMixin, validationMixin],
        components: {
            VSelect,
            VInput,
            modal
        },
        props: {
            fronts: {}
        },
        data() {
            return {
                email: '',
                front: '',
                password: '',
                repeat: ''
            };
        },
        validations: {
            email: {required, email},
            front: {required},
            password: {required, minLength: minLength(8)},
            repeat: {required, sameAs: sameAs('password')}
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.net().post(this.getRoute('settings.createUser'), {}, {
                    email: this.email,
                    front: this.front,
                    password: this.password,
                }).then(() => {
                    this.showMessageBox({text: 'Пользователь создан'});
                });
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
            frontOptions() {
                return Object.values(this.fronts).map(front => ({value: front.id, text: front.name}));
            },
            // =========================================================================================================
            errorEmail() {
                if (this.$v.email.$dirty) {
                    if (!this.$v.email.required) return 'Обязательное поле!';
                    if (!this.$v.email.email) return 'Неверный формат email!';
                }
            },
            errorFront() {
                if (this.$v.front.$dirty) {
                    if (!this.$v.front.required) return 'Обязательное поле!';
                }
            },
            errorPassword() {
                if (this.$v.password.$dirty) {
                    if (!this.$v.password.required) return 'Обязательное поле!';
                    if (!this.$v.password.minLength) return 'Минимум 8 символов!';
                }
            },
            errorRepeat() {
                if (this.$v.repeat.$dirty) {
                    if (!this.$v.repeat.sameAs) return 'Введите такой же пароль!';
                }
            }
        }
    }
</script>

<style scoped>

</style>