<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('legalEdit')" type="wide">
            <div slot="header">
                Редактирование реквизитов мерчанта
            </div>
            <div slot="body">
                <v-input v-model="$v.form.legal_name.$model">
                    Юридическое название
                </v-input>
                <v-input v-model="$v.form.legal_address.$model">
                    Юридический адрес
                </v-input>
                <div class="row">
                    <v-input v-model="$v.form.inn.$model" class="col">
                        ИНН
                    </v-input>
                    <v-input v-model="$v.form.kpp.$model" class="col">
                        КПП
                    </v-input>
                </div>
                <div class="row">
                    <v-input v-model="$v.form.payment_account.$model" class="col">
                        Расчётный счёт
                    </v-input>
                    <v-input v-model="$v.form.payment_account_bank.$model" class="col">
                        Расчётный банк
                    </v-input>
                </div>
                <div class="row">
                    <v-input v-model="$v.form.correspondent_account.$model" class="col">
                        Корреспонденский счёт
                    </v-input>
                    <v-input v-model="$v.form.correspondent_account_bank.$model" class="col">
                        Корреспонденский банк
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

    import modalMixin from '../../../../mixins/modal.js';
    import { validationMixin } from 'vuelidate';
    import Services from '../../../../../scripts/services/services';

    export default {
        mixins: [modalMixin, validationMixin],
        components: {
            modal,
            VInput
        },
        props: {
            source: Object,
        },
        data() {
            return {
                form: Object.assign({}, this.source),
            };
        },
        validations: {
            form: {
                legal_name: {},
                legal_address: {},
                inn: {},
                kpp: {},
                correspondent_account: {},
                correspondent_account_bank: {},
                payment_account: {},
                payment_account_bank: {},
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.net().post(this.getRoute('merchant.edit', {id: this.source.id}), {}, {
                    legal_name: this.form.legal_name,
                    legal_address: this.form.legal_address,
                    inn: this.form.inn,
                    kpp: this.form.kpp,
                    correspondent_account: this.form.correspondent_account,
                    correspondent_account_bank: this.form.correspondent_account_bank,
                    payment_account: this.form.payment_account,
                    payment_account_bank: this.form.payment_account_bank,
                })
                    .then((data)=> {
                        this.$emit('edited', data.merchant);
                        this.closeModal();
                    });
            }
        },
        watch: {
            '$store.state.modal.currentModal': function(newValue) {
                if (newValue === 'legalEdit') {
                    this.$set(this, 'form', Object.assign({}, this.source));
                }
            }
        }
    }
</script>