<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                Отклонение карточки товара
            </div>
            <div slot="body">
                <v-input tag="textarea" v-model="$v.form['message'].$model">
                    Сообщение для мерчанта
                </v-input>
                <button @click="save" class="btn btn-dark mt-3" :disabled="!$v.form.$anyDirty">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Services from '../../../../../scripts/services/services';
    import {mapGetters} from 'vuex';

    import modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import modalMixin from '../../../../mixins/modal';

    export default {
        components: {
            modal,
            VInput,
        },
        mixins: [validationMixin, modalMixin],
        props: {
            modalName: String,
            productId: Number,
        },
        data () {
            return {
                form: {}
            };
        },
        validations() {
            return {
                form: {
                    'message': {
                        required,
                    }
                }
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.net().put(
                    this.getRoute('products.reject', {
                        id: this.productId,
                    }),
                    {},
                    {approval_status_comment: this.form['message']}
                )
                .then(result => {
                    this.$emit('onSave', result);
                    this.closeModal();
                });
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
        }
    }
</script>