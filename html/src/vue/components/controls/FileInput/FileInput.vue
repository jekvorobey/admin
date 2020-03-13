<template>
    <div>
        <div v-show="!showProgress">
            <label :for="inputId" v-if="label" class="mb-3">{{ label }}</label>
            <div class="custom-file">
                <input
                        type="file"
                        ref="file"
                        :id="inputId"
                        class="custom-file-input"
                        :class="{'is-invalid': fileError, 'form-control-sm': sm}"
                        :disabled="disabled"
                        @change="change"
                >
                <span :id="`${inputId}-alert`" class="invalid-feedback" role="alert">
                    <slot name="error" :error="fileError">
                        {{ fileError }}
                    </slot>
                </span>
                <label class="custom-file-label mb-0" :class="{'col-form-label-sm': sm}" :for="inputId">
                    Выберите файл
                </label>
            </div>
        </div>

        <div v-show="showProgress" class="progress upload-progress">
            <div class="progress-bar" role="progressbar" :style="{width: `${uploadProgress}%`}" :aria-valuenow="uploadProgress" aria-valuemin="0" aria-valuemax="100">
                {{ uploadProgress < 100 ? `Отправка ${uploadProgress}%` : 'Сохранение' }}
            </div>
        </div>
    </div>
</template>

<script>
    import inputMixin from '../../../mixins/input-mixin';
    import Services from '../../../../scripts/services/services';

    export default {
        name: "FileInput",
        mixins: [inputMixin],
        props: {
            disabled: {
                type: Boolean,
                default: false,
            },
            label: {},
            size: {
                type: String,
                default: 'normal',
            }
        },
        data() {
            return {
                inputId: `v-input-id-${this._uid}`,
                showProgress: false,
                uploadProgress: 0,
                uploadError: '',
            };
        },
        methods: {
            change() {
                let files = this.$refs.file.files;
                if (files.length > 0) {
                    let file = files[0],
                        formData = new FormData();
                    formData.append('file', file);
                    this.showProgress = true;
                    this.uploadProgress = 0;
                    this.uploadError = '';
                    Services.net().post(this.getRoute('uploadFile'), {}, formData, {
                        onUploadProgress: progressEvent => {
                            this.uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
                        }
                    })
                        .then(
                            data => {
                                this.$emit('uploaded', data);
                            },
                            error => {
                                this.uploadError = 'Ошибка при загрузке файла';
                            }
                        )
                        .finally(data => {
                            this.showProgress = false;
                        });
                }
            }
        },
        computed: {
            fileError() {
                return this.uploadError || this.error;
            },
            sm() {
                return this.size === 'sm';
            }
        },
    }
</script>

<style scoped>
    .upload-progress {
        height: 38px;
    }
</style>