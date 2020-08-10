<template>
    <b-modal id="modal-add-variant-group" hide-footer ref="modal">
        <template v-slot:modal-title>
            Добавление новой товарной группы
        </template>
        <template v-slot:default="{close}">
            <b-form>
                <b-form-row>
                    <b-col>
                        <v-input v-model="$v.form.name.$model">
                            Название
                        </v-input>
                    </b-col>
                </b-form-row>
                <b-form-row>
                    <b-col>
                        <v-select v-model="$v.form.merchant_id.$model" :options="merchantOptions">
                            Мерчант
                        </v-select>
                    </b-col>
                </b-form-row>

                <div class="float-right mt-3">
                    <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                    <button class="btn btn-info" @click="save">Создать</button>
                </div>
            </b-form>
        </template>
    </b-modal>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';

    import {validationMixin} from 'vuelidate';
    import {requiredIf} from 'vuelidate/lib/validators';

    export default {
        name: 'modal-add-variant-group',
        components: {
            VInput,
            VSelect,
        },
        mixins: [
            validationMixin,
        ],
        props: [
            'merchantOptions',
        ],
        data() {
            return {
                form: {
                    name: null,
                    merchant_id: null,
                },
            }
        },
        validations() {
            const notRequired = {required: requiredIf(() => {return false;})};

            let form = {
                name: {notRequired},
                merchant_id: {notRequired},
            };

            return {
                form: form
            };
        },
        methods: {
            save() {
                let self = this;
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().post(this.getRoute('variantGroups.create'), {}, this.form).then((data) => {
                    this.$bvModal.hide('modal-add-variant-group');
                    Services.msg("Товарная группа создана");
                    window.location.href = self.getRoute('variantGroups.detail', {id: data.id});
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
};
</script>