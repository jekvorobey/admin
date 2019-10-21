<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('userAdd')">
            <div slot="header">
                {{source ? 'Редактирование пользователя' : 'Добавление пользователя'}}
            </div>
            <div slot="body">
                <v-input v-model="$v.form.email.$model" :error="errorEmail"  autocomplete="no">
                    E-mail
                </v-input>
                <v-select v-model="$v.form.front.$model" :options="frontOptions" :error="errorFront">
                    Система
                </v-select>
                <div class="row">
                    <v-input v-model="$v.form.password.$model" class="col" :error="errorPassword" type="password" autocomplete="new-password">
                        Пароль
                    </v-input>
                    <v-input v-model="$v.form.repeat.$model" class="col" :error="errorRepeat" type="password" autocomplete="new-password">
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
    import {required, email, minLength, sameAs} from 'vuelidate/lib/validators'
    import Services from "../../../../scripts/services/services";
    import {mapGetters} from "vuex";

    export default {
        mixins: [modalMixin, validationMixin],
        components: {
            VSelect,
            VInput,
            modal
        },
        props: {
            source: Object,
            fronts: Object,
        },
        data() {
            return {
                form: {
                    email: '',
                    front: '',
                    password: '',
                    repeat: ''
                }
            };
        },
        validations() {
            let validations = {
                form: {
                    email: {required, email},
                    front: {required},
                    password: {},
                    repeat: {},
                }
            };
            if (!this.source.id) {
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
                    email: this.form.email,
                    front: this.form.front,
                };
                if (this.source) {
                    formData.id = this.source.id;
                }
                if (this.form.password) {
                    formData.password = this.form.password;
                }
                Services.net().post(this.getRoute('settings.createUser'), {}, formData).then(() => {
                    this.$emit('onSave', {email: this.form.email, front: this.form.front});
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
                if (this.$v.form.email.$dirty) {
                    if (!this.$v.form.email.required) return 'Обязательное поле!';
                    if (!this.$v.form.email.email) return 'Неверный формат email!';
                }
            },
            errorFront() {
                if (this.$v.form.front.$dirty) {
                    if (!this.$v.form.front.required) return 'Обязательное поле!';
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
            }
        },
        watch: {
            '$store.state.modal.currentModal': function(newValue) {
                if (newValue === 'userAdd' && this.source) {
                    this.form.email = this.source.email;
                    this.form.front = this.source.front;
                }
            }
        }
    }
</script>

<style scoped>

</style>