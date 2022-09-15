<template>
    <layout-main>
        <table class="table">
            <thead>
            <tr class="table-secondary" v-if="canUpdate(blocks.content)">
                <th colspan="5" style="text-align: right">
                    <button class="btn btn-success btn-bg"
                            @click="openContactEditModal(null)">
                        Добавить <fa-icon icon="plus"/>
                    </button>
                </th>
            </tr>
            <tr>
                <th>Название и описание</th>
                <th>Адрес / Телефон</th>
                <th>Иконка</th>
                <th>Тип</th>
                <th v-if="canUpdate(blocks.content)">Действия</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(contact, index) in contacts">
                <td>
                    <h5 class="d-block">{{ contact.name }}</h5>
                    <small class="d-block text-muted">{{ contact.description }}</small>
                </td>
                <td v-if="contact.address">
                    {{ contact.address }}
                </td>
                <td v-else><em>(Не указано)</em></td>
                <td v-if="contact.icon_file">
                    <img :src="media.file(contact.icon_file)"
                         style="max-width: 50px;"/>
                </td>
                <td v-else>
                    <em>Нет иконки</em>
                </td>
                <td>
                    <span class="badge"
                          :class="typeClass(contact.type)">
                        {{ contact_types[contact.type] || 'Другое' }}
                    </span>
                </td>
                <td v-if="canUpdate(blocks.content)">
                    <button class="btn btn-sm btn-success"
                            @click="openContactEditModal(contact.id)">
                        <fa-icon icon="pencil-alt"/>
                    </button>

                    <v-delete-button
                        btnClass="btn btn-sm btn-danger"
                        @delete="deleteContact(contact.id, index)"
                    />
                </td>
            </tr>
            </tbody>
        </table>

        <modal-edit-form
                @saved="saveContact"
                modal-name="EditContactModal"
                :editing-contact.sync="this.contactToEdit"
                :types="this.contact_types"/>
    </layout-main>
</template>

<script>
    import Services from "../../../../scripts/services/services";
    import modalMixin from '../../../mixins/modal';
    import Modal from '../../../components/controls/modal/modal.vue';
    import ModalEditForm from "./components/modal-edit-form.vue"
    import VDeleteButton from "../../../components/controls/VDeleteButton/VDeleteButton.vue";

    export default {
        components: {
            Modal,
            VDeleteButton,
            ModalEditForm
        },
        mixins: [modalMixin],
        props: [
            'iContacts',
            'iContactTypes',
        ],
        data() {
            return {
                contacts: this.iContacts,
                contact_types: this.iContactTypes,
                contactToEdit: null,
                newContact: {
                    id: null,
                    name: '',
                    address: '',
                    icon_file: null,
                    description: '',
                    type: '',
                },
            };
        },
        methods: {
            /**
             * Добавить новый контакт или обновить старый
             * @param contact
             */
            saveContact(contact) {
                // При обновлении старого контакта //
                if (contact.id) {
                    Services.showLoader();
                    Services.net().put(this.getRoute('contacts.edit'), contact)
                        .then(data => {
                        this.contacts = data.contacts;
                        Services.msg("Изменения сохранены");
                    }, () => {
                            Services.msg("Не удалось сохранить изменения",'danger');
                        }).finally(() => {
                        Services.hideLoader();
                    })
                }
                // При создании нового контакта //
                else {
                    Services.showLoader();
                    Services.net().post(this.getRoute('contacts.add'), contact)
                        .then(data => {
                            this.contacts = data.contacts;
                            Services.msg("Изменения сохранены");
                        }, () => {
                            Services.msg("Не удалось сохранить изменения",'danger');
                        }).finally(() => {
                        Services.hideLoader();
                    })
                }
            },
            /**
             * Удалить контакт
             * @param contactId
             * @param index
             */
            deleteContact(contactId, index) {
                Services.showLoader();
                Services.net().delete(this.getRoute('contacts.remove'), {
                    id: contactId
                })
                    .then(() => {
                        this.$delete(this.contacts, index);
                        Services.msg("Контакт успешно удален");
                    }, () => {
                        Services.msg("Не удалось удалить контакт",'danger');
                    }).finally(() => {
                    Services.hideLoader();
                })
            },
            openContactEditModal: async function(contactId) {
                this.contactToEdit = contactId ?
                    this.contacts[contactId] : this.newContact
                await this.$nextTick();
                this.openModal('EditContactModal')
            },
            typeClass(typeId) {
                switch (typeId) {
                    case 1: return 'badge-warning';
                    case 2: return 'badge-primary';
                    case 3: return 'badge-dark';
                    case 4: return 'badge-info';
                    case 5: return 'badge-secondary';
                    default: return 'badge-light';
                }
            },
        },
    }
</script>

<style scoped>

</style>