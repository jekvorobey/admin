<template>
    <layout-main>
        <div class="d-flex justify-content-start align-items-start">
            <div class="d-flex flex-column">
                <shadow-card title="Основная информация" :buttons="canUpdate(blocks.settings) ? {onEdit: 'pencil-alt'} : {}" @onEdit="openModal('userAdd')">
                    <div>
                        <values-table :values="userInfo" :names="userValuesNames"/>
                    </div>
                    <div v-if="user.fronts.includes(this.userFronts.showcase)" class="mt-4">
                        <span >Профиль в разделе клиенты: <a :href="getRoute('customers.detail', { id: customerId })">{{ user.full_name }}</a></span>
                    </div>
                </shadow-card>
                <shadow-card title="Роли пользователя" padding="3" :buttons="canUpdate(blocks.settings) ? {onAdd: 'plus'} : {}" @onAdd="changeUserRoles()">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Роль</th>
                            <th v-if="canUpdate(blocks.settings)">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="role in roles">
                            <td>{{role.name}}</td>
                            <td v-if="canUpdate(blocks.settings)"><fa-icon @click="deleteRole(role.id)" icon="trash-alt" class="icon-btn icon-btn--red"></fa-icon></td>
                        </tr>
                        </tbody>
                    </table>
                </shadow-card>
            </div>
        </div>
        <transition name="modal" v-if="canUpdate(blocks.settings)">
            <modal :close="closeModal" v-if="isModalOpen('add-user-role')">
                <div slot="header">
                    Выбор ролей
                </div>
                <div slot="body">
                    <template v-for="role in roleOptions">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   :id="`role-${role.id}`"
                                   @change="e => rolesCheckbox(e, role.id)"
                                   :value="role.id"
                                   :checked="rolesIds.includes(role.id)"
                            >
                            <label class="form-check-label" :for="`role-${role.id}`">
                                {{ role.name }}
                            </label>
                        </div>
                    </template>
                    <button @click="addRole" class="btn btn-dark mt-3">Сохранить</button>
                </div>
            </modal>
        </transition>
        <user-edit-modal :source="user" :userCheckedRoles="roles" :fronts="options.fronts" :roles="options.roles"
                         :merchantId="merchantId" :merchants="options.merchants" @onSave="updateUser"></user-edit-modal>
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
            customerId: null,
            merchantId: null,
            iRoles: {},
            options: {}
        },
        data() {
            let sip = {
                id: 'ID',
                name: 'ФИО',
                login: 'Логин',
                front: 'Система',
                email_verified: 'E-mail подтверждён',
                created_at: 'Дата регистрации',
                infinity_sip_extension: 'Infinity SIP Extension',
            };

            return {
                user: this.iUser,
                roles: this.iRoles,
                userValuesNames: sip,
                rolesSelect: [],
            };
        },
        methods: {
            frontName(frontValues) {
                let fronts = Object.values(this.options.fronts).filter(front => frontValues.includes(front.id)).map(front => front.name);
                return fronts.length > 0 ? fronts.join(', ') : 'N/A';
            },
            roleName(id) {
                let rolesList = Object.values(this.options.roles).filter(role => role.id === id);
                return rolesList.length > 0 ? rolesList[0].name : 'N/A';
            },
            addRole() {
                let cross = this.rolesSelect.filter(
                    role => Object.keys(this.roles)
                        .map(roleId => parseInt(roleId))
                        .includes(role)
                );
                let rolesChange = {};
                rolesChange = this.rolesSelect.filter(role => !cross.includes(role));
                Services.net().put(this.getRoute('user.addRoles', {id: this.user.id}), {}, {
                    roles: rolesChange
                })
                    .then(data => {
                        this.roles = data.roles;
                        this.showMessageBox({text: 'Роли добавлены'});
                    });
            },
            deleteRole(id) {
                Services.net().post(this.getRoute('user.deleteRoles', {id: this.user.id}), {}, {
                    roles: [id]
                })
                    .then(data => {
                        this.roles = data.roles;
                    });
            },
            updateUser(newData) {
                Object.assign(this.user, newData);
                this.roles = Object.values(this.options.roles)
                    .filter(role => newData.roles.includes(role.id))
                    .map(role => ({id: role.id, name: role.name}));
                this.closeModal();
            },
            rolesCheckbox(e, id) {
                id = parseInt(id);
                if (e.target.checked) {
                    this.rolesSelect.push(id);
                } else {
                    this.rolesSelect = this.rolesSelect.filter((roleId) => {
                        return roleId !== id;
                    });
                }
            },
            changeUserRoles() {
                this.openModal('add-user-role');
                this.rolesSelect = Object.values(this.roles).map(role => parseInt(role.id));
            },
        },
        computed: {
            userInfo() {
                return {
                    id: this.user.id,
                    name: this.user.full_name,
                    login: this.user.login_email ? this.user.login_email : this.user.login,
                    front: this.frontName(this.user.fronts),
                    email_verified: this.user.email_verified ? 'Да' : 'Нет',
                    infinity_sip_extension: this.user.infinity_sip_extension ? this.user.infinity_sip_extension : 'N/A',
                    created_at: this.datePrint(this.user.created_at),
                };
            },
            roleOptions() {
                return Object.values(this.options.roles)
                    .filter(role => this.user.fronts.includes(role.front))
                    .map(role => ({id: role.id, name: role.name}));
            },
            rolesIds() {
                return Object.values(this.roles).map(role => parseInt(role.id));
            },
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
