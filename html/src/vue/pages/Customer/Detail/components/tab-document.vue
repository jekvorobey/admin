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
            <td>{{ datePrint(document.period_since) }} — {{ datePrint(document.period_to) }}</td>
            <td>{{ datetimePrint(document.date) }}</td>
            <td>{{ document.amount_reward }} руб.</td>
            <td>
                <span class="badge" :class="statusClass(document.status)">{{ document.status}}</span></td>
            <td>
                <a :href="document.url" target="_blank">{{ document.name }}</a>
                <v-delete-button btn-class="btn-danger btn-sm" @delete="deleteDocument(document.id, i)"/>
            </td>
        </tr>
        </tbody>
    </table>

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
            <td><input type="date" class="form-control form-control-sm" v-model="newDocument.period_since"/></td>
        </tr>
        <tr>
            <th>Конец периода подсчета:</th>
            <td><input type="date" class="form-control form-control-sm" v-model="newDocument.period_to"/></td>
        </tr>
        <tr>
            <th>Сумма вознаграждения</th>
            <td>
                <input type="number" class="form-control form-control-sm" v-model="newDocument.amount_reward"/>
            </td>
        </tr>
        <tr>
            <th>Статус</th>
            <td>
                <select class="form-control form-control-sm" v-model="newDocument.status">
                    <option :value="1">Сформирован</option>
                    <option :value="2">Согласован</option>
                    <option :value="3">Отклонен</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>Добавить файл</th>
            <td>
                <div>
                    <file-input v-if="!newDocument.file" @uploaded="(data) => newDocument.file = data" class="mb-3"></file-input>
                    <div v-else class="alert alert-success py-1 px-3" role="alert">
                        Файл <a :href="newDocument.file.url" target="_blank" class="alert-link">{{ newDocument.file.name }}</a> загружен
                        <v-delete-button @delete="newDocument.file = null" btn-class="btn-danger btn-sm"/>
                        <button class="btn btn-success btn-sm" @click="createDocument(newDocument)"><fa-icon icon="plus"/></button>
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
                newDocument: {
                    period_since: '',
                    period_to: '',
                    amount_reward: '',
                    status: '',
                    file: null,
                },
            }
        },
        computed: {
            customer: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
        },
        methods: {
            statusClass(status) {
                switch (status) {
                    case 'Сформирован': return 'badge-secondary';
                    case 'Согласован': return 'badge-success';
                    case 'Отклонен': return 'badge-danger';
                    default: return 'badge-secondary';
                }
            },
            deleteDocument(document_id, index) {
                Services.showLoader();
                Services.net().delete(this.getRoute('customers.detail.document.delete', {
                    id: this.customer.id,
                    document_id: document_id
                })).then(data => {
                    this.$delete(this.documents, index);
                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                })
            },
            createDocument(newDocument) {
                Services.showLoader();
                Services.net().post(this.getRoute('customers.detail.document.create', {
                    id: this.customer.id,
                }), newDocument).then(data => {
                    this.$set(this.documents, this.documents.length, {
                        id: data.id,
                        period_since: data.period_since,
                        period_to: data.period_to,
                        date: data.date,
                        amount_reward: data.amount_reward,
                        status: data.status,
                        name: this.newDocument.file.name,
                        url: this.newDocument.file.url,
                    });
                    this.newDocument.file = null;
                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                })
            },
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('customers.detail.document', {id: this.model.id})).then(data => {
                this.documents = data.documents;
            }).finally(() => {
                Services.hideLoader();
            })
        }
    };
</script>

<style scoped>

</style>