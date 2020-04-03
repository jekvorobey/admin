<template>
<div>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="4">Документы
                <button class="btn btn-success btn-sm" @click="createNewDocument">
                    Добавить новый <fa-icon icon="plus"/>
                </button>
            </th>
            <th colspan="4">
                Экспорт XLSX:
                <a :href="getRoute('customers.detail.document.export', {id: this.model.id,},) + '?format=xlsx'" class="btn btn-success btn-sm" title="Экспорт XLSX">
                <fa-icon icon="file-excel"/>
                </a>
                Экспорт ODS:
                <a :href="getRoute('customers.detail.document.export', {id: this.model.id,},) + '?format=ods'" class="btn btn-info btn-sm" data-title="Экспорт ODS">
                    <fa-icon icon="file-download"/>
                </a>
                Экспорт CSV:
                <a :href="getRoute('customers.detail.document.export', {id: this.model.id,},) + '?format=csv'" class="btn btn-secondary btn-sm" data-title="Экспорт CSV">
                    <fa-icon icon="file-archive"/>
                </a>
            </th>
        </tr>
        <tr>
            <th>ID</th>
            <th>Период</th>
            <th>Дата документа</th>
            <th>Сумма вознаграждения</th>
            <th>Статус</th>
            <th>Файл</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(document, i) in documents">
            <td>{{ document.id }}</td>
            <td>{{ datePrint(document.period_since) }} — {{ datePrint(document.period_to) }}</td>
            <td>{{ datetimePrint(document.date) }}</td>
            <td>{{ document.amount_reward }} руб.</td>
            <td>
                <span class="badge" :class="statusClass(document.statusId)">{{ document.statusVerbal || 'N/A'}}</span></td>
            <td>
                <a :href="document.url" target="_blank">{{ document.name }}</a>
            </td>
            <td>
                <v-delete-button btn-class="btn-danger btn-sm" @delete="deleteDocument(document.id, i)"/>
                <button class="btn btn-info btn-sm" title="Отправить пользователю на Email" @click="sendEmail(document.id)">
                    <fa-icon icon="envelope"/>
                </button>
            </td>
        </tr>
        </tbody>
    </table>

    <div v-if="!documents.length">-</div>

    <document-create-modal
            @add="createDocument"
            modal-name="CreateDocumentModal"/>

</div>
</template>

<script>
    import modalMixin from '../../../../mixins/modal';
    import Modal from '../../../../components/controls/modal/modal.vue';
    import DocumentCreateModal from './modal-document-create.vue';
    import Services from '../../../../../scripts/services/services.js';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import FileInput from '../../../../components/controls/FileInput/FileInput.vue';

    export default {
        name: 'tab-document',
        components: {FileInput,
            VDeleteButton,
            DocumentCreateModal,
            Modal,
        },
        mixins: [modalMixin],
        props: ['model'],
        data() {
            return {
                documents: [],
            }
        },
        computed: {
            customer: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
        },
        methods: {
            createNewDocument(){
                this.openModal('CreateDocumentModal');
            },
            statusClass(statusId) {
                switch (statusId) {
                    case 1: return 'badge-secondary';
                    case 2: return 'badge-success';
                    case 3: return 'badge-danger';
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
            sendEmail(document_id) {
                Services.showLoader();
                Services.net().post(this.getRoute('customers.detail.document.send', {
                    id: this.customer.id,
                }),{document_id: document_id}).then(data => {
                    Services.msg(`Акт о реферальном зачислении ${document_id} отправлен на Email пользователя ${data.email}`);
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
                        statusId: data.statusId,
                        statusVerbal: data.statusVerbal,
                        name: newDocument.file.name,
                        url: newDocument.file.url,
                    });
                    this.closeModal();
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