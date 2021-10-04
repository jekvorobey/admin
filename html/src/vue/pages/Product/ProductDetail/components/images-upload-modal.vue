<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                Загрузка изображений товара
            </div>
            <div slot="body">
                <file-uploader v-model="files" accept="image/png,image/jpeg,image/webp" />
                <b-button :disabled="!canAccept || uploading" @click="accept">
                    <template v-if="uploading">
                        <b-spinner small />
                    </template>
                    <template v-else>
                        Прикрепить загруженные файлы
                    </template>
                </b-button>
            </div>
        </modal>
    </transition>
</template>

<script>
import modal from '../../../../components/controls/modal/modal.vue';
import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
import modalMixin from '../../../../mixins/modal.js';
import FileUploader from '../../../../components/controls/FileUploader/FileUploader.vue';

import Services from "../../../../../scripts/services/services";

export default {
    components: {
        FileUploader,
        modal,
        FileInput,
    },

    mixins: [modalMixin],

    props: {
        modalName: {
            type: String,
            required: true
        },

        productId: {
            type: Number,
            required: true
        }
    },

    data () {
        return {
            files: [],
            uploading: false,
        };
    },

    computed: {
        canAccept() {
            if (this.files.length === 0) {
                return false;
            }

            for (const file of this.files) {
                if (file.success === false) {
                    return false;
                }
            }

            return true;
        }
    },

    methods: {
        async accept() {
            if (!this.canAccept) {
                return false;
            }

            const files = this.files.map(file => {
                return file.response;
            });

            this.uploading = true;

            for (const file of files) {
                try {
                    await Services.net().post(
                        this.getRoute('products.saveImage', { id: this.productId }),
                        {},
                        {
                            id: file.id,
                            type: 3
                        }
                    );
                } catch (error) {
                    console.error(error);
                }
            }

            this.uploading = false;
            this.files = [];

            this.$emit('upload', files);
        },
    },
}
</script>
