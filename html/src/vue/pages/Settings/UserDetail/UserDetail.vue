<template>
    <layout-main>
        <div class="d-flex justify-content-start align-items-start">
            <div class="d-flex flex-column">
                <shadow-card title="Основная информация" :buttons="{onEdit: 'pencil-alt'}" @onEdit="openModal('userAdd')">
                    <values-table :values="userInfo" :names="userValuesNames"/>
                </shadow-card>
                <shadow-card title="Роли пользователя" padding="3" :buttons="{onAdd: 'plus'}" @onAdd="openModal('add-user-role')">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Роль</th>
                            <th>Опции</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="role in roles">
                            <td>{{role.name}}</td>
                            <td><fa-icon @click="deleteRole(role.id)" icon="trash-alt" class="icon-btn icon-btn--red"></fa-icon></td>
                        </tr>
                        </tbody>
                    </table>
                </shadow-card>
            </div>
        </div>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('add-user-role')">
                <div slot="header">
                    Добавление роли
                </div>
                <div slot="body">
                    <v-select v-model="newRole" :options="roleOptions"></v-select>
                    <button @click="addRole" class="btn btn-dark">Сохранить</button>
                </div>
            </modal>
        </transition>
        <user-edit-modal :source="user" :fronts="options.fronts" @onSave="updateUser"></user-edit-modal>
    </layout-main>
</template>

<script>

    import Services from '../../../../scripts/services/services';
    import modalMixin from '../../../mixins/modal.js';

    import ShadowCard from '../../../components/shadow-card.vue';
    import ValuesTable from '../../../components/values-table.vue';

    import modal from '../../../components/controls/modal/modal.vue';
    import VSelect from '../../../components/controls/VSelect/VSelect.vue';

    import UserEditModal from '../components/user-add-modal.vue';

    export default {
        mixins: [modalMixin],
        components: {
            ShadowCard,
            ValuesTable,
            modal,
            VSelect,
            UserEditModal
        },
        props: {
            iUser: {},
            iRoles: {},
            options: {}
        },
        data() {
            return {
                user: this.iUser,
                roles: this.iRoles,
                userValuesNames: {
                    id: 'ID',
                    login: 'Логин',
                    front: 'Система',
                    email_verified: 'E-mail подтверждён',
                    created_at: 'Дата регистрации',
                    infinity_sip_extension: 'Infinity SIP Extension'
                },
                newRole: ''
            };
        },
        methods: {
            frontName(id) {
                let fronts = Object.values(this.options.fronts).filter(front => front.id === id);
                return fronts.length > 0 ? fronts[0].name : 'N/A';
            },
            roleName(id) {
                let rolesList = Object.values(this.options.roles).filter(role => role.id === id);
                return rolesList.length > 0 ? rolesList[0].name : 'N/A';
            },
            addRole() {
                Services.net().post(this.getRoute('user.addRole', {id: this.user.id}), {}, {
                    role: this.newRole
                })
                    .then(data => {
                        this.newRole = null;
                        this.roles = data.roles;
                        this.showMessageBox({text: 'Роль добавлена'});
                    });
            },
            deleteRole(id) {
                Services.net().post(this.getRoute('user.deleteRole', {id: this.user.id}), {}, {
                    role: id
                })
                    .then(data => {
                        this.roles = data.roles;
                    });
            },
            updateUser(newData) {
                Object.assign(this.user, newData);
                this.closeModal();
            }
        },
        computed: {
            userInfo() {
                return {
                    id: this.user.id,
                    login: this.user.login,
                    front: this.frontName(this.user.front),
                    email_verified: this.user.email_verified ? 'Да' : 'Нет',
                    infinity_sip_extension: this.user.infinity_sip_extension,
                    created_at: this.user.created_at
                };
            },
            roleOptions() {
                const usedIds = this.roles.map(role => role.id);
                return this.options.roles
                    .map(role => ({value: role.id, text: role.name}))
                    .filter(item => usedIds.indexOf(item.value) === -1 && item.value !== 1);
            }
        }
    };
</script>

<style scoped>
    .icon-btn {
        cursor: pointer;
        transition: 0.3s all;
    }
    .icon-btn--red {
        color:var(--red);
    }
    .icon-btn--red:hover {
        color:var(--pink);
    }
</style>
