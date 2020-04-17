<template>
    <div>
        <v-input v-model="$v.form.description.$model" tag="textarea" rows="15">Описание</v-input>
        <div class="form-group mt-3">
            <button @click="save" class="btn btn-dark" :disabled="!$v.$anyDirty">Сохранить</button>
        </div>
    </div>
</template>

<script>
    import {ACT_SAVE_PUBLIC_EVENT, NAMESPACE} from '../../../../../store/modules/public-events';
    import {validationMixin} from 'vuelidate';

    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import {mapActions} from "vuex";

    export default {
        mixins: [validationMixin],
        components: {
            VInput
        },
        props: {
            publicEvent: {}
        },
        data () {
            return {
                form: {
                    description: this.publicEvent.description,
                },
            };
        },
        validations: {
            form: {
                description: {}
            }
        },
        methods: {
            ...mapActions(NAMESPACE, {
                saveEvent: ACT_SAVE_PUBLIC_EVENT
            }),
            async save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                await this.saveEvent({
                    id: this.publicEvent.id,
                    data: this.form,
                });
                this.$emit('onSave');
            }
        }
    }
</script>

<style scoped>

</style>