<template>
    <layout-main>
        <div class="d-flex justify-content-start align-items-start">
            <div class="d-flex flex-column">
                <shadow-card title="Основная информация" :buttons="canEdit ? {onEdit: 'pencil-alt'} : {}"
                             @onEdit="openModal('roleAdd')">
                    <values-table :values="roleInfo" :names="roleValuesNames"/>
                </shadow-card>
                <shadow-card title="Разрешения" padding="3">
                    <button class="btn btn-success mb-2" @click="updateBlockPermissions()" v-if="canEdit">Сохранить</button>

                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Блок</th>
                            <th>Разрешение</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="blockPermission in roleBlockPermissions">
                            <td>{{ blockName(blockPermission.block_id) }}</td>
                            <td>
                                <v-select :options="permissionsOptions" v-model="blockPermission.permission_id"></v-select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </shadow-card>
            </div>
        </div>
        <role-edit-modal :source="role" :fronts="options.fronts" @onSave="updateRole"></role-edit-modal>
    </layout-main>
</template>

<script>

import Services from '../../../../scripts/services/services';
import modalMixin from '../../../mixins/modal.js';

import ShadowCard from '../../../components/shadow-card.vue';
import ValuesTable from '../../../components/values-table.vue';

import modal from '../../../components/controls/modal/modal.vue';
import VSelect from '../../../components/controls/VSelect/VSelect.vue';

import RoleEditModal from '../components/role-add-modal.vue';
import AddRoleBlockModal from '../components/add-role-block-modal.vue';

export default {
    mixins: [modalMixin],
    components: {
        ShadowCard,
        ValuesTable,
        modal,
        VSelect,
        RoleEditModal,
        AddRoleBlockModal
    },
    props: {
        iRole: {},
        iBlockPermissions: {},
        options: {
            fronts: [],
            blocks: [],
            permissions: [],
        },
    },
    data() {
        return {
            role: this.iRole,
            roleValuesNames: {
                name: 'Наименование',
                front: 'Система',
                created_at: 'Дата добавления',
                updated_at: 'Дата обновления',
            },
            roleBlockPermissions: [],
        };
    },
    methods: {
        frontName(id) {
            let front = Object.values(this.options.fronts).find(front => front.id === id);
            return front ? front.name : 'N/A';
        },
        updateRole(newData) {
            Object.assign(this.role, newData.role);
            this.closeModal();
            this.showMessageBox({text: 'Роль обновлена'});
        },
        blockName(id) {
            let block = Object.values(this.options.blocks).find(block => block.id === id);
            return block ? block.name : 'N/A';
        },
        rolePermissionIdByBlock(blockId) {
            let blockPermission = this.iBlockPermissions.find(permission => permission.block_id === blockId);

            return blockPermission ? blockPermission.permission_id : null;
        },
        updateBlockPermissions() {
            Services.showLoader();

            Services.net().put(this.getRoute('settings.updateBlockPermissions', {id: this.iRole.id}), {}, {
                blockPermissions: this.roleBlockPermissions
            })
                .then(() => {
                    this.showMessageBox({text: 'Разрешения обновлены'});
                })
                .finally(() => {
                    Services.hideLoader();
                })
        },
    },
    computed: {
        roleInfo() {
            return {
                name: this.role.name,
                front: this.frontName(this.role.front),
                created_at: this.datetimePrint(this.role.created_at),
                updated_at: this.datetimePrint(this.role.updated_at)
            };
        },
        permissionsOptions() {
            let options = Object.values(this.options.permissions)
                .map(permission => ({ value: permission.id, text: permission.name }));

            options.unshift({ value: null, text: '-' });

            return options;
        },
        canEdit() {
            return this.role.can_edit && this.canUpdate(this.blocks.settings);
        }
    },
    created() {
        this.roleBlockPermissions = Object.values(this.options.blocks).map(block => {
            return {
                block_id: block.id,
                permission_id: this.rolePermissionIdByBlock(block.id)
            }
        });
    }
};
</script>

<style scoped>
</style>
