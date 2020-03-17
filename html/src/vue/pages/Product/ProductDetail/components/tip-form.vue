<template>
    <div>
        <v-input v-model="$v.form.description.$model" tag="textarea" :error="errorDescription">Название</v-input>
        <div>
            <div class="row">
                <div v-if="form.fileId" class="col">Файл прикреплён</div>
                <div class="col">
                    <file-input @uploaded="data => $v.form.fileId.$model = data.id" :error="errorImage">Файл</file-input>
                </div>
            </div>
            <img v-if="form.fileId" :src="imageUrl" alt="preview">
        </div>
        <button @click="save" class="btn btn-dark mt-3" :disabled="!$v.form.$anyDirty">Сохранить</button>
    </div>
</template>

<script>
    import { validationMixin } from 'vuelidate';
    import { required } from 'vuelidate/lib/validators';

    import Services from '../../../../../scripts/services/services';

    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
    import Media from '../../../../../scripts/media';

    export default {
        components: {
            VInput,
            FileInput,
        },
        mixins: [validationMixin],
        props: {
            tip: {},
            product: {},
        },
        data () {
            return {
                tipId: this.tip.id,
                form: {
                    description: this.tip.description,
                    fileId: this.tip.file_id,
                },
            };
        },
        validations: {
            form: {
                description: {required},
                fileId: {required}
            },
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                if (this.isEdit) {
                    Services.net().post(this.getRoute('product.editTip', {id: this.product.id, tipId: this.tip.id}), {}, this.form)
                        .then(result => {
                            this.$emit('onSave', result);
                        });
                } else {
                    Services.net().post(this.getRoute('product.addTip', {id: this.product.id}), {}, this.form)
                        .then(result => {
                            this.$emit('onSave', result);
                        });
                }
            }
        },
        computed: {
            errorDescription() {
                if (this.$v.form.description.$dirty) {
                    if (this.$v.form.description.required === false) return "Обязательное поле!";
                }
            },
            errorImage() {
                if (this.$v.form.fileId.$dirty) {
                    if (this.$v.form.fileId.required === false) return "Обязательное поле!";
                }
            },
            imageUrl() {
                return Media.compressed(this.form.fileId, 290, 290);
            },
            isEdit() {
                return !!this.tip.id;
            }
        }
    }
</script>