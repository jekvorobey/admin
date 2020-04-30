<template>
    <div>
        <v-input tag="textarea" v-model="$v.form['message'].$model">
            Сообщение для менеджера iBT
        </v-input>
        <button @click="save" class="btn btn-dark mt-3" :disabled="!$v.form.$anyDirty">Сохранить</button>
    </div>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Services from '../../../../../../scripts/services/services';

    import VInput from '../../../../../components/controls/VInput/VInput.vue';

    export default {
        components: {
            VInput,
        },
        mixins: [validationMixin],
        props: {
            shipment: {},
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
                    this.getRoute('orderAssembly.reportProblem', {
                        id: this.shipment.id,
                    }),
                    {},
                    {assembly_problem_comment: this.form['message']}
                )
                .then(result => {
                    this.$emit('onSave', result);
                });
            },
        },
    }
</script>
