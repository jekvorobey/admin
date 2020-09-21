<template>
    <b-card>
        <b-row>
            <b-col>
                <p class="font-weight-bold">Инфопанель</p>
            </b-col>
            <b-col>
                <div class="float-right">
                    <button class="btn btn-success btn-sm" @click="save" :disabled="!$v.form.$anyDirty">
                        Сохранить
                    </button>
                    <button @click="cancel" class="btn btn-outline-danger btn-sm mr-1" :disabled="!$v.form.$anyDirty">
                        Отмена
                    </button>
                    <v-delete-button @delete="deleteVariantGroup()" btn-class="btn-danger"/>
                </div>
            </b-col>
        </b-row>

        <b-row>
            <b-col>
                <v-input v-model="$v.form.name.$model">
                    Название
                </v-input>
            </b-col>
            <b-col>
                <span class="font-weight-bold">Мерчант:</span>
                <span>
                    <a :href="getRoute('merchant.detail', {id: variantGroup.merchant.id})" v-if="variantGroup.merchant">
                        {{variantGroup.merchant.legal_name}}
                    </a>
                    <template v-else>N/A</template>
                </span>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <span class="font-weight-bold">Дата создания:</span>
                <span>{{variantGroup.created_at}}</span>
            </b-col>
            <b-col>
                <span class="font-weight-bold">Дата изменения:</span>
                <span>{{variantGroup.updated_at}}</span>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import VDeleteButton from '../../../../../components/controls/VDeleteButton/VDeleteButton.vue';

    import {validationMixin} from 'vuelidate';
    import {requiredIf} from 'vuelidate/lib/validators';

    export default {
        name: 'infopanel',
        components: {
            VInput,
            VDeleteButton,
        },
        mixins: [
            validationMixin,
        ],
        props: [
            'model',
        ],
        data() {
            return {
                form: {
                    name: this.model.name,
                },
            };
        },
        validations() {
            const notRequired = {required: requiredIf(() => {return false;})};

            let form = {
                name: {notRequired},
            };

            return {
                form: form
            };
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('variantGroups.detail.save', {id: this.variantGroup.id}), {}, this.form).then(() => {
                    if (this.variantGroup.name !== this.form.name) {
                        location.reload();
                    }
                    this.variantGroup.name = this.form.name;

                    Services.msg("Изменения сохранены");
                }).finally(data => {
                    Services.hideLoader();
                });
            },
            cancel() {
                this.form.name = this.variantGroup.name;
            },
            deleteVariantGroup() {
                let self = this;

                Services.showLoader();
                Services.net().delete(this.getRoute('variantGroups.delete'), {
                    ids: [this.variantGroup.id],
                }).then(() => {
                    Services.msg("Удаление прошло успешно");
                    window.location.href = self.getRoute('variantGroups.list');
                }).finally(() => {
                    Services.hideLoader();
                });
            }
        },
        computed: {
            variantGroup: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
        },
    };
</script>
