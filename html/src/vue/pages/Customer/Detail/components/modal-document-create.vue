<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                Формирование акта пользователю
            </div>
            <div slot="body">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <!-- TODO: нужно переделать это представление, чтобы оно было в модалке-->
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
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../../mixins/modal.js';
    import Services from '../../../../../scripts/services/services.js';
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
                file: undefined,
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
                    this.closeModal();
                })
            }
        },
    }
</script>