<template>
    <div>
        <b-card>
            <b-row>
                <div class="col-sm-6">
                    <p class="font-weight-bold">Комментарий менеджера</p>
                </div>
                <div class="col-sm-6" v-if="canEdit">
                    <div class="float-right">
                        <button class="btn btn-success btn-sm" @click="save" :disabled="!$v.form.$anyDirty">
                            Сохранить
                        </button>
                    </div>
                </div>
            </b-row>
            <b-row>
                <b-col>
                    <v-input v-model="$v.form.manager_comment.$model" tag="textarea" :disabled="!canEdit"></v-input>
                </b-col>
            </b-row>
        </b-card>
    </div>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';

    import {required, requiredIf} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    export default {
        components: {
            VInput,
        },
        props: [
            'model',
        ],
        mixins: [
            validationMixin,
        ],
        data() {
            return {
                form: {
                    manager_comment: this.model.manager_comment,
                },
            }
        },
        validations() {
            const notRequired = {required: requiredIf(() => {return false;})};

            return {
                form: {
                    manager_comment: notRequired,
                }
            };
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('baskets.detail.save', {id: this.basket.id}), {}, this.form).then((data) => {
                    this.$set(this, 'basket', data.basket);
                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        computed: {
            basket: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
            canEdit() {
                return this.canUpdate(this.blocks.baskets)
            },
        },
        created() {

        }
    }
</script>

<style scoped>

</style>
