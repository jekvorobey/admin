<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('roleAdd')">
            <div slot="header">
                {{source ? 'Редактирование роли' : 'Добавление роли'}}
            </div>
            <div slot="body">
                <v-input v-model="$v.form.name.$model" :error="errorName"  autocomplete="no">
                    Наименование
                </v-input>
                <v-select v-model="$v.form.front.$model" :options="frontOptions" :error="errorFront">
                    Система
                </v-select>
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
    import { validationMixin } from 'vuelidate';
    import { required } from 'vuelidate/lib/validators';
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
            fronts: Object,
        },
        data() {
            return {
                form: {
                    name: '',
                    front: '',
                }
            };
        },
        validations() {
            let validations = {
                form: {
                    name: {required},
                    front: {required},
                }
            };
            if (!this.source) {
                validations.form.name[required] = required;
                validations.form.front[required] = required;
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
                    name: this.form.name,
                    front: this.form.front,
                };
                if (this.source) {
                    formData.id = this.source.id;
                }
                Services.net().post(this.getRoute('settings.createRole'), {}, formData).then(() => {
                    this.$emit('onSave', {name: this.form.name, front: this.form.front});
                });
            },
        },
        computed: {
            frontOptions() {
                return Object.values(this.fronts).map(front => ({value: front.id, text: front.name}));
            },
            // =========================================================================================================
            errorName() {
                if (this.$v.form.name.$dirty) {
                    if (!this.$v.form.name.required) return 'Обязательное поле!';
                }
            },
            errorFront() {
                if (this.$v.form.front.$dirty) {
                    if (!this.$v.form.front.required) return 'Обязательное поле!';
                }
            },
        },
        watch: {
            '$store.state.modal.currentModal': function(newValue) {
                if (newValue === 'roleAdd' && this.source) {
                    this.form.name = this.source.name;
                    this.form.front = this.source.front;
                }
            }
        }
    }
</script>