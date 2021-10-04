<template>
    <div class="file-uploader">
        <div class="file-uploader__drag-area">
            <div class="file-uploader__drag" @click="openFilesDialog">
                <div class="file-uploader__drag-content">
                    <b-icon
                        class="file-uploader__drag-content-icon"
                        icon="upload" style="width: 48px; height: 48px;"
                    />
                    <span>Перенесите файлы на страницу или выберите из списка</span>
                </div>
            </div>
            <div class="text-center">
                <file-upload
                    ref="upload"
                    v-model="files"
                    class="file-uploader__button"
                    style="display: none;"
                    :drop="true"
                    :custom-action="uploadAction"
                    :accept="accept"
                    multiple
                    @input-filter="fileFilter"
                >
                    <b-button variant="primary" :disabled="isUploading">Выбрать файлы</b-button>
                </file-upload>
            </div>
        </div>
        <div class="file-uploader__file-list">
            <div
                v-for="(file, i) in files"
                :key="file.id"
                :class="{
                    'file-uploader__file': true,
                    'file-uploader__file--last': (i + 1) === files.length
                }"
            >
                <div class="file-uploader__file-thumb">
                    <template v-if="/image\/*/.test(file.type)">
                        <b-img :src="file.thumb" fluid />
                    </template>
                    <template else>

                    </template>
                </div>
                <div class="file-uploader__file-details">
                    <button class="file-uploader__file-remove-button" @click="$refs.upload.remove(file)">
                        <svg viewBox="0 0 329.26933 329" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="m194.800781 164.769531 128.210938-128.214843c8.34375-8.339844 8.34375-21.824219 0-30.164063-8.339844-8.339844-21.824219-8.339844-30.164063 0l-128.214844 128.214844-128.210937-128.214844c-8.34375-8.339844-21.824219-8.339844-30.164063 0-8.34375 8.339844-8.34375 21.824219 0 30.164063l128.210938 128.214843-128.210938 128.214844c-8.34375 8.339844-8.34375 21.824219 0 30.164063 4.15625 4.160156 9.621094 6.25 15.082032 6.25 5.460937 0 10.921875-2.089844 15.082031-6.25l128.210937-128.214844 128.214844 128.214844c4.160156 4.160156 9.621094 6.25 15.082032 6.25 5.460937 0 10.921874-2.089844 15.082031-6.25 8.34375-8.339844 8.34375-21.824219 0-30.164063zm0 0"
                            />
                        </svg>
                    </button>
                    <div class="file-uploader__file-name">{{ file.name }}</div>
                    <div class="file-uploader__file-progress">
                        <b-progress
                            height="3px"
                            :value="Number.parseFloat(file.progress)"
                            :variant="file.error && file.error.length > 0 ? 'danger' : 'primary'"
                        />

                        <span>{{ formatBytes(file.size) }}</span>
                    </div>
                    <div v-if="file.error && file.error.length > 0" class="file-uploader__file-error">
                        {{ file.error }}
                    </div>
                </div>
            </div>

            <div v-if="files.length > 0" class="file-uploader__upload-tip mb-3">
                Для подтверждения загрузки файлов на сервер нажмите на кнопку "Загрузить"
            </div>

            <b-button
                v-if="files.length > 0"
                variant="light"
                :disabled="isUploading"
                @click="$refs.upload.active = true"
            >Загрузить</b-button>

            <span v-if="files.length === 0">Не выбрано ни одного файла</span>
        </div>
    </div>
</template>

<script>
import { BIcon, BIconUpload } from 'bootstrap-vue';

import './FileUploader.css';

export default {
    name: 'file-uploader',

    components: {
        BIcon,
        BIconUpload,
    },

    props: {
        value: {
            type: Array,
            default() {
                return [];
            }
        },

        destination: {
            type: String,
            default: 'catalog'
        },

        accept: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            files: this.value
        };
    },

    watch: {
        files() {
            this.$emit('input', this.files);
        }
    },

    computed: {
        uploadUrl() {
            return this.getRoute('uploadFile');
        },

        isUploading() {
            if (typeof this.$refs.upload !== 'undefined' && this.$refs.upload.active === true) {
                return this.$refs.upload.active === true;
            }

            return false;
        }
    },

    methods: {
        fileFilter(newFile, oldFile) {
            if (newFile && !oldFile) {
                if (window.URL) {
                    newFile.thumb = window.URL.createObjectURL(newFile.file);
                }
            }
        },

        openFilesDialog() {
            console.log(this.$refs.upload);

            this.$refs.upload.$el.querySelector('input').click();
        },

        uploadAction(file, fileUploadComponent) {
            let form = new window.FormData();
            let value;

            for (let key in file.data) {
                value = file.data[key];

                if (value && typeof value === 'object' && typeof value.toString !== 'function') {
                    if (value instanceof File) {
                        form.append(key, value, value.name);
                    } else {
                        form.append(key, JSON.stringify(value));
                    }
                } else if (value !== null && value !== undefined) {
                    form.append(key, value);
                }
            }

            form.append('file', file.file, file.file.filename || file.name);

            let xhr = new XMLHttpRequest();
            xhr.open('POST', `${this.uploadUrl}?destination=${this.destination}`);

            return fileUploadComponent.uploadXhr(xhr, file, form);
        },

        formatBytes(size, precision = 2) {
            if (size === 0) return '0 Bytes';

            const k = 1024;
            const dm = precision < 0 ? 0 : precision;
            const sizes = ['Bytes', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];

            const i = Math.floor(Math.log(size) / Math.log(k));

            return parseFloat((size / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
    }
};
</script>
