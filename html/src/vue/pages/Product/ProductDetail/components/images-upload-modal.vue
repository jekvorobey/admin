<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                Загрузка изображений товара
            </div>
            <div slot="body">
                <file-uploader v-model="files" accept="image/png,image/jpeg,image/webp" />
                <b-button :disabled="!canAccept" @click="accept">Прикрепить загруженные файлы</b-button>
            </div>
        </modal>
    </transition>
</template>

<script>
import modal from '../../../../components/controls/modal/modal.vue';
import FileInput from '../../../../components/controls/FileInput/FileInput.vue';
import modalMixin from '../../../../mixins/modal.js';
import FileUploader from '../../../../components/controls/FileUploader/FileUploader.vue';

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
    },

    data () {
        return {
            files: [],
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
        accept() {
            if (!this.canAccept) {
                return false;
            }

            const files = this.files.map(file => {
                return file.response;
            });

            this.$emit('accept', files);
        },
    },
}
</script>
