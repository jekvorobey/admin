<template>
    <b-modal :id="id" :title="`Пометить статусом '${customerStatusName[status]}'`" hide-footer>
        <template v-slot:default="{close}">

            <v-input v-model="$v.form.comment_status.$model" tag="textarea" :error="errorCommentStatus">
                Комментарий{{isCommentRequired ? '*' : ''}}
            </v-input>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="saveStatus">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import Services from '../../../../../scripts/services/services.js';
    import {requiredIf} from 'vuelidate/lib/validators';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import {validationMixin} from 'vuelidate';

    export default {
    name: 'modal-mark-status',
    props: ['model', 'status', 'id'],
    components: {
        VInput,
    },
    data() {
        return {
            form: {
                comment_status: this.model.comment_status,
            }
        }
    },
    mixins: [
        validationMixin,
    ],
    validations() {
        let self = this;

        return {
            form: {
                comment_status: {
                    required: requiredIf(() => {
                        return self.isCommentRequired;
                    })
                },
            }
        };
    },
    watch: {
        'model.comment_status': function (val, oldVal) {
            this.form.comment_status = this.model.comment_status;
        }
    },
    computed: {
        customer: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        isCommentRequired() {
            return this.status == this.customerStatus.problem || this.status == this.customerStatus.block;
        },
        errorCommentStatus() {
            if (this.$v.form.comment_status.$dirty) {
                if (!this.$v.form.comment_status.required) {
                    return "Обязательное поле";
                }
            }
        },
    },
    methods: {
        saveStatus() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.save', {id: this.model.id}), {}, {
                customer: {
                    status: this.status,
                    comment_status: this.form.comment_status,
                },
            }).then(data => {
                this.customer.comment_status = this.form.comment_status;
                this.customer.status = this.status;
                Services.hideLoader();
                this.$bvModal.hide(this.id);
                Services.msg("Изменения сохранены");
            });
        },
    }
};
</script>

<style scoped>

</style>