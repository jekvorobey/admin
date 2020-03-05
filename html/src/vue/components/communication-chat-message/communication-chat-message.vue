<template>
    <div>
        <h3>Сообщение</h3>
        <b-form-textarea v-model="form.message"/>
        <hr/>
        <h3>Файлы</h3>
        <div v-for="(file, key) in form.files">
            <file-input v-if="!file.is_load" @uploaded="(data) => onUpload(data, file)" class="mb-3"></file-input>
            <div v-else class="alert alert-success py-1 px-3" role="alert">
                Файл <a :href="file.file.url" target="_blank" class="alert-link">{{ file.file.name }}</a> загружен
                <v-delete-button @delete="onDelete(key)" btn-class="btn-danger btn-sm"/>
            </div>
        </div>
        <button type="button" @click="onAdd()" class="btn btn-success"><fa-icon icon="plus"/></button>
        <hr/>
        <button type="button" @click="onSend()" class="btn btn-success" v-if="this.addButton">Отправить сообщение</button>
    </div>
</template>

<script>
import FileInput from '../controls/FileInput/FileInput.vue';
import VDeleteButton from '../controls/VDeleteButton/VDeleteButton.vue';

export default {
    name: 'communication-chat-message',
    components: {VDeleteButton, FileInput},
    props: ['addButton'],
    data() {
        return {
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
        onUpload(data, file) {
            file.is_load = true;
            file.file = data;
        },
        onDelete(key) {
            this.$delete(this.form.files, key);
        },
        onAdd() {
            this.$set(this.form.files, this.form.files.length, this.initNewFile());
        },
        onSend() {
            const files = [];
            this.form.files.forEach(file => {
                if (file.is_load && file.file.id) {
                    files.push(file.file.id);
                }
            });

            this.$emit('send', {files, message: this.form.message});
            this.form.files = [
                this.initNewFile(),
            ];
            this.form.message = '';
        },
    }
};
</script>

<style scoped>

</style>