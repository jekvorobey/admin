<template>
    <div>
        <h3>Сообщение</h3>
        <b-form-textarea v-model="form.message"/>
        <hr/>
        <h3>Файлы</h3>
        <div v-for="(file, key) in form.files">
            <file-input v-if="!file.is_load"
                        @uploaded="(data) => onFileUpload(data, file)"
                        class="mb-3"
                        destination='communications'
            ></file-input>
            <div v-else class="alert alert-success py-1 px-3" role="alert">
                Файл <a :href="file.file.url" target="_blank" class="alert-link">{{ file.file.name }}</a> загружен
                <v-delete-button @delete="onFileDelete(key)" btn-class="btn-danger btn-sm"/>
            </div>
        </div>
        <button type="button" @click="onFileAdd()" class="btn btn-success"><fa-icon icon="plus"/></button>
        <hr/>
        <button type="button" @click="onClickSend()" class="btn btn-success">{{ sendButtonName }}</button>
    </div>
</template>

<script>
import FileInput from '../controls/FileInput/FileInput.vue';
import VDeleteButton from '../controls/VDeleteButton/VDeleteButton.vue';

export default {
    name: 'communication-chat-message',
    components: {VDeleteButton, FileInput},
    props: ['kind'],
    data() {
        let sendButtonName;
        switch (this.kind) {
            case 'createChat':
                sendButtonName = 'Создать чат';
                break;
            default:
                sendButtonName = 'Отправить сообщение';
        }
        return {
            sendButtonName: sendButtonName,
            form: {
                message: '',
                files: [
                    this.initNewFile(),
                ]
            }
        }
    },
    methods: {
        initNewFile() {
            return {
                is_load: false,
                file: null
            }
        },
        onFileUpload(data, file) {
            file.is_load = true;
            file.file = data;
        },
        onFileDelete(key) {
            this.$delete(this.form.files, key);
        },
        onFileAdd() {
            this.$set(this.form.files, this.form.files.length, this.initNewFile());
        },
        initComponent() {
            this.form.message = '';
            this.form.files = [
                this.initNewFile(),
            ];
        },
        onClickSend() {
            const files = [];
            this.form.files.forEach(file => {
                if (file.is_load && file.file.id) {
                    files.push(file.file.id);
                }
            });

            this.$emit('send', { message: this.form.message, files});

            this.initComponent();
        },
    },
};
</script>

<style scoped>

</style>