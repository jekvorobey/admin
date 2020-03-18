<template>
<div>
    <div>
        Документы
    </div>

    <div v-for="(document, i) in documents" class="mb-1">
        <a :href="document.url" target="_blank">{{ document.name }}</a>
        <v-delete-button btn-class="btn-danger btn-sm" @delete="deleteDocument(document.id, i)"/>
    </div>
    <div v-if="!documents.length">-</div>

    <div>
        <file-input v-if="!form.file" @uploaded="(data) => form.file = data" class="mb-3"></file-input>
        <div v-else class="alert alert-success py-1 px-3" role="alert">
            Файл <a :href="form.file.url" target="_blank" class="alert-link">{{ form.file.name }}</a> загружен
            <v-delete-button @delete="form.file = null" btn-class="btn-danger btn-sm"/>
            <button class="btn btn-success btn-sm" @click="createDocument"><fa-icon icon="plus"/></button>
        </div>
    </div>
</div>
</template>

<script>
    import Services from '../../../../../scripts/services/services.js';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import FileInput from '../../../../components/controls/FileInput/FileInput.vue';

    export default {
        name: 'tab-document',
        components: {FileInput, VDeleteButton},
        props: ['model'],
        data() {
            return {
                documents: [],

                form: {
                    file: null
                }
            }
        },
        computed: {
            customer: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
        },
        methods: {
            deleteDocument(document_id, index) {
                Services.showLoader();
                Services.net().delete(this.getRoute('customers.detail.document.delete', {
                    id: this.customer.id,
                    document_id: document_id
                })).then(data => {
                    this.$delete(this.documents, index);
                    Services.hideLoader();
                    Services.msg("Изменения сохранены");
                })
            },
            createDocument() {
                Services.showLoader();
                Services.net().post(this.getRoute('customers.detail.document.create', {
                    id: this.customer.id,
                    file_id: this.form.file.id,
                })).then(data => {
                    this.$set(this.documents, this.documents.length, {
                        id: data.id,
                        name: this.form.file.name,
                        url: this.form.file.url,
                    });
                    this.form.file = null;
                    Services.hideLoader();
                    Services.msg("Изменения сохранены");
                })
            },
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('customers.detail.document', {id: this.model.id})).then(data => {
                this.documents = data.documents;
                Services.hideLoader();
            })
        }
    };
</script>

<style scoped>

</style>