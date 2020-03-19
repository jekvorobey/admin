<template>
<div>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="14">Документы</th>
        </tr>
        <tr>
            <th>ID</th>
            <th>Период</th>
            <th>Дата документа</th>
            <th>Сумма вознаграждения</th>
            <th>Статус</th>
            <th>Файл</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(document, i) in documents">
            <td>{{ document.id }}</td>
            <td>(заглушка)</td>
            <td>{{ document.date }}</td>
            <td>(заглушка)</td>
            <td>(заглушка)</td>
            <td>
                <a :href="document.url" target="_blank">{{ document.name }}</a>
                <v-delete-button btn-class="btn-danger btn-sm" @delete="deleteDocument(document.id, i)"/>
            </td>
        </tr>
        </tbody>
    </table>

    <!--<div v-for="(document, i) in documents" class="mb-1">
        <a :href="document.url" target="_blank">{{ document.name }}</a>
        <v-delete-button btn-class="btn-danger btn-sm" @delete="deleteDocument(document.id, i)"/>
    </div> -->
    <div v-if="!documents.length">-</div>

    <div>
        Добавление документа пользователю
    </div>

    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="2">
                Действия:
                <button  class="btn btn-success" :disabled="!showBtn">Добавить</button>
                <button  class="btn btn-outline-danger" :disabled="!showBtn">Отмена</button>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Начало периода подсчета:</th>
            <td><input type="date" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Конец периода подсчета:</th>
            <td><input type="date" class="form-control form-control-sm"/></td>
        </tr>
        <tr>
            <th>Сумма вознаграждения</th>
            <td>
                <input type="number" class="form-control form-control-sm" />
            </td>
        </tr>
        <tr>
            <th>Статус</th>
            <td>
                <select class="form-control form-control-sm" >
                    <option :value="null">-</option>
                    <option :value="1">Доступен для просмотра</option>
                    <option :value="2">Недоступен для просмотра</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>Добавить файл</th>
            <td>
                <div>
                    <file-input v-if="!form.file" @uploaded="(data) => form.file = data" class="mb-3"></file-input>
                    <div v-else class="alert alert-success py-1 px-3" role="alert">
                        Файл <a :href="form.file.url" target="_blank" class="alert-link">{{ form.file.name }}</a> загружен
                        <v-delete-button @delete="form.file = null" btn-class="btn-danger btn-sm"/>
                        <button class="btn btn-success btn-sm" @click="createDocument"><fa-icon icon="plus"/></button>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
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