<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                Загрузка файла
            </div>
            <div slot="body">
                <file-input v-if="!file" @uploaded="onUpload" class="mb-3"/>
                <div v-else class="alert alert-success" role="alert">
                    Файл загружен
                </div>
                <button @click="accept" :disabled="!file" class="btn btn-dark">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../../components/controls/modal/modal.vue';

    import FileInput from '../../../../components/controls/FileInput/FileInput.vue';

    import modalMixin from '../../../../mixins/modal.js';

    export default {
        components: {
            modal,
            FileInput
        },
        mixins: [modalMixin],
        props: {
            modalName: String,
        },
        data () {
            return {
                file: undefined
            };
        },
        methods: {
            onUpload(file) {
                this.file = file;
            },
            accept() {
                this.$emit('accept', this.file);
                this.file = undefined;
            }
        },
    }
</script>