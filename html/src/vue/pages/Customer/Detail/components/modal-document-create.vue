<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                Формирование акта пользователю
            </div>
            <div slot="body">
                <table class="table table-sm">
                    <tfoot>
                    <tr align="right">
                        <th colspan="2">
                            <button  class="btn btn-success" @click="addDocument(newDocument)">Сохранить</button>
                            <button  class="btn btn-outline-danger" @click="cancel">Отмена</button>
                        </th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <tr>
                        <th>Начало периода подсчета:</th>
                        <td><input type="date" class="form-control form-control-sm" v-model="newDocument.period_since" required/></td>
                    </tr>
                    <tr>
                        <th>Конец периода подсчета:</th>
                        <td><input type="date" class="form-control form-control-sm" v-model="newDocument.period_to" required/></td>
                    </tr>
                    <tr>
                        <th>Сумма вознаграждения</th>
                        <td>
                            <input type="number" class="form-control form-control-sm" v-model="newDocument.amount_reward" required/>
                        </td>
                    </tr>
                    <tr>
                        <th>Статус</th>
                        <td>
                            <select class="form-control form-control-sm" v-model="newDocument.status" required>
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
                                    <button class="btn btn-danger btn-sm" @click="nullifyUploaded"><fa-icon icon="trash-alt"/>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../../mixins/modal.js';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import FileInput from '../../../../components/controls/FileInput/FileInput.vue';

    export default {
        components: {
            modal,
            FileInput,
            VDeleteButton,
        },
        mixins: [modalMixin],
        props: {
            modalName: String,
        },
        data () {
            return {
                newDocument: {
                    period_since: '',
                    period_to: '',
                    amount_reward: '',
                    status: '',
                    file: null,
                },
            };
        },
        methods: {
            addDocument(newDocument) {
                this.$emit('add', this.newDocument);
            },
            clearFields() {
                this.newDocument.period_since = '';
                this.newDocument.period_to = '';
                this.newDocument.amount_reward = '';
                this.newDocument.status = '';
            },
            nullifyUploaded() {
                this.newDocument.file = null;
            },
            cancel() {
                this.clearFields();
                this.nullifyUploaded();
                this.closeModal();
            },
        },
    };
</script>