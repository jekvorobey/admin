<template>
    <div class="form-group">
        <label :for="inputId">
            <slot />
        </label>
        <div class="custom-file mt-3">
            <input @change="change" :class="{ 'is-invalid': fileError }" type="file" class="custom-file-input" :id="inputId" :disabled="disabled" ref="file">
            <span :id="`${inputId}-alert`" class="invalid-feedback" role="alert">
            <slot name="error" :error="fileError">
                {{ fileError }}
            </slot>
        </span>
            <label class="custom-file-label" :for="inputId">Choose file</label>
            <div v-if="showProgress" class="progress upload-progress">
                <div class="progress-bar" role="progressbar" :style="{width: `${uploadProgress}%`}" :aria-valuenow="uploadProgress" aria-valuemin="0" aria-valuemax="100">
                    {{ uploadProgress < 100 ? `Отправка ${uploadProgress}%` : 'Сохранение' }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import inputMixin from '../VInput/inputMixin';
    import Services from "../../../../scripts/services/services";
    import {mapGetters} from "vuex";
    export default {
        name: "FileInput",
        mixins: [inputMixin],
        props: {
            disabled: {
                type: Boolean,
                default: false,
            },
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
            ...mapGetters(['getRoute']),
            fileError() {
                return this.uploadError || this.error;
            }
        },
    }
</script>

<style scoped>
    .upload-progress {
        height: 38px;
        position: relative;
        top: -38px;
        z-index: 999999;
    }
</style>